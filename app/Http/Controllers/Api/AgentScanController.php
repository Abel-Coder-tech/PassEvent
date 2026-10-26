<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Services\TicketScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\PersonalAccessToken;

class AgentScanController extends Controller
{
    public function pin(Request $request): JsonResponse
    {
        $agent = $request->user();

        $request->validate([
            'code_acces' => ['required', 'digits:6'],
        ]);

        if ($agent->evenement && $agent->evenement->date_event < now()) {
            return response()->json(['message' => 'event_ended'], 403);
        }

        if (isset($agent->actif) && !$agent->actif) {
            return response()->json(['message' => 'account_disabled'], 403);
        }

        if ($agent->bloque_jusqua && Carbon::parse($agent->bloque_jusqua)->isFuture()) {
            $seconds = now()->diffInSeconds(Carbon::parse($agent->bloque_jusqua));
            return response()->json([
                'message' => 'Code bloqué. Réessayez dans '.ceil($seconds/60).' minutes.',
                'code' => 'pin_blocked',
                'retry_after' => $seconds,
            ], 429);
        }

        $key = 'agent-pin:'.$agent->id;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
                'code' => 'pin_throttled',
                'retry_after' => $seconds,
            ], 429);
        }

        if ($request->code_acces !== $agent->code_acces) {
            RateLimiter::hit($key, 300);
            $agent->increment('tentatives_code');

            if ($agent->tentatives_code >= 10) {
                $agent->update([
                    'bloque_jusqua' => now()->addMinutes(30),
                    'tentatives_code' => 10,
                ]);
            }

            return response()->json([
                'message' => 'Code d\'accès incorrect.',
                'code' => 'pin_invalid',
            ], 422);
        }

        $agent->update([
            'tentatives_code' => 0,
            'bloque_jusqua' => null,
        ]);
        RateLimiter::clear($key);

        // Un seul token scan actif par agent
        $agent->tokens()->where('name', 'agent-scan')->delete();

        $token = $agent->createToken('agent-scan', ['agent:scan'], now()->addHours(8))->plainTextToken;
        $tokenModel = $agent->tokens()->where('name', 'agent-scan')->latest()->first();

        return response()->json([
            'scan_token' => $token,
            'expires_at' => $tokenModel && $tokenModel->expires_at ? $tokenModel->expires_at->toIso8601String() : now()->addHours(8)->toIso8601String(),
            'evenement' => [
                'titre' => $agent->evenement->titre ?? null,
            ],
        ], 200);
    }

    public function verify(Request $request, TicketScanService $service): JsonResponse
    {
        $agent = $request->user();

        $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $result = $service->verifier($agent, $request->code);

        $total = $agent->logs()->where('type_operation', 'scan')->count();
        $valides = $agent->logs()->where('type_operation', 'scan')->where('details->resultat', 'valide')->count();
        $invalides = $total - $valides;

        $result['stats'] = [
            'total' => $total,
            'valides' => $valides,
            'invalides' => $invalides,
        ];

        return response()->json($result, 200);
    }

    public function history(Request $request): JsonResponse
    {
        $agent = $request->user();

        $total = $agent->logs()->where('type_operation', 'scan')->count();
        $valides = $agent->logs()->where('type_operation', 'scan')->where('details->resultat', 'valide')->count();
        $dejaUtilises = $agent->logs()->where('type_operation', 'scan')->where('details->resultat', 'deja_utilise')->count();
        $invalides = $total - $valides;

        $recent = $agent->logs()->where('type_operation', 'scan')->latest('created_at')->limit(20)->get();

        return response()->json([
            'stats' => [
                'total' => $total,
                'valides' => $valides,
                'invalides' => $invalides,
                'deja_utilises' => $dejaUtilises,
            ],
            'recent' => $recent->map(function ($log) {
                return [
                    'heure' => $log->created_at->format('H:i:s'),
                    'code' => $log->details['code'] ?? '',
                    'resultat' => $log->details['resultat'] ?? 'invalide',
                    'nom' => $log->ticket?->nom_acheteur,
                ];
            })->values(),
        ], 200);
    }

    public function exit(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token && $token->name === 'agent-scan') {
            $token->delete();
        }

        return response()->json(['message' => 'scan_exited'], 200);
    }
}
