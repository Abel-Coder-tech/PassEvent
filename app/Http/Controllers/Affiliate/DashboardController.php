<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliateEventLink;
use App\Models\Evenement;
use App\Services\AffiliationService;
use App\Services\AffiliateStatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    // Tableau de bord
    public function index()
    {
        $affiliate = $this->affiliateConnecte();

        $stats = [
            'solde_disponible' => $affiliate->solde_disponible,
            'solde_en_attente' => $affiliate->solde_en_attente,
            'total_gagne' => $affiliate->total_gagne,
            'filleuls' => $affiliate->referrals()->count(),
            'liens' => $affiliate->eventLinks()->count(),
            'commissions' => $affiliate->commissions()->count(),
        ];

        $commissions = $affiliate->commissions()
            ->with(['evenement', 'ticket'])
            ->latest()
            ->limit(10)
            ->get();

        $liens = $affiliate->eventLinks()
            ->with(['evenement.user', 'evenement.tarifs'])
            ->latest()
            ->limit(5)
            ->get();

        $evenementsOuverts = Evenement::query()
            ->withUpcomingDate()
            ->where('co_parrainage_actif', true)
            ->where('gratuit', false)
            ->where('statut', 'publié')
            ->whereNotIn('id', $affiliate->eventLinks()->pluck('evenement_id'))
            ->with(['user', 'tarifs'])
            ->orderBy('date_event')
            ->limit(6)
            ->get();

        return view('affiliate.dashboard', compact('affiliate', 'stats', 'commissions', 'liens', 'evenementsOuverts'));
    }

    // Historique des organisateurs parrainés avant l'arrêt du parrainage direct.
    public function filleuls()
    {
        $affiliate = $this->affiliateConnecte();

        $referrals = $affiliate->referrals()
            ->with(['organisateur', 'premierEvenement'])
            ->latest()
            ->get();

        $stats = [
            'total' => $referrals->count(),
            'valides' => $referrals->where('statut', 'valide')->count(),
            'clics' => (int) $affiliate->clics_parrainage,
            'commissions' => $affiliate->commissions()
                ->where('type', AffiliateCommission::TYPE_PARRAINAGE)->sum('montant'),
        ];

        return view('affiliate.filleuls', compact('affiliate', 'referrals', 'stats'));
    }

    // Mecanisme B : liens de co-parrainage
    public function liens(Request $request, AffiliateStatisticsService $statistics)
    {
        $affiliate = $this->affiliateConnecte();

        $statut = in_array($request->query('statut'), ['actif', 'inactif'], true) ? $request->query('statut') : null;
        $recherche = trim((string) $request->query('q'));

        $liensQuery = $affiliate->eventLinks()->with(['evenement.user', 'evenement.tarifs']);
        if ($statut !== null) {
            $liensQuery->where('actif', $statut === 'actif');
        }
        if ($recherche !== '') {
            $liensQuery->whereHas('evenement', fn ($q) => $q->where('titre', 'like', "%{$recherche}%"));
        }

        $liens = $liensQuery->latest()->paginate(15)->withQueryString();

        $evenementsOuvertsQuery = Evenement::query()
            ->withUpcomingDate()
            ->where('co_parrainage_actif', true)
            ->where('gratuit', false)
            ->where('statut', 'publié')
            ->whereNotIn('id', $affiliate->eventLinks()->pluck('evenement_id'))
            ->with(['user', 'tarifs']);
        if ($recherche !== '') {
            $evenementsOuvertsQuery->where('titre', 'like', "%{$recherche}%");
        }

        $evenementsOuverts = $evenementsOuvertsQuery->orderBy('date_event')->limit(30)->get();

        $stats = $statistics->links($affiliate);
        $stats['evenements_ouverts'] = Evenement::query()
                ->withUpcomingDate()
                ->where('co_parrainage_actif', true)
                ->where('gratuit', false)
                ->where('statut', 'publié')
                ->count();

        return view('affiliate.liens', compact('affiliate', 'liens', 'evenementsOuverts', 'stats', 'statut', 'recherche'));
    }

    // Detail interne d'un evenement co-parraine (sans redirection vers la page publique)
    public function evenement(Evenement $evenement, AffiliateStatisticsService $statistics)
    {
        $affiliate = $this->affiliateConnecte();

        $lien = $affiliate->eventLinks()
            ->where('evenement_id', $evenement->id)
            ->first();

        // Un evenement est consultable s'il est ouvert au co-parrainage ou si
        // l'affilie possede deja un lien dessus (meme si l'evenement est passe).
        $accessible = $lien !== null
            || ($evenement->co_parrainage_actif && !$evenement->gratuit && $evenement->statut === 'publié');

        if (!$accessible) {
            abort(404);
        }

        $evenement->load([
            'user',
            'dates',
            'tarifs' => fn ($q) => $q->where('statut', 'actif')->orderBy('prix'),
        ]);

        $commissionPax = $evenement->commissionEffective();
        $commissionAffilie = (float) ($lien?->pourcentage ?? $evenement->co_parrainage_pourcentage ?? 0);

        // Note detaillee PaxEvent : pour chaque tarif, on decompose le prix du
        // billet entre commission PaxEvent, commission de co-parrainage et net
        // organiseur. La commission de co-parrainage est a la charge de
        // l'organisateur et s'ajoute a la commission PaxEvent.
        $noteLignes = $evenement->tarifs->map(function ($tarif) use ($commissionPax, $commissionAffilie) {
            $prix = (float) $tarif->prix;
            $partPax = round($prix * $commissionPax / 100, 2);
            $partAffilie = round($prix * $commissionAffilie / 100, 2);

            return [
                'nom' => $tarif->nom,
                'prix' => $prix,
                'part_pax' => $partPax,
                'part_affilie' => $partAffilie,
                'part_organisateur' => round($prix - $partPax - $partAffilie, 2),
            ];
        });

        $statsCommissions = $statistics->commissions($affiliate, $evenement->id, true);

        $organisateur = $evenement->user;
        $nbEvenementsOrganisateur = $organisateur
            ? $organisateur->evenements()->where('statut', 'publié')->count()
            : 0;

        // Seul le co-parrainage (mecanisme B) est conservé.
        $lienAchat = $lien
            ? route('evenements.public.show', $evenement).'?ref='.$lien->code
            : null;
        return view('affiliate.evenements.show', compact(
            'affiliate',
            'evenement',
            'lien',
            'commissionPax',
            'commissionAffilie',
            'noteLignes',
            'statsCommissions',
            'organisateur',
            'nbEvenementsOrganisateur',
            'lienAchat'
        ));
    }

    // Genere (ou retrouve) le lien de co-parrainage d'un evenement
    public function genererLien(Evenement $evenement)
    {
        $affiliate = $this->affiliateConnecte();

        if (!$evenement->co_parrainage_actif || $evenement->gratuit || $evenement->statut !== 'publié') {
            return back()->with('error', 'Cet événement n\'est pas ouvert au co-parrainage.');
        }

        $lien = $affiliate->eventLinks()->firstOrCreate(
            ['evenement_id' => $evenement->id],
            [
                'code' => $this->genererCode(),
                'pourcentage' => $evenement->co_parrainage_pourcentage ?? 0,
                'actif' => true,
            ]
        );

        $message = $lien->wasRecentlyCreated
            ? 'Votre lien de co-parrainage a été généré.'
            : 'Vous avez déjà un lien pour cet événement.';

        return back()->with('success', $message);
    }

    // Active / desactive un lien de co-parrainage
    public function toggleLien(AffiliateEventLink $link)
    {
        $affiliate = $this->affiliateConnecte();

        if ($link->affiliate_id !== $affiliate->id) {
            abort(403);
        }

        $link->update(['actif' => !$link->actif]);

        return back()->with('success', $link->actif
            ? 'Lien réactivé.'
            : 'Lien désactivé.');
    }

    public function supprimerLien(AffiliateEventLink $link)
    {
        $affiliate = $this->affiliateConnecte();

        if ($link->affiliate_id !== $affiliate->id) {
            abort(403);
        }

        $link->delete();

        return redirect()->route('affiliate.liens')
            ->with('success', 'L’événement a été retiré de vos liens. Vos commissions déjà générées restent conservées.');
    }

    // Mecanisme A + B : historique complet des commissions
    public function commissions(Request $request, AffiliateStatisticsService $statistics)
    {
        $affiliate = $this->affiliateConnecte();

        $query = $affiliate->commissions()->with(['evenement', 'ticket']);

        if (in_array($request->query('type'), ['co_parrainage'], true)) {
            $query->where('type', $request->query('type'));
        }
        if (in_array($request->query('statut'), ['en_attente', 'disponible', 'paye', 'annule'], true)) {
            $query->where('statut', $request->query('statut'));
        }

        $commissions = $query->latest()->paginate(20)->withQueryString();

        $totaux = $statistics->commissions($affiliate);

        return view('affiliate.commissions', compact('affiliate', 'commissions', 'totaux'));
    }

    // Demandes de reversement
    public function paiements()
    {
        $affiliate = $this->affiliateConnecte();

        $payouts = $affiliate->payouts()->latest()->get();

        return view('affiliate.paiements', compact('affiliate', 'payouts'));
    }

    // Cree une demande de reversement
    public function demanderPaiement(Request $request, AffiliationService $affiliation)
    {
        $affiliate = $this->affiliateConnecte();

        $data = $request->validate([
            'montant' => 'required|numeric|min:5000',
        ], [
            'montant.required' => 'Indiquez le montant à retirer.',
            'montant.min' => 'Le montant minimum de retrait est de 5 000 FCFA.',
        ]);

        if (!$affiliate->operateur_paiement || !$affiliate->numero_paiement) {
            return back()->with('error', 'Renseignez d\'abord vos coordonnées de paiement dans votre profil.');
        }

        try {
            $payout = $affiliation->creerDemandeReversement($affiliate, (float) $data['montant']);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = 'Votre demande de retrait a été enregistrée.';
        if ((float) $payout->montant > (float) $data['montant']) {
            $message .= ' Le montant a été arrondi à '.number_format((float) $payout->montant, 0, ',', ' ')
                .' FCFA pour couvrir vos commissions disponibles.';
        }

        return back()->with('success', $message);
    }

    // Profil
    public function profil()
    {
        $affiliate = $this->affiliateConnecte();

        return view('affiliate.profil', compact('affiliate'));
    }

    // Met a jour les informations du compte
    public function majProfil(Request $request)
    {
        $affiliate = $this->affiliateConnecte();

        $data = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('affiliates', 'email')->ignore($affiliate->id)],
            'telephone' => 'nullable|string|max:30',
            'operateur_paiement' => ['nullable', Rule::in(['MTN MoMo', 'Moov Money', 'Celtiis Cash'])],
            'numero_paiement' => 'nullable|string|max:50',
            'titulaire_paiement' => 'nullable|string|max:100',
        ]);

        $affiliate->update($data);

        return back()->with('success', 'Vos informations ont été mises à jour.');
    }

    // Change le mot de passe
    public function majMotDePasse(Request $request)
    {
        $affiliate = $this->affiliateConnecte();

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        if (!Hash::check($data['current_password'], $affiliate->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }

        $affiliate->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Votre mot de passe a été modifié.');
    }

    private function affiliateConnecte(): Affiliate
    {
        $affiliate = Auth::guard('affiliate')->user();

        abort_unless($affiliate instanceof Affiliate, 403);

        return $affiliate;
    }

    // Genere un code unique pour un lien de co-parrainage
    private function genererCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (AffiliateEventLink::where('code', $code)->exists());

        return $code;
    }
}
