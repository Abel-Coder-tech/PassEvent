<?php

namespace App\Http\Controllers;

use App\Jobs\SendTicketEmailJob;
use App\Mail\PaymentErrorAlert;
use App\Models\AgentVente;
use App\Models\CodePromo;
use App\Models\Log as LogModel;
use App\Models\LotPhysique;
use App\Models\Ticket;
use App\Services\FedapayService;
use App\Services\LotAutoService;
use App\Services\PaiementMapper;
use App\Services\PaymentMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PaiementController extends Controller
{
    protected FedapayService $fedapay;

    public function __construct(
        FedapayService $fedapay,
        protected PaymentMonitoringService $paymentMonitoring,
    ) {
        $this->fedapay = $fedapay;
    }

    private function verifierTransaction(string $transactionId, Request $request, ?int $ticketId = null, ?string $source = null): ?array
    {
        try {
            $txData = $this->fedapay->getTransaction($transactionId);
        } catch (Throwable $exception) {
            $this->paymentMonitoring->record(
                'fedapay_verification_error',
                [
                    'transaction_id' => $transactionId,
                    'source' => $source,
                    'error_class' => $exception::class,
                ],
                $request,
                $ticketId,
                'erreur_paiement',
                'critical',
                true,
            );

            throw $exception;
        }

        $status = is_array($txData) ? ($txData['status'] ?? null) : null;
        $currency = $txData['currency'] ?? null;
        $currency = is_array($currency) ? ($currency['iso'] ?? null) : $currency;
        $this->paymentMonitoring->record(
            'transaction_verifiee',
            [
                'transaction_id' => $transactionId,
                'ticket_id' => $ticketId,
                'verified_status' => $status,
                'amount' => is_array($txData) ? ($txData['amount'] ?? null) : null,
                'currency' => $currency,
                'source' => $source,
            ],
            $request,
            $ticketId,
            'paiement',
            $status ? 'info' : 'warning',
        );

        return is_array($txData) ? $txData : null;
    }

    // Vérifie que le ticket appartient à la commande en cours (session) pour prévenir l'IDOR
    private function verifierAccesAcheteur(Ticket $ticket): void
    {
        $autorises = session('paiement_tickets', []);

        if (! in_array($ticket->id, $autorises, true)) {
            abort(403, 'Accès non autorisé à ce billet.');
        }
    }

    // Vérifie que la transaction FedaPay a été créée pour CETTE commande (custom_metadata).
    // Le montant ne doit JAMAIS servir de lien transaction <-> commande : cela permettrait de
    // réutiliser une même transaction approuvée pour confirmer plusieurs commandes différentes.
    private function verifierLiaisonTransaction(?array $txData, Ticket $ticket): bool
    {
        if (! is_array($txData)) {
            return false;
        }

        $metadata = $txData['custom_metadata'] ?? null;
        if (! is_array($metadata)) {
            return false;
        }

        $metadataGroup = trim((string) ($metadata['group_transaction_id'] ?? ''));
        $metadataTicketId = trim((string) ($metadata['ticket_id'] ?? ''));

        // 1. Commande (groupe GRP-...) : la transaction doit référencer le même groupe.
        $transactionGroup = (string) $ticket->transaction_id;
        if ($transactionGroup !== '' && str_starts_with($transactionGroup, 'GRP-')) {
            return $metadataGroup !== '' && hash_equals($transactionGroup, $metadataGroup);
        }

        // 2. Ticket isolé sans groupe (vente agent, lots) : la transaction doit référencer ce ticket.
        if ($metadataGroup === '' && $metadataTicketId !== '') {
            return hash_equals((string) $ticket->id, $metadataTicketId);
        }

        return false;
    }

    // Extrait uniquement un identifiant de transaction d'un payload (pour journaliser sans PII)
    private function extraireTrace(string $payload): array
    {
        try {
            $data = json_decode($payload, true);
            $tx = $data['data']['transaction'] ?? $data['transaction'] ?? $data['data'] ?? $data;

            return ['transaction_id' => $tx['id'] ?? $data['id'] ?? null];
        } catch (\Throwable) {
            return [];
        }
    }

    // Affiche la page de paiement FedaPay pour un ticket
    public function show($ticketId)
    {
        $ticket = Ticket::with('evenement.dates', 'tarif')->findOrFail($ticketId);

        $this->verifierAccesAcheteur($ticket);

        if ($ticket->statut_paiement === 'payé') {
            return redirect()->route('confirmation.show', $ticket->id); // Déjà payé, redirige
        }

        // Billets de la même commande. Avec le panier, une commande peut mêler un
        // jour gratuit et des billets payants : la gratuité se juge sur le TOTAL,
        // jamais sur le seul billet affiché.
        $commande = collect([$ticket]);
        if (str_starts_with((string) $ticket->transaction_id, 'GRATUIT-') || str_starts_with((string) $ticket->transaction_id, 'GRP-')) {
            $commande = Ticket::where('transaction_id', $ticket->transaction_id)
                ->where('statut_paiement', 'en_attente')
                ->with('evenement.dates', 'tarif')
                ->get();
            if ($commande->isEmpty()) {
                $commande = collect([$ticket]);
            }
        }

        if ((float) $commande->sum('montant') <= 0) { // Commande entièrement gratuite
            $freeTickets = $commande;

            $groupId = 'GRATUIT-'.$freeTickets->first()->id;
            foreach ($freeTickets as $ft) {
                $reservationDejaComptee = $ft->reservation_expire_le !== null;
                $ft->update([
                    'statut_paiement' => 'payé', // Confirme le paiement gratuit
                    'transaction_id' => $groupId,
                    'reservation_expire_le' => null,
                ]);
                if (! $reservationDejaComptee) {
                    $ft->evenement->increment('quota_vendu', $ft->quantite);
                }
                if ($ft->tarif) {
                    $ft->tarif->increment('quantite_vendue', $ft->quantite);
                }
            }

            SendTicketEmailJob::dispatchAfterResponse($freeTickets->pluck('id')->all(), $ticket->email_acheteur);

            return redirect()->route('confirmation.show', $ticket->id)
                ->with('success', 'Participation confirmée ! Votre billet a été envoyé par email.');
        }

        // Calcule le montant total pour les transactions groupées
        $groupTickets = collect([$ticket]);
        if (str_starts_with($ticket->transaction_id, 'GRP-')) {
            $groupe = Ticket::where('transaction_id', $ticket->transaction_id)
                ->with('evenement.dates', 'tarif')
                ->get();
            if ($groupe->isNotEmpty()) {
                $groupTickets = $groupe;
            }
        }
        $montantTotal = $groupTickets->sum('montant');

        // Lignes du récapitulatif : une par tarif, quantités cumulées.
        $lignesCommande = $groupTickets->groupBy(fn ($t) => $t->tarif_id ?? $t->nom_tarif)
            ->map(fn ($tickets) => [
                'nom' => $tickets->first()->nom_tarif ?? $tickets->first()->tarif?->nom ?? 'Billet',
                'quantite' => (int) $tickets->sum('quantite'),
                'montant' => (float) $tickets->sum('montant'),
            ])->values();
        $nombreBillets = $lignesCommande->sum('quantite');

        $publicKey = $this->fedapay->getPublicKey();
        $sandbox = $this->fedapay->isSandbox();

        return view('evenement-public.paiement', compact('ticket', 'publicKey', 'sandbox', 'montantTotal', 'lignesCommande', 'nombreBillets'));
    }

    // Accès au paiement via un lien signé (ex: ticket réservé depuis la file d'attente).
    // Ajoute le ticket à la session de l'acheteur pour valider l'anti-IDOR, puis redirige
    // vers la page de paiement standard.
    public function accederDepuisLien($ticketId)
    {
        $ticket = Ticket::find($ticketId);

        if (! $ticket || $ticket->statut_paiement === 'payé') {
            abort(404);
        }

        $sessionTickets = session('paiement_tickets', []);
        session(['paiement_tickets' => array_values(array_unique(array_merge($sessionTickets, [$ticket->id])))]);

        return redirect()->route('paiement.show', $ticket->id);
    }

    // Callback FedaPay : traite le résultat du paiement
    public function callback(Request $request)
    {
        $ticketId = $request->query('ticket');
        $transactionId = $request->query('id');
        $source = $request->query('source');

        if (! $transactionId) {
            $this->paymentMonitoring->record(
                'callback_sans_transaction',
                [
                    'ticket_id' => $ticketId,
                    'source' => $source,
                ],
                $request,
                null,
                'echec_paiement',
            );

            // Redirige vers la bonne page selon la source de la vente
            $fallback = match ($source) {
                'agent_vente' => route('agent-vente.dashboard'),
                'vente_manuelle' => route('ventes-manuelles.create'),
                'lot_physique' => route('admin.lots-physiques.index'),
                default => route('paiement.show', $ticketId),
            };

            return redirect()->to($fallback)
                ->with('error', 'Aucune transaction retournee par FedaPay.'); // Pas de transaction
        }

        // Commande de QR codes auto-générés : flux dédié (pas de ticket existant avant paiement)
        if ($source === 'lot_physique') {
            $this->paymentMonitoring->record(
                'callback_recu',
                [
                    'transaction_id' => $transactionId,
                    'reference' => $request->query('reference'),
                    'source' => $source,
                ],
                $request,
            );

            return $this->callbackLotPhysique($request, $transactionId);
        }

        $ticket = Ticket::with('evenement.dates', 'tarif')->findOrFail($ticketId);

        // Find all tickets in the group (if group transaction)
        $groupTickets = collect([$ticket]);
        if (str_starts_with($ticket->transaction_id, 'GRP-')) {
            $groupTickets = Ticket::where('transaction_id', $ticket->transaction_id)
                ->with('evenement.dates', 'tarif')
                ->get();
        }

        if ($ticket->statut_paiement === 'payé') {
            $this->paymentMonitoring->record(
                'callback_paiement_deja_confirme',
                [
                    'transaction_id' => $transactionId,
                    'status_before' => 'payé',
                    'source' => $source,
                    'amount' => (float) $ticket->montant,
                    'currency' => 'XOF',
                ],
                $request,
                $ticket->id,
            );

            $fallback = match ($source) {
                'agent_vente' => route('agent-vente.dashboard'),
                'vente_manuelle' => route('ventes-manuelles.create'),
                default => route('confirmation.show', $ticket->id),
            };

            return redirect()->to($fallback);
        }

        // Sécurité : un ticket annulé, remboursé ou échoué ne peut jamais être (re)confirmé.
        if ($ticket->statut_paiement !== 'en_attente') {
            $this->paymentMonitoring->record(
                'callback_ticket_non_confirmable',
                [
                    'ticket_status' => $ticket->statut_paiement,
                    'transaction_id' => $transactionId,
                    'source' => $source,
                ],
                $request,
                $ticket->id,
                'erreur_paiement',
                'warning',
                true,
            );

            FacadesLog::warning('FedaPay callback - tentative de confirmation d\'un ticket non confirmable', [
                'ticket' => $ticket->id,
                'statut' => $ticket->statut_paiement,
                'transaction_id' => $transactionId,
            ]);

            $fallback = match ($source) {
                'agent_vente' => route('agent-vente.dashboard'),
                'vente_manuelle' => route('ventes-manuelles.create'),
                default => route('paiement.show', $ticket->id),
            };

            return redirect()->to($fallback)
                ->with('error', 'Cette commande ne peut plus être confirmée.');
        }

        // Sécurité : vérifier le statut réel via l'API FedaPay (ne JAMAIS faire confiance aux query params)
        $txData = $this->verifierTransaction($transactionId, $request, $ticket->id, $source);
        $status = $txData['status'] ?? null;

        if (! $status || ! in_array($status, ['approved', 'completed', 'accepted'], true)) {
            $definitif = $status && in_array(strtolower($status), ['declined', 'canceled', 'cancelled', 'cancel'], true);

            FacadesLog::warning('FedaPay callback - status non approuvé via API', [
                'ticket' => $ticket->id,
                'transaction_id' => $transactionId,
                'api_status' => $status,
                'query_status' => $request->query('status'),
            ]);

            if ($definitif) {
                $this->paymentMonitoring->recordFailure(
                    'paiement_refuse',
                    [
                        'transaction_id' => $transactionId,
                        'ticket_id' => $ticket->id,
                        'verified_status' => $status,
                        'expected_amount' => (float) $groupTickets->sum('montant'),
                        'currency' => 'XOF',
                        'source' => $source,
                    ],
                    $request,
                    $ticket->id,
                );
            } elseif (! $status) {
                $this->paymentMonitoring->record(
                    'statut_paiement_indisponible',
                    [
                        'transaction_id' => $transactionId,
                        'source' => $source,
                    ],
                    $request,
                    $ticket->id,
                    'erreur_paiement',
                    'critical',
                    true,
                );
            } else {
                $this->paymentMonitoring->record(
                    'paiement_en_attente',
                    [
                        'transaction_id' => $transactionId,
                        'verified_status' => $status,
                        'expected_amount' => (float) $groupTickets->sum('montant'),
                        'currency' => 'XOF',
                        'source' => $source,
                    ],
                    $request,
                    $ticket->id,
                );
            }

            if ($ticket->statut_paiement === 'en_attente') {
                try {
                    Mail::to($ticket->email_acheteur)->send(new PaymentErrorAlert(
                        $ticket->nom_acheteur,
                        $ticket->evenement->titre,
                        $transactionId
                    ));
                } catch (\Exception $e) {
                    FacadesLog::error('Email incident paiement non envoye : '.$e->getMessage());
                }

                // Ne supprime les tickets que si le paiement a DÉFINITIVEMENT échoué.
                // En cas de statut indéterminé (API injoignable, pending…), on garde les tickets :
                // le webhook FedaPay (vérifié) confirmera la vente si le paiement a réellement abouti.
                if ($definitif) {
                    foreach ($groupTickets as $t) {
                        $t->delete();
                    }
                } else {
                    FacadesLog::warning('FedaPay callback - verification indeterminee, tickets conserves en_attente', [
                        'ticket' => $ticket->id,
                        'transaction_id' => $transactionId,
                        'api_status' => $status,
                    ]);

                    // Conserve l'ID de transaction FedaPay pour permettre au support de vérifier/réconcilier plus tard
                    foreach ($groupTickets as $t) {
                        $t->update(['fedapay_transaction_id' => $transactionId]);
                    }
                }
            }

            $fallback = match ($source) {
                'agent_vente' => route('agent-vente.dashboard'),
                'vente_manuelle' => route('ventes-manuelles.create'),
                default => route('evenements.public.show', $ticket->evenement_id ?? 0),
            };

            return redirect()->to($fallback)
                ->with('error', 'Le paiement n\'a pas pu etre verifie. Veuillez reessayer.');
        }

        // Paiement vérifié via API — traitement
        // Sécurité : la transaction doit avoir été créée pour CETTE commande (custom_metadata),
        // jamais uniquement pour un montant équivalent.
        if (! $this->verifierLiaisonTransaction($txData, $ticket)) {
            $this->paymentMonitoring->record(
                'transaction_non_liee_a_la_commande',
                [
                    'transaction_id' => $transactionId,
                    'source' => $source,
                    'ticket_group' => $ticket->transaction_id,
                ],
                $request,
                $ticket->id,
                'erreur_paiement',
                'critical',
                true,
            );

            FacadesLog::warning('FedaPay callback - transaction non liée à cette commande (metadata)', [
                'ticket' => $ticket->id,
                'transaction_id' => $transactionId,
            ]);

            $fallback = match ($source) {
                'agent_vente' => route('agent-vente.dashboard'),
                'vente_manuelle' => route('ventes-manuelles.create'),
                default => route('paiement.show', $ticket->id),
            };

            return redirect()->to($fallback)
                ->with('error', 'Impossible de rattacher cette transaction à votre commande. Contactez le support.');
        }

        $paymentMethod = $request->query('payment_method', 'mobile_money');
        $paymentPhone = $request->query('phone', $ticket->telephone_acheteur);

        // Sécurité : le montant réellement payé sur FedaPay doit correspondre au montant des tickets.
        // Empêche de marquer payé un ticket cher avec une petite transaction approuvée.
        $montantAttendu = (float) $groupTickets->sum('montant');
        $montantTx = (float) ($txData['amount'] ?? 0);
        if ($montantTx <= 0 || abs($montantAttendu - $montantTx) >= 1) {
            $this->paymentMonitoring->record(
                'montant_paiement_incoherent',
                [
                    'transaction_id' => $transactionId,
                    'expected_amount' => $montantAttendu,
                    'received_amount' => $montantTx,
                    'currency' => 'XOF',
                    'source' => $source,
                ],
                $request,
                $ticket->id,
                'erreur_paiement',
                'critical',
                true,
            );

            FacadesLog::warning('FedaPay callback - montant incohérent avec le ticket', [
                'ticket' => $ticket->id,
                'transaction_id' => $transactionId,
                'montant_attendu' => $montantAttendu,
                'montant_transaction' => $montantTx,
            ]);

            $fallback = match ($source) {
                'agent_vente' => route('agent-vente.dashboard'),
                'vente_manuelle' => route('ventes-manuelles.create'),
                default => route('paiement.show', $ticket->id),
            };

            return redirect()->to($fallback)
                ->with('error', 'Le montant de la transaction ne correspond pas à votre commande.');
        }

        // Normalise l'opérateur depuis les données API (mode FedaPay en priorité, puis payment_method)
        $apiMethod = $txData['mode'] ?? null;
        if (! $apiMethod && isset($txData['payment_method'])) {
            $apiMethod = is_array($txData['payment_method'])
                ? ($txData['payment_method']['provider'] ?? $txData['payment_method']['name'] ?? null)
                : $txData['payment_method'];
        }
        if ($apiMethod && $apiMethod !== 'mobile_money') {
            $paymentMethod = $apiMethod;
        }

        $moyenPaiement = PaiementMapper::moyenPaiement($paymentMethod);
        $operateur = PaiementMapper::operateur($paymentMethod);

        // Met à jour tous les tickets du groupe (atomique, anti double-traitement)
        $confirme = $this->confirmerTickets($groupTickets, $transactionId, $moyenPaiement, $operateur, $paymentPhone, $source, $request->ip(), $request->userAgent());

        if (! $confirme) {
            $fallback = match ($source) {
                'agent_vente' => route('agent-vente.dashboard'),
                'vente_manuelle' => route('ventes-manuelles.create'),
                default => route('confirmation.show', $ticket->id),
            };

            return redirect()->to($fallback);
        }

        SendTicketEmailJob::dispatchAfterResponse($groupTickets->pluck('id')->all(), $ticket->email_acheteur);

        if ($source === 'agent_vente') {
            session()->flash('ticket_created', $ticket->id);

            return redirect()->route('agent-vente.dashboard')
                ->with('success', 'Paiement confirmé ! Ticket vendu avec succès.');
        }

        if ($source === 'vente_manuelle') {
            return redirect()->route('ventes-manuelles.create')
                ->with('success', 'Paiement confirmé ! Ticket vendu avec succès.');
        }

        return redirect()->route('confirmation.show', $ticket->id)
            ->with('success', 'Paiement confirme avec succes!');
    }

    // Callback FedaPay d'une commande de QR codes auto-générés.
    // Les tickets ne sont créés qu'ici, APRÈS vérification du paiement via l'API FedaPay.
    private function callbackLotPhysique(Request $request, string $transactionId)
    {
        $reference = $request->query('reference');
        $lots = LotPhysique::with('tarif')
            ->where('reference_paiement', $reference)
            ->get();

        if ($lots->isEmpty()) {
            $this->paymentMonitoring->record(
                'commande_lot_introuvable',
                [
                    'reference' => $reference,
                    'transaction_id' => $transactionId,
                    'source' => 'callback',
                ],
                $request,
                null,
                'erreur_paiement',
                'critical',
                true,
            );

            return redirect()->route('admin.lots-physiques.index')
                ->with('error', 'Commande introuvable.');
        }

        $estDemande = $lots->first()->estUneDemande();

        // Déjà confirmé (par le webhook par exemple) : simple redirection
        if ($lots->first()->statut !== 'en_attente_paiement') {
            return redirect()->route('admin.lots-physiques.index')
                ->with('qr_succes', LotAutoService::donneesResultat($reference));
        }

        // Sécurité : vérifier le statut réel via l'API FedaPay (ne JAMAIS faire confiance aux query params)
        $txData = $this->verifierTransaction($transactionId, $request, null, 'lot_physique_callback');
        $status = $txData['status'] ?? null;

        if (! $status || ! in_array($status, ['approved', 'completed', 'accepted'], true)) {
            $definitif = $status && in_array(strtolower($status), ['declined', 'canceled', 'cancelled', 'cancel'], true);

            if ($definitif) {
                $this->paymentMonitoring->recordFailure(
                    'paiement_lot_refuse',
                    [
                        'reference' => $reference,
                        'transaction_id' => $transactionId,
                        'verified_status' => $status,
                        'expected_amount' => (float) $lots->sum('montant_commission'),
                        'currency' => 'XOF',
                        'source' => 'callback',
                    ],
                    $request,
                );
            } elseif (! $status) {
                $this->paymentMonitoring->record(
                    'statut_paiement_lot_indisponible',
                    [
                        'reference' => $reference,
                        'transaction_id' => $transactionId,
                        'source' => 'callback',
                    ],
                    $request,
                    null,
                    'erreur_paiement',
                    'critical',
                    true,
                );
            } else {
                $this->paymentMonitoring->record(
                    'paiement_lot_en_attente',
                    [
                        'reference' => $reference,
                        'transaction_id' => $transactionId,
                        'verified_status' => $status,
                        'expected_amount' => (float) $lots->sum('montant_commission'),
                        'currency' => 'XOF',
                    ],
                    $request,
                );
            }

            FacadesLog::warning('FedaPay callback lot - status non approuvé via API', [
                'reference' => $reference,
                'transaction_id' => $transactionId,
                'api_status' => $status,
            ]);

            if ($definitif) {
                LotPhysique::where('reference_paiement', $reference)->where('statut', 'en_attente_paiement')->delete();

                return redirect()->route('admin.lots-physiques.index')
                    ->with('qr_echec', 'Le paiement a été refusé ou annulé. Votre commande n\'a pas été facturée : vous pouvez relancer une génération.');
            }

            LotPhysique::where('reference_paiement', $reference)->where('statut', 'en_attente_paiement')
                ->update(['fedapay_transaction_id' => $transactionId]);

            return redirect()->route('admin.lots-physiques.index')
                ->with('qr_attente', 'Le statut du paiement est en cours de vérification. Si il est confirmé, vos planches apparaîtront automatiquement dans la liste ci-dessous.');
        }

        // Sécurité : la commande doit correspondre à la référence des QR codes auto-générés.
        $metadata = $txData['custom_metadata'] ?? [];
        $metadataReference = trim((string) (is_array($metadata) ? ($metadata['reference'] ?? '') : ''));
        if ($metadataReference !== '' && ! hash_equals($metadataReference, (string) $reference)) {
            $this->paymentMonitoring->record(
                'transaction_lot_non_liee_a_la_commande',
                [
                    'reference' => $reference,
                    'transaction_id' => $transactionId,
                    'metadata_reference' => $metadataReference,
                    'source' => 'callback',
                ],
                $request,
                null,
                'erreur_paiement',
                'critical',
                true,
            );

            FacadesLog::warning('FedaPay callback lot - transaction non liée à la commande (metadata)', [
                'reference' => $reference,
                'transaction_id' => $transactionId,
            ]);

            return redirect()->route('admin.lots-physiques.index')
                ->with('error', 'Impossible de rattacher cette transaction à votre commande.');
        }

        // Sécurité : le montant payé doit correspondre à la commission totale de la commande
        $montantAttendu = round((float) $lots->sum('montant_commission'), 2);
        $montantTx = (float) ($txData['amount'] ?? 0);
        if ($montantTx <= 0 || abs($montantAttendu - $montantTx) >= 1) {
            $this->paymentMonitoring->record(
                'montant_paiement_lot_incoherent',
                [
                    'reference' => $reference,
                    'transaction_id' => $transactionId,
                    'expected_amount' => $montantAttendu,
                    'received_amount' => $montantTx,
                    'currency' => 'XOF',
                    'source' => 'callback',
                ],
                $request,
                null,
                'erreur_paiement',
                'critical',
                true,
            );

            FacadesLog::warning('FedaPay callback lot - montant incohérent avec la commande', [
                'reference' => $reference,
                'transaction_id' => $transactionId,
                'montant_attendu' => $montantAttendu,
                'montant_transaction' => $montantTx,
            ]);

            return redirect()->route('admin.lots-physiques.checkout', $reference)
                ->with('error', 'Le montant de la transaction ne correspond pas à votre commande.');
        }

        // Paiement vérifié : génération des tickets (idempotent)
        $confirme = LotAutoService::confirmerLots($lots, $transactionId);

        if ($confirme) {
            LotAutoService::notifierPaiementAccepte($lots);
        }

        // Demande au super admin : la génération est prise en charge par le super admin
        if ($estDemande) {
            return redirect()->route('admin.lots-physiques.index')
                ->with('success', 'Paiement confirmé. Votre demande est transmise : le super admin génère vos QR codes et vous les transmettra.');
        }

        return redirect()->route('admin.lots-physiques.index')
            ->with('qr_succes', LotAutoService::donneesResultat($reference));
    }

    // Webhook FedaPay : notification serveur à serveur
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('x-fedapay-signature');
        $secret = config('services.fedapay.webhook_secret');

        // Sécurité : seule une signature FedaPay valide (HMAC, horodatée) est acceptée.
        if ($secret) {
            try {
                \FedaPay\Webhook::constructEvent($payload, $signature, $secret);
            } catch (\Throwable $e) {
                $trace = $this->extraireTrace($payload);
                $this->paymentMonitoring->record(
                    'signature_webhook_invalide',
                    [
                        'transaction_id' => $trace['transaction_id'] ?? null,
                        'source' => 'webhook',
                    ],
                    $request,
                    null,
                    'erreur_paiement',
                    'critical',
                    true,
                );

                FacadesLog::warning('FedaPay webhook - signature invalide', [
                    'ip' => $request->ip(),
                    'trace' => $trace,
                ]);

                return response()->json(['error' => 'Invalid signature'], 400);
            }
        } else {
            $this->paymentMonitoring->record(
                'secret_webhook_absent',
                ['source' => 'webhook'],
                $request,
                null,
                'erreur_paiement',
                'critical',
                true,
            );

            FacadesLog::warning('FedaPay webhook - Aucun endpoint secret configuré, signature non vérifiée');
        }

        $data = $request->all();

        // L'événement peut être direct (transaction) ou enveloppé (Event.data.transaction)
        $tx = $data['data']['transaction'] ?? $data['transaction'] ?? $data['data'] ?? $data;

        $transactionId = (string) ($tx['id'] ?? $data['id'] ?? '');
        $webhookStatus = $tx['status'] ?? $data['status'] ?? null;

        if ($transactionId === '') {
            $this->paymentMonitoring->record(
                'payload_webhook_invalide',
                ['source' => 'webhook'],
                $request,
                null,
                'erreur_paiement',
                'warning',
                true,
            );

            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Sécurité : vérifier le statut réel via l'API FedaPay (ne JAMAIS faire confiance au webhook)
        $txData = $this->verifierTransaction($transactionId, $request, null, 'webhook');
        $verifiedStatus = $txData['status'] ?? null;

        if (! $verifiedStatus || ! in_array($verifiedStatus, ['approved', 'completed', 'accepted'], true)) {
            $statutsEchec = ['declined', 'canceled', 'cancelled', 'cancel', 'expired'];
            if ($verifiedStatus && in_array(strtolower($verifiedStatus), $statutsEchec, true)) {
                $this->paymentMonitoring->recordFailure(
                    'paiement_webhook_refuse',
                    [
                        'transaction_id' => $transactionId,
                        'verified_status' => $verifiedStatus,
                        'source' => 'webhook',
                    ],
                    $request,
                );
            } elseif ($verifiedStatus) {
                $this->paymentMonitoring->record(
                    'webhook_statut_non_confirmable',
                    [
                        'transaction_id' => $transactionId,
                        'verified_status' => $verifiedStatus,
                        'source' => 'webhook',
                    ],
                    $request,
                );
            } else {
                $this->paymentMonitoring->record(
                    'webhook_statut_indisponible',
                    [
                        'transaction_id' => $transactionId,
                        'source' => 'webhook',
                    ],
                    $request,
                    null,
                    'erreur_paiement',
                    'critical',
                    true,
                );
            }

            FacadesLog::warning('FedaPay webhook - status non approuvé via API', [
                'transaction_id' => $transactionId,
                'api_status' => $verifiedStatus,
                'webhook_status' => $webhookStatus,
            ]);

            return response()->json(['status' => 'ignored', 'reason' => 'status_not_verified']);
        }

        // Référence externe envoyée au checkout (id du premier ticket du groupe)
        $externalRef = $tx['external_id'] ?? $data['external_id'] ?? null;
        $metadata = $tx['custom_metadata'] ?? $data['custom_metadata'] ?? [];
        // Les metadata peuvent aussi venir des données API re-vérifiées
        if (empty($metadata)) {
            $metadata = $txData['custom_metadata'] ?? [];
        }
        $metadataTicketId = $metadata['ticket_id'] ?? null;
        $metadataGroup = $metadata['group_transaction_id'] ?? null;

        // Commande de QR codes auto-générés : flux dédié, traité AVANT toute recherche de ticket
        // (aucun ticket n'existe encore tant que le paiement n'est pas confirmé)
        $metadataType = $metadata['type'] ?? null;
        if ($metadataType === 'lot_physique'
            || str_starts_with((string) $externalRef, 'LOTAUTO-')
            || str_starts_with((string) $externalRef, LotPhysique::PREFIXE_DEMANDE)) {
            $referenceLot = (string) ($metadata['reference'] ?? $externalRef);
            $transactionIdPayload = (string) ($tx['id'] ?? $data['id'] ?? '');

            return $this->webhookLotPhysique($txData, $referenceLot, $transactionIdPayload, $request);
        }

        $ticket = null;
        if ($metadataTicketId) {
            $ticket = Ticket::with('evenement')->find($metadataTicketId);
        }
        if (! $ticket && $metadataGroup) {
            // Groupe identifié par la référence de la commande groupée (robuste même si le 1er ticket a disparu)
            $ticket = Ticket::where('transaction_id', $metadataGroup)
                ->with('evenement')
                ->first();
        }
        if (! $ticket && $externalRef) {
            $ticket = Ticket::with('evenement')->find($externalRef);
        }
        if (! $ticket) {
            $ticket = Ticket::where('transaction_id', $transactionId)
                ->with('evenement')
                ->first();
        }

        // Sécurité : la transaction doit avoir été créée pour CETTE commande (custom_metadata).
        // Supprimé : la recherche par email + montant (appariement non fiable et source de détournement).
        if ($ticket && ! $this->verifierLiaisonTransaction($txData, $ticket)) {
            $this->paymentMonitoring->record(
                'webhook_transaction_non_liee_a_la_commande',
                [
                    'transaction_id' => $transactionId,
                    'ticket_group' => $ticket->transaction_id,
                    'source' => 'webhook',
                ],
                $request,
                $ticket->id,
                'erreur_paiement',
                'critical',
                true,
            );

            FacadesLog::warning('FedaPay webhook - transaction non liée à la commande (metadata)', [
                'ticket' => $ticket->id,
                'transaction_id' => $transactionId,
            ]);

            return response()->json(['status' => 'ignored', 'reason' => 'transaction_not_linked']);
        }

        if (! $ticket) {
            // Paiement approuvé mais aucun billet retrouvé : incident journalisé pour le support
            $currency = $txData['currency'] ?? null;
            $currency = is_array($currency) ? ($currency['iso'] ?? 'XOF') : ($currency ?: 'XOF');

            $this->paymentMonitoring->record(
                'paiement_approuve_sans_ticket',
                [
                    'transaction_id' => $transactionId,
                    'received_amount' => (float) ($txData['amount'] ?? 0),
                    'currency' => $currency,
                    'source' => 'webhook',
                ],
                $request,
                null,
                'reconciliation',
                'critical',
                true,
            );

            FacadesLog::warning('FedaPay webhook - paiement approuvé sans ticket trouvé', [
                'transaction_id' => $transactionId,
                'amount' => (float) ($txData['amount'] ?? 0),
            ]);
            return response()->json(['status' => 'ok', 'warning' => 'ticket_not_found']);
        }

        if ($ticket->statut_paiement === 'payé') {
            return response()->json(['status' => 'ok']);
        }

        $groupTickets = collect([$ticket]);
        if (str_starts_with($ticket->transaction_id, 'GRP-')) {
            $groupTickets = Ticket::where('transaction_id', $ticket->transaction_id)
                ->with('evenement.dates', 'tarif')
                ->get();
        }

        // Extraction du réseau depuis mode FedaPay (prioritaire) puis payment_method (string ou objet)
        $paymentMethodRaw = $tx['mode'] ?? $data['mode'] ?? $tx['payment_method'] ?? $data['payment_method'] ?? 'mobile_money';

        // Normalise depuis les données API (mode en priorité)
        $apiMethod = $txData['mode'] ?? null;
        if (! $apiMethod && isset($txData['payment_method'])) {
            $apiMethod = is_array($txData['payment_method'])
                ? ($txData['payment_method']['provider'] ?? $txData['payment_method']['name'] ?? null)
                : $txData['payment_method'];
        }
        if ($apiMethod && $apiMethod !== 'mobile_money') {
            $paymentMethodRaw = $apiMethod;
        }

        $moyenPaiement = PaiementMapper::moyenPaiement($paymentMethodRaw);
        $operateur = PaiementMapper::operateur($paymentMethodRaw);

        // Vérifie la cohérence montant ↔ tickets (montant issu des données API re-vérifiées)
        $montantAttendu = (float) $groupTickets->sum('montant');
        $montantTx = (float) ($txData['amount'] ?? 0);
        if ($montantTx <= 0 || abs($montantAttendu - $montantTx) >= 1) {
            $this->paymentMonitoring->record(
                'montant_webhook_incoherent',
                [
                    'transaction_id' => $transactionId,
                    'ticket_id' => $ticket->id,
                    'expected_amount' => $montantAttendu,
                    'received_amount' => $montantTx,
                    'currency' => 'XOF',
                    'source' => 'webhook',
                ],
                $request,
                $ticket->id,
                'erreur_paiement',
                'critical',
                true,
            );

            FacadesLog::warning('FedaPay webhook - montant incohérent avec le groupe de tickets', [
                'ticket' => $ticket->id,
                'transaction_id' => $transactionId,
                'montant_attendu' => $montantAttendu,
                'montant_transaction' => $montantTx,
            ]);

            return response()->json(['status' => 'ignored', 'reason' => 'amount_mismatch']);
        }

        $confirme = $this->confirmerTickets($groupTickets, $transactionId, $moyenPaiement, $operateur, $tx['phone'] ?? $data['phone'] ?? $ticket->telephone_acheteur, $request->input('source'), $request->ip(), $request->userAgent());

        if ($confirme) {
            SendTicketEmailJob::dispatchAfterResponse($groupTickets->pluck('id')->all(), $ticket->email_acheteur);
        }

        return response()->json(['status' => 'ok']);
    }

    // Webhook FedaPay d'une commande de QR codes auto-générés.
    // Vérifie statut + montant via l'API, puis génère les tickets (idempotent).
    private function webhookLotPhysique(array $txData, string $reference, string $transactionIdPayload, Request $request)
    {
        if ($reference === '') {
            $this->paymentMonitoring->record(
                'reference_lot_webhook_absente',
                [
                    'transaction_id' => $txData['id'] ?? null,
                    'source' => 'webhook',
                ],
                $request,
                null,
                'erreur_paiement',
                'critical',
                true,
            );

            FacadesLog::warning('FedaPay webhook lot - reference manquante', ['transaction_id' => $txData['id'] ?? null]);

            return response()->json(['status' => 'ignored', 'reason' => 'missing_reference']);
        }

        $lots = LotPhysique::with('tarif')
            ->where('reference_paiement', $reference)
            ->get();

        if ($lots->isEmpty()) {
            $this->paymentMonitoring->record(
                'commande_lot_webhook_introuvable',
                [
                    'reference' => $reference,
                    'transaction_id' => $txData['id'] ?? null,
                    'source' => 'webhook',
                ],
                $request,
                null,
                'reconciliation',
                'critical',
                true,
            );

            FacadesLog::warning('FedaPay webhook lot - commande introuvable', [
                'reference' => $reference,
                'transaction_id' => $txData['id'] ?? null,
            ]);

            return response()->json(['status' => 'ok', 'warning' => 'lot_not_found']);
        }

        if ($lots->first()->statut !== 'en_attente_paiement') {
            return response()->json(['status' => 'ok']); // Déjà traité via callback
        }

        // Sécurité : vérifier le statut réel via l'API FedaPay (ne JAMAIS faire confiance au webhook)
        $verifiedStatus = $txData['status'] ?? null;
        if (! $verifiedStatus || ! in_array($verifiedStatus, ['approved', 'completed', 'accepted'], true)) {
            if ($verifiedStatus && in_array(strtolower($verifiedStatus), ['declined', 'canceled', 'cancelled', 'cancel', 'expired'], true)) {
                $this->paymentMonitoring->recordFailure(
                    'paiement_lot_webhook_refuse',
                    [
                        'reference' => $reference,
                        'transaction_id' => $txData['id'] ?? null,
                        'verified_status' => $verifiedStatus,
                        'expected_amount' => (float) $lots->sum('montant_commission'),
                        'currency' => 'XOF',
                        'source' => 'webhook',
                    ],
                    $request,
                );
            } elseif (! $verifiedStatus) {
                $this->paymentMonitoring->record(
                    'statut_paiement_lot_webhook_indisponible',
                    [
                        'reference' => $reference,
                        'transaction_id' => $txData['id'] ?? null,
                        'source' => 'webhook',
                    ],
                    $request,
                    null,
                    'erreur_paiement',
                    'critical',
                    true,
                );
            } else {
                $this->paymentMonitoring->record(
                    'paiement_lot_webhook_en_attente',
                    [
                        'reference' => $reference,
                        'transaction_id' => $txData['id'] ?? null,
                        'verified_status' => $verifiedStatus,
                        'source' => 'webhook',
                    ],
                    $request,
                );
            }

            FacadesLog::warning('FedaPay webhook lot - status non approuvé via API', [
                'reference' => $reference,
                'api_status' => $verifiedStatus,
            ]);

            return response()->json(['status' => 'ignored', 'reason' => 'status_not_verified']);
        }

        // Sécurité : la transaction doit référencer la commande de QR codes auto-générés.
        $metadata = $txData['custom_metadata'] ?? [];
        $metadataReference = trim((string) (is_array($metadata) ? ($metadata['reference'] ?? '') : ''));
        if ($metadataReference !== '' && ! hash_equals($metadataReference, (string) $reference)) {
            $this->paymentMonitoring->record(
                'transaction_lot_webhook_non_liee',
                [
                    'reference' => $reference,
                    'metadata_reference' => $metadataReference,
                    'transaction_id' => $txData['id'] ?? null,
                    'source' => 'webhook',
                ],
                $request,
                null,
                'erreur_paiement',
                'critical',
                true,
            );

            FacadesLog::warning('FedaPay webhook lot - transaction non liée à la commande (metadata)', [
                'reference' => $reference,
                'transaction_id' => $txData['id'] ?? null,
            ]);

            return response()->json(['status' => 'ignored', 'reason' => 'transaction_not_linked']);
        }

        // Sécurité : cohérence du montant payé avec la commission totale de la commande
        $montantAttendu = round((float) $lots->sum('montant_commission'), 2);
        $montantTx = (float) ($txData['amount'] ?? 0);
        if ($montantTx <= 0 || abs($montantAttendu - $montantTx) >= 1) {
            $this->paymentMonitoring->record(
                'montant_lot_webhook_incoherent',
                [
                    'reference' => $reference,
                    'transaction_id' => $txData['id'] ?? null,
                    'expected_amount' => $montantAttendu,
                    'received_amount' => $montantTx,
                    'currency' => 'XOF',
                    'source' => 'webhook',
                ],
                $request,
                null,
                'erreur_paiement',
                'critical',
                true,
            );

            FacadesLog::warning('FedaPay webhook lot - montant incohérent avec la commande', [
                'reference' => $reference,
                'montant_attendu' => $montantAttendu,
                'montant_transaction' => $montantTx,
            ]);

            return response()->json(['status' => 'ignored', 'reason' => 'amount_mismatch']);
        }

        $transactionId = (string) ($txData['id'] ?? '') !== '' ? (string) $txData['id'] : $transactionIdPayload;
        if ($transactionId === '') {
            $this->paymentMonitoring->record(
                'transaction_lot_webhook_absente',
                [
                    'reference' => $reference,
                    'source' => 'webhook',
                ],
                $request,
                null,
                'erreur_paiement',
                'critical',
                true,
            );

            FacadesLog::warning('FedaPay webhook lot - id de transaction introuvable', ['reference' => $reference]);

            return response()->json(['status' => 'ignored', 'reason' => 'missing_transaction_id']);
        }

        $confirme = LotAutoService::confirmerLots($lots, $transactionId);

        if ($confirme) {
            LotAutoService::notifierPaiementAccepte($lots);
        }

        return response()->json(['status' => 'ok']);
    }

    // Confirme atomiquement un groupe de tickets : verrouillage anti double-traitement,
    // contrôle de capacité, incrément des quotas, compteurs agent et code promo.
    protected function confirmerTickets(iterable $groupTickets, string $transactionId, string $moyenPaiement, ?string $operateur, ?string $telephonePaiement, ?string $source, ?string $ip, ?string $userAgent): bool
    {
        return DB::transaction(function () use ($groupTickets, $transactionId, $moyenPaiement, $operateur, $telephonePaiement, $source, $ip, $userAgent) {
            $confirme = false;
            $codePromoIncremente = false;

            foreach ($groupTickets as $t) {
                // Verrouillage pessimiste : bloque le ticket pendant la mise à jour pour éviter les doubles confirmations
                $locked = Ticket::whereKey($t->id)->lockForUpdate()->first();
                // Seuls les tickets en attente sont confirmables : un ticket payé (idempotence),
                // annulé, remboursé ou échoué ne repasse jamais à 'payé'.
                if (! $locked || $locked->statut_paiement !== 'en_attente'
                    || $locked->annule
                    || $locked->expire_at !== null) {
                    continue;
                }

                $evenement = $locked->evenement()->lockForUpdate()->first();
                if (! $evenement) {
                    continue;
                }

                // Si le ticket provient d'un achat public en ligne, les places ont déjà été
                // réservées (comptées dans quota_vendu) lors de 'achat()' : on ne re-compte pas,
                // on lève simplement la réservation. Sinon (vente manuelle, agent, réconciliation),
                // on compte comme avant, sans jamais dépasser la capacité.
                $reservationDejaComptee = $locked->reservation_expire_le !== null;

                if (! $reservationDejaComptee) {
                    if ($evenement->estMultiJour()) {
                        // Multijours : l'occupation est calculée par jour à partir
                        // des billets actifs, le compteur global sert uniquement de
                        // statistique et n'est pas plafonné par la jauge de la salle.
                        $evenement->increment('quota_vendu', (int) $locked->quantite);
                    } else {
                        $nouveauQuota = (int) $evenement->quota_vendu + (int) $locked->quantite;
                        if ($nouveauQuota > (int) $evenement->capacite) {
                            FacadesLog::warning('FedaPay - dépassement de capacité à la confirmation, quota plafonné', [
                                'ticket' => $locked->id,
                                'evenement' => $evenement->id,
                                'quota_actuel' => $evenement->quota_vendu,
                                'capacite' => $evenement->capacite,
                            ]);
                            $nouveauQuota = (int) $evenement->capacite;
                        }
                        $evenement->update(['quota_vendu' => $nouveauQuota]);
                    }
                }

                $locked->update([
                    'statut_paiement' => 'payé',
                    'transaction_id' => $transactionId,
                    'fedapay_transaction_id' => $transactionId,
                    'methode_paiement' => $operateur ?? ($locked->methode_paiement ?? ($locked->montant > 0 ? 'mobile_money' : 'especes')),
                    'type_paiement' => $moyenPaiement,
                    'telephone_paiement' => $telephonePaiement,
                    'reservation_expire_le' => null,
                ]);

                if ($locked->tarif) {
                    $tarif = $locked->tarif()->lockForUpdate()->first();
                    if ($tarif) {
                        $tarif->increment('quantite_vendue', (int) $locked->quantite);
                    }
                }

                // Code promo : comptabilise UNE SEULE fois par groupe de tickets
                if ($locked->code_promo_utilise && ! $codePromoIncremente) {
                    $codePromo = CodePromo::where('code', $locked->code_promo_utilise)->first();
                    if ($codePromo) {
                        $codePromo->increment('nb_utilisations');
                        $codePromoIncremente = true;
                    }
                }

                LogModel::create([
                    'ticket_id' => $locked->id,
                    'type_operation' => 'achat',
                    'details' => [
                        'action' => 'paiement_confirme',
                        'transaction_id' => $transactionId,
                        'methode' => 'fedapay',
                        'payment_method' => $moyenPaiement,
                        'operator' => $operateur,
                        'source' => $source,
                        'amount' => (float) $locked->montant,
                        'currency' => 'XOF',
                        'quantity' => (int) $locked->quantite,
                        'event_id' => $locked->evenement_id,
                        'status_before' => 'en_attente',
                        'status_after' => 'payé',
                    ],
                    'ip' => $ip,
                    'user_agent' => $userAgent ? mb_substr($userAgent, 0, 255) : null,
                ]);

                $confirme = true;
            }

            // Compteurs de l'agent de vente (via agent_vente_id du ticket, quel que soit le canal de confirmation)
            if ($confirme) {
                $agentVenteId = null;
                $montantGroupe = 0;
                $nbTickets = 0;
                foreach ($groupTickets as $gt) {
                    if ($gt->agent_vente_id) {
                        $agentVenteId = $gt->agent_vente_id;
                    }
                    $montantGroupe += (float) $gt->montant;
                    $nbTickets++;
                }

                if ($agentVenteId) {
                    $agent = AgentVente::find($agentVenteId);
                    if ($agent) {
                        $agent->increment('tickets_count', $nbTickets);
                        $agent->increment('montant_total', $montantGroupe);
                    }
                }
            }

            return $confirme;
        });
    }

    // Affiche la page de confirmation après paiement réussi
    public function confirmation($ticketId)
    {
        $ticket = Ticket::with('evenement.dates', 'tarif')->findOrFail($ticketId);

        $this->verifierAccesAcheteur($ticket);

        if ($ticket->statut_paiement !== 'payé') {
            return redirect()->route('paiement.show', $ticket->id); // Paiement non confirmé
        }

        // Récupère tous les tickets du même groupe
        $groupTickets = Ticket::where('transaction_id', $ticket->transaction_id)
            ->with('evenement.dates', 'tarif')
            ->get();

        return view('evenement-public.confirmation', compact('ticket', 'groupTickets'));
    }
}
