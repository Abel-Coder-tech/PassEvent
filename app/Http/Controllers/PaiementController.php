<?php

namespace App\Http\Controllers;

use App\Mail\TicketEmail;
use App\Models\Ticket;
use App\Models\Log;
use App\Services\KkiaPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PaiementController extends Controller
{
    protected KkiaPayService $kkiapay;

    public function __construct(KkiaPayService $kkiapay)
    {
        $this->kkiapay = $kkiapay;
    }

    public function show($ticketId)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($ticketId);

        if ($ticket->statut_paiement === 'payé') {
            return redirect()->route('confirmation.show', $ticket->id);
        }

        return view('evenement-public.paiement', compact('ticket'));
    }

    public function confirmer(Request $request, $ticketId)
    {
        $ticket = Ticket::with('evenement')->findOrFail($ticketId);

        if ($ticket->statut_paiement === 'payé') {
            return redirect()->route('confirmation.show', $ticket->id);
        }

        $validated = $request->validate([
            'methode_paiement' => 'required|in:mtn,moov,celtiis,movimoney',
            'telephone_paiement' => 'required|string|min:6|max:20',
        ], [
            'methode_paiement.required' => 'Choisissez un moyen de paiement.',
            'methode_paiement.in' => 'Moyen de paiement invalide.',
            'telephone_paiement.required' => 'Le numero de telephone est obligatoire.',
            'telephone_paiement.min' => 'Le numero doit contenir au moins 6 caracteres.',
        ]);

        $ticket->update([
            'methode_paiement' => $validated['methode_paiement'],
            'telephone_paiement' => $validated['telephone_paiement'],
            'statut_paiement' => 'payé',
            'transaction_id' => strtoupper('TXN-' . \Illuminate\Support\Str::random(10)),
        ]);

        Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));

        return redirect()->route('confirmation.show', $ticket->id);
    }

    public function callback(Request $request)
    {
        $ticketId = $request->query('ticket');
        $transactionId = $request->query('transaction_id');

        $ticket = Ticket::with('evenement')->findOrFail($ticketId);

        if ($ticket->statut_paiement === 'payé') {
            return redirect()->route('confirmation.show', $ticket->id);
        }

        $verification = $this->kkiapay->verifyTransaction($transactionId);

        if (isset($verification['status']) && $verification['status'] === 'SUCCESS') {
            $ticket->update([
                'statut_paiement' => 'payé',
                'transaction_id' => $transactionId,
                'methode_paiement' => $verification['channel'] ?? 'mobile_money',
                'telephone_paiement' => $verification['phone'] ?? $ticket->telephone_acheteur,
            ]);

            Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));

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

        return redirect()->route('paiement.show', $ticket->id)
            ->with('error', 'Le paiement n\'a pas pu etre verifie. Veuillez reessayer.');
    }

    public function webhook(Request $request)
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

    public function confirmation($ticketId)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($ticketId);

        if ($ticket->statut_paiement !== 'payé') {
            return redirect()->route('paiement.show', $ticket->id);
        }

        return view('evenement-public.confirmation', compact('ticket'));
    }
}
