<?php

namespace App\Http\Controllers;

use App\Mail\VerificationEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InscriptionController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'telephone' => 'required|string|max:30',
            'organisation' => 'required|string|max:255',
            'mot_de_passe' => 'required|string|min:8',
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'],
            'organisation' => $validated['organisation'],
            'mot_de_passe' => Hash::make($validated['mot_de_passe']),
            'role' => 'admin',
        ]);

        $token = Str::random(60);
        $user->remember_token = $token;
        $user->save();

        $url = route('inscriptions.verify', ['id' => $user->id, 'token' => $token]);

        Mail::to($user->email)->send(new VerificationEmail($user, $url));

        return redirect()->route('login')
            ->with('success', 'Compte cree ! Verifiez votre email pour confirmer votre inscription.');
    }

    public function verify($id, $token)
    {
        $user = User::findOrFail($id);

        if ($user->remember_token !== $token) {
            return redirect()->route('login')->with('error', 'Lien de verification invalide.');
        }

        if ($user->email_verified_at) {
            return redirect()->route('login')->with('info', 'Email deja verifie.');
        }

        $user->email_verified_at = now();
        $user->remember_token = null;
        $user->save();

        return redirect()->route('login')
            ->with('success', 'Email confirme ! Vous pouvez maintenant vous connecter.');
    }
}
