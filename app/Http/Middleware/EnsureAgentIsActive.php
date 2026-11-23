<?php

namespace App\Http\Middleware;

use App\Models\Agent;
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

        if (isset($agent->actif) && !$agent->actif) {
            return response()->json(['message' => 'account_disabled'], 403);
        }

        $evenement = $agent->evenement ?? null;
        if ($evenement && $evenement->evenementPasse()) {
            return response()->json(['message' => 'event_ended'], 403);
        }

        return $next($request);
    }
}
