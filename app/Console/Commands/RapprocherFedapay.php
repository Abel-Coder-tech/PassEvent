<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\FedapayService;
use App\Services\PaiementMapper;
use App\Services\PaymentMonitoringService;
use App\Services\ReconciliationService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Filet de sécurité indépendant du webhook.
 *
 * Une vente publique n'enregistre l'ID FedaPay que si le retour navigateur ou
 * le webhook aboutit. Quand les deux échouent, le billet reste sans ID et la
 * purge finit par libérer la place alors que l'argent a été encaissé. Cette
 * commande balaie les transactions approuvées chez FedaPay et confirme les
 * billets correspondants, même sans ID enregistré.
 */
class RapprocherFedapay extends Command
{
    protected $signature = 'paiements:rapprocher-fedapay
        {--heures=48 : Fenêtre de recherche des transactions approuvées}
        {--pages=5 : Nombre maximum de pages FedaPay parcourues}
        {--dry-run : Affiche les rapprochements sans rien écrire}';

    protected $description = 'Confirme les billets dont le paiement FedaPay est approuvé mais jamais enregistré (webhook/callback manquants)';

    public function __construct(
        protected FedapayService $fedapay,
        protected ReconciliationService $reconciliation,
        protected PaymentMonitoringService $paymentMonitoring,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $heures = max(1, (int) $this->option('heures'));
        $pages = max(1, (int) $this->option('pages'));
        $dryRun = (bool) $this->option('dry-run');

        $transactions = $this->fedapay->transactionsApprouvees(now()->subHours($heures), $pages);

        $this->info(count($transactions).' transaction(s) FedaPay approuvée(s) sur les '.$heures.' dernières heures.');

        $confirmes = 0;
        $dejaPayes = 0;
        $sansBillet = 0;
        $sansPlace = 0;
        $anomalies = 0;

        foreach ($transactions as $transaction) {
            $references = $this->fedapay->referencesTransaction($transaction);
            $transactionId = $references['id'];

            if ($transactionId === '') {
                continue;
            }

            // Les lots de QR codes ont leur propre flux (webhook/callback dédiés
            // et table lots_physiques) : ne pas les signaler comme orphelins.
            if ($this->estTransactionLotPhysique($references, $transaction)) {
                continue;
            }

            $tickets = $this->ticketsPour($references);

            if ($tickets->isEmpty()) {
                $sansBillet++;
                $this->line("  · {$transactionId} : aucun billet rattaché");

                if (! $dryRun) {
                    $this->signalerOrphelin($transactionId, $transaction);
                }

                continue;
            }

            $impayes = $tickets->filter(
                fn (Ticket $ticket) => $ticket->statut_paiement !== 'payé'
                    && ! $ticket->annule
                    && $ticket->expire_at === null
            );

            if ($impayes->isEmpty()) {
                $dejaPayes++;
                continue;
            }

            // Le montant payé couvre la commande entière, y compris un billet
            // déjà confirmé ou annulé : on compare donc au groupe complet.
            $montantAttendu = (float) $tickets->sum(fn (Ticket $ticket) => (float) $ticket->montant);
            $montantTransaction = (float) ($transaction['amount'] ?? 0);

            if ($montantTransaction <= 0 || abs($montantAttendu - $montantTransaction) >= 1) {
                $anomalies++;
                $this->warn("  · {$transactionId} : montant incohérent (FedaPay {$montantTransaction} F / billets {$montantAttendu} F)");

                if (! $dryRun) {
                    $this->paymentMonitoring->record(
                        'rapprochement_montant_incoherent',
                        [
                            'transaction_id' => $transactionId,
                            'expected_amount' => $montantAttendu,
                            'received_amount' => $montantTransaction,
                            'currency' => 'XOF',
                            'source' => 'rapprochement',
                        ],
                        null,
                        $impayes->first()->id,
                        'reconciliation',
                        'critical',
                    );
                }

                continue;
            }

            if ($dryRun) {
                $this->line("  · {$transactionId} : confirmerait {$impayes->count()} billet(s) pour {$montantTransaction} F");
                continue;
            }

            $mode = $this->modeTransaction($transaction);
            $resultat = $this->reconciliation->confirmerTickets(
                $impayes,
                $transactionId,
                PaiementMapper::operateur($mode),
                $this->telephoneTransaction($transaction),
            );

            if (! ($resultat['success'] ?? false)) {
                $anomalies++;
                $this->error("  · {$transactionId} : ".($resultat['message'] ?? 'confirmation impossible'));
                continue;
            }

            $confirmesIci = (int) ($resultat['confirmes'] ?? 0);
            $sansPlaceIci = (int) ($resultat['sans_place'] ?? 0);
            $confirmes += $confirmesIci;
            $sansPlace += $sansPlaceIci;
            $dejaPayes += (int) ($resultat['deja_payes'] ?? 0);

            if ($confirmesIci > 0) {
                $this->info("  · {$transactionId} : {$confirmesIci} billet(s) confirmé(s)");

                $this->paymentMonitoring->record(
                    'paiement_rapproche',
                    [
                        'transaction_id' => $transactionId,
                        'ticket_group' => $tickets->first()->transaction_id,
                        'confirmed_tickets' => $confirmesIci,
                        'amount' => $montantTransaction,
                        'currency' => 'XOF',
                        'source' => 'rapprochement',
                    ],
                    null,
                    $impayes->first()->id,
                    'reconciliation',
                    'info',
                );
            }

            if ($sansPlaceIci > 0) {
                $this->warn("  · {$transactionId} : {$sansPlaceIci} billet(s) non confirmé(s) faute de place");
                $this->signalerSansPlace($transactionId, $impayes, $montantTransaction);
            }
        }

        $this->info(
            "Rapprochement : {$confirmes} confirmé(s), {$dejaPayes} déjà payé(s), "
            ."{$sansBillet} sans billet, {$sansPlace} sans place, {$anomalies} anomalie(s)."
        );

        return self::SUCCESS;
    }

    /**
     * Détecte les transactions du flux « lots de QR codes », traitées par le
     * webhook/callback dédié et non rattachées à un billet existant.
     *
     * @param  array{id: string, external_id: string, ticket_id: string, groupe: string, reference_marchande: string}  $references
     */
    protected function estTransactionLotPhysique(array $references, array $transaction): bool
    {
        $metadata = $transaction['custom_metadata'] ?? [];

        if (is_array($metadata) && ($metadata['type'] ?? null) === 'lot_physique') {
            return true;
        }

        $external = $references['external_id'];

        return $external !== ''
            && (str_starts_with($external, 'LOTAUTO-') || str_starts_with($external, \App\Models\LotPhysique::PREFIXE_DEMANDE));
    }

    /**
     * Billets référencés par une transaction FedaPay : ID direct, external_id,
     * metadata ticket_id ou groupe de commande GRP-….
     *
     * @param  array{id: string, external_id: string, ticket_id: string, groupe: string, reference_marchande: string}  $references
     * @return Collection<int, Ticket>
     */
    protected function ticketsPour(array $references): Collection
    {
        $ids = array_values(array_filter(
            [$references['external_id'], $references['ticket_id']],
            fn (string $value) => $value !== '' && ctype_digit($value)
        ));

        return Ticket::query()
            ->with('evenement', 'tarif')
            ->where(function ($query) use ($references, $ids) {
                $query->where('fedapay_transaction_id', $references['id']);

                if ($ids !== []) {
                    $query->orWhereIn('id', $ids);
                }

                if ($references['groupe'] !== '') {
                    $query->orWhere('transaction_id', $references['groupe']);
                }

                if ($references['reference_marchande'] !== '') {
                    $query->orWhere('transaction_id', $references['reference_marchande']);

                    if (ctype_digit($references['reference_marchande'])) {
                        $query->orWhere('id', (int) $references['reference_marchande']);
                    }
                }
            })
            ->get();
    }

    /**
     * Journalise un paiement approuvé sans billet, sans alerter toutes les
     * 5 minutes : une seule entrée par transaction et par période de 12 h.
     */
    protected function signalerOrphelin(string $transactionId, array $transaction): void
    {
        $cle = 'rapprochement-fedapay:orphelin:'.$transactionId;

        if (! Cache::add($cle, true, now()->addHours(12))) {
            return;
        }

        $this->paymentMonitoring->record(
            'paiement_approuve_sans_billet',
            [
                'transaction_id' => $transactionId,
                'received_amount' => (float) ($transaction['amount'] ?? 0),
                'currency' => 'XOF',
                'source' => 'rapprochement',
            ],
            null,
            null,
            'reconciliation',
            'critical',
        );

        Log::warning('Rapprochement FedaPay - paiement approuvé sans billet', [
            'transaction_id' => $transactionId,
            'amount' => (float) ($transaction['amount'] ?? 0),
            'external_id' => $transaction['external_id'] ?? null,
        ]);
    }

    /**
     * Paiement approuvé mais place devenue indisponible (survente) : alerter
     * le support sans répéter l'alerte à chaque balayage de 5 minutes.
     *
     * @param  Collection<int, Ticket>  $tickets
     */
    protected function signalerSansPlace(string $transactionId, Collection $tickets, float $montant): void
    {
        $cle = 'rapprochement-fedapay:sans-place:'.$transactionId;

        if (! Cache::add($cle, true, now()->addHours(12))) {
            return;
        }

        $this->paymentMonitoring->record(
            'paiement_approuve_sans_place',
            [
                'transaction_id' => $transactionId,
                'ticket_group' => $tickets->first()->transaction_id,
                'amount' => $montant,
                'currency' => 'XOF',
                'source' => 'rapprochement',
            ],
            null,
            $tickets->first()->id,
            'reconciliation',
            'critical',
        );
    }

    /**
     * Mode FedaPay (mtn_open, moov_open, bank_card…) sous forme de chaîne.
     */
    protected function modeTransaction(array $transaction): ?string
    {
        $mode = $transaction['mode'] ?? $transaction['payment_method'] ?? null;

        if (is_array($mode)) {
            return $mode['provider'] ?? $mode['name'] ?? null;
        }

        return $mode !== null ? (string) $mode : null;
    }

    /**
     * Numéro de paiement éventuellement fourni par FedaPay.
     */
    protected function telephoneTransaction(array $transaction): ?string
    {
        $telephone = $transaction['phone'] ?? null;

        if (is_array($telephone)) {
            $telephone = $telephone['number'] ?? $telephone['phone_number'] ?? null;
        }

        if (! $telephone && isset($transaction['customer']) && is_array($transaction['customer'])) {
            $telephone = $transaction['customer']['phone_number'] ?? null;
        }

        $telephone = trim((string) $telephone);

        return $telephone !== '' ? $telephone : null;
    }
}
