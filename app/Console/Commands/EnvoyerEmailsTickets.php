<?php

namespace App\Console\Commands;

use App\Jobs\SendTicketEmailJob;
use App\Models\Ticket;
use Illuminate\Console\Command;

class EnvoyerEmailsTickets extends Command
{
    protected $signature = 'tickets:envoyer-emails {--limit=200}';

    protected $description = 'Rattrapage : renvoie l\'email des billets payés dont la notification n\'a pas abouti (échec SMTP, worker absent)';

    public function handle()
    {
        $limite = max(1, (int) $this->option('limit'));

        // Billets payés jamais notifiés (échec ou jamais tenté), sous le plafond de tentatives.
        $tickets = Ticket::with('evenement', 'tarif')
            ->where('statut_paiement', 'payé')
            ->whereNull('email_envoye_at')
            ->where('email_tentatives', '<', 5)
            ->whereNotNull('email_acheteur')
            ->orderBy('date_achat')
            ->limit($limite)
            ->get();

        if ($tickets->isEmpty()) {
            $this->info('Aucun billet en attente de notification.');

            return;
        }

        // Un email par commande (regroupé par transaction), comme à l'achat.
        $groupes = $tickets->groupBy(fn (Ticket $t) => $t->transaction_id ?: 'TICKET-'.$t->id);

        $envoyes = 0;
        foreach ($groupes as $groupe) {
            SendTicketEmailJob::dispatchSync($groupe->pluck('id')->all(), $groupe->first()->email_acheteur);
            $envoyes++;
        }

        $this->info("{$envoyes} email(s) de billet renvoyé(s) (rattrapage).");
    }
}
