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
            return redirect()->route('superadmin.login');
        }

        if ($user->estSuperAdmin()) {
            return $next($request);
        }

        if ($user->estEquipe() && $user->must_change_password) {
            $autorises = ['superadmin.premiere-connexion', 'superadmin.premiere-connexion.post', 'superadmin.logout'];
            if (!in_array($request->route()->getName(), $autorises, true)) {
                return redirect()->route('superadmin.premiere-connexion')
                    ->with('info', 'Pour votre sécurité, vous devez définir votre propre mot de passe avant de continuer.');
            }
        }

        $chemins = explode('/', str_replace('superadmin/', '', trim($request->path(), '/')));
        $racine = $chemins[0] ?? '';

        foreach (self::CARTE as $motif => $roles) {
            if ($this->correspond($racine, $motif)) {
                foreach ($roles as $slug) {
                    if ($user->aRole($slug)) {
                        return $next($request);
                    }
                }
            }
        }

        return response()->view('errors.403-equipe', [], 403);
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
