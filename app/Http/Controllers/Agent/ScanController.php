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
    public function verifyPin()
    {
        $agent = Auth::guard('agent')->user();
        if (session('agent_scan_ok') === $agent->id) {
            return redirect()->route('agent.scan.index');
        }
        return view('agent.scan.verify-pin');
    }

    public function checkPin(Request $request)
    {
        $agent = Auth::guard('agent')->user();

        $request->validate([
            'code_acces' => 'required|string|size:6',
        ]);

        if ($agent->evenement->date_event < now()) {
            return back()->withErrors(['code_acces' => 'L\'événement est terminé.'])->onlyInput('code_acces');
        }

        if ($agent->evenement->date_fin_vente && $agent->evenement->date_fin_vente->isFuture()) {
            return back()->withErrors(['code_acces' => 'La vente est toujours en cours. Le scan sera disponible après la clôture des ventes.'])->onlyInput('code_acces');
        }

        if (!$agent->actif) {
            Auth::guard('agent')->logout();
            return redirect()->route('agent.login')->withErrors(['email' => 'Compte désactivé.']);
        }

        $key = 'agent-pin:' . $agent->id . ':' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['code_acces' => "Trop de tentatives. Réessayez dans {$seconds} secondes."])->onlyInput('code_acces');
        }

        if ($agent->bloque_jusqua && $agent->bloque_jusqua->isFuture()) {
            $minutes = now()->diffInMinutes($agent->bloque_jusqua);
            return back()->withErrors(['code_acces' => "Code bloqué. Réessayez dans {$minutes} minutes."])->onlyInput('code_acces');
        }

        if ($request->code_acces !== $agent->code_acces) {
            RateLimiter::hit($key, 300);
            $agent->increment('tentatives_code');

            if ($agent->tentatives_code >= 10) {
                $agent->update(['bloque_jusqua' => now()->addMinutes(30)]);
            }

            return back()->withErrors(['code_acces' => 'Code d\'accès incorrect.'])->onlyInput('code_acces');
        }

        $agent->update(['tentatives_code' => 0, 'bloque_jusqua' => null]);
        RateLimiter::clear($key);
        session(['agent_scan_ok' => $agent->id]);

        return redirect()->route('agent.scan.index');
    }

    public function index()
    {
        $agent = Auth::guard('agent')->user();
        if (session('agent_scan_ok') !== $agent->id) {
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

    public function verifier(Request $request)
    {
        $agent = Auth::guard('agent')->user();
        if (session('agent_scan_ok') !== $agent->id) {
            return response()->json(['success' => false, 'message' => 'Session de scan invalide.'], 403);
        }

        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        if ($agent->evenement->date_event < now()) {
            return response()->json(['success' => false, 'message' => 'L\'événement est terminé.']);
        }

        $ticket = Ticket::where('code_unique', $request->code)->first();

        if (!$ticket) {
            $this->logScan($agent, $request->code, 'invalide', 'Ticket introuvable');
            return response()->json(['success' => false, 'message' => 'Ticket introuvable.']);
        }

        if ($ticket->evenement_id !== $agent->evenement_id) {
            $this->logScan($agent, $request->code, 'invalide', 'Événement non autorisé');
            return response()->json(['success' => false, 'message' => 'Ce ticket ne correspond pas à cet événement.']);
        }

        if ($ticket->evenement->date_fin_vente && $ticket->evenement->date_fin_vente->isFuture()) {
            $this->logScan($agent, $request->code, 'invalide', 'Vente toujours en cours');
            return response()->json(['success' => false, 'message' => 'La vente est toujours en cours. Le scan n\'est pas encore autorisé.']);
        }

        if ($ticket->statut_paiement !== 'payé') {
            $this->logScan($agent, $request->code, 'invalide', 'Paiement non confirmé');
            return response()->json(['success' => false, 'message' => 'Ce ticket n\'a pas été payé.']);
        }

        if ($ticket->utilise) {
            $this->logScan($agent, $request->code, 'deja_utilise', 'Ticket déjà scanné');
            return response()->json(['success' => false, 'message' => 'Ce ticket a déjà été utilisé.', 'ticket' => [
                'nom' => $ticket->nom_acheteur,
                'categorie' => $ticket->categorie,
                'date' => $ticket->date_achat ? $ticket->date_achat->format('d/m/Y H:i') : null,
            ]]);
        }

        $ticket->update(['utilise' => true]);

        $this->logScan($agent, $request->code, 'valide', 'Scan réussi', $ticket->id);

        return response()->json(['success' => true, 'message' => 'Ticket validé avec succès !', 'ticket' => [
            'nom' => $ticket->nom_acheteur,
            'email' => $ticket->email_acheteur,
            'categorie' => $ticket->categorie,
            'montant' => number_format($ticket->montant, 0, ',', ' '),
            'date_achat' => $ticket->date_achat ? $ticket->date_achat->format('d/m/Y H:i') : null,
        ]]);
    }

    public function exitScan()
    {
        session()->forget('agent_scan_ok');
        return redirect()->route('agent.dashboard');
    }

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
