<?php

namespace App\Console\Commands;

use App\Models\AppRelease;
use Illuminate\Console\Command;

/**
 * Publie une nouvelle version de l'APK PaxAgent.
 *
 * Copie le fichier dans storage/app/private/apps (hors webroot), calcule son
 * empreinte SHA-256 et enregistre la version. Le dashboard organisateur et
 * les mails des nouveaux agents proposent ensuite automatiquement la
 * dernière version publiée.
 *
 * Exemple :
 *   php artisan paxagent:publier ~/app-release.apk --notes="Code d'accès à 4 chiffres"
 *
 * La version et le numéro de build sont lus DANS l'APK : impossible de
 * publier un fichier sous un autre numéro que le sien (sinon l'appli
 * installée proposerait la mise à jour en boucle). --nom-version / --build
 * restent acceptés et doivent alors correspondre exactement à l'APK.
 */
class PublierPaxAgent extends Command
{
    protected $signature = 'paxagent:publier
        {apk : Chemin du fichier .apk signé (build release)}
        {--nom-version= : Nom de version (facultatif, lu dans le fichier)}
        {--build= : Numéro de build (facultatif, lu dans le fichier)}
        {--package=com.paxevent.agent : applicationId Android attendu}
        {--notes= : Nouveautés de la version}
        {--obligatoire : Mise à jour obligatoire}
        {--brouillon : Enregistrer sans publier}';

    protected $description = 'Publie une version de l\'APK PaxAgent pour le dashboard et les mails agents';

    public function handle(): int
    {
        $source = (string) $this->argument('apk');

        if (! is_file($source) || strtolower(pathinfo($source, PATHINFO_EXTENSION)) !== 'apk') {
            $this->error("Fichier APK introuvable : {$source}");

            return self::FAILURE;
        }

        // Un APK est une archive ZIP : il commence par « PK ».
        $entete = (string) file_get_contents($source, false, null, 0, 2);
        if ($entete !== 'PK') {
            $this->error('Ce fichier n\'est pas un APK valide.');

            return self::FAILURE;
        }

        $manifeste = \App\Support\ApkManifest::lire($source);
        if ($manifeste === null || ! $manifeste['version_code'] || ! $manifeste['version_name']) {
            $this->error('Impossible de lire la version dans cet APK (AndroidManifest illisible).');

            return self::FAILURE;
        }
        $build = (int) $manifeste['version_code'];
        $version = (string) $manifeste['version_name'];
        $this->line("APK : {$manifeste['package']} — version {$version} (build {$build})");

        $attendu = (string) $this->option('package');
        if ($manifeste['package'] !== $attendu) {
            $this->error("Cet APK est « {$manifeste['package']} », pas « {$attendu} ».");

            return self::FAILURE;
        }
        if ($this->option('build') !== null && (int) $this->option('build') !== $build) {
            $this->error("--build={$this->option('build')} ne correspond pas à l'APK, qui est la build {$build}. "
                .'Reconstruisez l\'APK après avoir modifié pubspec.yaml, ou retirez --build.');

            return self::FAILURE;
        }
        if ($this->option('nom-version') !== null && trim((string) $this->option('nom-version')) !== $version) {
            $this->error("--nom-version={$this->option('nom-version')} ne correspond pas à l'APK ({$version}).");

            return self::FAILURE;
        }

        $derniere = AppRelease::where('platform', 'android')->max('version_code');
        if ($derniere !== null && $build <= (int) $derniere) {
            $this->error("Cet APK est la build {$build}, mais la build {$derniere} est déjà publiée : les téléphones ne "
                .'la verraient pas comme une mise à jour. Augmentez le numéro après le + dans pubspec.yaml et reconstruisez.');

            return self::FAILURE;
        }


        $dossier = storage_path('app/private/apps');
        if (! is_dir($dossier) && ! mkdir($dossier, 0750, true) && ! is_dir($dossier)) {
            $this->error("Impossible de créer {$dossier}.");

            return self::FAILURE;
        }

        $nom = sprintf('PaxAgent-%s-%d.apk', $version, $build);
        $cible = $dossier.DIRECTORY_SEPARATOR.$nom;

        if (! copy($source, $cible)) {
            $this->error("Copie impossible vers {$cible}.");

            return self::FAILURE;
        }
        @chmod($cible, 0640);

        $release = AppRelease::create([
            'platform' => 'android',
            'package_name' => (string) $this->option('package'),
            'version_code' => $build,
            'version_name' => $version,
            'sha256' => hash_file('sha256', $cible),
            'size_bytes' => filesize($cible),
            'path' => $nom,
            'notes' => $this->option('notes') ?: null,
            'mandatory' => (bool) $this->option('obligatoire'),
            'published_at' => $this->option('brouillon') ? null : now(),
        ]);

        $this->info(sprintf(
            'PaxAgent %s (build %d) %s — %.1f Mo, SHA-256 %s',
            $release->version_name,
            $release->version_code,
            $release->published_at ? 'publié' : 'enregistré en brouillon',
            $release->size_bytes / 1048576,
            $release->sha256,
        ));

        return self::SUCCESS;
    }
}
