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
    // Page principale du scan : choix d'événement ou affichage des scans
    public function index(Request $request)
    {
        $accessEvenementId = session('scan_access_evenement_id'); // Événement sélectionné via code d'accès
        $accessEvenement = null;

        if ($accessEvenementId) {
            $accessEvenement = Evenement::find($accessEvenementId);
        }

        if (!$accessEvenement) {
            // Pas d'accès : affiche la sélection d'événement
            $evenements = Evenement::where('user_id', '=', Auth::id())
                ->orderByRaw("CASE statut
                    WHEN 'publié' THEN 1
                    WHEN 'brouillon' THEN 2
                    WHEN 'terminé' THEN 3
                    WHEN 'annulé' THEN 4
                    ELSE 5
                END")
                ->orderBy('date_event', 'desc')
                ->get();

            return view('admin.scan.access', compact('evenements'));
        }

        // Récupère les scans récents et statistiques pour l'événement
        $selectedEvent = $accessEvenement->id;

        $scanQuery = Log::where('type_operation', 'scan')
            ->with(['ticket.evenement'])
            ->whereHas('ticket', function ($q) use ($selectedEvent) {
                $q->where('evenement_id', '=', $selectedEvent); // Filtre par événement
            });

        $scans = $scanQuery->orderByDesc('created_at')->limit(50)->get();

        $stats = [
            'total_scans' => Log::where('type_operation', '=', 'scan')
                ->whereHas('ticket', function ($t) use ($selectedEvent) {
                    return $t->where('evenement_id', '=', $selectedEvent);
                })
                ->count(),
            'scans_today' => Log::where('type_operation', '=', 'scan')
                ->whereDate('created_at', '=', today())
                ->whereHas('ticket', function ($t) use ($selectedEvent) {
                    return $t->where('evenement_id', '=', $selectedEvent);
                })
                ->count(),
            'scans_valides' => Log::where('type_operation', '=', 'scan')
                ->where('details->resultat', 'valide')
                ->whereHas('ticket', function ($t) use ($selectedEvent) {
                    return $t->where('evenement_id', '=', $selectedEvent);
                })
                ->count(),
            'scans_invalides' => Log::where('type_operation', '=', 'scan')
                ->where('details->resultat', 'invalide')
                ->whereHas('ticket', function ($t) use ($selectedEvent) {
                    return $t->where('evenement_id', '=', $selectedEvent);
                })
                ->count(),
        ];

        return view('admin.scan.index', compact('accessEvenement', 'scans', 'stats', 'selectedEvent'));
    }

    // Vérifie un code d'accès scan pour un événement donné
    public function verifierAccessCode(Request $request)
    {
        $evenementId = $request->input('evenement_id');
        $code = strtoupper(trim($request->input('code')));

        if (!$evenementId || !$code) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez sélectionner un événement et entrer un code d\'accès.', // Paramètres manquants
            ]);
        }

        $evenement = Evenement::where('id', '=', $evenementId)
            ->where('user_id', '=', Auth::id()) // Vérification de propriété
            ->first();
            

        if (!$evenement) {
            return response()->json([
                'success' => false,
                'message' => 'Événement introuvable ou non autorisé.',
            ]);
        }

        $accessCode = ScanAccessCode::where('code', '=', $code)
            ->where('evenement_id', '=', $evenementId)
            ->where('actif', '=', true) // Code actif uniquement
            ->with('evenement')
            ->first();

        if (!$accessCode) {
            return response()->json([
                'success' => false,
                'message' => 'Code d\'accès invalide ou désactivé pour cet événement.',
            ]);
        }

        session(['scan_access_evenement_id' => $accessCode->evenement_id]); // Stocke l'accès en session

        return response()->json([
            'success' => true,
            'message' => 'Accès autorisé pour l\'événement : ' . $accessCode->evenement->titre,
            'evenement' => [
                'id' => $accessCode->evenement_id,
                'titre' => $accessCode->evenement->titre,
            ],
        ]);
    }

    // Réinitialise l'accès scan (changement d'événement)
    public function clearAccess()
    {
        session()->forget('scan_access_evenement_id'); // Supprime l'accès de la session

        return redirect()->route('scan.index');
    }

    // Rafraîchit les stats et les scans récents de l'événement (AJAX polling)
    public function historiqueJson(Request $request)
    {
        $accessEvenementId = session('scan_access_evenement_id');

        if (!$accessEvenementId) {
            return response()->json([
                'stats' => null,
                'scans' => [],
            ]);
        }

        $scanQuery = Log::where('type_operation', 'scan')
            ->with(['ticket.evenement'])
            ->whereHas('ticket', function ($q) use ($accessEvenementId) {
                $q->where('evenement_id', '=', $accessEvenementId);
            });

        $scans = $scanQuery->orderByDesc('created_at')->limit(50)->get();

        $stats = [
            'total_scans' => Log::where('type_operation', '=', 'scan')
                ->whereHas('ticket', function ($t) use ($accessEvenementId) {
                    return $t->where('evenement_id', '=', $accessEvenementId);
                })
                ->count(),
            'scans_today' => Log::where('type_operation', '=', 'scan')
                ->whereDate('created_at', '=', today())
                ->whereHas('ticket', function ($t) use ($accessEvenementId) {
                    return $t->where('evenement_id', '=', $accessEvenementId);
                })
                ->count(),
            'scans_valides' => Log::where('type_operation', '=', 'scan')
                ->where('details->resultat', 'valide')
                ->whereHas('ticket', function ($t) use ($accessEvenementId) {
                    return $t->where('evenement_id', '=', $accessEvenementId);
                })
                ->count(),
            'scans_invalides' => Log::where('type_operation', '=', 'scan')
                ->where('details->resultat', 'invalide')
                ->whereHas('ticket', function ($t) use ($accessEvenementId) {
                    return $t->where('evenement_id', '=', $accessEvenementId);
                })
                ->count(),
        ];

        return response()->json([
            'stats' => $stats,
            'scans' => $scans->map(function ($scan) {
                $details = is_array($scan->details) ? $scan->details : json_decode($scan->details, true);

                return [
                    'id' => $scan->id,
                    'resultat' => $details['resultat'] ?? 'inconnu',
                    'raison' => $details['raison'] ?? null,
                    'nom' => $scan->ticket?->nom_acheteur,
                    'evenement' => $scan->ticket?->evenement?->titre,
                    'date' => $scan->created_at?->format('d/m/Y H:i'),
                    'heure' => $scan->created_at?->format('H:i'),
                    'jour' => $scan->created_at?->format('d/m/Y'),
                ];
            }),
        ]);
    }

    // Vérifie et valide un ticket par son code QR
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
                'message' => 'Accès non autorisé. Veuillez d\'abord entrer un code d\'accès.', // Pas de session de scan
                'type' => 'no_access',
            ]);
        }

        $ticket = Ticket::with('evenement', 'tarif')
            ->where('code_unique', Ticket::normaliserCodeSaisi($code))
            ->first();

        if (!$ticket) {
            // Ticket introuvable : log et retourne erreur
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
            // Mauvais événement : log et retourne erreur
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
            // Paiement non confirmé : log et retourne erreur
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
                    'nom_tarif' => $ticket->nom_tarif,
                ],
            ]);
        }

        if ($ticket->annule) {
            // Ticket annulé (erreur d'impression, billet physique invalidé) : log et retourne erreur
            Log::create([
                'ticket_id' => $ticket->id,
                'type_operation' => 'scan',
                'details' => json_encode([
                    'code' => $code,
                    'resultat' => 'invalide',
                    'raison' => 'ticket_annule',
                    'agent' => Auth::id(),
                ]),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ce ticket a été annulé et n\'est plus valide.',
                'type' => 'cancelled',
            ]);
        }

        // Vérification du jour : l'événement doit avoir une session aujourd'hui
        $jourScan = $ticket->evenement->jourScanActuel();

        if (! $jourScan) {
            // Aucune session prévue aujourd'hui
            Log::create([
                'ticket_id' => $ticket->id,
                'type_operation' => 'scan',
                'details' => json_encode([
                    'code' => $code,
                    'resultat' => 'invalide',
                    'raison' => 'pas_de_session_aujourdhui',
                    'agent' => Auth::id(),
                ]),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'L\'événement n\'a pas de session prévue aujourd\'hui. Consultez les dates de l\'événement.',
                'type' => 'no_session',
            ]);
        }

        // Vérification de l'heure : scan autorisé entre l'heure de début et +6h (tolérance)
        $maintenant = now();
        if ($maintenant->lt($jourScan->date_debut)) {
            Log::create([
                'ticket_id' => $ticket->id,
                'type_operation' => 'scan',
                'details' => json_encode([
                    'code' => $code,
                    'resultat' => 'invalide',
                    'raison' => 'session_pas_commencee',
                    'agent' => Auth::id(),
                    'debut' => $jourScan->date_debut->toDateTimeString(),
                ]),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'La session d\'aujourd\'hui commence à '.$jourScan->date_debut->format('H:i').'. Revenez à cette heure.',
                'type' => 'not_started',
            ]);
        }

        if ($maintenant->gt($jourScan->finFenetreScan())) {
            Log::create([
                'ticket_id' => $ticket->id,
                'type_operation' => 'scan',
                'details' => json_encode([
                    'code' => $code,
                    'resultat' => 'invalide',
                    'raison' => 'session_terminee',
                    'agent' => Auth::id(),
                ]),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'La session d\'aujourd\'hui est terminée. Revenez un autre jour de l\'événement.',
                'type' => 'session_ended',
            ]);
        }

        // 1 scan par jour civil : déjà scanné avec succès aujourd'hui ?
        if ($ticket->dejaScanneAujourdhui()) {
            Log::create([
                'ticket_id' => $ticket->id,
                'type_operation' => 'scan',
                'details' => json_encode([
                    'code' => $code,
                    'resultat' => 'deja_utilise',
                    'raison' => 'ticket_deja_scanne_aujourdhui',
                    'agent' => Auth::id(),
                ]),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ce billet a déjà été scanné aujourd\'hui. Il reste valable pour les prochains jours de l\'événement.',
                'type' => 'already_used_today',
                'ticket' => [
                    'code' => $ticket->code_unique,
                    'nom' => $ticket->nom_acheteur,
                    'evenement' => $ticket->evenement->titre,
                    'nom_tarif' => $ticket->nom_tarif,
                ],
            ]);
        }

        Log::create([ // Log du scan réussi
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
                'nom_tarif' => $ticket->nom_tarif,
                'montant' => number_format($ticket->montant, 0, ',', ' ') . ' FCFA',
            ],
        ]);
    }
}
