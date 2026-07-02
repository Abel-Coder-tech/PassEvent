<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('agent.auth.login');
    }

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

        if ($agent->evenement->date_event < now()) {
            return back()->withErrors(['email' => 'L\'événement est terminé. Accès refusé.'])->onlyInput('email');
        }

        Auth::guard('agent')->login($agent, $request->boolean('remember'));

        $agent->update(['dernier_acces' => now()]);

        return redirect()->intended(route('agent.dashboard'));
    }

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

    public function logout(Request $request)
    {
        Auth::guard('agent')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('agent.login');
    }
}
