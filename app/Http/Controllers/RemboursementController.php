<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Evenement;
use App\Models\Log;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use App\Models\DemandeRemboursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RemboursementController extends Controller
{
    public function index(Request $request)
    {
        $evenements = Evenement::where('user_id', Auth::id())
            ->orderByDesc('date_event')
            ->get();

        $evenementsIds = $evenements->pluck('id');

        $selectedEvent = $request->input('evenement_id');
        $q = $request->input('q');
        $statut = $request->input('statut', 'paye');

        $query = Ticket::with('evenement', 'tarif')->whereIn('evenement_id', $evenementsIds);

        if ($selectedEvent && $evenementsIds->contains($selectedEvent)) {
            $query->where('evenement_id', $selectedEvent);
        }

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nom_acheteur', 'like', '%' . $q . '%')
                    ->orWhere('email_acheteur', 'like', '%' . $q . '%')
                    ->orWhere('code_unique', 'like', '%' . $q . '%')
                    ->orWhere('transaction_id', 'like', '%' . $q . '%');
            });
        }

        if ($statut === 'rembourse') {
            $tickets = (clone $query)->where('statut_paiement', 'remboursé')->orderByDesc('updated_at')->paginate(20);
        } else {
            $tickets = (clone $query)->where('statut_paiement', 'payé')->orderByDesc('date_achat')->paginate(20);
        }

        $stats = [
            'payes' => (clone $query)->where('statut_paiement', 'payé')->count(),
            'rembourses' => (clone $query)->where('statut_paiement', 'remboursé')->count(),
            'montant_total_rembourse' => (clone $query)->where('statut_paiement', 'remboursé')->sum('montant'),
            'montant_remboursable' => (clone $query)->where('statut_paiement', 'payé')->sum('montant'),
            'demandes_encours' => DemandeRemboursement::whereIn('evenement_id', $evenementsIds)
                ->whereIn('statut', ['en_attente', 'en_cours'])->count(),
        ];

        $demandes = DemandeRemboursement::whereIn('evenement_id', $evenementsIds)
            ->with('evenement')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.remboursements.index', compact(
            'evenements', 'tickets', 'stats', 'demandes',
            'selectedEvent', 'q', 'statut'
        ));
    }

    public function demander(Request $request)
    {
        $validated = $request->validate([
            'ticket_id' => 'required_without:evenement_id|exists:ticket,id',
            'evenement_id' => 'required_without:ticket_id|exists:evenements,id',
            'motif' => 'required|string|min:5|max:1000',
        ]);

        if (!empty($validated['ticket_id'])) {
            $ticket = Ticket::with('evenement')->findOrFail($validated['ticket_id']);
            $evenement = $ticket->evenement;

            if ($evenement->user_id !== Auth::id()) {
                return back()->withErrors(['error' => 'Ce ticket ne vous appartient pas.']);
            }
            if ($ticket->statut_paiement !== 'payé') {
                return back()->withErrors(['error' => 'Ce ticket n\'est pas éligible au remboursement.']);
            }

            $demande = DemandeRemboursement::create([
                'organisateur_id' => Auth::id(),
                'evenement_id' => $evenement->id,
                'type' => 'individuel',
                'montant_total' => $ticket->montant,
                'motif' => $validated['motif'],
                'statut' => 'en_attente',
            ]);
            $demande->tickets()->attach($ticket->id);

            return back()->with('success', 'Demande de remboursement envoyée. Le superadmin va la traiter.');
        }

        if (!empty($validated['evenement_id'])) {
            $evenement = Evenement::findOrFail($validated['evenement_id']);
            if ($evenement->user_id !== Auth::id()) {
                return back()->withErrors(['error' => 'Cet événement ne vous appartient pas.']);
            }

            $tickets = Ticket::where('evenement_id', $evenement->id)
                ->where('statut_paiement', 'payé')
                ->get();

            if ($tickets->isEmpty()) {
                return back()->withErrors(['error' => 'Aucun ticket payant à rembourser pour cet événement.']);
            }

            $montantTotal = $tickets->sum('montant');

            $demande = DemandeRemboursement::create([
                'organisateur_id' => Auth::id(),
                'evenement_id' => $evenement->id,
                'type' => 'groupe',
                'montant_total' => $montantTotal,
                'motif' => $validated['motif'],
                'statut' => 'en_attente',
            ]);
            $demande->tickets()->attach($tickets->pluck('id'));

            return back()->with('success', 'Demande de remboursement groupé envoyée (' . $tickets->count() . ' tickets, ' . number_format($montantTotal, 0, ',', ' ') . ' F).');
        }

        return back()->withErrors(['error' => 'Aucun ticket ou événement spécifié.']);
    }

    public function annulerRemboursement(Request $request, $ticketId)
    {
        $ticket = Ticket::with('evenement')->findOrFail($ticketId);

        if ($ticket->statut_paiement !== 'remboursé') {
            return back()->withErrors(['error' => 'Ce ticket n\'est pas remboursé.']);
        }

        if ($ticket->evenement->user_id !== Auth::id()) {
            return back()->withErrors(['error' => 'Vous n\'avez pas l\'autorisation.']);
        }

        $ticket->update(['statut_paiement' => 'payé']);

        if ($ticket->tarif) {
            $ticket->tarif->increment('quantite_vendue');
        }
        if ($ticket->evenement) {
            $ticket->evenement->increment('quota_vendu');
        }

        Log::create([
            'ticket_id' => $ticket->id,
            'type_operation' => 'remboursement',
            'details' => json_encode([
                'action' => 'annulation_remboursement',
                'agent' => Auth::id(),
            ]),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Remboursement annulé. Le ticket est de nouveau valide.');
    }
}
