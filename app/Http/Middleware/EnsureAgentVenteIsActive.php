<?php

namespace App\Http\Middleware;

use App\Models\AgentVente;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgentVenteIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $agent = $request->user();

        // Un token d'agent de scan ne doit jamais ouvrir l'espace de vente.
        if (! $agent instanceof AgentVente) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $agent->actif) {
            return response()->json(['message' => 'account_disabled'], 403);
        }

        $evenement = $agent->evenement;

        if (! $evenement) {
            return response()->json(['message' => 'no_event'], 403);
        }

        if ($raison = $evenement->raisonBlocageAcces()) {
            return response()->json(['message' => $raison], 403);
        }

        return $next($request);
    }
}
