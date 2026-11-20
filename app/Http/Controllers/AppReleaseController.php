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
 * storage/app/private/apps/ et Nginx le sert via X-Accel-Redirect, donc PHP
 * ne charge jamais les ~70 Mo en mémoire.
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

        return response('', 200, [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Content-Disposition' => 'attachment; filename="'.$nom.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            // Nginx intercepte cet en-tête et sert le fichier lui-même.
            'X-Accel-Redirect' => '/protected/apps/'.$path,
        ]);
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
