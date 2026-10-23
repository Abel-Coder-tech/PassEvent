<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\AffiliateEventLink;
use App\Models\Evenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Vue organisateur du programme d'affiliation : affiliés qui promeuvent ses
 * événements (co-parrainage) et commissions qui lui sont dues.
 */
class AffiliationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $evenements = Evenement::where('user_id', $user->id)
            ->orderByDesc('date_event')
            ->get();

        $evenementIds = $evenements->pluck('id');

        $liens = AffiliateEventLink::whereIn('evenement_id', $evenementIds)
            ->with(['affiliate', 'evenement'])
            ->latest()
            ->get();

        $commissions = AffiliateCommission::whereIn('evenement_id', $evenementIds)
            ->where('type', AffiliateCommission::TYPE_CO_PARRAINAGE)
            ->with(['affiliate', 'evenement', 'ticket'])
            ->latest()
            ->limit(50)
            ->get();

        $stats = [
            'affilies' => $liens->pluck('affiliate_id')->unique()->count(),
            'liens' => $liens->count(),
            'liens_actifs' => $liens->where('actif', true)->count(),
            'clics' => (int) $liens->sum('clics'),
            'commissions' => (float) AffiliateCommission::whereIn('evenement_id', $evenementIds)
                ->where('type', AffiliateCommission::TYPE_CO_PARRAINAGE)
                ->where('statut', '!=', 'annule')
                ->sum('montant'),
            'commissions_en_attente' => (float) AffiliateCommission::whereIn('evenement_id', $evenementIds)
                ->where('type', AffiliateCommission::TYPE_CO_PARRAINAGE)
                ->where('statut', 'en_attente')
                ->sum('montant'),
            'commissions_payees' => (float) AffiliateCommission::whereIn('evenement_id', $evenementIds)
                ->where('type', AffiliateCommission::TYPE_CO_PARRAINAGE)
                ->where('statut', 'paye')
                ->sum('montant'),
        ];

        // Pour chaque événement ouvert au co-parrainage : nombre de liens et commission due.
        $parEvenement = $evenements
            ->where('co_parrainage_actif', true)
            ->map(function (Evenement $evenement) {
                $base = AffiliateCommission::where('evenement_id', $evenement->id)
                    ->where('type', AffiliateCommission::TYPE_CO_PARRAINAGE)
                    ->where('statut', '!=', 'annule');

                return [
                    'evenement' => $evenement,
                    'liens' => $evenement->affiliateEventLinks()->count(),
                    'commissions' => (float) $base->sum('montant'),
                    'en_attente' => (float) AffiliateCommission::where('evenement_id', $evenement->id)
                        ->where('type', AffiliateCommission::TYPE_CO_PARRAINAGE)
                        ->where('statut', 'en_attente')
                        ->sum('montant'),
                ];
            });

        return view('admin.affiliations.index', compact('evenements', 'parEvenement', 'liens', 'commissions', 'stats'));
    }
}
