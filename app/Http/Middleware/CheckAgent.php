<?php

namespace App\Http\Middleware;

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

        if (!$agent->actif) {
            auth('agent')->logout();
            return redirect()->route('agent.login')->withErrors(['email' => 'Votre compte a été désactivé.']);
        }

        if ($agent->evenement->date_event < now()) {
            auth('agent')->logout();
            return redirect()->route('agent.login')->withErrors(['email' => 'L\'événement est terminé.']);
        }

        return $next($request);
    }
}
