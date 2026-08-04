<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\ReconciliationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcilierTickets extends Command
{
    protected $signature = 'tickets:reconcilier';

    protected $description = 'Vérifie via l\'API FedaPay les tickets en attente avec transaction et répare automatiquement les paiements aboutis';

    public function __construct(
        protected ReconciliationService $reconciliation,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $candidats = Ticket::where('statut_paiement', 'en_attente')
            ->whereNotNull('fedapay_transaction_id')
            ->with('evenement', 'tarif')
            ->get();

        // Regroupe par transaction FedaPay : une décision par paiement
        $groupes = $candidats->groupBy('fedapay_transaction_id');

        $confirmees = 0;
        $supprimes = 0;
        $conserves = 0;

        foreach ($groupes as $txId => $groupe) {
            $verification = $this->reconciliation->verifier($txId);

            if ($verification['ok'] && $verification['approuve']) {
                // Paiement abouti : confirmation automatique (auto-réparation)
                $res = $this->reconciliation->confirmerTickets($groupe, $txId, null, null, false);
                $confirmees += $res['confirmes'] ?? 0;
            } elseif ($verification['ok'] && in_array(
                $verification['statut'],
                ['declined', 'canceled', 'cancelled', 'cancel'],
                true
            )) {
                // Paiement définitivement échoué : nettoyage
                $this->reconciliation->supprimerGroupe($groupe, 'Paiement décliné (réconciliation automatique)');
                $supprimes += $groupe->count();
            } else {
                // Statut indéterminé : conservé pour le support
                Log::warning('Réconciliation automatique - statut indéterminé, tickets conservés', [
                    'transaction_id' => $txId,
                    'statut' => $verification['statut'] ?? null,
                ]);
                $conserves += $groupe->count();
            }
        }

        $this->info("{$confirmees} ticket(s) confirmé(s), {$supprimes} supprimé(s), {$conserves} conservé(s) (indéterminé).");
    }
}
