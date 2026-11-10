<?php

namespace App\Console\Commands;

use App\Models\VenteIdempotence;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PurgerIdempotencesVentes extends Command
{
    protected $signature = 'ventes:purger-idempotences {--days=30 : Âge minimal des clés à supprimer}';

    protected $description = "Purge les clés d'idempotence des ventes anciennes (les retries très tardifs redeviennent possibles)";

    public function handle(): int
    {
        $jours = max(1, (int) $this->option('days'));

        $supprimees = VenteIdempotence::query()
            ->where('created_at', '<', now()->subDays($jours))
            ->delete();

        Log::info('Purge des clés d\'idempotence de vente', [
            'jours' => $jours,
            'supprimees' => $supprimees,
        ]);

        $this->info("{$supprimees} clé(s) d'idempotence purgée(s).");

        return self::SUCCESS;
    }
}
