<?php

namespace App\Observers;

use App\Models\Tarif;
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

    /**
     * Fige le jour du billet à partir du tarif au moment de la vente. Le jour
     * reste ainsi inchangé même si l'organisateur modifie le tarif ensuite.
     */
    public function creating(Ticket $ticket): void
    {
        if ($ticket->evenement_date_id !== null || ! $ticket->tarif_id) {
            return;
        }

        $ticket->evenement_date_id = Tarif::whereKey($ticket->tarif_id)->value('evenement_date_id');
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
