<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
            'mot_de_passe' => 'required|string|min:8|confirmed',
            'type' => 'required|string|in:universitaire,professionnel',
            'description' => 'nullable|string|max:1000',
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'],
            'organisation' => $validated['organisation'],
            'mot_de_passe' => Hash::make($validated['mot_de_passe']),
            'type' => $validated['type'],
            'description' => $validated['description'],
            'role' => 'admin',
            'statut' => 'en_attente',
        ]);

        // Notifier tous les super admins
        $superAdmins = User::where('role', 'super_admin')->get();
        foreach ($superAdmins as $sa) {
            Mail::raw(
                "Nouvel organisateur en attente :\n\n" .
                "Nom : {$user->nom}\n" .
                "Email : {$user->email}\n" .
                "Organisation : {$user->organisation}\n" .
                "Telephone : {$user->telephone}\n\n" .
                "Connectez-vous sur le super dashboard pour valider ou rejeter.",
                function ($message) use ($sa) {
                    $message->to($sa->email)
                        ->subject('[PaxEvent] Nouvel organisateur en attente');
                }
            );
        }

        return redirect()->route('login')
            ->with('success', 'Compte cree avec succes ! Votre demande est en attente de validation par l\'administrateur. Vous recevrez un email une fois votre compte active.');
    }
}
