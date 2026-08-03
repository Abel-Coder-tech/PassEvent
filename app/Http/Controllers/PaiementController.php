<?php

namespace App\Http\Controllers;

use App\Mail\PaymentErrorAlert;
use App\Mail\TicketEmail;
use App\Models\Ticket;
use App\Models\Log as LogModel;
use App\Services\FedapayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\Mail;

class PaiementController extends Controller
{
    protected FedapayService $fedapay;

    public function __construct(FedapayService $fedapay)
    {
        $this->fedapay = $fedapay;
    }

    // Affiche la page de paiement FedaPay pour un ticket
    public function show($ticketId)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($ticketId);

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

            $groupId = 'GRATUIT-' . $freeTickets->first()->id;
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
                FacadesLog::error('Email gratuit non envoye pour ticket ' . $ticket->id . ' : ' . $e->getMessage());
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

        if (!$transactionId) {
            // Redirige vers la bonne page selon la source de la vente
            $fallback = match($source) {
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
            $fallback = match($source) {
                'agent_vente' => route('agent-vente.dashboard'),
                'vente_manuelle' => route('ventes-manuelles.create'),
                default => route('confirmation.show', $ticket->id),
            };
            return redirect()->to($fallback);
        }

        // Sécurité : vérifier le statut réel via l'API FedaPay (ne JAMAIS faire confiance aux query params)
        $txData = $this->fedapay->getTransaction($transactionId);
        $status = $txData['status'] ?? null;

        if (!$status || !in_array($status, ['approved', 'completed', 'accepted'], true)) {
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
                    FacadesLog::error('Email incident paiement non envoye : ' . $e->getMessage());
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
                }
            }

            $fallback = match($source) {
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

        // Normalise l'opérateur depuis les données API
        if (isset($txData['payment_method'])) {
            $apiMethod = is_array($txData['payment_method'])
                ? ($txData['payment_method']['provider'] ?? $txData['payment_method']['name'] ?? null)
                : $txData['payment_method'];
            if ($apiMethod && $apiMethod !== 'mobile_money') {
                $paymentMethod = $apiMethod;
            }
        }

        $paymentMethod = self::extractPaymentMethod($paymentMethod);

        // Met à jour tous les tickets du groupe
        foreach ($groupTickets as $t) {
            $t->update([
                'statut_paiement' => 'payé',
                'transaction_id' => $transactionId,
                'methode_paiement' => $paymentMethod,
                'telephone_paiement' => $paymentPhone,
            ]);

            $t->evenement->increment('quota_vendu', $t->quantite);
            if ($t->tarif) {
                $t->tarif->increment('quantite_vendue', $t->quantite);
            }
        }

        // Met à jour les compteurs de l'agent de vente si applicable
        if ($source === 'agent_vente' && $ticket->agent_vente_id) {
            $agent = \App\Models\AgentVente::find($ticket->agent_vente_id);
            if ($agent) {
                $agent->increment('tickets_count', $groupTickets->count());
                $agent->increment('montant_total', $groupTickets->sum('montant'));
            }
        }

        try {
            Mail::to($ticket->email_acheteur)->send(new TicketEmail($groupTickets));
        } catch (\Exception $e) {
            FacadesLog::error('Email non envoye pour ticket ' . $ticket->id . ' : ' . $e->getMessage());
        }

        foreach ($groupTickets as $t) {
            LogModel::create([
                'ticket_id' => $t->id,
                'type_operation' => 'achat',
                'details' => ['transaction_id' => $transactionId, 'methode' => 'fedapay', 'agent_vente' => $source === 'agent_vente'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
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

        if (!$verifiedStatus || !in_array($verifiedStatus, ['approved', 'completed', 'accepted'], true)) {
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

        $ticket = null;
        if ($metadataTicketId) {
            $ticket = Ticket::with('evenement')->find($metadataTicketId);
        }
        if (!$ticket && $externalRef) {
            $ticket = Ticket::with('evenement')->find($externalRef);
        }
        if (!$ticket) {
            $ticket = Ticket::where('transaction_id', $transactionId)
                ->with('evenement')
                ->first();
        }

        if (!$ticket || $ticket->statut_paiement === 'payé') {
            return response()->json(['status' => 'ok']);
        }

        $groupTickets = collect([$ticket]);
        if (str_starts_with($ticket->transaction_id, 'GRP-')) {
            $groupTickets = Ticket::where('transaction_id', $ticket->transaction_id)
                ->with('evenement', 'tarif')
                ->get();
        }

        // Extraction du réseau depuis payment_method (string ou objet)
        $paymentMethodRaw = $tx['payment_method'] ?? $data['payment_method'] ?? 'mobile_money';

        // Normalise depuis les données API
        if (isset($txData['payment_method'])) {
            $apiMethod = is_array($txData['payment_method'])
                ? ($txData['payment_method']['provider'] ?? $txData['payment_method']['name'] ?? null)
                : $txData['payment_method'];
            if ($apiMethod && $apiMethod !== 'mobile_money') {
                $paymentMethodRaw = $apiMethod;
            }
        }

        $paymentMethod = self::extractPaymentMethod($paymentMethodRaw);

        FacadesLog::info('FedaPay webhook - payment_method normalisé', [
            'ticket_id' => $ticket->id,
            'payment_method_final' => $paymentMethod,
        ]);

        foreach ($groupTickets as $t) {
            $t->update([
                'statut_paiement' => 'payé',
                'transaction_id' => $transactionId,
                'methode_paiement' => $paymentMethod,
                'telephone_paiement' => $tx['phone'] ?? $data['phone'] ?? $t->telephone_acheteur,
            ]);

            $t->load('evenement', 'tarif');
            $t->evenement->increment('quota_vendu', $t->quantite);
            if ($t->tarif) {
                $t->tarif->increment('quantite_vendue', $t->quantite);
            }
        }

        try {
            Mail::to($ticket->email_acheteur)->send(new TicketEmail($groupTickets));
        } catch (\Exception $e) {
            FacadesLog::error('Webhook - email ticket non envoyé : ' . $e->getMessage());
        }

        return response()->json(['status' => 'ok']);
    }

    // Affiche la page de confirmation après paiement réussi
    public function confirmation($ticketId)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($ticketId);

        if ($ticket->statut_paiement !== 'payé') {
            return redirect()->route('paiement.show', $ticket->id); // Paiement non confirmé
        }

        // Récupère tous les tickets du même groupe
        $groupTickets = Ticket::where('transaction_id', $ticket->transaction_id)
            ->with('evenement', 'tarif')
            ->get();

        return view('evenement-public.confirmation', compact('ticket', 'groupTickets'));
    }

    // Extrait et normalise le réseau depuis payment_method (string ou objet FedaPay)
    protected static function extractPaymentMethod($paymentMethod): string
    {
        // Si c'est un objet avec clé "provider" ou "name"
        if (is_array($paymentMethod) || is_object($paymentMethod)) {
            $paymentMethod = (array) $paymentMethod;
            $provider = strtolower(trim($paymentMethod['provider'] ?? ''));
            $name = strtolower(trim($paymentMethod['name'] ?? ''));

            // Priorité au provider, sinon le name
            $raw = $provider ?: $name;
            return self::normalizePaymentMethod($raw ?: 'mobile_money');
        }

        return self::normalizePaymentMethod((string) $paymentMethod);
    }

    // Normalise les valeurs vers nos clés standardisées
    protected static function normalizePaymentMethod(?string $method): string
    {
        if (!$method) {
            return 'mobile_money';
        }

        $lower = strtolower(trim($method));

        // Espèces
        if (in_array($lower, ['cash', 'especes', 'espèces'])) {
            return 'especes';
        }

        // MTN
        if (str_contains($lower, 'mtn')) {
            return 'mtn';
        }

        // Moov
        if (str_contains($lower, 'moov')) {
            return 'moov';
        }

        // Celtiis
        if (str_contains($lower, 'celtiis') || str_contains($lower, 'celti')) {
            return 'celtiis';
        }

        // Orange
        if (str_contains($lower, 'orange')) {
            return 'orange';
        }

        // Togocel
        if (str_contains($lower, 'togocel') || str_contains($lower, 'togo')) {
            return 'togocel';
        }

        // Airtel
        if (str_contains($lower, 'airtel')) {
            return 'airtel';
        }

        // Free
        if (str_contains($lower, 'free')) {
            return 'free';
        }

        return $lower;
    }
}
