<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Evenement;
use App\Models\Log;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
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
        $statut = $request->input('statut', 'remboursable');

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

        $remboursables = (clone $query)
            ->where('statut_paiement', 'payé')
            ->where(function ($sub) {
                $sub->where('date_achat', '>=', now()->subDays(30))
                    ->orWhereHas('evenement', function ($ev) {
                        $ev->where('date_event', '>=', now());
                    });
            })
            ->orderByDesc('date_achat');

        $rembourses = (clone $query)
            ->where('statut_paiement', 'remboursé')
            ->orderByDesc('updated_at');

        $remboursements = $statut === 'rembourse' ? $rembourses->paginate(20) : $remboursables->paginate(20);

        $stats = [
            'remboursables' => (clone $query)->where('statut_paiement', 'payé')->count(),
            'rembourses' => (clone $query)->where('statut_paiement', 'remboursé')->count(),
            'montant_total_rembourse' => (clone $query)->where('statut_paiement', 'remboursé')->sum('montant'),
            'montant_remboursable' => (clone $query)->where('statut_paiement', 'payé')->sum('montant'),
        ];

        return view('admin.remboursements.index', compact(
            'evenements',
            'remboursements',
            'stats',
            'selectedEvent',
            'q',
            'statut'
        ));
    }

    public function rembourser(Request $request, $ticketId)
    {
        $ticket = Ticket::with('evenement')->findOrFail($ticketId);

        if ($ticket->statut_paiement !== 'payé') {
            return back()->withErrors(['error' => 'Ce ticket n\'est pas éligible au remboursement.']);
        }

        if ($ticket->evenement->user_id !== Auth::id()) {
            return back()->withErrors(['error' => 'Vous n\'avez pas l\'autorisation de rembourser ce ticket.']);
        }

        $validated = $request->validate([
            'motif' => 'required|string|min:10|max:500',
        ]);

        DB::beginTransaction();

        try {
            $ticket->update([
                'statut_paiement' => 'remboursé',
            ]);

            Log::create([
                'ticket_id' => $ticket->id,
                'type_operation' => 'remboursement',
                'details' => json_encode([
                    'motif' => $validated['motif'],
                    'montant' => $ticket->montant,
                    'agent' => Auth::id(),
                    'transaction_id' => $ticket->transaction_id,
                ]),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Notification::create([
                'ticket_id' => $ticket->id,
                'canal' => 'email',
                'statut' => 'envoyé',
                'message' => 'Votre ticket pour "' . $ticket->evenement->titre . '" a été remboursé. Motif : ' . $validated['motif'],
                'date_envoi' => now(),
            ]);

            DB::commit();

            $organisateur = Auth::user();

            Message::create([
                'user_id' => null,
                'evenement_id' => $ticket->evenement_id,
                'nom_complet' => $organisateur->nom,
                'email' => $organisateur->email,
                'objet' => '[Remboursement] ' . $ticket->evenement->titre,
                'message' => 'Remboursement de ' . number_format($ticket->montant, 0, ',', ' ') . ' F pour le ticket ' . $ticket->code_unique . ' (' . $ticket->nom_acheteur . ").\n\nMotif : " . $validated['motif'],
            ]);

            $superAdmins = User::where('role', 'super_admin')->get();
            foreach ($superAdmins as $sa) {
                Mail::raw(
                    "Nouvelle demande de remboursement\n\n" .
                    "Organisateur : {$organisateur->nom} ({$organisateur->email})\n" .
                    "Événement : {$ticket->evenement->titre}\n" .
                    "Ticket : {$ticket->code_unique}\n" .
                    "Acheteur : {$ticket->nom_acheteur} ({$ticket->email_acheteur})\n" .
                    "Montant : " . number_format($ticket->montant, 0, ',', ' ') . " F\n" .
                    "Motif : {$validated['motif']}\n\n" .
                    "Connectez-vous au super dashboard pour voir les détails.",
                    function ($m) use ($sa, $organisateur, $ticket) {
                        $m->to($sa->email)
                          ->subject("[PaxEvent] Remboursement - {$ticket->evenement->titre}");
                    }
                );
            }

            return back()->with('success', 'Remboursement effectué avec succès. L\'acheteur sera notifié.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors du remboursement. Veuillez réessayer.']);
        }
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

        $organisateur = Auth::user();

        Message::create([
            'user_id' => null,
            'evenement_id' => $ticket->evenement_id,
            'nom_complet' => $organisateur->nom,
            'email' => $organisateur->email,
            'objet' => '[Remboursement] Annulation - ' . $ticket->evenement->titre,
            'message' => 'Remboursement annulé pour le ticket ' . $ticket->code_unique . ' (' . $ticket->nom_acheteur . ") de " . number_format($ticket->montant, 0, ',', ' ') . " F.",
        ]);

        $superAdmins = User::where('role', 'super_admin')->get();
        foreach ($superAdmins as $sa) {
            Mail::raw(
                "Annulation de remboursement\n\n" .
                "Organisateur : {$organisateur->nom} ({$organisateur->email})\n" .
                "Événement : {$ticket->evenement->titre}\n" .
                "Ticket : {$ticket->code_unique}\n" .
                "Acheteur : {$ticket->nom_acheteur} ({$ticket->email_acheteur})\n" .
                "Montant : " . number_format($ticket->montant, 0, ',', ' ') . " F\n\n" .
                "Le ticket est de nouveau valide.",
                function ($m) use ($sa) {
                    $m->to($sa->email)
                      ->subject("[PaxEvent] Annulation remboursement");
                }
            );
        }

        return back()->with('success', 'Remboursement annulé. Le ticket est de nouveau valide.');
    }
}
