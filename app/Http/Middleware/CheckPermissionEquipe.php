<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionEquipe
{
    private const CARTE = [
        'organisateurs*' => ['validateur'],
        'retraits*' => ['validateur'],
        'remboursements*' => ['validateur'],
        'notifications*' => ['support_client', 'assistant_technique'],
        'support*' => ['assistant_technique'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('superadmin')->user();

        if (!$user) {
            return redirect()->route($request->is('equipe') || $request->is('equipe/*')
                ? 'equipe.login'
                : 'superadmin.login');
        }

        if ($user->estSuperAdmin()) {
            return $next($request);
        }

        if ($user->must_change_password) {
            $autorises = [
                'superadmin.premiere-connexion', 'superadmin.premiere-connexion.post',
                'equipe.premiere-connexion', 'equipe.premiere-connexion.post',
                'equipe.portail', 'superadmin.logout',
            ];
            if (!in_array($request->route()?->getName(), $autorises, true)) {
                return redirect()->route('equipe.premiere-connexion')
                    ->with('info', 'Pour votre sécurité, vous devez définir votre propre mot de passe avant de continuer.');
            }
        }

        $parts = explode('/', trim($request->path(), '/'));
        $prefixe = $parts[0] ?? '';
        $racine = $parts[1] ?? '';

        // Espace equipe (/equipe/...) : pages dediees aux membres
        if ($prefixe === 'equipe') {
            if ($this->autorise($user, $racine)) {
                return $next($request);
            }

            return response()->view('errors.403-equipe', [], 403);
        }

        // Espace admin (/superadmin/...) :
        // - les actions POST/PUT/DELETE des formulaires restent autorisees selon la carte ;
        // - les navigations GET sont redirigees vers la page equivalente de l'espace equipe.
        if (!$request->isMethod('GET')) {
            if ($this->autorise($user, $racine)) {
                return $next($request);
            }

            return response()->view('errors.403-equipe', [], 403);
        }

        $cible = $this->pageEquipe($racine, $parts[2] ?? '');

        if ($cible === null || !$this->autorise($user, $racine)) {
            return response()->view('errors.403-equipe', [], 403);
        }

        $redirection = route($cible[0], $cible[1]);
        if ($query = $request->getQueryString()) {
            $redirection .= '?' . $query;
        }

        return redirect()->to($redirection);
    }

    private function autorise($user, string $racine): bool
    {
        // Le tableau de bord est accessible a tout membre connecte : son contenu est deja filtre par role.
        if ($racine === '' || $racine === 'dashboard') {
            return true;
        }

        foreach (self::CARTE as $motif => $roles) {
            if ($this->correspond($racine, $motif)) {
                foreach ($roles as $slug) {
                    if ($user->aRole($slug)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    // Correspondance d'une page /superadmin/<module> vers son equivalente /equipe/<module>.
    // Retourne null si la page n'a pas d'equivalent membre (zone reservee au proprietaire).
    private function pageEquipe(string $racine, string $sousSegment): ?array
    {
        switch ($racine) {
            case '':
            case 'dashboard':
                return ['equipe.dashboard', []];

            case 'organisateurs':
                if ($sousSegment === '') {
                    return ['equipe.organisateurs', []];
                }

                return is_numeric($sousSegment)
                    ? ['equipe.organisateurs.voir', ['user' => $sousSegment]]
                    : null;

            case 'retraits':
                return ['equipe.retraits', []];

            case 'remboursements':
                if ($sousSegment === '') {
                    return ['equipe.remboursements.demandes', []];
                }

                return is_numeric($sousSegment)
                    ? ['equipe.remboursements.voir', ['demande' => $sousSegment]]
                    : null;

            case 'notifications':
                return ['equipe.notifications', []];

            case 'support':
                return ['equipe.support', []];

            default:
                return null;
        }
    }

    private function correspond(string $valeur, string $motif): bool
    {
        if ($motif === $valeur) {
            return true;
        }

        if (str_ends_with($motif, '*')) {
            return str_starts_with($valeur, rtrim($motif, '*'));
        }

        return false;
    }
}
