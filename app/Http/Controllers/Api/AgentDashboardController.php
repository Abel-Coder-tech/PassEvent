<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AgentScanStatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentDashboardController extends Controller
{
    public function show(Request $request, AgentScanStatisticsService $statistics): JsonResponse
    {
        $agent = $request->user();
        $evenement = $agent->evenement;

        $stats = $statistics->apiCounters($agent);
        $total = $stats['total'];
        $valides = $stats['valides'];
        $invalides = $total - $valides;

        $scans = $agent->logs()->where('type_operation', 'scan')->latest('created_at')->limit(50)->get();
        $organisateur = $evenement?->user;

        return response()->json([
            'event' => [
                'titre' => $evenement?->titre,
                'date_event' => $evenement && $evenement->date_event ? $evenement->date_event->toDateString() : null,
                'lieu' => $evenement?->lieu,
            ],
            'organisateur' => $organisateur ? [
                'nom' => trim(($organisateur->prenom ?? '').' '.$organisateur->nom),
                'email' => $organisateur->email,
            ] : null,
            'stats' => [
                'total' => $total,
                'valides' => $valides,
                'invalides' => $invalides,
                'deja_utilises' => $stats['deja_utilises'],
            ],
            'scans' => $scans->map(function ($log) {
                return [
                    'date' => $log->created_at->toIso8601String(),
                    'code' => $log->details['code'] ?? '',
                    'resultat' => $log->details['resultat'] ?? 'invalide',
                    'raison' => $log->details['raison'] ?? null,
                ];
            })->values(),
            'scan_disponible' => (bool) ($agent->actif && $evenement && $evenement->date_event >= now()),
        ], 200);
    }
}
