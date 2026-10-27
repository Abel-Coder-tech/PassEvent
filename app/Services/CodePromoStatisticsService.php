<?php

namespace App\Services;

use App\Models\CodePromo;

class CodePromoStatisticsService
{
    public function pourEvenements(iterable $evenementIds): array
    {
        $stats = CodePromo::query()
            ->whereIn('evenement_id', $evenementIds)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN nb_utilisations > 0 THEN 1 ELSE 0 END) as utilises')
            ->first();

        return [
            'total' => (int) $stats->total,
            'utilises' => (int) $stats->utilises,
        ];
    }
}
