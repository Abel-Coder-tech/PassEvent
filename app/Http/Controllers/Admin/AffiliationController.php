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

        $ventesParLien = AffiliateCommission::whereIn('evenement_id', $evenementIds)
            ->where('type', AffiliateCommission::TYPE_CO_PARRAINAGE)
            ->where('statut', '!=', 'annule')
            ->whereNotNull('affiliate_event_link_id')
            ->selectRaw('affiliate_event_link_id, COUNT(DISTINCT ticket_id) as ventes')
            ->groupBy('affiliate_event_link_id')
            ->pluck('ventes', 'affiliate_event_link_id');

        $ventesParEvenement = AffiliateCommission::whereIn('evenement_id', $evenementIds)
            ->where('type', AffiliateCommission::TYPE_CO_PARRAINAGE)
            ->where('statut', '!=', 'annule')
            ->selectRaw('evenement_id, COUNT(DISTINCT ticket_id) as ventes')
            ->groupBy('evenement_id')
            ->pluck('ventes', 'evenement_id');

        $stats = [
            'affilies' => $liens->pluck('affiliate_id')->unique()->count(),
            'liens' => $liens->count(),
            'liens_actifs' => $liens->where('actif', true)->count(),
            'clics' => (int) $liens->sum('clics'),
            'ventes' => (int) AffiliateCommission::whereIn('evenement_id', $evenementIds)
                ->where('type', AffiliateCommission::TYPE_CO_PARRAINAGE)
                ->where('statut', '!=', 'annule')
                ->whereNotNull('ticket_id')
                ->distinct('ticket_id')
                ->count('ticket_id'),
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
            ->map(function (Evenement $evenement) use ($ventesParEvenement) {
                $base = AffiliateCommission::where('evenement_id', $evenement->id)
                    ->where('type', AffiliateCommission::TYPE_CO_PARRAINAGE)
                    ->where('statut', '!=', 'annule');

                return [
                    'evenement' => $evenement,
                    'liens' => $evenement->affiliateEventLinks()->count(),
                    'clics' => (int) $evenement->affiliateEventLinks()->sum('clics'),
                    'ventes' => (int) ($ventesParEvenement[$evenement->id] ?? 0),
                    'commissions' => (float) $base->sum('montant'),
                    'en_attente' => (float) AffiliateCommission::where('evenement_id', $evenement->id)
                        ->where('type', AffiliateCommission::TYPE_CO_PARRAINAGE)
                        ->where('statut', 'en_attente')
                        ->sum('montant'),
                ];
            });

        return view('admin.affiliations.index', compact('evenements', 'parEvenement', 'liens', 'commissions', 'stats', 'ventesParLien'));

        return view('admin.affiliations.index', compact('evenements', 'parEvenement', 'liens', 'commissions', 'stats'));
    }
}
