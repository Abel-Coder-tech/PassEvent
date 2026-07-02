<?php

namespace App\Http\Controllers;

use App\Mail\TicketEmail;
use App\Models\Ticket;
use App\Models\Log;
use App\Models\User;
use App\Services\KkiaPayService;
use App\Services\SebpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\Mail;

class PaiementController extends Controller
{
    protected KkiaPayService $kkiapay;
    protected SebpayService $sebpay;

    public function __construct(KkiaPayService $kkiapay, SebpayService $sebpay)
    {
        $this->kkiapay = $kkiapay;
        $this->sebpay = $sebpay;
    }

    public function show($ticketId)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($ticketId);

        if ($ticket->statut_paiement === 'payé') {
            return redirect()->route('confirmation.show', $ticket->id);
        }

        if ($ticket->montant <= 0) {
            $ticket->update([
                'statut_paiement' => 'payé',
                'transaction_id' => 'GRATUIT-' . strtoupper(\Illuminate\Support\Str::random(8)),
            ]);

            try {
                $ticket->load('evenement', 'tarif');
                Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));
            } catch (\Exception $e) {
                FacadesLog::error('Email gratuit non envoye pour ticket ' . $ticket->id . ' : ' . $e->getMessage());
            }

            return redirect()->route('confirmation.show', $ticket->id)
                ->with('success', 'Participation confirmee ! Votre billet a ete envoye par email.');
        }

        $methodes = $this->getMethodesDisponibles();
        $configKkiapay = $methodes['kkiapay'] ?? null;
        $configSebpay = $methodes['sebpay'] ?? null;

        $kkiapayKey = $configKkiapay['cle'] ?? null;
        if (!$kkiapayKey) {
            $superAdmin = User::where('role', 'super_admin')->first();
            $kkiapayKey = $superAdmin->kkiapay_public_key ?? null;
        }
        $kkiapaySandbox = $configKkiapay['sandbox'] ?? true;

        $sebpayConfigured = $this->sebpay->isConfigured();

        return view('evenement-public.paiement',
            compact('ticket', 'methodes', 'kkiapayKey', 'kkiapaySandbox', 'sebpayConfigured'));
    }

    protected function getMethodesDisponibles(): array
    {
        $methodes = [];

        $configs = config('paiement.methodes', []);

        foreach ($configs as $cle => $config) {
            $active = $config['active'] ?? false;

            if ($cle === 'kkiapay' && !$active) {
                $superAdmin = User::where('role', 'super_admin')->first();
                $active = !empty($superAdmin->kkiapay_public_key);
            }

            if ($active) {
                $methodes[$cle] = $config;
            }
        }

        return $methodes;
    }

    // ========================================================================
    // KKiaPay
    // ========================================================================

    public function callback(Request $request)
    {
        $ticketId = $request->query('ticket');
        $transactionId = $request->query('transaction_id');

        if (!$transactionId) {
            return redirect()->route('paiement.show', $ticketId)
                ->with('error', 'Aucune transaction retournee par KKiaPay.');
        }

        $ticket = Ticket::with('evenement')->findOrFail($ticketId);

        if ($ticket->statut_paiement === 'payé') {
            return redirect()->route('confirmation.show', $ticket->id);
        }

        try {
            $verification = $this->kkiapay->verifyTransaction($transactionId);
        } catch (\Exception $e) {
            FacadesLog::error('KKiaPay verify failed for ticket ' . $ticket->id . ': ' . $e->getMessage());
            return redirect()->route('paiement.show', $ticket->id)
                ->with('error', 'Impossible de verifier le paiement. Veuillez reessayer.');
        }

        $status = $verification['status'] ?? ($verification['state'] ?? null);
        $isSuccess = in_array($status, ['SUCCESS', 'success', 'COMPLETED', 'completed', true], true);

        if ($isSuccess) {
            $ticket->update([
                'statut_paiement' => 'payé',
                'transaction_id' => $transactionId,
                'methode_paiement' => $verification['channel'] ?? $verification['operator'] ?? 'mobile_money',
                'telephone_paiement' => $verification['phone'] ?? $ticket->telephone_acheteur,
            ]);

            try {
                $ticket->load('evenement', 'tarif');
                Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));
            } catch (\Exception $e) {
                FacadesLog::error('Email non envoye pour ticket ' . $ticket->id . ' : ' . $e->getMessage());
            }

            Log::create([
                'ticket_id' => $ticket->id,
                'type_operation' => 'achat',
                'details' => ['transaction_id' => $transactionId, 'methode' => $verification['channel'] ?? 'mobile_money'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('confirmation.show', $ticket->id)
                ->with('success', 'Paiement confirme avec succes!');
        }

        FacadesLog::warning('KKiaPay verification failed', [
            'ticket' => $ticket->id,
            'transaction_id' => $transactionId,
            'response' => $verification,
        ]);

        return redirect()->route('paiement.show', $ticket->id)
            ->with('error', 'Le paiement n\'a pas pu etre verifie. Veuillez reessayer.');
    }

    public function webhookKKiaPay(Request $request)
    {
        $data = $request->all();

        if (!isset($data['transaction_id']) || !isset($data['status'])) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        if ($data['status'] === 'SUCCESS') {
            $ticket = Ticket::where('transaction_id', $data['transaction_id'])
                ->orWhere('id', $data['external_id'] ?? null)
                ->with('evenement')
                ->first();

            if ($ticket && $ticket->statut_paiement !== 'payé') {
                $ticket->update([
                    'statut_paiement' => 'payé',
                    'transaction_id' => $data['transaction_id'],
                    'methode_paiement' => $data['channel'] ?? 'mobile_money',
                    'telephone_paiement' => $data['phone'] ?? $ticket->telephone_acheteur,
                ]);

                Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));
            }
        }

        return response()->json(['status' => 'ok']);
    }

    // ========================================================================
    // Sebpay
    // ========================================================================

    public function initSebpay(Request $request)
    {
        $ticketId = $request->input('ticket_id');
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($ticketId);

        if ($ticket->statut_paiement === 'payé') {
            return redirect()->route('confirmation.show', $ticket->id);
        }

        if (!$this->sebpay->isConfigured()) {
            return redirect()->route('paiement.show', $ticket->id)
                ->with('error', 'Sebpay n\'est pas configuré.');
        }

        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'operator' => 'required|string|in:mtn,moov,orange,wav',
        ]);

        $result = $this->sebpay->createTransaction(
            montant: $ticket->montant,
            externalReference: (string) $ticket->id,
            phone: $validated['phone'],
            operator: $validated['operator'],
            country: 'BJ',
            callbackUrl: route('paiement.sebpay.webhook'),
        );

        if (!$result['success']) {
            return redirect()->route('paiement.show', $ticket->id)
                ->with('error', 'Erreur Sebpay : ' . ($result['error'] ?? 'Impossible de créer la transaction.'));
        }

        $transactionId = $result['transaction_id'];
        $providerLink = $result['provider_link'];

        if ($transactionId) {
            $ticket->update([
                'transaction_id' => $transactionId,
                'telephone_acheteur' => $validated['phone'],
            ]);
        }

        if ($providerLink) {
            return view('evenement-public.paiement-redirect', [
                'url' => $providerLink,
                'ticket' => $ticket,
                'transaction_id' => $transactionId,
            ]);
        }

        session()->flash('info', 'Un message USSD vous a été envoyé. Validez le paiement sur votre téléphone.');

        return redirect()->route('paiement.show', $ticket->id);
    }

    public function callbackSebpay(Request $request)
    {
        $ticketId = $request->query('ticket');
        $transactionId = $request->query('transaction_id') ?? $request->query('id');

        if (!$transactionId) {
            return redirect()->route('paiement.show', $ticketId)
                ->with('error', 'Aucune transaction retournee par Sebpay.');
        }

        $ticket = Ticket::with('evenement')->findOrFail($ticketId);

        if ($ticket->statut_paiement === 'payé') {
            return redirect()->route('confirmation.show', $ticket->id);
        }

        try {
            $verification = $this->sebpay->getTransaction($transactionId);
        } catch (\Exception $e) {
            FacadesLog::error('Sebpay verify failed for ticket ' . $ticket->id . ': ' . $e->getMessage());
            return redirect()->route('paiement.show', $ticket->id)
                ->with('error', 'Impossible de verifier le paiement Sebpay. Veuillez reessayer.');
        }

        if ($verification['success']) {
            $ticket->update([
                'statut_paiement' => 'payé',
                'transaction_id' => $transactionId,
                'methode_paiement' => 'sebpay',
            ]);

            try {
                $ticket->load('evenement', 'tarif');
                Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));
            } catch (\Exception $e) {
                FacadesLog::error('Email non envoye pour ticket ' . $ticket->id . ' : ' . $e->getMessage());
            }

            Log::create([
                'ticket_id' => $ticket->id,
                'type_operation' => 'achat',
                'details' => ['transaction_id' => $transactionId, 'methode' => 'sebpay'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('confirmation.show', $ticket->id)
                ->with('success', 'Paiement confirme avec succes!');
        }

        if ($verification['status'] === 'rejected') {
            $ticket->update(['transaction_id' => null]);
            return redirect()->route('paiement.show', $ticket->id)
                ->with('error', 'Le paiement a ete annule ou rejete.');
        }

        return redirect()->route('paiement.show', $ticket->id)
            ->with('info', 'Paiement en attente de validation. Verifiez votre telephone.');
    }

    public function webhookSebpay(Request $request)
    {
        $signature = $request->header('X-SebPay-Signature');
        $rawBody = $request->getContent();

        if ($signature && !$this->sebpay->verifyWebhookSignature($rawBody, $signature)) {
            FacadesLog::warning('Sebpay webhook signature mismatch', ['body' => $rawBody]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data = $request->all();

        $transactionId = $data['transaction_id'] ?? null;
        $externalRef = $data['external_reference'] ?? null;
        $status = $data['status'] ?? null;

        if (!$transactionId || !$status) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        if ($status === 'approved' && $externalRef) {
            $ticket = Ticket::find($externalRef);

            if ($ticket && $ticket->statut_paiement !== 'payé') {
                $ticket->update([
                    'statut_paiement' => 'payé',
                    'transaction_id' => $transactionId,
                    'methode_paiement' => 'sebpay',
                    'telephone_paiement' => $data['customer_phone'] ?? $ticket->telephone_acheteur,
                ]);

                Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));
            }
        }

        return response()->json(['status' => 'ok']);
    }

    // ========================================================================
    // Confirmation
    // ========================================================================

    public function confirmation($ticketId)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($ticketId);

        if ($ticket->statut_paiement !== 'payé') {
            return redirect()->route('paiement.show', $ticket->id);
        }

        return view('evenement-public.confirmation', compact('ticket'));
    }
}
