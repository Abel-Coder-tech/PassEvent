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
        // Terminé 12 h après le début de la DERNIÈRE session (la billetterie
        // reste ouverte le jour même ; un événement sur plusieurs jours n'est
        // pas clos après son premier jour).
        $count = 0;
        Evenement::where('statut', 'publié')
            ->where('date_event', '<', Evenement::seuilDebutEnVente())
            ->with('dates')
            ->each(function (Evenement $evenement) use (&$count) {
                if ($evenement->evenementPasse()) {
                    $evenement->update(['statut' => 'terminé']);
                    $count++;
                }
            });

        $this->info("{$count} événement(s) marqué(s) comme terminés.");
    }
}
