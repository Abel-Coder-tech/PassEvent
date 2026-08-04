<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\FedapayService;
use App\Services\ReconciliationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurgerTicketsEnAttente extends Command
{
    protected $signature = 'tickets:purger-en-attente';

    protected $description = 'Supprime les tickets manuels en attente de paiement abandonnés (source vente_manuelle) après vérification FedaPay';

    public function __construct(
        protected FedapayService $fedapay,
        protected ReconciliationService $reconciliation,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $candidats = Ticket::where('source', 'vente_manuelle')
            ->where('statut_paiement', 'en_attente')
            ->where('date_achat', '<', now()->subHours(2))
            ->with('evenement', 'tarif')
            ->get();

        // Regroupe par transaction groupée (GRP-xxxx) : une décision par groupe
        $groupes = $candidats->groupBy('transaction_id');

        $supprimes = 0;
        $confirmees = 0;
        $conserves = 0;

        foreach ($groupes as $groupe) {
            $transactionId = $groupe->first()->fedapay_transaction_id;

            // Aucune référence FedaPay connue : checkout jamais ouvert, abandon pur -> suppression sûre
            if (!$transactionId) {
                $supprimes += $this->supprimerGroupe($groupe);
                continue;
            }

            // Une transaction FedaPay existe : vérifier le statut réel avant toute décision
            $verification = $this->reconciliation->verifier($transactionId);

            if ($verification['ok'] && $verification['approuve']) {
                // Paiement réellement abouti mais ticket jamais confirmé (incident) : auto-réparation
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
                // Paiement définitivement échoué : suppression sûre
                $supprimes += $this->supprimerGroupe($groupe);
            } else {
                // Statut indéterminé (API injoignable, pending…) : on garde pour le support
                Log::warning('Purge en_attente - statut indéterminé, tickets conservés', [
                    'transaction_id' => $transactionId,
                    'statut' => $verification['statut'] ?? null,
                ]);
                $conserves += $groupe->count();
            }
        }

        $this->info("{$supprimes} ticket(s) supprimé(s), {$confirmees} confirmé(s) automatiquement, {$conserves} conservé(s) pour le support.");
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
