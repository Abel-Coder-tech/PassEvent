<?php

namespace App\Http\Middleware;

use App\Support\SessionMarker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('agent')->check()) {
            return redirect()->route('agent.login');
        }

        $agent = auth('agent')->user();

        if ($agent->session_cle !== null) {
            $cle = $request->session()->get('agent_session_cle');
            if (! $agent->sessionEstActive() || ! SessionMarker::correspond($cle, $agent->session_cle)) {
                auth('agent')->logout();
                $request->session()->forget('agent_session_cle');

                return redirect()->route('agent.login')
                    ->with('error', 'Cette session n’est plus valide. Un autre appareil utilise peut-être ce compte.');
            }
        }

        if (!$agent->actif) {
            auth('agent')->logout();
            return redirect()->route('agent.login')->withErrors(['email' => 'Votre compte a été désactivé.']);
        }

        if ($agent->evenement->evenementPasse()) {
            auth('agent')->logout();
            return redirect()->route('agent.login')->withErrors(['email' => 'L\'événement est terminé.']);
        }

        return $next($request);
    }
}
