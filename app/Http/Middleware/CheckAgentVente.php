<?php

namespace App\Http\Middleware;

use App\Support\SessionMarker;
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

        if ($agent->session_cle !== null) {
            $cle = $request->session()->get('agent_vente_session_cle');
            if (! $agent->garderSession($cle)) {
                auth('agent_vente')->logout();
                $request->session()->forget('agent_vente_session_cle');

                return redirect()->route('agent-vente.login')
                    ->with('error', 'Cette session n’est plus valide. Un autre appareil utilise peut-être ce compte.');
            }
        }

        if (!$agent->actif) {
            auth('agent_vente')->logout();
            return redirect()->route('agent-vente.login')->withErrors(['email' => 'Votre compte a été désactivé.']);
        }

        if ($agent->evenement->evenementPasse()) {
            auth('agent_vente')->logout();
            return redirect()->route('agent-vente.login')->withErrors(['email' => 'L\'événement est terminé.']);
        }

        return $next($request);
    }
}
