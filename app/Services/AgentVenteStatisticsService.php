<?php

namespace App\Services;

use App\Enums\TicketPaymentStatus;
use App\Models\AgentVente;
use App\Models\Evenement;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AgentVenteStatisticsService
{
    public function pourAgent(AgentVente $agent, ?int $evenementId = null): array
    {
        $tickets = Ticket::query()
            ->where('agent_vente_id', $agent->id)
            ->when($evenementId !== null, fn ($query) => $query->where('evenement_id', $evenementId));

        $todayStart = today()->startOfDay();
        $tomorrowStart = $todayStart->copy()->addDay();
        $stats = (clone $tickets)
            ->selectRaw('COUNT(*) as total_tickets')
            ->selectRaw('COALESCE(SUM(montant), 0) as montant_total')
            ->selectRaw(
                'SUM(CASE WHEN date_achat >= ? AND date_achat < ? THEN 1 ELSE 0 END) as aujourdhui',
                [$todayStart, $tomorrowStart]
            )
            ->first();

        $parTarif = (clone $tickets)
            ->selectRaw('tarif_id, COUNT(*) as total, COALESCE(SUM(montant), 0) as montant')
            ->groupBy('tarif_id')
            ->with('tarif')
            ->get();

        $parMethode = (clone $tickets)
            ->selectRaw('methode_paiement, COUNT(*) as total')
            ->groupBy('methode_paiement')
            ->get();

        return [
            'total_tickets' => (int) $stats->total_tickets,
            'montant_total' => (float) $stats->montant_total,
            'aujourd_hui' => (int) $stats->aujourdhui,
            'par_tarif' => $parTarif,
            'par_methode' => $parMethode,
        ];
    }

    public function ventesDuJour(AgentVente $agent): array
    {
        $todayStart = today()->startOfDay();
        $tomorrowStart = $todayStart->copy()->addDay();
        $stats = $agent->tickets()
            ->where('statut_paiement', TicketPaymentStatus::Paid->value)
            ->where('date_achat', '>=', $todayStart)
            ->where('date_achat', '<', $tomorrowStart)
            ->selectRaw('COUNT(*) as aujourd_hui')
            ->selectRaw('COALESCE(SUM(montant), 0) as montant_ajd')
            ->first();

        return [
            'aujourd_hui' => (int) $stats->aujourd_hui,
            'montant_ajd' => (float) $stats->montant_ajd,
        ];
    }

    public function apiStats(
        AgentVente $agent,
        ?Evenement $evenement = null,
        ?Carbon $debut = null,
        ?Carbon $fin = null
    ): array {
        $base = Ticket::query()
            ->where('agent_vente_id', $agent->id)
            ->where('statut_paiement', TicketPaymentStatus::Paid->value)
            ->where(fn (Builder $query) => $query->whereNull('annule')->orWhere('annule', false))
            ->whereNull('expire_at')
            ->when($evenement !== null, fn (Builder $query) => $query->where('evenement_id', $evenement->id));

        $filtres = (clone $base)
            ->when($debut !== null, fn (Builder $query) => $query->where('date_achat', '>=', $debut))
            ->when($fin !== null, fn (Builder $query) => $query->where('date_achat', '<', $fin));

        $totaux = (clone $filtres)
            ->selectRaw('COUNT(*) as total_tickets')
            ->selectRaw('COALESCE(SUM(montant), 0) as montant_total')
            ->selectRaw('COALESCE(AVG(montant), 0) as montant_moyen')
            ->first();

        $todayStart = today()->startOfDay();
        $tomorrowStart = $todayStart->copy()->addDay();
        $jour = (clone $base)
            ->where('date_achat', '>=', $todayStart)
            ->where('date_achat', '<', $tomorrowStart)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(montant), 0) as montant')
            ->first();

        $parTarif = (clone $filtres)
            ->selectRaw('tarif_id, COUNT(*) as total, COALESCE(SUM(montant), 0) as montant')
            ->groupBy('tarif_id')
            ->with('tarif')
            ->get()
            ->map(fn ($row) => [
                'tarif_id' => $row->tarif_id,
                'tarif' => $row->tarif?->getLabel() ?? 'N/A',
                'total' => (int) $row->total,
                'montant' => (float) $row->montant,
            ])
            ->values()
            ->all();

        $parMethode = (clone $filtres)
            ->selectRaw('methode_paiement, type_paiement, COUNT(*) as total, COALESCE(SUM(montant), 0) as montant')
            ->groupBy('methode_paiement', 'type_paiement')
            ->get()
            ->map(fn ($row) => [
                'methode' => $row->methode_paiement,
                'type' => $row->type_paiement,
                'label' => Ticket::methodePaiementLabel($row->methode_paiement, $row->type_paiement),
                'total' => (int) $row->total,
                'montant' => (float) $row->montant,
            ])
            ->values()
            ->all();

        $parJour = (clone $filtres)
            ->selectRaw('DATE(date_achat) as jour, COUNT(*) as total, COALESCE(SUM(montant), 0) as montant')
            ->groupBy('jour')
            ->orderBy('jour')
            ->get()
            ->map(fn ($row) => [
                'jour' => (string) $row->jour,
                'total' => (int) $row->total,
                'montant' => (float) $row->montant,
            ])
            ->values()
            ->all();

        $derniereVente = (clone $filtres)->latest('date_achat')->first();

        // Vue d'ensemble par statut : tout reste visible, payé ou non.
        $statuts = Ticket::query()
            ->where('agent_vente_id', $agent->id)
            ->when($evenement !== null, fn (Builder $query) => $query->where('evenement_id', $evenement->id))
            ->when($debut !== null, fn (Builder $query) => $query->where('date_achat', '>=', $debut))
            ->when($fin !== null, fn (Builder $query) => $query->where('date_achat', '<', $fin))
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COALESCE(SUM(CASE WHEN statut_paiement = 'payé' AND (annule IS NULL OR annule = 0) AND expire_at IS NULL THEN 1 ELSE 0 END), 0) as paye")
            ->selectRaw("COALESCE(SUM(CASE WHEN statut_paiement = 'en_attente' AND (annule IS NULL OR annule = 0) AND expire_at IS NULL THEN 1 ELSE 0 END), 0) as en_attente")
            ->selectRaw("COALESCE(SUM(CASE WHEN annule = 1 OR annule_at IS NOT NULL THEN 1 ELSE 0 END), 0) as annule")
            ->selectRaw("COALESCE(SUM(CASE WHEN expire_at IS NOT NULL THEN 1 ELSE 0 END), 0) as expire")
            ->selectRaw("COALESCE(SUM(CASE WHEN statut_paiement = 'échoué' THEN 1 ELSE 0 END), 0) as echoue")
            ->selectRaw("COALESCE(SUM(CASE WHEN statut_paiement = 'remboursé' THEN 1 ELSE 0 END), 0) as rembourse")
            ->first();

        return [
            'total_tickets' => (int) $totaux->total_tickets,
            'montant_total' => (float) $totaux->montant_total,
            'montant_moyen' => (float) $totaux->montant_moyen,
            'aujourd_hui' => (int) $jour->total,
            'montant_ajd' => (float) $jour->montant,
            'par_tarif' => $parTarif,
            'par_methode' => $parMethode,
            'par_jour' => $parJour,
            'par_statut' => [
                'total' => (int) ($statuts->total ?? 0),
                'paye' => (int) ($statuts->paye ?? 0),
                'en_attente' => (int) ($statuts->en_attente ?? 0),
                'annule' => (int) ($statuts->annule ?? 0),
                'expire' => (int) ($statuts->expire ?? 0),
                'echoue' => (int) ($statuts->echoue ?? 0),
                'rembourse' => (int) ($statuts->rembourse ?? 0),
            ],
            'derniere_vente' => $derniereVente?->date_achat?->toIso8601String(),
        ];
    }

    public function pourEvenement(Evenement $evenement, array $agentIds)
    {
        return Ticket::query()
            ->where('evenement_id', $evenement->id)
            ->whereIn('agent_vente_id', $agentIds)
            ->selectRaw('agent_vente_id, methode_paiement, COUNT(*) as total, MAX(date_achat) as derniere_vente')
            ->groupBy('agent_vente_id', 'methode_paiement')
            ->get()
            ->groupBy('agent_vente_id');
    }
}
