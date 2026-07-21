<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\Factory as Socialite;

class GoogleAuthController extends Controller
{
    protected Socialite $socialite;

    public function __construct(Socialite $socialite)
    {
        $this->socialite = $socialite;
    }

    // Redirige vers Google pour l'authentification OAuth
    public function redirect()
    {
        if (!config('services.google.client_id')) {
            return redirect()->route('inscriptions.organisateur')->withErrors(['email' => 'Authentification Google non configurée.']); // Config manquante
        }
        return $this->socialite->driver('google')->redirect();
    }

    // Traite le callback Google après authentification
    public function callback()
    {
        try {
            $googleUser = $this->socialite->driver('google')->user();
        } catch (\Exception $e) {
            logger()->error('Google auth callback error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('login')->withErrors([
                'email' => 'Authentification Google annulée ou échouée. ' . (config('app.debug') ? $e->getMessage() : '')
            ]);
        }

        if (!$googleUser->getEmail()) {
            return redirect()->route('login')->withErrors(['email' => 'Impossible de récupérer votre email Google.']);
        }

        $existing = User::where('email', $googleUser->getEmail())->first();

        if ($existing) {
            // Connexion automatique si le compte existe déjà
            if (in_array($existing->statut, ['en_attente', 'incomplet', 'corrections_demandees'])) {
                Auth::login($existing, true);
                request()->session()->regenerate();
                return redirect()->intended(route('dashboard'));
            }

            if ($existing->statut !== 'actif') {
                return redirect()->route('login')->withErrors([
                    'email' => 'Votre compte est ' . $existing->statut . ' (' . $googleUser->getEmail() . ').'
                ]);
            }

            Auth::login($existing, true);
            request()->session()->regenerate();

            return $existing->role === 'super_admin'
                ? redirect()->intended(route('superadmin.dashboard'))
                : redirect()->intended(route('dashboard'));
        }

        // Nouveau compte : stocke les infos Google en session pour l'inscription
        $reg = session('registration', []);
        $reg['email'] = $googleUser->getEmail();
        $reg['email_verified'] = true;
        $reg['from_google'] = true;
        $reg['google_name'] = $googleUser->getName();
        $reg['google_avatar'] = $googleUser->getAvatar();
        $reg['step'] = 1;
        session(['registration' => $reg]);

        return redirect()->route('inscriptions.identity');
    }
}
