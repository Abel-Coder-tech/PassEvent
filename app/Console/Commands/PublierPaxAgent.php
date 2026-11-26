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
 *   php artisan paxagent:publier /tmp/app-release.apk --nom-version=1.0.0 --build=7 \
 *       --notes="Code d'accès à 4 chiffres"
 */
class PublierPaxAgent extends Command
{
    protected $signature = 'paxagent:publier
        {apk : Chemin du fichier .apk signé (build release)}
        {--nom-version= : Nom de version, ex. 1.0.0 (pubspec.yaml, avant le +)}
        {--build= : Numéro de build, ex. 7 (pubspec.yaml, après le +)}
        {--package=com.example.pax_scan : applicationId Android}
        {--notes= : Nouveautés de la version}
        {--obligatoire : Mise à jour obligatoire}
        {--brouillon : Enregistrer sans publier}';

    protected $description = 'Publie une version de l\'APK PaxAgent pour le dashboard et les mails agents';

    public function handle(): int
    {
        $source = (string) $this->argument('apk');
        $version = trim((string) $this->option('nom-version'));
        $build = (int) $this->option('build');

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

        if (! preg_match('/^\d+\.\d+\.\d+$/', $version) || $build < 1) {
            $this->error('Indiquez --nom-version=X.Y.Z et --build=N (voir « version: » dans pubspec.yaml).');

            return self::FAILURE;
        }

        if (AppRelease::where('platform', 'android')->where('version_code', $build)->exists()) {
            $this->error("Le build {$build} est déjà publié. Incrémentez le numéro après le + dans pubspec.yaml.");

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
