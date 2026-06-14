<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        return view('auth.forgot');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Veuillez entrer votre email.',
            'email.email' => 'Email invalide.',
            'email.exists' => 'Aucun compte trouvé avec cet email.',
        ]);

        $user = User::where('email', $request->email)->first();

        $token = Str::random(60);
        $user->remember_token = $token;
        $user->save();

        $url = route('password.reset', ['token' => $token]);

        Mail::to($user->email)->send(new ResetPasswordEmail($user, $url));

        return back()->with('success', 'Un lien de réinitialisation vous a été envoyé par email.');
    }

    public function showResetForm($token)
    {
        return view('auth.reset', compact('token'));
    }

    public function reset(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'mot_de_passe' => 'required|min:8|confirmed',
        ], [
            'token.required' => 'Token invalide.',
            'email.required' => 'Veuillez entrer votre email.',
            'email.exists' => 'Aucun compte trouvé avec cet email.',
            'mot_de_passe.required' => 'Le mot de passe est obligatoire.',
            'mot_de_passe.min' => 'Minimum 8 caractères.',
            'mot_de_passe.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $user = User::where('email', $validated['email'])
            ->where('remember_token', $validated['token'])
            ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Lien de réinitialisation invalide ou expiré.']);
        }

        $user->mot_de_passe = bcrypt($validated['mot_de_passe']);
        $user->remember_token = null;
        $user->save();

        return redirect()->route('login')->with('success', 'Mot de passe réinitialisé. Connectez-vous avec votre nouveau mot de passe.');
    }
}
