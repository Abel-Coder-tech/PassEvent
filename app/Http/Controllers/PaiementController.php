<?php

namespace App\Http\Controllers;

use App\Mail\PaymentErrorAlert;
use App\Mail\TicketEmail;
use App\Models\AgentVente;
use App\Models\CodePromo;
use App\Models\Log as LogModel;
use App\Models\Ticket;
use App\Services\FedapayService;
use App\Services\PaiementMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\Mail;

class PaiementController extends Controller
{
    protected FedapayService $fedapay;

    public function __construct(FedapayService $fedapay)
    {
        $this->fedapay = $fedapay;
    }

    // Vérifie que le ticket appartient à la commande en cours (session) pour prévenir l'IDOR
    private function verifierAccesAcheteur(Ticket $ticket): void
    {
        $autorises = session('paiement_tickets', []);

        if (! in_array($ticket->id, $autorises, true)) {
            abort(403, 'Accès non autorisé à ce billet.');
        }
    }

    // Affiche la page de paiement FedaPay pour un ticket
    public function show($ticketId)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($ticketId);

        $this->verifierAccesAcheteur($ticket);

        if ($ticket->statut_paiement === 'payé') {
            return redirect()->route('confirmation.show', $ticket->id); // Déjà payé, redirige
        }

        if ($ticket->montant <= 0) { // Traitement spécial pour tickets gratuits
            // Find all tickets in the same free group or single ticket
            $freeTickets = collect([$ticket]);
            if (str_starts_with($ticket->transaction_id, 'GRATUIT-')) {
                $freeTickets = Ticket::where('transaction_id', $ticket->transaction_id)
                    ->where('statut_paiement', 'en_attente')
                    ->with('evenement', 'tarif')
                    ->get();
            }

            $groupId = 'GRATUIT-'.$freeTickets->first()->id;
            foreach ($freeTickets as $ft) {
                $ft->update([
                    'statut_paiement' => 'payé', // Confirme le paiement gratuit
                    'transaction_id' => $groupId,
                ]);
                $ft->evenement->increment('quota_vendu', $ft->quantite);
                if ($ft->tarif) {
                    $ft->tarif->increment('quantite_vendue', $ft->quantite);
                }
            }

            try {
                Mail::to($ticket->email_acheteur)->send(new TicketEmail($freeTickets));
            } catch (\Exception $e) {
                FacadesLog::error('Email gratuit non envoye pour ticket '.$ticket->id.' : '.$e->getMessage());
            }

            return redirect()->route('confirmation.show', $ticket->id)
                ->with('success', 'Participation confirmée ! Votre billet a été envoyé par email.');
        }

        // Calcule le montant total pour les transactions groupées
        $montantTotal = $ticket->montant;
        if (str_starts_with($ticket->transaction_id, 'GRP-')) {
            $groupTickets = Ticket::where('transaction_id', $ticket->transaction_id)
                ->with('evenement', 'tarif')
                ->get();
            if ($groupTickets->isNotEmpty()) {
                $montantTotal = $groupTickets->sum('montant');
            }
        }

        $publicKey = $this->fedapay->getPublicKey();
        $sandbox = $this->fedapay->isSandbox();

        return view('evenement-public.paiement', compact('ticket', 'publicKey', 'sandbox', 'montantTotal'));
    }

    // Callback FedaPay : traite le résultat du paiement
    public function callback(Request $request)
    {
        $ticketId = $request->query('ticket');
        $transactionId = $request->query('id');
        $source = $request->query('source');

        if (! $transactionId) {
            // Redirige vers la bonne page selon la source de la vente
            $fallback = match ($source) {
                'agent_vente' => route('agent-vente.dashboard'),
                'vente_manuelle' => route('ventes-manuelles.create'),
                default => route('paiement.show', $ticketId),
            };

            return redirect()->to($fallback)
                ->with('error', 'Aucune transaction retournee par FedaPay.'); // Pas de transaction
        }

        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($ticketId);

        // Find all tickets in the group (if group transaction)
        $groupTickets = collect([$ticket]);
        if (str_starts_with($ticket->transaction_id, 'GRP-')) {
            $groupTickets = Ticket::where('transaction_id', $ticket->transaction_id)
                ->with('evenement', 'tarif')
                ->get();
        }

        if ($ticket->statut_paiement === 'payé') {
            $fallback = match ($source) {
                'agent_vente' => route('agent-vente.dashboard'),
                'vente_manuelle' => route('ventes-manuelles.create'),
                default => route('confirmation.show', $ticket->id),
            };

            return redirect()->to($fallback);
        }

        // Sécurité : vérifier le statut réel via l'API FedaPay (ne JAMAIS faire confiance aux query params)
        $txData = $this->fedapay->getTransaction($transactionId);
        $status = $txData['status'] ?? null;

        if (! $status || ! in_array($status, ['approved', 'completed', 'accepted'], true)) {
            $definitif = $status && in_array(strtolower($status), ['declined', 'canceled', 'cancelled', 'cancel'], true);

            FacadesLog::warning('FedaPay callback - status non approuvé via API', [
                'ticket' => $ticket->id,
                'transaction_id' => $transactionId,
                'api_status' => $status,
                'query_status' => $request->query('status'),
            ]);

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
        $paymentMethod = $request->query('payment_method', 'mobile_money');
        $paymentPhone = $request->query('phone', $ticket->telephone_acheteur);

        // Sécurité : le montant réellement payé sur FedaPay doit correspondre au montant des tickets.
        // Empêche de marquer payé un ticket cher avec une petite transaction approuvée.
        $montantAttendu = (float) $groupTickets->sum('montant');
        $montantTx = (float) ($txData['amount'] ?? 0);
        if ($montantTx <= 0 || abs($montantAttendu - $montantTx) >= 1) {
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

        try {
            Mail::to($ticket->email_acheteur)->send(new TicketEmail($groupTickets));
        } catch (\Exception $e) {
            FacadesLog::error('Email non envoye pour ticket '.$ticket->id.' : '.$e->getMessage());
        }

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

    // Webhook FedaPay : notification serveur à serveur
    public function webhook(Request $request)
    {
        $data = $request->all();

        // Log complet du payload pour diagnostic
        FacadesLog::info('FedaPay webhook payload complet', $data);

        // L'événement peut être direct (transaction) ou enveloppé (Event.data.transaction)
        $tx = $data['data']['transaction'] ?? $data['transaction'] ?? $data['data'] ?? $data;

        $transactionId = (string) ($tx['id'] ?? $data['id'] ?? '');
        $webhookStatus = $tx['status'] ?? $data['status'] ?? null;

        if ($transactionId === '') {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Sécurité : vérifier le statut réel via l'API FedaPay (ne JAMAIS faire confiance au webhook)
        $txData = $this->fedapay->getTransaction($transactionId);
        $verifiedStatus = $txData['status'] ?? null;

        if (! $verifiedStatus || ! in_array($verifiedStatus, ['approved', 'completed', 'accepted'], true)) {
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
        $metadataTicketId = $metadata['ticket_id'] ?? null;
        $metadataGroup = $metadata['group_transaction_id'] ?? null;

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

        // Dernier recours : recherche par email client + montant de la transaction (callback/webhook perdus)
        $customer = $tx['customer'] ?? $data['customer'] ?? null;
        $customerEmail = is_array($customer) || is_object($customer)
            ? (data_get($customer, 'email') ?? null)
            : $customer;
        $montantTx = (float) ($tx['amount'] ?? $data['amount'] ?? 0);

        if (! $ticket && $customerEmail) {
            $candidats = Ticket::where('email_acheteur', $customerEmail)
                ->where('statut_paiement', 'en_attente')
                ->with('evenement', 'tarif')
                ->get();

            if ($montantTx > 0) {
                foreach ($candidats->groupBy('transaction_id') as $groupe) {
                    if (abs($groupe->sum('montant') - $montantTx) < 1) {
                        $ticket = $groupe->first();
                        break;
                    }
                }
            }
            if (! $ticket && $candidats->count() === 1) {
                $ticket = $candidats->first();
            }
        }

        if (! $ticket) {
            // Paiement approuvé mais aucun billet retrouvé : incident journalisé pour le support
            FacadesLog::warning('FedaPay webhook - paiement approuvé sans ticket trouvé', [
                'transaction_id' => $transactionId,
                'customer_email' => $customerEmail,
                'amount' => $montantTx,
            ]);
            LogModel::create([
                'type_operation' => 'reconciliation',
                'ticket_id' => null,
                'details' => [
                    'action' => 'webhook_incident_ticket_non_trouve',
                    'transaction_id' => $transactionId,
                    'email' => $customerEmail ?? null,
                    'montant' => $montantTx,
                ],
                'ip' => $request->ip(),
            ]);

            return response()->json(['status' => 'ok', 'warning' => 'ticket_not_found']);
        }

        if ($ticket->statut_paiement === 'payé') {
            return response()->json(['status' => 'ok']);
        }

        $groupTickets = collect([$ticket]);
        if (str_starts_with($ticket->transaction_id, 'GRP-')) {
            $groupTickets = Ticket::where('transaction_id', $ticket->transaction_id)
                ->with('evenement', 'tarif')
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

        // Vérifie la cohérence montant ↔ tickets avant de confirmer (billets retrouvés hors metadata)
        $montantAttendu = (float) $groupTickets->sum('montant');
        $montantTx = (float) ($txData['amount'] ?? $montantTx);
        if ($montantTx <= 0 || abs($montantAttendu - $montantTx) >= 1) {
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
            try {
                Mail::to($ticket->email_acheteur)->send(new TicketEmail($groupTickets));
            } catch (\Exception $e) {
                FacadesLog::error('Webhook - email ticket non envoyé : '.$e->getMessage());
            }
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
                if (! $locked || $locked->statut_paiement === 'payé') {
                    continue; // Déjà payé (callback + webhook simultanés)
                }

                $evenement = $locked->evenement()->lockForUpdate()->first();
                if (! $evenement) {
                    continue;
                }

                // Contrôle de capacité : on n'incrémente jamais au-delà de la capacité
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

                $locked->update([
                    'statut_paiement' => 'payé',
                    'transaction_id' => $transactionId,
                    'fedapay_transaction_id' => $transactionId,
                    'methode_paiement' => $operateur ?? ($locked->methode_paiement ?? ($locked->montant > 0 ? 'mobile_money' : 'especes')),
                    'type_paiement' => $moyenPaiement,
                    'telephone_paiement' => $telephonePaiement,
                ]);

                $evenement->update(['quota_vendu' => $nouveauQuota]);

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
                    'details' => ['transaction_id' => $transactionId, 'methode' => 'fedapay', 'agent_vente' => $source === 'agent_vente'],
                    'ip' => $ip,
                    'user_agent' => $userAgent,
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
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($ticketId);

        $this->verifierAccesAcheteur($ticket);

        if ($ticket->statut_paiement !== 'payé') {
            return redirect()->route('paiement.show', $ticket->id); // Paiement non confirmé
        }

        // Récupère tous les tickets du même groupe
        $groupTickets = Ticket::where('transaction_id', $ticket->transaction_id)
            ->with('evenement', 'tarif')
            ->get();

        return view('evenement-public.confirmation', compact('ticket', 'groupTickets'));
    }
}
