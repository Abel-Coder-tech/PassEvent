<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentNotification;
use App\Services\AgentScanStatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentDashboardController extends Controller
{
    public function show(Request $request, AgentScanStatisticsService $statistics): JsonResponse
    {
        $agent = $request->user();
        $evenement = \App\Models\Evenement::with('user')->find($agent->evenement_id);

        $stats = $statistics->apiCounters($agent);
        $total = $stats['total'];
        $valides = $stats['valides'];
        $invalides = $total - $valides;

        $scans = $agent->logs()->where('type_operation', 'scan')->latest('created_at')->limit(50)->get();
        $organisateur = $evenement?->user;

        $notifications = AgentNotification::query()
            ->where('agent_id', $agent->id)
            ->where(function ($q) use ($agent) {
                $q->whereNull('evenement_id')
                    ->orWhere('evenement_id', $agent->evenement_id);
            })
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($n) => [
                'titre' => $n->titre,
                'message' => $n->message,
                'lu' => $n->lu_at !== null,
            ])
            ->values();

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
            'notifications' => $notifications,
        ], 200);
    }
}
