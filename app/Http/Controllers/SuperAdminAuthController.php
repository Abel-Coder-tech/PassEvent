<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminAuthController extends Controller
{
    // Affiche le formulaire de connexion super admin
    public function showLoginForm()
    {
        return view('superadmin.auth.login');
    }

    // Authentifie le super admin par pseudo et mot de passe
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'pseudo' => 'required|string|max:50',
            'mot_de_passe' => 'required|min:8',
        ], [
            'pseudo.required' => 'Le pseudo est requis.',
            'mot_de_passe.required' => 'Le mot de passe est requis.',
            'mot_de_passe.min' => 'Le mot de passe doit contenir au minimum 8 caracteres.',
        ]);

        $user = \App\Models\User::where('pseudo', $credentials['pseudo'])->first();

        if (!$user) {
            return back()->withErrors(['pseudo' => 'Ce pseudo n\'existe pas.'])->onlyInput('pseudo');
        }

        if (!in_array($user->role, ['super_admin', 'equipe'], true)) {
            return back()->withErrors(['pseudo' => 'Cet acces est reserve a l\'equipe PaxEvent.']); // Rôle vérifié
        }

        if ($user->role === 'equipe' && $user->statut !== 'actif') {
            return back()->withErrors(['pseudo' => 'Votre compte a ete desactive. Contactez l\'administrateur.']);
        }

        if (!\Illuminate\Support\Facades\Hash::check($credentials['mot_de_passe'], $user->mot_de_passe)) {
            return back()->withErrors(['mot_de_passe' => 'Mot de passe incorrect.'])->onlyInput('pseudo');
        }

        auth('superadmin')->login($user); // Guard superadmin spécifique
        $request->session()->regenerate();

        // Membre de l'equipe : mot de passe temporaire -> changement obligatoire
        if ($user->estEquipe() && $user->must_change_password) {
            return redirect()->route('superadmin.premiere-connexion')
                ->with('info', 'Bienvenue ! Pour votre sécurité, définissez votre propre mot de passe pour continuer.');
        }

        return redirect()->intended(route('superadmin.dashboard'));
    }

    // Déconnecte le super admin
    public function logout(Request $request)
    {
        auth('superadmin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('superadmin.login');
    }
}
