<?php

namespace App\Console\Commands;

use App\Services\AffiliationService;
use Illuminate\Console\Command;

class LibererCommissionsAffiliation extends Command
{
    protected $signature = 'affiliations:liberer-commissions';

    protected $description = 'Libère les commissions d\'affiliation arrivées à maturité';

    public function handle(AffiliationService $affiliation): int
    {
        $count = $affiliation->libererEcheues();

        $this->info("{$count} commission(s) d'affiliation libérée(s).");

        return self::SUCCESS;
    }
}
