<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\AffiliateEventLink;
use App\Models\Evenement;
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
        $affiliate = Auth::guard('affiliate')->user();

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
            ->with('evenement')
            ->latest()
            ->limit(5)
            ->get();

        $evenementsOuverts = Evenement::query()
            ->where('co_parrainage_actif', true)
            ->where('gratuit', false)
            ->where('statut', 'publié')
            ->where('date_event', '>=', now())
            ->whereNotIn('id', $affiliate->eventLinks()->pluck('evenement_id'))
            ->with('user')
            ->orderBy('date_event')
            ->limit(5)
            ->get();

        return view('affiliate.dashboard', compact('affiliate', 'stats', 'commissions', 'liens', 'evenementsOuverts'));
    }

    // Mecanisme A : organisateurs parraines
    public function filleuls()
    {
        $affiliate = Auth::guard('affiliate')->user();

        $referrals = $affiliate->referrals()
            ->with(['organisateur', 'premierEvenement'])
            ->latest()
            ->get();

        $stats = [
            'total' => $referrals->count(),
            'valides' => $referrals->where('statut', 'valide')->count(),
            'commissions' => $affiliate->commissions()
                ->where('type', AffiliateCommission::TYPE_PARRAINAGE)->sum('montant'),
        ];

        $lienParrainage = route('inscriptions.organisateur').'?ref='.$affiliate->code_parrainage;

        return view('affiliate.filleuls', compact('affiliate', 'referrals', 'stats', 'lienParrainage'));
    }

    // Mecanisme B : liens de co-parrainage
    public function liens(Request $request)
    {
        $affiliate = Auth::guard('affiliate')->user();

        $statut = in_array($request->query('statut'), ['actif', 'inactif'], true) ? $request->query('statut') : null;
        $recherche = trim((string) $request->query('q'));

        $liensQuery = $affiliate->eventLinks()->with('evenement');
        if ($statut !== null) {
            $liensQuery->where('actif', $statut === 'actif');
        }
        if ($recherche !== '') {
            $liensQuery->whereHas('evenement', fn ($q) => $q->where('titre', 'like', "%{$recherche}%"));
        }

        $liens = $liensQuery->latest()->paginate(15)->withQueryString();

        $evenementsOuvertsQuery = Evenement::query()
            ->where('co_parrainage_actif', true)
            ->where('gratuit', false)
            ->where('statut', 'publié')
            ->where('date_event', '>=', now())
            ->whereNotIn('id', $affiliate->eventLinks()->pluck('evenement_id'))
            ->with('user');
        if ($recherche !== '') {
            $evenementsOuvertsQuery->where('titre', 'like', "%{$recherche}%");
        }

        $evenementsOuverts = $evenementsOuvertsQuery->orderBy('date_event')->limit(30)->get();

        $stats = [
            'liens' => $affiliate->eventLinks()->count(),
            'liens_actifs' => $affiliate->eventLinks()->where('actif', true)->count(),
            'clics' => $affiliate->eventLinks()->sum('clics'),
            'evenements_ouverts' => Evenement::query()
                ->where('co_parrainage_actif', true)
                ->where('gratuit', false)
                ->where('statut', 'publié')
                ->where('date_event', '>=', now())
                ->count(),
        ];

        return view('affiliate.liens', compact('affiliate', 'liens', 'evenementsOuverts', 'stats', 'statut', 'recherche'));
    }

    // Genere (ou retrouve) le lien de co-parrainage d'un evenement
    public function genererLien(Evenement $evenement)
    {
        $affiliate = Auth::guard('affiliate')->user();

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
        $affiliate = Auth::guard('affiliate')->user();

        if ($link->affiliate_id !== $affiliate->id) {
            abort(403);
        }

        $link->update(['actif' => !$link->actif]);

        return back()->with('success', $link->actif
            ? 'Lien réactivé.'
            : 'Lien désactivé.');
    }

    // Mecanisme A + B : historique complet des commissions
    public function commissions(Request $request)
    {
        $affiliate = Auth::guard('affiliate')->user();

        $query = $affiliate->commissions()->with(['evenement', 'ticket']);

        if (in_array($request->query('type'), ['parrainage_direct', 'co_parrainage'], true)) {
            $query->where('type', $request->query('type'));
        }
        if (in_array($request->query('statut'), ['en_attente', 'disponible', 'paye', 'annule'], true)) {
            $query->where('statut', $request->query('statut'));
        }

        $commissions = $query->latest()->paginate(20)->withQueryString();

        $totaux = [
            'total' => $affiliate->commissions()->sum('montant'),
            'en_attente' => $affiliate->commissions()->where('statut', 'en_attente')->sum('montant'),
            'disponible' => $affiliate->commissions()->where('statut', 'disponible')->sum('montant'),
            'paye' => $affiliate->commissions()->where('statut', 'paye')->sum('montant'),
        ];

        return view('affiliate.commissions', compact('affiliate', 'commissions', 'totaux'));
    }

    // Demandes de reversement
    public function paiements()
    {
        $affiliate = Auth::guard('affiliate')->user();

        $payouts = $affiliate->payouts()->latest()->get();

        return view('affiliate.paiements', compact('affiliate', 'payouts'));
    }

    // Cree une demande de reversement
    public function demanderPaiement(Request $request)
    {
        $affiliate = Auth::guard('affiliate')->user();

        $data = $request->validate([
            'montant' => 'required|numeric|min:5000',
        ], [
            'montant.required' => 'Indiquez le montant à retirer.',
            'montant.min' => 'Le montant minimum de retrait est de 5 000 FCFA.',
        ]);

        if (!$affiliate->operateur_paiement || !$affiliate->numero_paiement) {
            return back()->with('error', 'Renseignez d\'abord vos coordonnées de paiement dans votre profil.');
        }

        if ($data['montant'] > (float) $affiliate->solde_disponible) {
            return back()->with('error', 'Le montant demandé dépasse votre solde disponible.');
        }

        $affiliate->payouts()->create([
            'montant' => $data['montant'],
            'operateur' => $affiliate->operateur_paiement,
            'numero_paiement' => $affiliate->numero_paiement,
            'titulaire' => $affiliate->titulaire_paiement,
            'statut' => 'demande',
        ]);

        $affiliate->decrement('solde_disponible', $data['montant']);

        return back()->with('success', 'Votre demande de retrait a été enregistrée.');
    }

    // Profil
    public function profil()
    {
        $affiliate = Auth::guard('affiliate')->user();

        return view('affiliate.profil', compact('affiliate'));
    }

    // Met a jour les informations du compte
    public function majProfil(Request $request)
    {
        $affiliate = Auth::guard('affiliate')->user();

        $data = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('affiliates', 'email')->ignore($affiliate->id)],
            'telephone' => 'nullable|string|max:30',
            'operateur_paiement' => 'nullable|string|max:50',
            'numero_paiement' => 'nullable|string|max:50',
            'titulaire_paiement' => 'nullable|string|max:100',
        ]);

        $affiliate->update($data);

        return back()->with('success', 'Vos informations ont été mises à jour.');
    }

    // Change le mot de passe
    public function majMotDePasse(Request $request)
    {
        $affiliate = Auth::guard('affiliate')->user();

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

    // Genere un code unique pour un lien de co-parrainage
    private function genererCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (AffiliateEventLink::where('code', $code)->exists());

        return $code;
    }
}
