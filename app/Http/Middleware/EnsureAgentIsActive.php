<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgentIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $agent = $request->user();

        if (!$agent) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (isset($agent->actif) && !$agent->actif) {
            return response()->json(['message' => 'account_disabled'], 403);
        }

        $evenement = $agent->evenement ?? null;
        if ($evenement && isset($evenement->date_event) && $evenement->date_event < now()) {
            return response()->json(['message' => 'event_ended'], 403);
        }

        return $next($request);
    }
}
