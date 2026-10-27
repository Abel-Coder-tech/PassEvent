<?php

namespace App\Services;

use App\Enums\TicketPaymentStatus;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TicketStatisticsService
{
    public function organisateur(Builder $tickets): array
    {
        $stats = (clone $tickets)
            ->selectRaw('COUNT(*) as total_tickets')
            ->selectRaw(
                'SUM(CASE WHEN statut_paiement = ? AND utilise = ? THEN 1 ELSE 0 END) as valides',
                [TicketPaymentStatus::Paid->value, false]
            )
            ->selectRaw('SUM(CASE WHEN utilise = ? THEN 1 ELSE 0 END) as scannes', [true])
            ->selectRaw('SUM(CASE WHEN nom_tarif LIKE ? THEN 1 ELSE 0 END) as etudiants', ['%tudiant%'])
            ->selectRaw(
                'SUM(CASE WHEN statut_paiement IN (?, ?) THEN 1 ELSE 0 END) as annules',
                [TicketPaymentStatus::Cancelled->value, TicketPaymentStatus::Refunded->value]
            )
            ->selectRaw(
                'SUM(CASE WHEN statut_paiement = ? AND transaction_id NOT LIKE ? AND transaction_id NOT LIKE ? THEN 1 ELSE 0 END) as transactions_reussies',
                [TicketPaymentStatus::Paid->value, 'MANUEL-%', 'GRATUIT-%']
            )
            ->selectRaw(
                'SUM(CASE WHEN statut_paiement = ? THEN 1 ELSE 0 END) as transactions_echouees',
                [TicketPaymentStatus::Failed->value]
            )
            ->first();

        return [
            'total_tickets' => (int) $stats->total_tickets,
            'valides' => (int) $stats->valides,
            'scannes' => (int) $stats->scannes,
            'etudiants' => (int) $stats->etudiants,
            'annules' => (int) $stats->annules,
            'transactions_reussies' => (int) $stats->transactions_reussies,
            'transactions_echouees' => (int) $stats->transactions_echouees,
        ];
    }

    public function physiques(Builder $tickets): array
    {
        $stats = (clone $tickets)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN annule = ? THEN 1 ELSE 0 END) as annules', [true])
            ->selectRaw('SUM(CASE WHEN utilise = ? THEN 1 ELSE 0 END) as scannes', [true])
            ->first();

        return [
            'total' => (int) $stats->total,
            'annules' => (int) $stats->annules,
            'scannes' => (int) $stats->scannes,
        ];
    }

    public function ventesEvenement(int $evenementId): array
    {
        $stats = Ticket::query()
            ->where('evenement_id', $evenementId)
            ->where('statut_paiement', TicketPaymentStatus::Paid->value)
            ->selectRaw('COUNT(*) as ventes')
            ->selectRaw('COALESCE(SUM(montant), 0) as revenus')
            ->first();

        return [
            'ventes' => (int) $stats->ventes,
            'revenus' => (float) $stats->revenus,
        ];
    }

    public function alertes(iterable $evenementIds): array
    {
        $now = now();
        $stats = Ticket::query()
            ->whereIn('evenement_id', $evenementIds)
            ->selectRaw(
                'SUM(CASE WHEN statut_paiement = ? AND date_achat < ? THEN 1 ELSE 0 END) as en_attente',
                [TicketPaymentStatus::Pending->value, $now->copy()->subMinutes(30)]
            )
            ->selectRaw(
                'SUM(CASE WHEN statut_paiement = ? AND transaction_id IS NULL THEN 1 ELSE 0 END) as sans_transaction',
                [TicketPaymentStatus::Paid->value]
            )
            ->selectRaw(
                'SUM(CASE WHEN statut_paiement = ? AND date_achat >= ? THEN 1 ELSE 0 END) as reussies',
                [TicketPaymentStatus::Paid->value, $now->copy()->subDays(7)]
            )
            ->selectRaw(
                'SUM(CASE WHEN statut_paiement = ? AND date_achat >= ? THEN 1 ELSE 0 END) as echouees',
                [TicketPaymentStatus::Failed->value, $now->copy()->subDays(7)]
            )
            ->first();

        return [
            'en_attente' => (int) $stats->en_attente,
            'sans_transaction' => (int) $stats->sans_transaction,
            'reussies' => (int) $stats->reussies,
            'echouees' => (int) $stats->echouees,
        ];
    }

    public function tableauDeBord(iterable $evenementIds): array
    {
        $stats = Ticket::query()
            ->whereIn('evenement_id', $evenementIds)
            ->selectRaw(
                'SUM(CASE WHEN statut_paiement = ? AND lot_physique_id IS NULL THEN 1 ELSE 0 END) as tickets_vendus',
                [TicketPaymentStatus::Paid->value]
            )
            ->selectRaw(
                'SUM(CASE WHEN statut_paiement = ? AND lot_physique_id IS NULL THEN montant ELSE 0 END) as recettes_totales',
                [TicketPaymentStatus::Paid->value]
            )
            ->selectRaw(
                'SUM(CASE WHEN statut_paiement = ? AND lot_physique_id IS NULL AND methode_paiement NOT IN (?, ?) THEN montant ELSE 0 END) as recettes_mobile',
                [TicketPaymentStatus::Paid->value, 'cash', 'especes']
            )
            ->selectRaw(
                'SUM(CASE WHEN statut_paiement = ? AND lot_physique_id IS NULL AND methode_paiement IN (?, ?) THEN montant ELSE 0 END) as recettes_especes',
                [TicketPaymentStatus::Paid->value, 'cash', 'especes']
            )
            ->selectRaw('SUM(CASE WHEN utilise = ? THEN 1 ELSE 0 END) as tickets_scannes', [true])
            ->first();

        return [
            'tickets_vendus' => (int) $stats->tickets_vendus,
            'recettes_totales' => (float) $stats->recettes_totales,
            'recettes_mobile' => (float) $stats->recettes_mobile,
            'recettes_especes' => (float) $stats->recettes_especes,
            'tickets_scannes' => (int) $stats->tickets_scannes,
        ];
    }

    public function ventesParJour(iterable $evenementIds): Collection
    {
        $start = today()->subDays(6);
        $end = $start->copy()->addDays(7);

        return Ticket::query()
            ->whereIn('evenement_id', $evenementIds)
            ->where('statut_paiement', TicketPaymentStatus::Paid->value)
            ->whereNull('lot_physique_id')
            ->where('date_achat', '>=', $start)
            ->where('date_achat', '<', $end)
            ->selectRaw('DATE(date_achat) as date, COUNT(*) as total')
            ->groupByRaw('DATE(date_achat)')
            ->get()
            ->pluck('total', 'date');
    }

    public function repartitionPaiements(iterable $evenementIds): array
    {
        $rows = Ticket::query()
            ->whereIn('evenement_id', $evenementIds)
            ->where('statut_paiement', TicketPaymentStatus::Paid->value)
            ->whereNull('lot_physique_id')
            ->selectRaw('type_paiement, methode_paiement, COUNT(*) as total, COALESCE(SUM(montant), 0) as montant')
            ->groupBy('type_paiement', 'methode_paiement')
            ->get();

        return [
            'operateurs' => $rows->where('type_paiement', 'mobile_money')->keyBy('methode_paiement'),
            'moyens' => $rows->groupBy('type_paiement')->map(fn (Collection $methodes) => (object) [
                'total' => (int) $methodes->sum('total'),
                'montant' => (float) $methodes->sum('montant'),
            ]),
        ];
    }
}
