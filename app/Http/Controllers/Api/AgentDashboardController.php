<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentDashboardController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $agent = $request->user();
        $evenement = $agent->evenement;

        $total = $agent->logs()->where('type_operation', 'scan')->count();
        $valides = $agent->logs()->where('type_operation', 'scan')->where('details->resultat', 'valide')->count();
        $dejaUtilises = $agent->logs()->where('type_operation', 'scan')->where('details->resultat', 'deja_utilise')->count();
        $invalides = $total - $valides;

        $scans = $agent->logs()->where('type_operation', 'scan')->latest('created_at')->limit(50)->get();

        return response()->json([
            'event' => [
                'titre' => $evenement->titre ?? null,
                'date_event' => $evenement && $evenement->date_event ? $evenement->date_event->toDateString() : null,
                'lieu' => $evenement->lieu ?? null,
            ],
            'stats' => [
                'total' => $total,
                'valides' => $valides,
                'invalides' => $invalides,
                'deja_utilises' => $dejaUtilises,
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
