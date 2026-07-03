<?php

namespace App\Http\Controllers;

use App\Mail\TicketEmail;
use App\Models\Ticket;
use App\Models\Log;
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

        $publicKey = $this->fedapay->getPublicKey();
        $sandbox = $this->fedapay->isSandbox();

        return view('evenement-public.paiement', compact('ticket', 'publicKey', 'sandbox'));
    }

    public function callback(Request $request)
    {
        $ticketId = $request->query('ticket');
        $transactionId = $request->query('id');

        if (!$transactionId) {
            return redirect()->route('paiement.show', $ticketId)
                ->with('error', 'Aucune transaction retournee par FedaPay.');
        }

        $ticket = Ticket::with('evenement')->findOrFail($ticketId);

        if ($ticket->statut_paiement === 'payé') {
            return redirect()->route('confirmation.show', $ticket->id);
        }

        $status = $request->query('status');

        if (in_array($status, ['approved', 'completed', 'accepted'], true)) {
            $ticket->update([
                'statut_paiement' => 'payé',
                'transaction_id' => $transactionId,
                'methode_paiement' => $request->query('payment_method', 'mobile_money'),
                'telephone_paiement' => $request->query('phone', $ticket->telephone_acheteur),
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
                'details' => ['transaction_id' => $transactionId, 'methode' => 'fedapay'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('confirmation.show', $ticket->id)
                ->with('success', 'Paiement confirme avec succes!');
        }

        FacadesLog::warning('FedaPay verification failed', [
            'ticket' => $ticket->id,
            'transaction_id' => $transactionId,
            'status' => $status,
        ]);

        return redirect()->route('paiement.show', $ticket->id)
            ->with('error', 'Le paiement n\'a pas pu etre verifie. Veuillez reessayer.');
    }

    public function webhook(Request $request)
    {
        $data = $request->all();

        if (!isset($data['id']) || !isset($data['status'])) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        if (in_array($data['status'], ['approved', 'completed', 'accepted'], true)) {
            $ticket = Ticket::where('transaction_id', $data['id'])
                ->orWhere('id', $data['external_id'] ?? null)
                ->with('evenement')
                ->first();

            if ($ticket && $ticket->statut_paiement !== 'payé') {
                $ticket->update([
                    'statut_paiement' => 'payé',
                    'transaction_id' => $data['id'],
                    'methode_paiement' => $data['payment_method'] ?? 'mobile_money',
                    'telephone_paiement' => $data['phone'] ?? $ticket->telephone_acheteur,
                ]);

                Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function confirmation($ticketId)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($ticketId);

        if ($ticket->statut_paiement !== 'payé') {
            return redirect()->route('paiement.show', $ticket->id);
        }

        return view('evenement-public.confirmation', compact('ticket'));
    }
}
