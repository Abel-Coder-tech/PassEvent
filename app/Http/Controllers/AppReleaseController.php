<?php

namespace App\Http\Controllers;

use App\Models\AppRelease;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Diffusion de l'APK PaxAgent.
 *
 * Le fichier n'est jamais dans public/ : il vit dans
 * storage/app/private/apps/. Il est envoyé par morceaux (Apache), ou servi
 * par Nginx via X-Accel-Redirect si PAXAGENT_X_ACCEL=true.
 */
class AppReleaseController extends Controller
{
    public function download(Request $request, AppRelease $release): Response
    {
        // Lien signé et expirant : impossible de le partager durablement.
        abort_unless($request->hasValidSignature(), 403, 'Lien de téléchargement expiré.');

        // Un organisateur ne voit que les versions Android publiées.
        abort_unless($release->platform === 'android', 404);
        abort_unless($release->published_at?->isPast(), 404);
        abort_unless($request->user()?->can('download-app'), 403);

        // Le lien est lié à son destinataire : une copie envoyée à un autre
        // utilisateur connecté ne fonctionne pas, même avant expiration.
        abort_unless(
            $request->query('uid') !== null
                && (int) $request->query('uid') === (int) $request->user()->getAuthIdentifier(),
            403,
            'Ce lien n\'est pas destiné à votre compte.',
        );

        return $this->servir($release);
    }

    /**
     * Lien envoyé dans le mail de création d'un agent.
     *
     * L'agent n'a pas encore l'appli, donc pas de session : le lien signé
     * (7 jours) suffit. Il sert toujours la dernière version publiée et
     * cesse de fonctionner si l'agent est supprimé ou désactivé.
     */
    public function downloadForAgent(Request $request, string $type, int $agent): Response
    {
        abort_unless($request->hasValidSignature(), 403, 'Lien de téléchargement expiré.');

        $modele = match ($type) {
            'scan' => \App\Models\Agent::class,
            'vente' => \App\Models\AgentVente::class,
            default => abort(404),
        };

        $compte = $modele::find($agent);
        abort_unless($compte !== null && $compte->actif, 403, 'Ce compte agent n\'est plus actif.');

        $release = AppRelease::latest();
        abort_if($release === null, 404, 'Aucune version de PaxAgent n\'est disponible.');

        return $this->servir($release);
    }

    /** Lien à mettre dans le mail d'un agent, ou null si aucune version n'est publiée. */
    public static function lienPourAgent(string $type, int $agentId): ?string
    {
        if (AppRelease::latest() === null) {
            return null;
        }

        return URL::temporarySignedRoute(
            'app.download.agent',
            now()->addDays(7),
            ['type' => $type, 'agent' => $agentId],
        );
    }

    private function servir(AppRelease $release): Response
    {
        // `path` ne doit contenir qu'un nom de fichier, jamais un chemin.
        $path = basename((string) $release->path);
        abort_unless($path !== '' && $path === $release->path, 404);

        $file = storage_path('app/private/apps/'.$path);
        abort_unless(is_file($file), 404);

        $nom = sprintf(
            'PaxAgent-%s-v%d.apk',
            $release->version_name,
            $release->version_code,
        );

        $entetes = [
            'Content-Type' => 'application/vnd.android.package-archive',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ];

        if (config('app.paxagent_x_accel')) {
            // Nginx intercepte cet en-tête et sert le fichier lui-même.
            return response('', 200, $entetes + [
                'Content-Disposition' => 'attachment; filename="'.$nom.'"',
                'X-Accel-Redirect' => '/protected/apps/'.$path,
            ]);
        }

        // Apache (o2switch) : envoi par morceaux, le fichier n'est jamais
        // chargé entièrement en mémoire.
        return response()->download($file, $nom, $entetes);
    }

    /**
     * Version à jour pour l'application (agent authentifié).
     *
     * Renvoie un lien signé de courte durée : l'app compare `version_code`
     * avec le sien, puis télécharge et vérifie `sha256` avant d'ouvrir
     * l'installeur.
     */
    public function latest(Request $request): JsonResponse
    {
        $release = AppRelease::latest();
        abort_if($release === null, 404);

        return response()->json([
            'version_code' => $release->version_code,
            'version_name' => $release->version_name,
            'mandatory' => $release->mandatory,
            'notes' => $release->notes,
            'published_at' => $release->published_at?->toIso8601String(),
            'sha256' => $release->sha256,
            'size_bytes' => $release->size_bytes,
            'url' => URL::temporarySignedRoute(
                'app.download',
                now()->addMinutes(15),
                [
                    'release' => $release->id,
                    'uid' => $request->user()->getAuthIdentifier(),
                ],
            ),
        ]);
    }
}
