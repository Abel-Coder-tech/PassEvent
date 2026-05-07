<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Evenement;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    public function index(Request $request)
    {
        $evenements = Evenement::where('user_id', Auth::id())
            ->where('statut', 'publié')
            ->orderByDesc('date_event')
            ->get();

        $selectedEvent = $request->input('evenement_id');

        $scanQuery = Log::where('type_operation', 'scan')
            ->with(['ticket.evenement']);

        if ($selectedEvent) {
            $scanQuery->whereHas('ticket', function ($q) use ($selectedEvent) {
                $q->where('evenement_id', $selectedEvent);
            });
        }

        $scans = $scanQuery->orderByDesc('created_at')->limit(50)->get();

        $stats = [
            'total_scans' => Log::where('type_operation', 'scan')
                ->when($selectedEvent, function ($q) use ($selectedEvent) {
                    $q->whereHas('ticket', fn($t) => $t->where('evenement_id', $selectedEvent));
                })
                ->count(),
            'scans_today' => Log::where('type_operation', 'scan')
                ->whereDate('created_at', today())
                ->when($selectedEvent, function ($q) use ($selectedEvent) {
                    $q->whereHas('ticket', fn($t) => $t->where('evenement_id', $selectedEvent));
                })
                ->count(),
            'scans_valides' => Log::where('type_operation', 'scan')
                ->where('details->resultat', 'valide')
                ->when($selectedEvent, function ($q) use ($selectedEvent) {
                    $q->whereHas('ticket', fn($t) => $t->where('evenement_id', $selectedEvent));
                })
                ->count(),
            'scans_invalides' => Log::where('type_operation', 'scan')
                ->where('details->resultat', 'invalide')
                ->when($selectedEvent, function ($q) use ($selectedEvent) {
                    $q->whereHas('ticket', fn($t) => $t->where('evenement_id', $selectedEvent));
                })
                ->count(),
        ];

        return view('admin.scan.index', compact('evenements', 'scans', 'stats', 'selectedEvent'));
    }

    public function verifier(Request $request)
    {
        $code = $request->input('code');

        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun code fourni.',
            ]);
        }

        $ticket = Ticket::with('evenement', 'tarif')
            ->where('code_unique', $code)
            ->first();

        if (!$ticket) {
            Log::create([
                'type_operation' => 'scan',
                'details' => json_encode([
                    'code' => $code,
                    'resultat' => 'invalide',
                    'raison' => 'ticket_introuvable',
                    'agent' => Auth::id(),
                ]),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ticket introuvable. Ce code n\'existe pas dans notre systeme.',
                'type' => 'not_found',
            ]);
        }

        if ($ticket->statut_paiement !== 'payé') {
            Log::create([
                'ticket_id' => $ticket->id,
                'type_operation' => 'scan',
                'details' => json_encode([
                    'code' => $code,
                    'resultat' => 'invalide',
                    'raison' => 'paiement_non_confirmé',
                    'statut' => $ticket->statut_paiement,
                    'agent' => Auth::id(),
                ]),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Paiement non confirmé. Ce ticket n\'est pas encore valide.',
                'type' => 'unpaid',
                'ticket' => [
                    'code' => $ticket->code_unique,
                    'nom' => $ticket->nom_acheteur,
                    'evenement' => $ticket->evenement->titre,
                    'categorie' => ucfirst($ticket->categorie),
                    'type' => ucfirst($ticket->type),
                ],
            ]);
        }

        if ($ticket->utilise) {
            Log::create([
                'ticket_id' => $ticket->id,
                'type_operation' => 'scan',
                'details' => json_encode([
                    'code' => $code,
                    'resultat' => 'deja_utilise',
                    'raison' => 'ticket_deja_scanne',
                    'agent' => Auth::id(),
                ]),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ticket déjà utilisé ! Ce billet a déjà été scanné.',
                'type' => 'already_used',
                'ticket' => [
                    'code' => $ticket->code_unique,
                    'nom' => $ticket->nom_acheteur,
                    'evenement' => $ticket->evenement->titre,
                    'categorie' => ucfirst($ticket->categorie),
                    'type' => ucfirst($ticket->type),
                    'date_scan' => $ticket->updated_at->format('d/m/Y H:i'),
                ],
            ]);
        }

        if ($ticket->evenement->date_event->isPast()) {
            Log::create([
                'ticket_id' => $ticket->id,
                'type_operation' => 'scan',
                'details' => json_encode([
                    'code' => $code,
                    'resultat' => 'invalide',
                    'raison' => 'evenement_passe',
                    'agent' => Auth::id(),
                ]),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Événement terminé. Ce ticket n\'est plus valide.',
                'type' => 'expired',
                'ticket' => [
                    'code' => $ticket->code_unique,
                    'nom' => $ticket->nom_acheteur,
                    'evenement' => $ticket->evenement->titre,
                    'date_event' => $ticket->evenement->date_event->format('d/m/Y'),
                ],
            ]);
        }

        $ticket->update(['utilise' => true]);

        Log::create([
            'ticket_id' => $ticket->id,
            'type_operation' => 'scan',
            'details' => json_encode([
                'code' => $code,
                'resultat' => 'valide',
                'agent' => Auth::id(),
            ]),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket valide ! Accès autorisé.',
            'type' => 'valid',
            'ticket' => [
                'code' => $ticket->code_unique,
                'nom' => $ticket->nom_acheteur,
                'email' => $ticket->email_acheteur,
                'evenement' => $ticket->evenement->titre,
                'categorie' => ucfirst($ticket->categorie),
                'type' => ucfirst($ticket->type),
                'montant' => number_format($ticket->montant, 0, ',', ' ') . ' FCFA',
            ],
        ]);
    }
}
