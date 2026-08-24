<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'superadmin' => \App\Http\Middleware\CheckSuperAdmin::class,
            'agent' => \App\Http\Middleware\CheckAgent::class,
            'agent_vente' => \App\Http\Middleware\CheckAgentVente::class,
            'profil_verifie' => \App\Http\Middleware\CheckProfilActif::class,
            'compte_actif' => \App\Http\Middleware\CheckCompteActif::class,
            'no_cache' => \App\Http\Middleware\NoCache::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\CheckInactivite::class, // Deconnexion aphe 30 min d'inactivite
        ]);

        $middleware->validateCsrfTokens(except: [
            'paiement/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Session expirée / token CSRF invalide (419) : on redirige au lieu de laisser la page morte.
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Votre session a expiré. Veuillez rafraîchir la page.'], 419);
            }
            return redirect()->back()->with('error', 'Votre session a expiré. Le formulaire a été rechargé, veuillez réessayer.');
        });
    })->create();
