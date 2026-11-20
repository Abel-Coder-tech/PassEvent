<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Un compte « incomplet » ne doit voir que deux rubriques : créer un
 * événement et finaliser son profil. Tout le reste est inaccessible, même
 * si l'utilisateur tape l'URL directement.
 */
class RestreindreCompteIncomplet
{
    private const ROUTES_AUTORISEES = [
        'dashboard',
        'logout',
        'profil.step2',
        'profil.post-step2',
        'profil.recap',
        'profil.submit',
        'admin.evenements.create',
        'admin.evenements.store',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'admin' || $user->statut !== 'incomplet') {
            return $next($request);
        }

        $route = $request->route()?->getName();

        if ($route && in_array($route, self::ROUTES_AUTORISEES, true)) {
            return $next($request);
        }

        $message = 'Finalisez votre profil avant d\'accéder à cette rubrique.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->route('dashboard')->with('error', $message);
    }
}
