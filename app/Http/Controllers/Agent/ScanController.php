<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Log;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class ScanController extends Controller
{
    // Affiche le formulaire de vérification du PIN d'accès au scan
    public function verifyPin()
    {
        $agent = Auth::guard('agent')->user();
        if (session('agent_scan_ok') === $agent->id) {
            return redirect()->route('agent.scan.index');
        }
        return view('agent.scan.verify-pin');
    }

    // Vérifie le PIN d'accès avec protection contre les tentatives multiples
    public function checkPin(Request $request)
    {
        $agent = Auth::guard('agent')->user();

        $request->validate([
            'code_acces' => 'required|string|size:6',
        ]);

        if ($agent->evenement->date_event < now()) {
            return back()->withErrors(['code_acces' => 'L\'événement est terminé.'])->onlyInput('code_acces');
        }

        if (!$agent->actif) {
            Auth::guard('agent')->logout();
            return redirect()->route('agent.login')->withErrors(['email' => 'Compte désactivé.']);
        }

        $key = 'agent-pin:' . $agent->id . ':' . $request->ip(); // Clé de rate limiting par agent et IP

        if (RateLimiter::tooManyAttempts($key, 5)) { // Max 5 tentatives par fenêtre
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['code_acces' => "Trop de tentatives. Réessayez dans {$seconds} secondes."])->onlyInput('code_acces');
        }

        if ($agent->bloque_jusqua && $agent->bloque_jusqua->isFuture()) { // Agent temporairement bloqué
            $minutes = now()->diffInMinutes($agent->bloque_jusqua);
            return back()->withErrors(['code_acces' => "Code bloqué. Réessayez dans {$minutes} minutes."])->onlyInput('code_acces');
        }

        if ($request->code_acces !== $agent->code_acces) {
            RateLimiter::hit($key, 300); // Bloque pendant 5 minutes après tentative échouée
            $agent->increment('tentatives_code');

            if ($agent->tentatives_code >= 10) { // Blocage automatique après 10 échecs
                $agent->update(['bloque_jusqua' => now()->addMinutes(30)]);
            }

            return back()->withErrors(['code_acces' => 'Code d\'accès incorrect.'])->onlyInput('code_acces');
        }

        $agent->update(['tentatives_code' => 0, 'bloque_jusqua' => null]);
        RateLimiter::clear($key);
        session(['agent_scan_ok' => $agent->id]);

        return redirect()->route('agent.scan.index');
    }

    // Page principale de scan avec statistiques et scans récents
    public function index()
    {
        $agent = Auth::guard('agent')->user();
        if (session('agent_scan_ok') !== $agent->id) { // Vérifie que le PIN a été validé
            return redirect()->route('agent.scan.pin');
        }

        $stats = [
            'total' => $agent->logs()->where('type_operation', 'scan')->count(),
            'valides' => $agent->logs()->where('type_operation', 'scan')->where('details->resultat', 'valide')->count(),
            'invalides' => $agent->logs()->where('type_operation', 'scan')->where('details->resultat', '!=', 'valide')->count(),
        ];
        $recent = $agent->logs()->where('type_operation', 'scan')->latest('created_at')->limit(20)->get();

        return view('agent.scan.index', [
            'evenement' => $agent->evenement,
            'stats' => $stats,
            'recent' => $recent,
        ]);
    }

    // Vérifie un ticket par son code QR et valide l'entrée
    public function verifier(Request $request)
    {
        $agent = Auth::guard('agent')->user();
        if (session('agent_scan_ok') !== $agent->id) {
            return response()->json(['success' => false, 'message' => 'Session de scan invalide.'], 403);
        }

        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $ticket = Ticket::trouverParCodeSaisi($request->code);

        if (!$ticket) {
            $this->logScan($agent, $request->code, 'invalide', 'Ticket introuvable'); // Log tentative invalide
            return response()->json(['success' => false, 'message' => 'Ticket introuvable.']);
        }

        if ($ticket->evenement_id !== $agent->evenement_id) { // Vérifie l'appartenance à l'événement
            $this->logScan($agent, $request->code, 'invalide', 'Événement non autorisé');
            return response()->json(['success' => false, 'message' => 'Ce ticket ne correspond pas à cet événement.']);
        }

        if ($ticket->statut_paiement !== 'payé') { // Ticket non payé
            $this->logScan($agent, $request->code, 'invalide', 'Paiement non confirmé');
            return response()->json(['success' => false, 'message' => 'Ce ticket n\'a pas été payé.']);
        }

        if ($ticket->annule) { // Ticket annulé (billet physique invalidé)
            $this->logScan($agent, $request->code, 'invalide', 'Ticket annulé');
            return response()->json(['success' => false, 'message' => 'Ce ticket a été annulé et n\'est plus valide.']);
        }

        // Vérification du jour : l'événement doit avoir une session aujourd'hui
        $jourScan = $agent->evenement->jourScanActuel();

        if (! $jourScan) {
            $this->logScan($agent, $request->code, 'invalide', 'Pas de session aujourd\'hui');
            return response()->json(['success' => false, 'message' => 'L\'événement n\'a pas de session prévue aujourd\'hui.']);
        }

        // Vérification de l'heure : scan autorisé entre l'heure de début et +6h (tolérance)
        $maintenant = now();
        if ($maintenant->lt($jourScan->date_debut)) {
            $this->logScan($agent, $request->code, 'invalide', 'Session pas commencée');
            return response()->json(['success' => false, 'message' => 'La session d\'aujourd\'hui commence à '.$jourScan->date_debut->format('H:i').'. Revenez à cette heure.']);
        }

        if ($maintenant->gt($jourScan->finFenetreScan())) {
            $this->logScan($agent, $request->code, 'invalide', 'Session terminée');
            return response()->json(['success' => false, 'message' => 'La session d\'aujourd\'hui est terminée. Revenez un autre jour de l\'événement.']);
        }

        // 1 scan par jour civil : déjà scanné avec succès aujourd'hui ?
        if ($ticket->dejaScanneAujourdhui()) {
            $this->logScan($agent, $request->code, 'deja_utilise', 'Déjà scanné aujourd\'hui', $ticket->id);
            return response()->json(['success' => false, 'message' => 'Ce billet a déjà été scanné aujourd\'hui. Il reste valable pour les prochains jours.', 'ticket' => [
                'nom' => $ticket->nom_acheteur,
                'nom_tarif' => $ticket->nom_tarif,
            ]]);
        }

        $this->logScan($agent, $request->code, 'valide', 'Scan réussi', $ticket->id); // Log scan réussi

        return response()->json(['success' => true, 'message' => 'Ticket validé avec succès !', 'ticket' => [
            'nom' => $ticket->nom_acheteur,
            'email' => $ticket->email_acheteur,
            'nom_tarif' => $ticket->nom_tarif,
            'montant' => number_format($ticket->montant, 0, ',', ' '),
            'transaction_id' => $ticket->transaction_id,
            'date_achat' => $ticket->date_achat ? $ticket->date_achat->format('d/m/Y H:i') : null,
        ]]);
    }

    // Quitte le mode scan et revient au dashboard
    public function exitScan()
    {
        session()->forget('agent_scan_ok');
        return redirect()->route('agent.dashboard');
    }

    // Rafraîchit les stats et les scans récents de l'agent (AJAX polling)
    public function historiqueJson()
    {
        $agent = Auth::guard('agent')->user();
        if (session('agent_scan_ok') !== $agent->id) {
            return response()->json(['error' => 'Session de scan invalide.'], 403);
        }

        $stats = [
            'total' => $agent->logs()->where('type_operation', 'scan')->count(),
            'valides' => $agent->logs()->where('type_operation', 'scan')->where('details->resultat', 'valide')->count(),
            'invalides' => $agent->logs()->where('type_operation', 'scan')->where('details->resultat', '!=', 'valide')->count(),
        ];
        $recent = $agent->logs()->where('type_operation', 'scan')->latest('created_at')->limit(20)->get();

        return response()->json([
            'stats' => $stats,
            'recent' => $recent->map(function ($log) {
                return [
                    'heure' => $log->created_at->format('H:i:s'),
                    'code' => $log->details['code'] ?? '',
                    'resultat' => $log->details['resultat'] ?? 'invalide',
                    'nom' => $log->ticket?->nom_acheteur,
                ];
            }),
        ]);
    }

    // Enregistre un log de scan avec les détails de la tentative
    private function logScan(Agent $agent, string $code, string $resultat, string $raison, ?int $ticketId = null): void
    {
        Log::create([
            'ticket_id' => $ticketId,
            'agent_id' => $agent->id,
            'type_operation' => 'scan',
            'details' => [
                'code' => $code,
                'resultat' => $resultat,
                'raison' => $raison,
                'evenement_id' => $agent->evenement_id,
            ],
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
