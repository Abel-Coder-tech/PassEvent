<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    // Formulaire de connexion de l'espace affilie
    public function showLoginForm()
    {
        return view('affiliate.auth.login');
    }

    // Authentifie un affilie
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'L\'email est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $affiliate = Affiliate::where('email', $request->email)->first();

        if (!$affiliate || !Hash::check($request->password, $affiliate->password)) {
            return back()->withErrors(['email' => 'Ces identifiants ne correspondent pas.'])->onlyInput('email');
        }

        if (!$affiliate->actif) {
            return back()->withErrors(['email' => 'Votre compte a été désactivé. Contactez le support.'])->onlyInput('email');
        }

        Auth::guard('affiliate')->login($affiliate, $request->boolean('remember'));
        $affiliate->update(['dernier_acces' => now()]);

        return redirect()->intended(route('affiliate.dashboard'));
    }

    // Formulaire d'inscription
    public function showRegisterForm()
    {
        return view('affiliate.auth.register');
    }

    // Cree un compte affilie (compte distinct des comptes organisateurs)
    public function register(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => [
                'required', 'email', 'max:150',
                'unique:affiliates,email',
                Rule::unique('users', 'email'),
            ],
            'telephone' => 'nullable|string|max:30',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.unique' => 'Cet email est déjà utilisé. Un compte organisateur ne peut pas devenir affilié : le programme est réservé aux non-organisateurs.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $affiliate = Affiliate::create([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'] ?? null,
            'email' => $data['email'],
            'telephone' => $data['telephone'] ?? null,
            'password' => Hash::make($data['password']),
            'code_parrainage' => Affiliate::genererCodeParrainage(),
            'actif' => true,
            'dernier_acces' => now(),
        ]);

        Auth::guard('affiliate')->login($affiliate);

        return redirect()->route('affiliate.dashboard')
            ->with('success', 'Bienvenue ! Votre compte affilié est actif.');
    }

    // Deconnexion de l'affilie
    public function logout(Request $request)
    {
        Auth::guard('affiliate')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('affiliate.login');
    }
}
