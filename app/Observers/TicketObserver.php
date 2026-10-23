<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Services\AffiliationService;

/**
 * Génère (ou annule) les commissions d'affiliation à chaque changement de statut
 * de paiement d'un billet, quel que soit le canal de vente.
 */
class TicketObserver
{
    public function __construct(private AffiliationService $affiliation)
    {
    }

    public function saved(Ticket $ticket): void
    {
        $paiementModifie = $ticket->wasRecentlyCreated || $ticket->wasChanged('statut_paiement');
        $annulationModifiee = $ticket->wasChanged('annule');

        if (! $paiementModifie && ! $annulationModifiee) {
            return;
        }

        $estPaye = $ticket->statut_paiement === 'payé' && ! $ticket->annule;

        if ($estPaye) {
            $this->affiliation->enregistrerPourTicket($ticket);

            return;
        }

        // Un billet qui n'est plus payé (remboursé, annulé, échoué) ne doit
        // plus porter de commission.
        if ($ticket->wasChanged('statut_paiement') || $ticket->annule) {
            $this->affiliation->annulerPourTicket($ticket);
        }
    }
}
