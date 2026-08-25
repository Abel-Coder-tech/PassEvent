<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Déconnecte chaque guard apres sa propre duree d inactivite.
// web  (organisateurs) : 60 min
// superadmin (proprietaire + equipe) : 30 min
class CheckInactivite
{
    private const TIMEOUTS = [
        'web'         => 60,
        'superadmin'  => 30,
    ];

    private const CLES = [
        'web'         => 'derniere_activite_web',
        'superadmin'  => 'derniere_activite_superadmin',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        foreach (self::TIMEOUTS as $guard => $dureeMinutes) {
            if (Auth::guard($guard)->check()) {
                $cle = self::CLES[$guard];
                $derniere = $request->session()->get($cle);

                if ($derniere && abs(now()->diffInMinutes($derniere)) > $dureeMinutes) {
                    Auth::guard($guard)->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Session expirée suite à une inactivité.'], 401);
                    }

                    $route = $guard === 'superadmin' ? 'superadmin.login' : 'login';

                    return redirect()->route($route)
                        ->with('error', 'Votre session a expiré après '.$dureeMinutes.' minutes d\'inactivité. Veuillez vous reconnecter.');
                }

                $request->session()->put($cle, now());
            }
        }

        return $next($request);
    }
}
