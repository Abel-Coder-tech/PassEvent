<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\FedapayService;
use App\Services\ReconciliationService;
use App\Services\WaitlistPromotionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurgerTicketsEnAttente extends Command
{
    protected $signature = 'tickets:purger-en-attente';

    protected $description = 'Libère les réservations en ligne expirées (achat public non payé) et supprime les tickets manuels en attente abandonnés, après vérification FedaPay';

    public function __construct(
        protected FedapayService $fedapay,
        protected ReconciliationService $reconciliation,
        protected WaitlistPromotionService $promotion,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $liberees = $this->purgerReservationsEnLigne();
        $supprimes = $this->purgerManuels();

        $this->info("{$liberees} réservation(s) en ligne expirée(s) libérée(s), {$supprimes} ticket(s) manuel(s) supprimé(s).");
    }

    /**
     * Libère les réservations en ligne (achat public) dont la durée de réservation est expirée,
     * puis relance la file d'attente pour proposer les places aux suivants.
     */
    protected function purgerReservationsEnLigne(): int
    {
        $reservations = Ticket::whereNotNull('reservation_expire_le')
            ->where('reservation_expire_le', '<', now())
            ->where('statut_paiement', 'en_attente')
            ->with('evenement')
            ->get();

        $liberees = 0;
        $confirmees = 0;
        $conserves = 0;

        foreach ($reservations->groupBy('evenement_id') as $evenementId => $groupe) {
            $promotionNecessaire = false;

            foreach ($groupe->groupBy('transaction_id') as $groupeTickets) {
                $fedapayId = $groupeTickets->first()->fedapay_transaction_id;

                // Une transaction FedaPay existe : vérifier son statut réel avant toute décision
                if ($fedapayId) {
                    $verification = $this->reconciliation->verifier($fedapayId);

                    if ($verification['ok'] && $verification['approuve']) {
                        // Paiement abouti mais jamais confirmé : auto-réparation.
                        // La place est DÉJÀ comptée dans quota_vendu par la réservation en ligne,
                        // donc on confirme sans re-compter (évite le double comptage).
                        foreach ($groupeTickets as $t) {
                            $t->update([
                                'statut_paiement' => 'payé',
                                'reservation_expire_le' => null,
                            ]);
                        }
                        $confirmees += $groupeTickets->count();
                        $conserves += $groupeTickets->count();
                        continue;
                    }

                    if ($verification['ok'] && in_array(
                        $verification['statut'],
                        ['declined', 'canceled', 'cancelled', 'cancel'],
                        true
                    )) {
                        $this->libererGroupe($groupeTickets);
                        $liberees += $groupeTickets->count();
                        $promotionNecessaire = true;
                        continue;
                    }

                    // Statut indéterminé : on garde pour le support
                    Log::warning('Purge réservation en ligne - statut indéterminé, conservé', [
                        'transaction_id' => $fedapayId,
                        'statut' => $verification['statut'] ?? null,
                    ]);
                    $conserves += $groupeTickets->count();
                    continue;
                }

                // Aucune transaction : abandon pur avant d'ouvrir FedaPay -> libérer la place
                $this->libererGroupe($groupeTickets);
                $liberees += $groupeTickets->count();
                $promotionNecessaire = true;
            }

            if ($promotionNecessaire) {
                $this->promotion->promouvoirEvenement((int) $evenementId);
            }
        }

        if ($liberees || $confirmees || $conserves) {
            Log::info("Purge réservations en ligne : {$liberees} libérées, {$confirmees} confirmées, {$conserves} conservées.");
        }

        return $liberees;
    }

    /**
     * Libère atomiquement la place d'un ticket en_attente expiré : décrémente le quota
     * (la place avait été réservée à l'achat) et marque le ticket comme échoué.
     */
    protected function libererGroupe($groupeTickets): void
    {
        DB::transaction(function () use ($groupeTickets) {
            foreach ($groupeTickets as $ticket) {
                // Verrouille et re-vérifie pour éviter de libérer deux fois une même place
                $locked = Ticket::whereKey($ticket->id)->lockForUpdate()->first();
                if (! $locked || $locked->statut_paiement !== 'en_attente' || ! $locked->reservation_expire_le) {
                    continue;
                }

                $evenement = $locked->evenement()->lockForUpdate()->first();
                if ($evenement) {
                    $evenement->decrement('quota_vendu', max(1, (int) $locked->quantite));
                }

                $locked->update([
                    'statut_paiement' => 'échoué',
                    'reservation_expire_le' => null,
                ]);
            }
        });
    }

    /**
     * Logique existante : purge des tickets manuels abandonnés (source vente_manuelle).
     */
    protected function purgerManuels(): int
    {
        $candidats = Ticket::where('source', 'vente_manuelle')
            ->where('statut_paiement', 'en_attente')
            ->where('date_achat', '<', now()->subHours(2))
            ->with('evenement', 'tarif')
            ->get();

        $groupes = $candidats->groupBy('transaction_id');

        $supprimes = 0;
        $confirmees = 0;
        $conserves = 0;

        foreach ($groupes as $groupe) {
            $transactionId = $groupe->first()->fedapay_transaction_id;

            if (! $transactionId) {
                $supprimes += $this->supprimerGroupe($groupe);
                continue;
            }

            $verification = $this->reconciliation->verifier($transactionId);

            if ($verification['ok'] && $verification['approuve']) {
                $resultat = $this->reconciliation->confirmerTickets(
                    $groupe,
                    $transactionId,
                    null,
                    null,
                    false
                );
                $confirmees += $resultat['confirmes'] ?? 0;
                $conserves += $groupe->count();
            } elseif ($verification['ok'] && in_array(
                $verification['statut'],
                ['declined', 'canceled', 'cancelled', 'cancel'],
                true
            )) {
                $supprimes += $this->supprimerGroupe($groupe);
            } else {
                Log::warning('Purge en_attente - statut indéterminé, tickets conservés', [
                    'transaction_id' => $transactionId,
                    'statut' => $verification['statut'] ?? null,
                ]);
                $conserves += $groupe->count();
            }
        }

        Log::info("Purge manuels : {$supprimes} supprimés, {$confirmees} confirmés, {$conserves} conservés.");

        return $supprimes;
    }

    protected function supprimerGroupe($groupe): int
    {
        foreach ($groupe as $ticket) {
            try {
                $ticket->delete();
            } catch (\Exception $e) {
                Log::error('Purge en_attente - échec suppression ticket ' . $ticket->id . ' : ' . $e->getMessage());
            }
        }
        return $groupe->count();
    }
}
