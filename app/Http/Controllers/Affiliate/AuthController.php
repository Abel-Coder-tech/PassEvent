<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Mail\AffiliatePasswordResetOtp;
use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    // Formulaire de connexion de l'espace affilie
    public function showLoginForm()
    {
        return view('affiliate.auth.login');
    }

    public function showForgotPasswordForm()
    {
        return view('affiliate.auth.forgot-password');
    }

    public function showResetPasswordForm(Request $request)
    {
        $email = $request->session()->get('affiliate_password_reset_email');
        $verifiedAt = $request->session()->get('affiliate_password_reset_verified_at');
        $affiliate = is_string($email)
            ? Affiliate::whereRaw('LOWER(email) = ?', [$email])->first()
            : null;

        if (
            ! $affiliate
            || ! is_numeric($verifiedAt)
            || (int) $verifiedAt <= now()->subMinutes(10)->timestamp
            || ! $affiliate->password_reset_code
            || ! $affiliate->password_reset_expires_at
            || $affiliate->password_reset_expires_at->isPast()
        ) {
            $request->session()->forget('affiliate_password_reset_verified_at');

            return redirect()->route('affiliate.password.request')
                ->withErrors(['code' => 'Veuillez vérifier un code valide avant de choisir un nouveau mot de passe.']);
        }

        return view('affiliate.auth.reset-password');
    }

    public function sendPasswordResetCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:150',
        ], [
            'email.required' => 'Veuillez saisir votre adresse email.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
        ]);

        $email = Str::lower($validated['email']);
        $affiliate = Affiliate::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($affiliate) {
            $sentAt = $request->session()->get('affiliate_password_reset_sent_at');
            if (
                $request->session()->get('affiliate_password_reset_email') === $email
                && is_numeric($sentAt)
                && (int) $sentAt > now()->subMinutes(10)->timestamp
            ) {
                return back()->withErrors([
                    'email' => 'Vous pourrez demander un nouveau code après la fin du compte à rebours.',
                ]);
            }

            $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $affiliate->forceFill([
                'password_reset_code' => Hash::make($code),
                'password_reset_expires_at' => now()->addMinutes(10),
            ])->save();

            Mail::to($affiliate->email)->send(new AffiliatePasswordResetOtp($code));

            $request->session()->put([
                'affiliate_password_reset_email' => $email,
                'affiliate_password_reset_sent_at' => now()->timestamp,
            ]);
            $request->session()->forget('affiliate_password_reset_verified_at');

            return back()->with('success', 'Un code de réinitialisation vient de vous être envoyé.');
        }

        $request->session()->forget([
            'affiliate_password_reset_email',
            'affiliate_password_reset_sent_at',
            'affiliate_password_reset_verified_at',
        ]);

        return back()->with('success', 'Si un compte est associé à cet email, un code de réinitialisation vient de vous être envoyé.');
    }

    public function verifyPasswordResetCode(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|digits:4',
        ], [
            'code.required' => 'Veuillez saisir le code reçu par email.',
            'code.digits' => 'Le code doit contenir 4 chiffres.',
        ]);

        $email = $request->session()->get('affiliate_password_reset_email');
        if (! is_string($email)) {
            return redirect()->route('affiliate.password.request')
                ->withErrors(['email' => 'Veuillez d’abord demander un code de réinitialisation.']);
        }

        $affiliate = Affiliate::whereRaw('LOWER(email) = ?', [$email])->first();
        if (
            ! $affiliate
            || ! $affiliate->password_reset_code
            || ! $affiliate->password_reset_expires_at
            || $affiliate->password_reset_expires_at->isPast()
            || ! Hash::check($validated['code'], $affiliate->password_reset_code)
        ) {
            return back()->withErrors([
                'code' => 'Ce code est invalide ou expiré. Demandez un nouveau code.',
            ]);
        }

        $request->session()->put('affiliate_password_reset_verified_at', now()->timestamp);

        return redirect()->route('affiliate.password.reset-form');
    }

    public function resetPassword(Request $request)
    {
        $email = $request->session()->get('affiliate_password_reset_email');
        $verifiedAt = $request->session()->get('affiliate_password_reset_verified_at');
        $affiliate = is_string($email)
            ? Affiliate::whereRaw('LOWER(email) = ?', [$email])->first()
            : null;

        if (
            ! $affiliate
            || ! is_numeric($verifiedAt)
            || (int) $verifiedAt <= now()->subMinutes(10)->timestamp
            || ! $affiliate->password_reset_code
            || ! $affiliate->password_reset_expires_at
            || $affiliate->password_reset_expires_at->isPast()
        ) {
            $request->session()->forget('affiliate_password_reset_verified_at');

            return redirect()->route('affiliate.password.request')
                ->withErrors(['code' => 'Veuillez vérifier un code valide avant de choisir un nouveau mot de passe.']);
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8|max:255|confirmed',
        ], [
            'password.required' => 'Veuillez saisir un nouveau mot de passe.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $affiliate->forceFill([
            'password' => Hash::make($validated['password']),
            'password_reset_code' => null,
            'password_reset_expires_at' => null,
        ])->save();

        $request->session()->forget('affiliate_password_reset_email');
        $request->session()->forget('affiliate_password_reset_sent_at');
        $request->session()->forget('affiliate_password_reset_verified_at');

        return redirect()->route('affiliate.login')
            ->with('success', 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.');
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
            'email.unique' => 'Cet email est déjà utilisé.',
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
