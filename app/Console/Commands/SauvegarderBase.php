<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Sauvegarde quotidienne de la base MySQL dans storage/app/private/backups
 * (hors du dossier public), compressée, avec rotation.
 *
 * Complète les sauvegardes de l'hébergeur : une copie récente est toujours
 * disponible sur le serveur pour restaurer un compte ou une table.
 */
class SauvegarderBase extends Command
{
    protected $signature = 'paxevent:sauvegarder-base {--garder=14 : Nombre de jours de sauvegardes conservées}';

    protected $description = 'Sauvegarde la base de données (mysqldump compressé) et supprime les anciennes copies';

    public function handle(): int
    {
        $connexion = config('database.connections.'.config('database.default'));
        if (($connexion['driver'] ?? null) !== 'mysql' && ($connexion['driver'] ?? null) !== 'mariadb') {
            $this->error('Sauvegarde disponible uniquement pour MySQL/MariaDB.');

            return self::FAILURE;
        }

        $dossier = storage_path('app/private/backups');
        File::ensureDirectoryExists($dossier, 0700);

        $fichier = $dossier.'/paxevent-'.now()->format('Y-m-d_His').'.sql.gz';

        // Le mot de passe passe par un fichier d'options temporaire (jamais sur la ligne
        // de commande, où il serait visible dans la liste des processus).
        $options = tempnam(sys_get_temp_dir(), 'pxdump');
        file_put_contents($options, sprintf(
            "[client]\nuser=%s\npassword=%s\nhost=%s\nport=%s\n",
            $connexion['username'],
            str_replace(["\\", '"'], ["\\\\", '\\"'], (string) $connexion['password']),
            $connexion['host'] ?? '127.0.0.1',
            $connexion['port'] ?? 3306,
        ));
        chmod($options, 0600);

        try {
            // pipefail : un échec de mysqldump fait échouer toute la commande (pas un .gz vide)
            $process = new Process(
                ['bash', '-c', 'set -o pipefail; mysqldump --defaults-extra-file="$OPTIONS" --single-transaction --quick --routines --no-tablespaces "$BASE" | gzip -9 > "$FICHIER"'],
                null,
                ['OPTIONS' => $options, 'BASE' => $connexion['database'], 'FICHIER' => $fichier],
                null,
                1800,
            );
            $process->run();
        } finally {
            @unlink($options);
        }

        if (! $process->isSuccessful() || ! is_file($fichier) || filesize($fichier) < 100) {
            @unlink($fichier);
            Log::critical('Sauvegarde de la base échouée', ['erreur' => mb_substr($process->getErrorOutput(), 0, 500)]);
            $this->error('Sauvegarde échouée : '.$process->getErrorOutput());

            return self::FAILURE;
        }

        chmod($fichier, 0600);

        // Rotation : on supprime les sauvegardes plus anciennes que --garder jours
        $garder = max(1, (int) $this->option('garder'));
        $supprimees = 0;
        foreach (File::glob($dossier.'/paxevent-*.sql.gz') as $ancien) {
            if (filemtime($ancien) < now()->subDays($garder)->getTimestamp()) {
                File::delete($ancien);
                $supprimees++;
            }
        }

        $taille = round(filesize($fichier) / 1024 / 1024, 2);
        Log::info('Sauvegarde de la base réussie', ['fichier' => basename($fichier), 'mo' => $taille, 'anciennes_supprimees' => $supprimees]);
        $this->info("Sauvegarde créée : ".basename($fichier)." ({$taille} Mo). {$supprimees} ancienne(s) supprimée(s).");

        return self::SUCCESS;
    }
}
