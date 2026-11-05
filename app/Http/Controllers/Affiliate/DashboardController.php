<?php

namespace App\Http\Controllers\Affiliate;

use App\Enums\AffiliateCommissionStatus;
use App\Http\Controllers\Controller;
use App\Models\Affiliate;
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
    public function index(Request $request, AffiliateStatisticsService $statistics)
    {
        $affiliate = $this->affiliateConnecte();

        $linkStats = $statistics->links($affiliate);
        $commStats = $affiliate->commissions()
            ->where('statut', '!=', AffiliateCommissionStatus::Cancelled->value)
            ->selectRaw('COUNT(*) as ventes')
            ->selectRaw('COALESCE(SUM(montant), 0) as total_gagne')
            ->first();

        $stats = [
            'liens' => $linkStats['liens'],
            'liens_actifs' => $linkStats['liens_actifs'],
            'clics' => $linkStats['clics'],
            'ventes' => (int) ($commStats->ventes ?? 0),
            'total_gagne' => (float) ($commStats->total_gagne ?? 0),
            'evenements_promus' => (int) $affiliate->eventLinks()->where('actif', true)->distinct('evenement_id')->count('evenement_id'),
        ];

        $periode = (int) $request->query('periode', 30);
        if (!in_array($periode, [7, 30, 90], true)) {
            $periode = 30;
        }
        $days = $periode;
        $start = now()->subDays($days - 1)->startOfDay();
        $rows = $affiliate->commissions()
            ->where('statut', '!=', AffiliateCommissionStatus::Cancelled->value)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d')
            ->selectRaw('COUNT(*) as ventes')
            ->selectRaw('COALESCE(SUM(montant), 0) as montant')
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $chartLabels = [];
        $chartVentes = [];
        $chartMontants = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->toDateString();
            $row = $rows->get($key);
            $chartLabels[] = $date->format('d/m');
            $chartVentes[] = (int) ($row->ventes ?? 0);
            $chartMontants[] = (float) ($row->montant ?? 0);
        }
        $chart = [
            'labels' => $chartLabels,
            'ventes' => $chartVentes,
            'montants' => $chartMontants,
        ];

        $commissions = $affiliate->commissions()
            ->with(['evenement', 'ticket'])
            ->where('statut', '!=', AffiliateCommissionStatus::Cancelled->value)
            ->latest()
            ->limit(5)
            ->get();

        $liens = $affiliate->eventLinks()
            ->with(['evenement.user', 'evenement.tarifs'])
            ->withCount(['commissions as ventes_count' => function ($q) {
                $q->where('statut', '!=', AffiliateCommissionStatus::Cancelled->value);
            }])
            ->withSum(['commissions as commissions_sum_montant' => function ($q) {
                $q->where('statut', '!=', AffiliateCommissionStatus::Cancelled->value);
            }], 'montant')
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

        return view('affiliate.dashboard', compact('affiliate', 'stats', 'commissions', 'liens', 'evenementsOuverts', 'chart', 'periode'));
    }

    public function liens(Request $request, AffiliateStatisticsService $statistics)
    {
        $affiliate = $this->affiliateConnecte();

        $statut = in_array($request->query('statut'), ['actif', 'inactif'], true) ? $request->query('statut') : null;
        $recherche = trim((string) $request->query('q'));

        $liensQuery = $affiliate->eventLinks()->with(['evenement.user', 'evenement.tarifs'])
            ->withCount(['commissions as ventes_count' => function ($q) {
                $q->where('statut', '!=', AffiliateCommissionStatus::Cancelled->value);
            }])
            ->withSum(['commissions as commissions_sum_montant' => function ($q) {
                $q->where('statut', '!=', AffiliateCommissionStatus::Cancelled->value);
            }], 'montant');
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
        $ventesStats = $affiliate->commissions()
            ->where('statut', '!=', AffiliateCommissionStatus::Cancelled->value)
            ->selectRaw('COUNT(*) as ventes')
            ->selectRaw('COALESCE(SUM(montant), 0) as total')
            ->first();
        $stats['ventes'] = (int) ($ventesStats->ventes ?? 0);
        $stats['commissions_total'] = (float) ($ventesStats->total ?? 0);
        $stats['evenements_ouverts'] = Evenement::query()
                ->withUpcomingDate()
                ->where('co_parrainage_actif', true)
                ->where('gratuit', false)
                ->where('statut', 'publié')
                ->count();

        return view('affiliate.liens', compact('affiliate', 'liens', 'evenementsOuverts', 'stats', 'statut', 'recherche'));
    }

    public function evenement(Evenement $evenement, AffiliateStatisticsService $statistics)
    {
        $affiliate = $this->affiliateConnecte();

        $lien = $affiliate->eventLinks()
            ->where('evenement_id', $evenement->id)
            ->first();

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

        $lienAchat = $lien
            ? route('evenements.affilie.redirect', ['code' => $lien->code])
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

    public function paiements()
    {
        $affiliate = $this->affiliateConnecte();

        $payouts = $affiliate->payouts()->latest()->get();

        return view('affiliate.paiements', compact('affiliate', 'payouts'));
    }

    public function demanderPaiement(Request $request, AffiliationService $affiliation)
    {
        $affiliate = $this->affiliateConnecte();

        $data = $request->validate([
            'montant' => 'required|numeric|min:1000',
        ], [
            'montant.required' => 'Indiquez le montant à retirer.',
            'montant.min' => 'Le montant minimum de retrait est de 1 000 FCFA.',
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

    public function profil()
    {
        $affiliate = $this->affiliateConnecte();

        return view('affiliate.profil', compact('affiliate'));
    }

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

    private function genererCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (AffiliateEventLink::where('code', $code)->exists());

        return $code;
    }
}
