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
        $annulationModifiee = $ticket->wasChanged('annule') || $ticket->wasChanged('annule_at');
        $expirationModifiee = $ticket->wasChanged('expire_at');

        if (! $paiementModifie && ! $annulationModifiee && ! $expirationModifiee) {
            return;
        }

        $estPaye = $ticket->statut_paiement === 'payé'
            && ! $ticket->annule
            && $ticket->expire_at === null;

        // Un billet ne reçoit son code définitif qu'au moment où il devient
        // réellement délivrable. Avant cela il ne porte qu'un code de
        // réservation TMP- (voir Ticket::genererCodeReservation()).
        // updateQuietly évite de rappeler cet observer en boucle.
        if ($estPaye && ! $ticket->aUnCodeDefinitif()) {
            $ticket->updateQuietly(['code_unique' => Ticket::genererCodeSecurise()]);
        }

        if ($estPaye) {
            $this->affiliation->enregistrerPourTicket($ticket);

            return;
        }

        // Un billet qui n'est plus payé (remboursé, annulé, expiré, échoué) ne doit
        // plus porter de commission.
        if ($ticket->wasChanged('statut_paiement') || $ticket->annule || $ticket->expire_at !== null || $ticket->annule_at !== null) {
            $this->affiliation->annulerPourTicket($ticket);
        }
    }
}
