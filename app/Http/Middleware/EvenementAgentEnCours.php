<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Après l'événement, l'agent (scan ou vente) peut toujours se connecter et
 * consulter son tableau de bord, son historique, l'aide ou contacter
 * l'organisateur. Seules les actions (scanner, vendre, renvoyer ou annuler
 * un billet…) passent par ce contrôle et sont refusées.
 */
class EvenementAgentEnCours
{
    public function handle(Request $request, Closure $next): Response
    {
        $evenement = $request->user()?->evenement;

        if ($evenement && $evenement->evenementPasse()) {
            return response()->json([
                'message' => 'event_ended',
                'detail' => 'L\'événement est terminé : cette action n\'est plus possible. Vous pouvez toujours consulter votre tableau de bord.',
            ], 403);
        }

        return $next($request);
    }
}
