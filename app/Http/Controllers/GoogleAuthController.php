<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Authentification Google annulée ou échouée.']);
        }

        if (!$googleUser->getEmail()) {
            return redirect()->route('login')->withErrors(['email' => 'Impossible de récupérer votre email Google.']);
        }

        $existing = User::where('email', $googleUser->getEmail())->first();

        if ($existing) {
            if ($existing->statut !== 'actif') {
                return redirect()->route('login')->withErrors([
                    'email' => 'Votre compte est en attente de validation (' . $googleUser->getEmail() . ').'
                ]);
            }

            Auth::login($existing, true);
            request()->session()->regenerate();

            return $existing->role === 'super_admin'
                ? redirect()->intended(route('superadmin.dashboard'))
                : redirect()->intended(route('dashboard'));
        }

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
