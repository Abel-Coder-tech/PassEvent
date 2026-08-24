<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionEquipe
{
    // Correspondance [module@action] => cles d'acces acceptees (au moins une suffit).
    // Absent de la carte = reserve au proprietaire.
    private const ACCES_REQUIS = [
        'organisateurs@liste' => ['organisateurs.consulter'],
        'organisateurs@voir' => ['organisateurs.consulter'],
        'organisateurs@approuver' => ['organisateurs.valider'],
        'organisateurs@rejeter' => ['organisateurs.valider'],
        'organisateurs@corrections' => ['organisateurs.valider'],
        'organisateurs@suspendre' => ['organisateurs.suspendre'],
        'organisateurs@reactiver' => ['organisateurs.suspendre'],
        'organisateurs@controles' => ['organisateurs.valider'],
        'organisateurs@supprimer' => ['organisateurs.supprimer'],

        'retraits@liste' => ['retraits.consulter'],
        'retraits@approuver' => ['retraits.traiter'],
        'retraits@confirmer' => ['retraits.traiter'],
        'retraits@rejeter' => ['retraits.rejeter'],

        'remboursements@liste' => ['remboursements.consulter'],
        'remboursements@voir' => ['remboursements.consulter'],
        'remboursements@approuver' => ['remboursements.traiter'],
        'remboursements@confirmer' => ['remboursements.traiter'],
        'remboursements@refuser' => ['remboursements.traiter'],

        'notifications@liste' => ['notifications.consulter'],
        'notifications@lire' => ['notifications.repondre', 'notifications.supprimer'],
        'notifications@repondre' => ['notifications.repondre'],
        'notifications@supprimer' => ['notifications.supprimer'],

        'support@liste' => ['support.consulter'],
        'support@tarifs' => ['support.verifier', 'support.recreeer'],
        'support@incident-message' => ['support.consulter'],
        'support@verifier' => ['support.verifier'],
        'support@confirmer' => ['support.confirmer'],
        'support@recreeer' => ['support.recreeer'],
        'support@renvoyer-email' => ['support.renvoyer'],
        'support@rembourser' => ['support.rembourser'],
        'support@supprimer' => ['support.supprimer'],
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
        $module = $parts[1] ?? '';

        // Espace equipe (/equipe/...) : pages dediees aux membres
        if ($prefixe === 'equipe') {
            if ($this->autorise($user, $request, $parts)) {
                return $next($request);
            }

            return response()->view('errors.403-equipe', [], 403);
        }

        // Espace admin (/superadmin/...) :
        // - les actions POST/PUT/DELETE des formulaires restent partagees et filtres par acces ;
        // - les navigations GET sont redirigees vers la page equivalente de l'espace equipe.
        if (!$request->isMethod('GET')) {
            if ($this->autorise($user, $request, $parts)) {
                return $next($request);
            }

            return response()->view('errors.403-equipe', [], 403);
        }

        $cible = $this->pageEquipe($module, $parts[2] ?? '');

        if ($cible === null || !$this->autorise($user, $request, $parts)) {
            return response()->view('errors.403-equipe', [], 403);
        }

        $redirection = route($cible[0], $cible[1]);
        if ($query = $request->getQueryString()) {
            $redirection .= '?' . $query;
        }

        return redirect()->to($redirection);
    }

    // Derive module@action puis verifie les acces fins du membre
    private function autorise($user, Request $request, array $parts): bool
    {
        $module = $parts[1] ?? '';
        $segment2 = $parts[2] ?? '';
        $segment3 = $parts[3] ?? '';

        // Le tableau de bord est accessible a tout membre connecte (contenu deja filtre par role)
        if ($module === '' || $module === 'dashboard') {
            return true;
        }

        $action = $this->resoudreAction($request, $module, $segment2, $segment3);
        if ($action === null) {
            return false;
        }

        $cles = self::ACCES_REQUIS["$module@$action"] ?? null;

        return $cles !== null && $user->peut(...$cles);
    }

    private function resoudreAction(Request $request, string $module, string $segment2, string $segment3): ?string
    {
        // /module/{id}/action ou /module/action
        if ($segment2 !== '' && !ctype_digit((string) $segment2)) {
            return $segment2;
        }

        if ($segment3 !== '') {
            return $segment3;
        }

        if ($segment2 !== '') {
            // identifiant present sans action : voir (GET) ou supprimer (DELETE)
            if ($request->isMethod('DELETE')) {
                return 'supprimer';
            }

            return 'voir';
        }

        return 'liste';
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
}
