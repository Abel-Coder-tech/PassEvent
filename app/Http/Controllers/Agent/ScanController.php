<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Services\TicketScanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'code_acces' => 'required|digits:4',
        ]);

        if ($agent->evenement->scanTermine()) {
            return back()->withErrors(['code_acces' => 'L\'événement est terminé.'])->onlyInput('code_acces');
        }

        if (!$agent->actif) {
            Auth::guard('agent')->logout();
            return redirect()->route('agent.login')->withErrors(['email' => 'Compte désactivé.']);
        }

        // Limitation par agent ET par IP : un tiers ne peut pas bloquer un agent,
        // et un agent qui se trompe ne bloque pas ses collègues sur le même wifi.
        $key = 'agent-pin:' . $agent->id . ':' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['code_acces' => "Trop de tentatives. Réessayez dans {$seconds} secondes."])->onlyInput('code_acces');
        }

        if (! Hash::check($request->code_acces, (string) $agent->code_acces)) {
            RateLimiter::hit($key, 300);
            $agent->increment('tentatives_code');

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
    public function verifier(Request $request, TicketScanService $service)
    {
        $agent = Auth::guard('agent')->user();
        if (session('agent_scan_ok') !== $agent->id) {
            return response()->json(['success' => false, 'message' => 'Session de scan invalide.'], 403);
        }

        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $result = $service->verifier($agent, $request->code);

        return response()->json($result);
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


}
