<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;

class SuperAdminAuthController extends Controller
{
    // Portail super admin : formulaire de connexion super admin
    public function showLoginForm()
    {
        $user = auth('superadmin')->user();

        if ($user) {
            return redirect()->route($user->estSuperAdmin() ? 'superadmin.dashboard' : 'equipe.dashboard');
        }

        return view('superadmin.auth.login');
    }

    // Portail equipe : formulaire de connexion reserve aux membres de l'equipe
    public function showLoginFormEquipe()
    {
        $user = auth('superadmin')->user();

        if ($user) {
            return redirect()->route($user->estEquipe() ? 'equipe.dashboard' : 'superadmin.dashboard');
        }

        return view('superadmin.auth.login-equipe');
    }

    // Entree de /equipe : formulaire pour un invite, redirection pour un utilisateur connecte
    public function portail()
    {
        $user = auth('superadmin')->user();

        if (!$user) {
            return view('superadmin.auth.login-equipe');
        }

        return $this->redirigerVersEspace($user);
    }

    // Authentifie le super admin par pseudo et mot de passe
    public function login(Request $request)
    {
        [$user, $erreur] = $this->identifier($request, 'super_admin');

        if ($erreur) {
            return back()->withErrors($erreur)->onlyInput('pseudo');
        }

        auth('superadmin')->login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('superadmin.dashboard'));
    }

    // Authentifie un membre de l'equipe par pseudo et mot de passe
    public function loginEquipe(Request $request)
    {
        [$user, $erreur] = $this->identifier($request, 'equipe');

        if ($erreur) {
            return back()->withErrors($erreur)->onlyInput('pseudo');
        }

        auth('superadmin')->login($user);
        $request->session()->regenerate();

        // Mot de passe temporaire -> changement obligatoire
        if ($user->must_change_password) {
            return redirect()->route('equipe.premiere-connexion')
                ->with('info', 'Bienvenue ! Pour votre sécurité, définissez votre propre mot de passe pour continuer.');
        }

        return redirect()->intended(route('equipe.dashboard'));
    }

    // Déconnecte et renvoie chacun vers son portail
    public function logout(Request $request)
    {
        $versEquipe = auth('superadmin')->user()?->estEquipe();

        auth('superadmin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($versEquipe ? 'equipe.login' : 'superadmin.login');
    }

    // Verifie les identifiants pour un type de compte donne ; retourne [utilisateur, erreur]
    private function identifier(Request $request, string $roleAttendu): array
    {
        $credentials = $request->validate([
            'pseudo' => 'required|string|max:50',
            'mot_de_passe' => 'required|min:8',
        ], [
            'pseudo.required' => 'Le pseudo est requis.',
            'mot_de_passe.required' => 'Le mot de passe est requis.',
            'mot_de_passe.min' => 'Le mot de passe doit contenir au minimum 8 caracteres.',
        ]);

        $user = User::where('pseudo', $credentials['pseudo'])->first();

        if (!$user) {
            return [null, ['pseudo' => 'Ce pseudo n\'existe pas.']];
        }

        if ($user->role !== $roleAttendu) {
            $message = $roleAttendu === 'super_admin'
                ? 'Cet acces est reserve au proprietaire de la plateforme. Les membres de l\'equipe se connectent sur leur propre portail.'
                : 'Cet espace est reserve aux membres de l\'equipe. Le proprietaire se connecte via l\'administration.';
            return [null, ['pseudo' => $message]];
        }

        if ($user->role === 'equipe' && $user->statut !== 'actif') {
            return [null, ['pseudo' => 'Votre compte a ete desactive. Contactez l\'administrateur.']];
        }

        if (!\Illuminate\Support\Facades\Hash::check($credentials['mot_de_passe'], $user->mot_de_passe)) {
            return [null, ['mot_de_passe' => 'Mot de passe incorrect.']];
        }

        return [$user, null];
    }

    private function redirigerVersEspace(User $user): RedirectResponse
    {
        if ($user->must_change_password) {
            return redirect()->route('equipe.premiere-connexion')
                ->with('info', 'Pour votre sécurité, vous devez définir votre propre mot de passe avant de continuer.');
        }

        return redirect()->route($user->estSuperAdmin() ? 'superadmin.dashboard' : 'equipe.dashboard');
    }
}
