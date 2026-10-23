<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliatePayout;
use App\Models\AffiliateReferral;
use App\Services\AffiliationService;
use App\Support\PerPage;
use Illuminate\Http\Request;

/**
 * Back-office plateforme du programme d'affiliation : comptes affiliés,
 * commissions et demandes de reversement.
 */
class AffiliationController extends Controller
{
    public function __construct(private AffiliationService $affiliation)
    {
    }

    // Liste des affiliés
    public function index(Request $request)
    {
        $recherche = trim((string) $request->query('q'));
        $statut = $request->query('statut');

        $query = Affiliate::query()->withCount(['referrals', 'eventLinks', 'commissions', 'payouts']);

        if ($recherche !== '') {
            $query->where(function ($q) use ($recherche) {
                $q->where('nom', 'like', "%{$recherche}%")
                    ->orWhere('prenom', 'like', "%{$recherche}%")
                    ->orWhere('email', 'like', "%{$recherche}%")
                    ->orWhere('code_parrainage', 'like', "%{$recherche}%")
                    ->orWhere('telephone', 'like', "%{$recherche}%");
            });
        }

        if (in_array($statut, ['actif', 'inactif'], true)) {
            $query->where('actif', $statut === 'actif');
        }

        $affilies = $query->latest()->paginate(PerPage::resolve())->withQueryString();

        $stats = [
            'total' => Affiliate::count(),
            'actifs' => Affiliate::where('actif', true)->count(),
            'total_gagne' => (float) AffiliateCommission::where('statut', '!=', 'annule')->sum('montant'),
            'en_attente' => (float) AffiliateCommission::where('statut', 'en_attente')->sum('montant'),
            'disponible' => (float) AffiliateCommission::where('statut', 'disponible')->whereNull('affiliate_payout_id')->sum('montant'),
            'paye' => (float) AffiliateCommission::where('statut', 'paye')->sum('montant'),
            'reversements_demandes' => AffiliatePayout::whereIn('statut', ['demande', 'en_cours'])->count(),
        ];

        return view('superadmin.affiliations.index', compact('affilies', 'stats', 'recherche', 'statut'));
    }

    // Fiche détaillée d'un affilié
    public function show(Affiliate $affiliate)
    {
        $affiliate->loadCount(['referrals', 'eventLinks', 'commissions', 'payouts']);

        $referrals = $affiliate->referrals()
            ->with(['organisateur', 'premierEvenement'])
            ->latest()
            ->get();

        $liens = $affiliate->eventLinks()
            ->with('evenement')
            ->latest()
            ->limit(20)
            ->get();

        $commissions = $affiliate->commissions()
            ->with(['evenement', 'ticket', 'eventLink'])
            ->latest()
            ->paginate(15, ['*'], 'commissions_page');

        $payouts = $affiliate->payouts()
            ->withCount('commissions')
            ->latest()
            ->get();

        return view('superadmin.affiliations.show', compact('affiliate', 'referrals', 'liens', 'commissions', 'payouts'));
    }

    // Active / désactive un compte affilié
    public function basculerStatut(Affiliate $affiliate)
    {
        $affiliate->update(['actif' => ! $affiliate->actif]);

        return back()->with('success', $affiliate->actif
            ? 'Compte affilié réactivé.'
            : 'Compte affilié désactivé.');
    }

    // Toutes les commissions
    public function commissions(Request $request)
    {
        $type = in_array($request->query('type'), [AffiliateCommission::TYPE_PARRAINAGE, AffiliateCommission::TYPE_CO_PARRAINAGE], true)
            ? $request->query('type')
            : null;
        $statut = in_array($request->query('statut'), ['en_attente', 'disponible', 'paye', 'annule'], true)
            ? $request->query('statut')
            : null;
        $recherche = trim((string) $request->query('q'));

        $query = AffiliateCommission::query()->with(['affiliate', 'evenement', 'ticket']);

        if ($type) {
            $query->where('type', $type);
        }
        if ($statut) {
            $query->where('statut', $statut);
        }
        if ($recherche !== '') {
            $query->where(function ($q) use ($recherche) {
                $q->whereHas('affiliate', function ($a) use ($recherche) {
                    $a->where('nom', 'like', "%{$recherche}%")
                        ->orWhere('prenom', 'like', "%{$recherche}%")
                        ->orWhere('email', 'like', "%{$recherche}%");
                })->orWhereHas('evenement', fn ($e) => $e->where('titre', 'like', "%{$recherche}%"));
            });
        }

        $commissions = $query->latest()->paginate(PerPage::resolve())->withQueryString();

        $stats = [
            'total' => (float) AffiliateCommission::where('statut', '!=', 'annule')->sum('montant'),
            'en_attente' => (float) AffiliateCommission::where('statut', 'en_attente')->sum('montant'),
            'disponible' => (float) AffiliateCommission::where('statut', 'disponible')->sum('montant'),
            'paye' => (float) AffiliateCommission::where('statut', 'paye')->sum('montant'),
        ];

        return view('superadmin.affiliations.commissions', compact('commissions', 'stats', 'type', 'statut', 'recherche'));
    }

    // Annule une commission (litige)
    public function annulerCommission(AffiliateCommission $commission)
    {
        if ($commission->statut === 'paye') {
            return back()->with('error', 'Cette commission a déjà été payée : elle ne peut plus être annulée.');
        }

        if ($commission->affiliate_payout_id) {
            return back()->with('error', 'Cette commission est réservée par une demande de reversement en cours.');
        }

        $commission->update(['statut' => 'annule']);

        if ($affiliate = $commission->affiliate) {
            $this->affiliation->recalculerSoldes($affiliate);
        }

        return back()->with('success', 'Commission annulée.');
    }

    // Demandes de reversement
    public function reversements(Request $request)
    {
        $statut = in_array($request->query('statut'), ['demande', 'en_cours', 'paye', 'refuse'], true)
            ? $request->query('statut')
            : null;
        $recherche = trim((string) $request->query('q'));

        $query = AffiliatePayout::query()->with('affiliate')->withCount('commissions');

        if ($statut) {
            $query->where('statut', $statut);
        }
        if ($recherche !== '') {
            $query->where(function ($q) use ($recherche) {
                $q->whereHas('affiliate', function ($a) use ($recherche) {
                    $a->where('nom', 'like', "%{$recherche}%")
                        ->orWhere('prenom', 'like', "%{$recherche}%")
                        ->orWhere('email', 'like', "%{$recherche}%");
                })->orWhere('numero_paiement', 'like', "%{$recherche}%");
            });
        }

        $reversements = $query->latest()->paginate(PerPage::resolve())->withQueryString();

        $stats = [
            'demande' => AffiliatePayout::where('statut', 'demande')->count(),
            'en_cours' => AffiliatePayout::where('statut', 'en_cours')->count(),
            'paye' => (float) AffiliatePayout::where('statut', 'paye')->sum('montant'),
            'total' => AffiliatePayout::where('statut', 'paye')->count(),
        ];

        return view('superadmin.affiliations.reversements', compact('reversements', 'stats', 'statut', 'recherche'));
    }

    public function approuverReversement(AffiliatePayout $payout)
    {
        if (! $this->affiliation->approuverReversement($payout)) {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        return back()->with('success', 'Demande approuvée. En attente du transfert.');
    }

    public function confirmerReversement(AffiliatePayout $payout)
    {
        if (! $this->affiliation->confirmerReversement($payout)) {
            return back()->with('error', 'Cette demande n\'est pas en cours de traitement.');
        }

        return back()->with('success', 'Paiement confirmé. Les commissions ont été marquées comme payées.');
    }

    public function rejeterReversement(Request $request, AffiliatePayout $payout)
    {
        $note = trim((string) $request->input('note')) ?: null;

        if (! $this->affiliation->rejeterReversement($payout, $note)) {
            return back()->with('error', 'Cette demande ne peut plus être rejetée.');
        }

        return back()->with('success', 'Demande rejetée. Les commissions sont de nouveau disponibles.');
    }
}
