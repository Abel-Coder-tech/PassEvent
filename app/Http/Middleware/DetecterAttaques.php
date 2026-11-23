<?php

namespace App\Http\Middleware;

use App\Models\IpBloquee;
use App\Services\SecuriteService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Première barrière de PaxEvent :
 *  - refuse les IP bloquées par le superadmin ;
 *  - refuse les outils d'attaque automatiques reconnus (sqlmap, nikto…) ;
 *  - enregistre les liens suspects et les tentatives d'attaque dans le journal
 *    de sécurité. Ces requêtes-là ne sont pas refusées ici : le code les
 *    neutralise déjà, et un faux positif ne doit jamais bloquer un vrai client.
 */
class DetecterAttaques
{
    public function __construct(private SecuriteService $securite) {}

    public function handle(Request $request, Closure $next): Response
    {
        $ip = (string) $request->ip();

        if (IpBloquee::estBloquee($ip)) {
            $this->securite->enregistrer('ip_bloquee', $request);

            return response('Accès refusé.', 403);
        }

        $detection = $this->securite->analyser($request);

        if ($detection !== null) {
            [$type, $regle] = $detection;
            $this->securite->enregistrer($type, $request, ['regle' => $regle]);

            if ($type === 'robot') {
                return response('Accès refusé.', 403);
            }
        }

        return $next($request);
    }
}
