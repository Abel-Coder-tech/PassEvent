<?php

namespace App\Console\Commands;

use App\Support\ProfileTypeNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormaliserTypesProfils extends Command
{
    protected $signature = 'profils:normaliser-types
        {--dry-run : Afficher les changements prévus sans rien modifier}';

    protected $description = 'Remplace les anciens types de profils (professionnel, entreprise, association, club) par le vrai type de profil organisateur';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $modifies = 0;
        $inspects = 0;

        DB::table('users')
            ->where('role', 'admin')
            ->whereNotNull('type')
            ->orderBy('id')
            ->chunkById(200, function ($utilisateurs) use ($dryRun, &$modifies, &$inspects) {
                foreach ($utilisateurs as $utilisateur) {
                    $inspects++;

                    [$type, $detail] = ProfileTypeNormalizer::normaliser(
                        $utilisateur->type,
                        $utilisateur->type_detail
                    );

                    if ($type === $utilisateur->type && $detail === $utilisateur->type_detail) {
                        continue;
                    }

                    $this->line(sprintf(
                        'Utilisateur #%d (%s) : %s/%s → %s/%s',
                        $utilisateur->id,
                        $utilisateur->email,
                        $utilisateur->type ?? 'null',
                        $utilisateur->type_detail ?? 'null',
                        $type ?? 'null',
                        $detail ?? 'null'
                    ));

                    if (! $dryRun) {
                        DB::table('users')->where('id', $utilisateur->id)->update([
                            'type' => $type,
                            'type_detail' => $detail,
                            'updated_at' => now(),
                        ]);
                    }

                    $modifies++;
                }
            });

        $this->info(sprintf(
            '%s %d profil(s) sur %d examiner(s).',
            $dryRun ? 'À modifier :' : 'Modifié(s) :',
            $modifies,
            $inspects
        ));

        return self::SUCCESS;
    }
}
