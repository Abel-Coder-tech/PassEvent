<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Déconnecte l'utilisateur apres une inactivite superieure a la duree de vie de session (SESSION_LIFETIME).
// Complète la durée native : même si un cookie "remember me" existe encore, la session morte reste morte.
class CheckInactivite
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $dureeMinutes = (int) config('session.lifetime', 30);
            $derniere = $request->session()->get('derniere_activite');

            // abs() : Carbon 3 renvoie un ecart SIGNE selon l'ordre des dates
            if ($derniere && abs(now()->diffInMinutes($derniere)) > $dureeMinutes) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Session expirée suite à une inactivité.'], 401);
                }

                return redirect()->route('login')
                    ->with('error', 'Votre session a expiré après '.$dureeMinutes.' minutes d\'inactivité. Veuillez vous reconnecter.');
            }

            $request->session()->put('derniere_activite', now());
        }

        return $next($request);
    }
}
