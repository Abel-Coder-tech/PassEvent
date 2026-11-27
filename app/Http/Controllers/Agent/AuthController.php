<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Support\SessionMarker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Affiche le formulaire de connexion des agents de scan
    public function showLoginForm()
    {
        return view('agent.auth.login');
    }

    // Authentifie un agent de scan avec vérifications (actif, événement non terminé)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'L\'email est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $agent = Agent::where('email', $request->email)->first();

        if (!$agent || !Hash::check($request->password, $agent->password)) {
            return back()->withErrors(['email' => 'Ces identifiants ne correspondent pas.'])->onlyInput('email');
        }

        if (!$agent->actif) {
            return back()->withErrors(['email' => 'Votre compte a été désactivé. Contactez l\'organisateur.'])->onlyInput('email');
        }

        if ($agent->evenement->scanTermine()) {
            return back()->withErrors(['email' => 'L\'événement est terminé. Accès refusé.'])->onlyInput('email');
        }

        // Un seul poste peut utiliser ce compte : la réservation atomique
        // refuse la deuxième connexion sans déconnecter le premier (message
        // flash, pas d'erreur de validation pour ne pas alimenter le verrou).
        $cle = SessionMarker::nouvelleCle();
        if (! $agent->reserverSession($cle)) {
            return back()->with('error', 'Ce compte est déjà connecté sur un autre appareil. Déconnectez-vous d’abord.')
                ->onlyInput('email');
        }

        Auth::guard('agent')->login($agent, $request->boolean('remember'));
        $request->session()->put('agent_session_cle', $cle);

        $agent->update(['dernier_acces' => now()]);

        return redirect()->intended(route('agent.dashboard'));
    }

    // Tableau de bord de l'agent avec ses statistiques de scan
    public function dashboard()
    {
        $agent = Auth::guard('agent')->user();
        $evenement = $agent->evenement;
        $scans = $agent->logs()->where('type_operation', 'scan')->latest('created_at')->limit(50)->get();
        $stats = [
            'total' => $agent->logs()->where('type_operation', 'scan')->count(),
            'valides' => $agent->logs()->where('type_operation', 'scan')->where('details->resultat', 'valide')->count(),
            'invalides' => $agent->logs()->where('type_operation', 'scan')->where('details->resultat', '!=', 'valide')->count(),
        ];
        return view('agent.dashboard', compact('agent', 'evenement', 'scans', 'stats'));
    }

    // Déconnecte l'agent et invalide la session
    public function logout(Request $request)
    {
        $agent = Auth::guard('agent')->user();
        if ($agent) {
            $agent->fermerSession($request->session()->get('agent_session_cle'));
        }

        Auth::guard('agent')->logout();
        $request->session()->forget('agent_session_cle');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('agent.login');
    }
}
