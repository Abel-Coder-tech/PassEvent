<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloque la connexion après trop d'échecs. Seuls les échecs comptent (un
 * identifiant correct n'use pas le compteur) :
 *  - 5 échecs sur un même couple email + IP -> bloqué 15 minutes ;
 *  - 20 échecs depuis une même IP, tous comptes confondus -> bloqué 15 minutes.
 * Une connexion réussie remet à zéro le compteur du couple email + IP.
 */
class BloquerTentativesConnexion
{
    public const MAX_PAR_COMPTE = 5;

    public const MAX_PAR_IP = 20;

    public const DUREE_SECONDES = 900;

    public function handle(Request $request, Closure $next): Response
    {
        [$cleCompte, $cleIp] = $this->cles($request);

        foreach ([[$cleCompte, self::MAX_PAR_COMPTE], [$cleIp, self::MAX_PAR_IP]] as [$cle, $max]) {
            if (RateLimiter::tooManyAttempts($cle, $max)) {
                return $this->bloque($request, RateLimiter::availableIn($cle));
            }
        }

        $reponse = $next($request);

        if ($this->estEchec($request, $reponse)) {
            RateLimiter::hit($cleCompte, self::DUREE_SECONDES);
            RateLimiter::hit($cleIp, self::DUREE_SECONDES);
        } elseif ($reponse->isSuccessful() || $reponse->isRedirection()) {
            RateLimiter::clear($cleCompte);
        }

        return $reponse;
    }

    private function cles(Request $request): array
    {
        $identifiant = strtolower(trim((string) $request->input('email')));
        $ip = (string) $request->ip();

        return ['connexion-verrou-compte:'.hash('sha256', $identifiant.'|'.$ip), 'connexion-verrou-ip:'.$ip];
    }

    // Échec = identifiants refusés : 401 en API, retour au formulaire avec
    // erreur côté web. Un formulaire sans mot de passe n'est pas une tentative.
    private function estEchec(Request $request, Response $reponse): bool
    {
        if (! $request->filled('mot_de_passe') && ! $request->filled('password')) {
            return false;
        }

        if ($request->expectsJson()) {
            return $reponse->getStatusCode() === 401;
        }

        return $reponse->isRedirection() && (bool) $request->session()->get('errors')?->any();
    }

    private function bloque(Request $request, int $secondes): Response
    {
        $minutes = max(1, (int) ceil($secondes / 60));
        $message = "Trop de tentatives de connexion. Accès bloqué pendant {$minutes} minute(s), réessayez plus tard.";

        if ($request->expectsJson()) {
            return response()->json(['message' => 'too_many_attempts', 'detail' => $message], 429)
                ->header('Retry-After', (string) $secondes);
        }

        return back()->withErrors(['email' => $message])->onlyInput('email');
    }
}
