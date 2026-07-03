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

            $ticket->evenement->increment('quota_vendu', $ticket->quantite);
            if ($ticket->tarif) {
                $ticket->tarif->increment('quantite_vendue', $ticket->quantite);
            }

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
        $source = $request->query('source');

        if (!$transactionId) {
            $fallback = match($source) {
                'agent_vente' => route('agent-vente.dashboard'),
                'vente_manuelle' => route('ventes-manuelles.create'),
                default => route('paiement.show', $ticketId),
            };
            return redirect()->to($fallback)
                ->with('error', 'Aucune transaction retournee par FedaPay.');
        }

        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($ticketId);

        if ($ticket->statut_paiement === 'payé') {
            $fallback = match($source) {
                'agent_vente' => route('agent-vente.dashboard'),
                'vente_manuelle' => route('ventes-manuelles.create'),
                default => route('confirmation.show', $ticket->id),
            };
            return redirect()->to($fallback);
        }

        $status = $request->query('status');

        if (in_array($status, ['approved', 'completed', 'accepted'], true)) {
            $ticket->update([
                'statut_paiement' => 'payé',
                'transaction_id' => $transactionId,
                'methode_paiement' => $request->query('payment_method', 'mobile_money'),
                'telephone_paiement' => $request->query('phone', $ticket->telephone_acheteur),
            ]);

            $ticket->evenement->increment('quota_vendu', $ticket->quantite);
            if ($ticket->tarif) {
                $ticket->tarif->increment('quantite_vendue', $ticket->quantite);
            }

            if ($source === 'agent_vente' && $ticket->agent_vente_id) {
                $agent = \App\Models\AgentVente::find($ticket->agent_vente_id);
                if ($agent) {
                    $agent->increment('tickets_count');
                    $agent->increment('montant_total', $ticket->montant);
                }
            }

            try {
                $ticket->load('evenement', 'tarif');
                Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));
            } catch (\Exception $e) {
                FacadesLog::error('Email non envoye pour ticket ' . $ticket->id . ' : ' . $e->getMessage());
            }

            Log::create([
                'ticket_id' => $ticket->id,
                'type_operation' => 'achat',
                'details' => ['transaction_id' => $transactionId, 'methode' => 'fedapay', 'agent_vente' => $source === 'agent_vente'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

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

        FacadesLog::warning('FedaPay verification failed', [
            'ticket' => $ticket->id,
            'source' => $source,
            'transaction_id' => $transactionId,
            'status' => $status,
        ]);

        $fallback = match($source) {
            'agent_vente' => route('agent-vente.dashboard'),
            'vente_manuelle' => route('ventes-manuelles.create'),
            default => route('paiement.show', $ticket->id),
        };
        return redirect()->to($fallback)
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

                $ticket->load('evenement', 'tarif');
                $ticket->evenement->increment('quota_vendu', $ticket->quantite);
                if ($ticket->tarif) {
                    $ticket->tarif->increment('quantite_vendue', $ticket->quantite);
                }

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
