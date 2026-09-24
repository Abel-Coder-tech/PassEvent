<?php

namespace App\Console\Commands;

use App\Models\Consentement;
use App\Models\ParametreSite;
use Illuminate\Console\Command;

class ConsentementsPurger extends Command
{
    protected $signature = 'consentements:purger
        {--statut= : Ne supprimer que ce statut (accepte, refuse, personnalise)}
        {--sans-rotation : Supprimer les decisions sans changer le cookie de consentement}';

    protected $description = 'Supprime les decisions de consentement cookies et force les visiteurs a re-accepter (rotation du cookie TAC)';

    public function handle(): int
    {
        $statut = $this->option('statut');
        if ($statut && ! in_array($statut, ['accepte', 'refuse', 'personnalise'], true)) {
            $this->error("Statut invalide : {$statut} (accepte, refuse ou personnalise)");

            return self::FAILURE;
        }

        $avant = Consentement::count();
        $supprimes = $statut
            ? Consentement::where('statut', $statut)->delete()
            : Consentement::query()->delete();

        $this->info("{$supprimes} décision(s) de consentement supprimée(s) sur {$avant} enregistrée(s).");

        if ($this->option('sans-rotation')) {
            $this->warn('Aucune rotation : les visiteurs ayant deja choisi ne verront pas la modale.');

            return self::SUCCESS;
        }

        $version = date('Y-m-d');
        $cookie = 'tarteaucitron-'.date('YmdHis');

        ParametreSite::definir([
            'consentement_cookie' => $cookie,
            'consentement_version' => $version,
        ]);

        $this->info("Nouveau cookie de consentement : {$cookie}");
        $this->info("Version politique : {$version}");
        $this->info('La modale cookies s’affichera a nouveau pour tous les visiteurs.');

        return self::SUCCESS;
    }
}