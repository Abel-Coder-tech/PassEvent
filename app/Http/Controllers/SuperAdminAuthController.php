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

        if (auth()->attempt([
            'pseudo' => $credentials['pseudo'],
            'password' => $credentials['mot_de_passe'],
            'role' => 'super_admin',
        ])) {
            $request->session()->regenerate();
            return redirect()->intended(route('superadmin.dashboard'));
        }

        $user = \App\Models\User::where('pseudo', $credentials['pseudo'])->first();
        if ($user && $user->role !== 'super_admin') {
            return back()->withErrors(['pseudo' => 'Cet acces est reserve au super administrateur.'])->onlyInput('pseudo');
        }

        return back()->withErrors([
            'pseudo' => 'Identifiants incorrects.',
        ])->onlyInput('pseudo');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('superadmin.login');
    }
}
