<?php

namespace App\Services;

use App\Enums\TicketPaymentStatus;
use App\Models\AgentVente;
use App\Models\Evenement;
use App\Models\Ticket;
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
