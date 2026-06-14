<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Evenement;
use App\Models\ScanAccessCode;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    public function index(Request $request)
    {
        $accessEvenementId = session('scan_access_evenement_id');
        $accessEvenement = null;

        if ($accessEvenementId) {
            $accessEvenement = Evenement::find($accessEvenementId);
        }

        if (!$accessEvenement) {
            $evenements = Evenement::where('user_id', Auth::id())
                ->orderByRaw("FIELD(statut, 'publié', 'brouillon', 'terminé', 'annulé')")
                ->orderBy('date_event', 'desc')
                ->get();

            return view('admin.scan.access', compact('evenements'));
        }

        $selectedEvent = $accessEvenement->id;

        $scanQuery = Log::where('type_operation', 'scan')
            ->with(['ticket.evenement'])
            ->whereHas('ticket', function ($q) use ($selectedEvent) {
                $q->where('evenement_id', $selectedEvent);
            });

        $scans = $scanQuery->orderByDesc('created_at')->limit(50)->get();

        $stats = [
            'total_scans' => Log::where('type_operation', 'scan')
                ->whereHas('ticket', fn($t) => $t->where('evenement_id', $selectedEvent))
                ->count(),
            'scans_today' => Log::where('type_operation', 'scan')
                ->whereDate('created_at', today())
                ->whereHas('ticket', fn($t) => $t->where('evenement_id', $selectedEvent))
                ->count(),
            'scans_valides' => Log::where('type_operation', 'scan')
                ->where('details->resultat', 'valide')
                ->whereHas('ticket', fn($t) => $t->where('evenement_id', $selectedEvent))
                ->count(),
            'scans_invalides' => Log::where('type_operation', 'scan')
                ->where('details->resultat', 'invalide')
                ->whereHas('ticket', fn($t) => $t->where('evenement_id', $selectedEvent))
                ->count(),
        ];

        return view('admin.scan.index', compact('accessEvenement', 'scans', 'stats', 'selectedEvent'));
    }

    public function verifierAccessCode(Request $request)
    {
        $evenementId = $request->input('evenement_id');
        $code = strtoupper(trim($request->input('code')));

        if (!$evenementId || !$code) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez sélectionner un événement et entrer un code d\'accès.',
            ]);
        }

        $evenement = Evenement::where('id', $evenementId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$evenement) {
            return response()->json([
                'success' => false,
                'message' => 'Événement introuvable ou non autorisé.',
            ]);
        }

        $accessCode = ScanAccessCode::where('code', $code)
            ->where('evenement_id', $evenementId)
            ->where('actif', true)
            ->with('evenement')
            ->first();

        if (!$accessCode) {
            return response()->json([
                'success' => false,
                'message' => 'Code d\'accès invalide ou désactivé pour cet événement.',
            ]);
        }

        session(['scan_access_evenement_id' => $accessCode->evenement_id]);

        return response()->json([
            'success' => true,
            'message' => 'Accès autorisé pour l\'événement : ' . $accessCode->evenement->titre,
            'evenement' => [
                'id' => $accessCode->evenement_id,
                'titre' => $accessCode->evenement->titre,
            ],
        ]);
    }

    public function clearAccess()
    {
        session()->forget('scan_access_evenement_id');

        return redirect()->route('scan.index');
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

        $accessEvenementId = session('scan_access_evenement_id');

        if (!$accessEvenementId) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé. Veuillez d\'abord entrer un code d\'accès.',
                'type' => 'no_access',
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
                    'evenement_id' => $accessEvenementId,
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

        if ($ticket->evenement_id !== $accessEvenementId) {
            Log::create([
                'ticket_id' => $ticket->id,
                'type_operation' => 'scan',
                'details' => json_encode([
                    'code' => $code,
                    'resultat' => 'invalide',
                    'raison' => 'evenement_non_autorise',
                    'agent' => Auth::id(),
                ]),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ce ticket ne correspond pas à l\'événement que vous scannéz.',
                'type' => 'wrong_event',
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
