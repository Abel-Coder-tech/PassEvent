<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Throwable;

class ForgotPasswordController extends Controller
{
    public function __construct(private OtpService $otp)
    {
    }

    public function showForm(Request $request)
    {
        $verifiedAt = $request->session()->get('organizer_password_reset_verified_at');
        $verified = is_numeric($verifiedAt) && (int) $verifiedAt > now()->subMinutes(10)->timestamp;

        if ($verifiedAt && ! $verified) {
            $request->session()->forget('organizer_password_reset_verified_at');
        }

        return view('auth.forgot', [
            'email' => $request->session()->get('organizer_password_reset_email'),
            'sentAt' => $request->session()->get('organizer_password_reset_sent_at'),
            'verified' => $verified,
        ]);
    }

    public function sendResetCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Veuillez entrer votre email.',
            'email.email' => 'Email invalide.',
            'email.exists' => 'Aucun compte trouvé avec cet email.',
        ]);

        $email = $validated['email'];
        $sentAt = $request->session()->get('organizer_password_reset_sent_at');
        if (
            $request->session()->get('organizer_password_reset_email') === $email
            && is_numeric($sentAt)
            && (int) $sentAt > now()->subMinutes(10)->timestamp
        ) {
            return back()->withErrors([
                'email' => 'Vous pourrez demander un nouveau code après la fin du compte à rebours.',
            ]);
        }

        return $this->sendCode($request, $email);
    }

    public function resendResetCode(Request $request)
    {
        $email = $request->session()->get('organizer_password_reset_email');
        if (! is_string($email) || ! User::where('email', $email)->exists()) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Veuillez d’abord demander un code de réinitialisation.']);
        }

        $sentAt = $request->session()->get('organizer_password_reset_sent_at');
        if (is_numeric($sentAt) && (int) $sentAt > now()->subMinutes(10)->timestamp) {
            return back()->withErrors([
                'email' => 'Vous pourrez demander un nouveau code après la fin du compte à rebours.',
            ]);
        }

        return $this->sendCode($request, $email);
    }

    private function sendCode(Request $request, string $email)
    {
        try {
            $this->otp->generateAndSend($email);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'email' => "Échec d'envoi du code de réinitialisation.",
            ]);
        }

        $request->session()->put([
            'organizer_password_reset_email' => $email,
            'organizer_password_reset_sent_at' => now()->timestamp,
        ]);
        $request->session()->forget('organizer_password_reset_verified_at');

        return back()->with('success', 'Un code de réinitialisation vient de vous être envoyé.');
    }

    public function verifyResetCode(Request $request)
    {
        $email = $request->session()->get('organizer_password_reset_email');
        if (! is_string($email)) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Veuillez d’abord demander un code de réinitialisation.']);
        }

        $validated = $request->validate([
            'code' => 'required|string|size:4|regex:/^[0-9]{4}$/',
        ], [
            'code.required' => 'Veuillez saisir le code reçu par email.',
            'code.size' => 'Le code doit contenir 4 chiffres.',
            'code.regex' => 'Le code doit contenir 4 chiffres.',
        ]);

        $result = $this->otp->verify($email, $validated['code']);
        if ($result !== 'valide') {
            return back()->withErrors([
                'code' => $result === 'expire'
                    ? 'Ce code a expiré. Demandez un nouveau code.'
                    : 'Ce code est invalide. Vérifiez votre email ou demandez un nouveau code.',
            ]);
        }

        $request->session()->put('organizer_password_reset_verified_at', now()->timestamp);

        return back()->with('success', 'Code vérifié. Vous pouvez choisir un nouveau mot de passe.');
    }

    public function reset(Request $request)
    {
        $email = $request->session()->get('organizer_password_reset_email');
        $verifiedAt = $request->session()->get('organizer_password_reset_verified_at');

        if (
            ! is_string($email)
            || ! is_numeric($verifiedAt)
            || (int) $verifiedAt <= now()->subMinutes(10)->timestamp
        ) {
            $request->session()->forget('organizer_password_reset_verified_at');

            return redirect()->route('password.request')
                ->withErrors(['code' => 'Veuillez vérifier un code valide avant de choisir un nouveau mot de passe.']);
        }

        $validated = $request->validate([
            'mot_de_passe' => 'required|string|min:8|max:255|confirmed',
        ], [
            'mot_de_passe.required' => 'Le mot de passe est obligatoire.',
            'mot_de_passe.min' => 'Minimum 8 caractères.',
            'mot_de_passe.max' => 'Mot de passe trop long.',
            'mot_de_passe.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $user = User::where('email', $email)->first();
        if (! $user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Aucun compte trouvé avec cet email.']);
        }

        $user->mot_de_passe = Hash::make($validated['mot_de_passe']);
        $user->save();

        $request->session()->forget([
            'organizer_password_reset_email',
            'organizer_password_reset_sent_at',
            'organizer_password_reset_verified_at',
        ]);

        return redirect()->route('login')
            ->with('success', 'Mot de passe réinitialisé. Connectez-vous avec votre nouveau mot de passe.');
    }
}
