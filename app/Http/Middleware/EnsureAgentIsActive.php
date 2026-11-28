<?php

namespace App\Http\Middleware;

use App\Models\Agent;
use App\Support\SessionMarker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgentIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $agent = $request->user();

        // L'identité vient toujours du token (JWT/Sanctum). Un token émis pour
        // un autre type de compte (organisateur, agent de vente) ne doit jamais
        // ouvrir l'espace scan, même s'il porte une capability « agent:read ».
        if (! $agent instanceof Agent) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Session unique : un jeton qui ne correspond plus à la session ouverte
        // (autre appareil connecté, déconnexion, expiration) est refusé.
        if ($agent->session_cle !== null) {
            $cle = SessionMarker::cleDepuis($request->user()?->currentAccessToken());
            if (! $agent->garderSession($cle)) {
                return response()->json(['message' => 'session_remplacee'], 401);
            }
        }

        if (isset($agent->actif) && !$agent->actif) {
            return response()->json(['message' => 'account_disabled'], 403);
        }

        $evenement = $agent->evenement ?? null;
        // Événement terminé : l'accès reste ouvert en consultation ; les actions
        // (scan) sont refusées par le contrôle « agent.evenement.ouvert ».

        return $next($request);
    }
}
