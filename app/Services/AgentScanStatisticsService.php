<?php

namespace App\Services;

use App\Enums\LogOperationType;
use App\Enums\ScanResult;
use App\Models\Agent;
use App\Models\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AgentScanStatisticsService
{
    public function apiCounters(Agent $agent): array
    {
        $resultColumn = DB::connection()->getQueryGrammar()->wrap('details->resultat');
        $stats = $agent->logs()
            ->where('type_operation', LogOperationType::Scan->value)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN {$resultColumn} = ? THEN 1 ELSE 0 END) as valides", [ScanResult::Valid->value])
            ->selectRaw("SUM(CASE WHEN {$resultColumn} = ? THEN 1 ELSE 0 END) as deja_utilises", [ScanResult::AlreadyUsed->value])
            ->first();

        return [
            'total' => (int) $stats->total,
            'valides' => (int) $stats->valides,
            'deja_utilises' => (int) $stats->deja_utilises,
        ];
    }

    public function adminStats(Agent $agent, ?int $evenementId = null): array
    {
        $resultColumn = DB::connection()->getQueryGrammar()->wrap('details->resultat');
        $logs = $agent->logs()->where('type_operation', LogOperationType::Scan->value);

        if ($evenementId !== null) {
            $logs->whereHas('ticket', fn (Builder $query) => $query->where('evenement_id', $evenementId));
        }

        $todayStart = today()->startOfDay();
        $tomorrowStart = $todayStart->copy()->addDay();
        $stats = $logs
            ->selectRaw('COUNT(*) as total_scans')
            ->selectRaw(
                'SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) as scans_ajd',
                [$todayStart, $tomorrowStart]
            )
            ->selectRaw("SUM(CASE WHEN {$resultColumn} = ? THEN 1 ELSE 0 END) as valides", [ScanResult::Valid->value])
            ->selectRaw("SUM(CASE WHEN {$resultColumn} = ? THEN 1 ELSE 0 END) as deja_utilises", [ScanResult::AlreadyUsed->value])
            ->selectRaw("SUM(CASE WHEN {$resultColumn} != ? THEN 1 ELSE 0 END) as invalides", [ScanResult::Valid->value])
            ->first();

        return [
            'total_scans' => (int) $stats->total_scans,
            'scans_ajd' => (int) $stats->scans_ajd,
            'valides' => (int) $stats->valides,
            'deja_utilises' => (int) $stats->deja_utilises,
            'invalides' => (int) $stats->invalides,
            'dernier_acces' => $agent->dernier_acces,
        ];
    }

    public function evenement(int $evenementId): array
    {
        $resultColumn = DB::connection()->getQueryGrammar()->wrap('details->resultat');
        $todayStart = today()->startOfDay();
        $tomorrowStart = $todayStart->copy()->addDay();
        $stats = Log::query()
            ->where('type_operation', LogOperationType::Scan->value)
            ->whereHas('ticket', fn (Builder $query) => $query->where('evenement_id', $evenementId))
            ->selectRaw('COUNT(*) as total_scans')
            ->selectRaw(
                'SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) as scans_today',
                [$todayStart, $tomorrowStart]
            )
            ->selectRaw("SUM(CASE WHEN {$resultColumn} = ? THEN 1 ELSE 0 END) as scans_valides", [ScanResult::Valid->value])
            ->selectRaw("SUM(CASE WHEN {$resultColumn} = ? THEN 1 ELSE 0 END) as scans_invalides", [ScanResult::Invalid->value])
            ->first();

        return [
            'total_scans' => (int) $stats->total_scans,
            'scans_today' => (int) $stats->scans_today,
            'scans_valides' => (int) $stats->scans_valides,
            'scans_invalides' => (int) $stats->scans_invalides,
        ];
    }
}
