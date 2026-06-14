<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('superadmin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'pseudo' => 'required|string|max:50',
            'mot_de_passe' => 'required|min:8',
        ], [
            'pseudo.required' => 'Le pseudo est requis.',
            'mot_de_passe.required' => 'Le mot de passe est requis.',
            'mot_de_passe.min' => 'Le mot de passe doit contenir au moins 8 caracteres.',
        ]);

        $user = \App\Models\User::where('pseudo', $credentials['pseudo'])->first();

        if (!$user) {
            return back()->withErrors(['pseudo' => 'Ce pseudo n\'existe pas.'])->onlyInput('pseudo');
        }

        if ($user->role !== 'super_admin') {
            return back()->withErrors(['pseudo' => 'Cet acces est reserve au super administrateur.'])->onlyInput('pseudo');
        }

        if (!\Illuminate\Support\Facades\Hash::check($credentials['mot_de_passe'], $user->mot_de_passe)) {
            return back()->withErrors(['mot_de_passe' => 'Mot de passe incorrect.'])->onlyInput('pseudo');
        }

        auth('superadmin')->login($user);
        $request->session()->regenerate();
        return redirect()->intended(route('superadmin.dashboard'));
    }

    public function logout(Request $request)
    {
        auth('superadmin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('superadmin.login');
    }
}
