<?php

namespace App\Http\Middleware;

use App\Models\ActionPaxevent;
use App\Services\GestionOrganisateur;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mode « Gérer l'organisateur » : PaxEvent agit au nom d'un organisateur.
 * Vérifie que la prise en main est valide, applique la liste des actions
 * autorisées et journalise chaque modification.
 */
class GestionOrganisateurMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $gestion = $request->session()->get('gestion');

        if (! $gestion) {
            return $next($request);
        }

        $admin = auth('superadmin')->user();
        $organisateur = Auth::guard('web')->user();
        $inactif = now()->timestamp - (int) ($gestion['activite'] ?? 0) > GestionOrganisateur::DUREE_INACTIVITE_SECONDES;

        if (! $admin || ! $organisateur
            || (int) $admin->id !== (int) $gestion['admin_id']
            || (int) $organisateur->id !== (int) $gestion['org_id']
            || ! $admin->peut('organisateurs.gerer')
            || ! $organisateur->mandat_actif
            || $inactif) {
            self::terminer($request);

            return redirect()->route('superadmin.login')
                ->with('error', 'La prise en main de l\'organisateur a pris fin. Reconnectez-vous pour continuer.');
        }

        $request->session()->put('gestion.activite', now()->timestamp);

        $route = $request->route()?->getName();
        $decision = GestionOrganisateur::decision($route, $request->method());

        if ($decision === GestionOrganisateur::REFUSE) {
            return redirect()->route('dashboard')
                ->with('error', 'Cette action est réservée à l\'organisateur : PaxEvent ne peut pas la faire en son nom.');
        }

        $reponse = $next($request);

        // Une action refusée (erreurs de saisie, ou refus explicite du contrôleur) n'est pas consignée.
        $refusee = $request->attributes->get('gestion_action_refusee', false)
            || in_array('errors', (array) $request->session()->get('_flash.new', []), true);

        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD') && $reponse->getStatusCode() < 400 && ! $refusee) {
            ActionPaxevent::create([
                'user_id' => $organisateur->id,
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'methode' => $request->method(),
                'route' => $route,
                'resume' => GestionOrganisateur::libelle($route, $request->method()),
                'ip' => $request->ip(),
            ]);
        }

        return $reponse;
    }

    // Met fin à la prise en main sans toucher à la session du superadmin.
    public static function terminer(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->forget(['gestion', 'profil']);
    }
}
