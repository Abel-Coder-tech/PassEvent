<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// En-têtes de sécurité appliqués à toutes les réponses.
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Anti-clickjacking : interdit tout encadrement du site (frame/iframe/embed)
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'none'");

        // Anti-sniffing MIME
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer minimisé (protège les URLs signées dans l'en-tête Referer)
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions navigateur minimales
        // La caméra reste interdite partout, sauf sur les pages de scan
        // (/scan pour l'organisateur, /agent/scan pour l'agent) qui en ont
        // besoin, et uniquement en same-origin (camera=(self)).
        $camera = ($request->is('agent/scan') || $request->is('scan')) ? '(self)' : '()';
        $response->headers->set('Permissions-Policy', "geolocation=(), microphone=(), camera={$camera}");

        // HSTS : uniquement sur HTTPS
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}