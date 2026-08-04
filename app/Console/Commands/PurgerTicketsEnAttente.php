<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;

class PurgerTicketsEnAttente extends Command
{
    protected $signature = 'tickets:purger-en-attente';

    protected $description = 'Supprime les tickets manuels en attente de paiement abandonnés (source vente_manuelle)';

    public function handle()
    {
        $count = Ticket::where('source', 'vente_manuelle')
            ->where('statut_paiement', 'en_attente')
            ->where('date_achat', '<', now()->subHours(2))
            ->delete();

        $this->info("{$count} ticket(s) manuel(s) en attente supprimé(s).");
    }
}
