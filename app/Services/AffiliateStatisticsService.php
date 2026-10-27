<?php

namespace App\Services;

use App\Enums\AffiliateCommissionStatus;
use App\Models\Affiliate;

class AffiliateStatisticsService
{
    public function links(Affiliate $affiliate): array
    {
        $stats = $affiliate->eventLinks()
            ->selectRaw('COUNT(*) as liens')
            ->selectRaw('SUM(CASE WHEN actif = ? THEN 1 ELSE 0 END) as liens_actifs', [true])
            ->selectRaw('COALESCE(SUM(clics), 0) as clics')
            ->first();

        return [
            'liens' => (int) $stats->liens,
            'liens_actifs' => (int) $stats->liens_actifs,
            'clics' => (int) $stats->clics,
        ];
    }

    public function commissions(Affiliate $affiliate, ?int $evenementId = null, bool $excludeCancelled = false): array
    {
        $commissions = $affiliate->commissions();
        if ($evenementId !== null) {
            $commissions->where('evenement_id', $evenementId);
        }
        if ($excludeCancelled) {
            $commissions->where('statut', '!=', AffiliateCommissionStatus::Cancelled->value);
        }

        $stats = $commissions
            ->selectRaw('COUNT(*) as nombre')
            ->selectRaw('COALESCE(SUM(montant), 0) as total')
            ->selectRaw(
                'SUM(CASE WHEN statut = ? THEN montant ELSE 0 END) as en_attente',
                [AffiliateCommissionStatus::Pending->value]
            )
            ->selectRaw(
                'SUM(CASE WHEN statut = ? THEN montant ELSE 0 END) as disponible',
                [AffiliateCommissionStatus::Available->value]
            )
            ->selectRaw(
                'SUM(CASE WHEN statut = ? THEN montant ELSE 0 END) as paye',
                [AffiliateCommissionStatus::Paid->value]
            )
            ->first();

        return [
            'nombre' => (int) $stats->nombre,
            'total' => (float) $stats->total,
            'en_attente' => (float) $stats->en_attente,
            'disponible' => (float) $stats->disponible,
            'paye' => (float) $stats->paye,
        ];
    }
}
