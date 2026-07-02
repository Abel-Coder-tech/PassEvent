<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAgentVente
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('agent_vente')->check()) {
            return redirect()->route('agent-vente.login');
        }

        $agent = auth('agent_vente')->user();

        if (!$agent->actif) {
            auth('agent_vente')->logout();
            return redirect()->route('agent-vente.login')->withErrors(['email' => 'Votre compte a été désactivé.']);
        }

        if ($agent->evenement->date_event < now()) {
            auth('agent_vente')->logout();
            return redirect()->route('agent-vente.login')->withErrors(['email' => 'L\'événement est terminé.']);
        }

        return $next($request);
    }
}
