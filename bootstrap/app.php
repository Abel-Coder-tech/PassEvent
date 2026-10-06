<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'superadmin' => \App\Http\Middleware\CheckSuperAdmin::class,
            'equipe_permission' => \App\Http\Middleware\CheckPermissionEquipe::class,
            'agent' => \App\Http\Middleware\CheckAgent::class,
            'agent_vente' => \App\Http\Middleware\CheckAgentVente::class,
            'profil_verifie' => \App\Http\Middleware\CheckProfilActif::class,
            'compte_actif' => \App\Http\Middleware\CheckCompteActif::class,
            'no_cache' => \App\Http\Middleware\NoCache::class,
        ]);

        // En-têtes de sécurité sur toutes les réponses
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Le site est servi derrière un proxy/Apache : autoriser X-Forwarded-* (HTTPS, IP)
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\CheckInactivite::class, // Deconnexion aphe 30 min d'inactivite
        ]);

        $middleware->validateCsrfTokens(except: [
            'paiement/webhook',
            'consentement',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Session expirée / token CSRF invalide (419) : on redirige au lieu de
        // laisser la page morte. Attention : Laravel convertit TokenMismatchException
        // en HttpException(419) dans prepareException AVANT de consulter les
        // callbacks de rendu, donc on matche HttpException, pas TokenMismatchException.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null; // laisser les autres erreurs HTTP suivre leur cours
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Votre session a expiré. Veuillez rafraîchir la page.'], 419);
            }

            // L'utilisateur reviendra sur la page d'ou il venait apres sa
            // reconnexion (sauf si le formulaire arrivait d'une page de login).
            $retour = $request->headers->get('referer');
            $chemin = $retour ? parse_url($retour, PHP_URL_PATH) : null;
            if ($retour && $chemin && ! in_array($chemin, ['/login', '/superadmin/login', '/connexion'], true)) {
                $request->session()->put('url.intended', $retour);
            }

            return redirect()->back()->with('error', 'Votre session a expiré. Le formulaire a été rechargé, veuillez réessayer.');
        });
    })->create();
