<?php

namespace App\Console\Commands;

use App\Models\Evenement;
use Illuminate\Console\Command;

class TerminerEvenementsPasses extends Command
{
    protected $signature = 'evenements:terminer';

    protected $description = 'Marque les événements passés comme terminés';

    public function handle()
    {
        $count = Evenement::where('statut', 'publié')
            ->where('date_event', '<', now()->subHours(6))
            ->update(['statut' => 'terminé']);

        $this->info("{$count} événement(s) marqué(s) comme terminés.");
    }
}
