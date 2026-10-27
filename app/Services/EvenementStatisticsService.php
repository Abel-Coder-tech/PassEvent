<?php

namespace App\Services;

use App\Enums\EventStatus;
use App\Models\Evenement;

class EvenementStatisticsService
{
    public function pourOrganisateur(int $userId): array
    {
        $stats = Evenement::query()
            ->where('user_id', $userId)
            ->selectRaw('COUNT(*) as total_evenements')
            ->selectRaw('SUM(CASE WHEN statut = ? THEN 1 ELSE 0 END) as en_cours', [EventStatus::Published->value])
            ->selectRaw('SUM(CASE WHEN statut = ? THEN 1 ELSE 0 END) as a_venir', [EventStatus::Draft->value])
            ->selectRaw('SUM(CASE WHEN statut = ? THEN 1 ELSE 0 END) as termines', [EventStatus::Finished->value])
            ->selectRaw('COALESCE(SUM(quota_vendu), 0) as total_billets_vendus')
            ->first();

        return [
            'total_evenements' => (int) $stats->total_evenements,
            'en_cours' => (int) $stats->en_cours,
            'a_venir' => (int) $stats->a_venir,
            'termines' => (int) $stats->termines,
            'total_billets_vendus' => (int) $stats->total_billets_vendus,
        ];
    }
}
