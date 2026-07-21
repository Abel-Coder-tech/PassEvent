<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ParametresController extends Controller
{
    // Page d'accueil des paramètres
    public function index()
    {
        return view('admin.parametres.index');
    }

    // Met à jour le profil de l'organisateur (nom, email, avatar)
    public function profil(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|min:3|max:255',
            'organisation' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'description' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar); // Supprime l'ancien avatar
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->update([
            'nom' => $validated['nom'],
            'organisation' => $validated['organisation'],
            'email' => $validated['email'],
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Profil mis a jour avec succes.');
    }

    // Supprime la photo de profil
    public function supprimerAvatar()
    {
        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }

        return back()->with('success', 'Photo de profil supprimee.');
    }

    // Modifie le mot de passe après vérification de l'ancien
    public function securite(Request $request)
    {
        $validated = $request->validate([
            'mot_de_passe_actuel' => 'required',
            'mot_de_passe' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($validated['mot_de_passe_actuel'], Auth::user()->mot_de_passe)) {
            return back()->withErrors(['mot_de_passe_actuel' => 'Le mot de passe actuel est incorrect.'])->withInput();
        }

        Auth::user()->update([
            'mot_de_passe' => Hash::make($validated['mot_de_passe']),
        ]);

        return back()->with('success', 'Mot de passe modifie avec succes.');
    }

    // Met à jour les préférences de notification par email
    public function notifications(Request $request)
    {
        $validated = $request->validate([
            'notif_email_evenement' => 'boolean',
            'notif_email_ticket' => 'boolean',
            'notif_email_paiement' => 'boolean',
            'notif_scan' => 'boolean',
        ]);

        Auth::user()->update([
            'notif_email_evenement' => $request->boolean('notif_email_evenement'),
            'notif_email_ticket' => $request->boolean('notif_email_ticket'),
            'notif_email_paiement' => $request->boolean('notif_email_paiement'),
            'notif_scan' => $request->boolean('notif_scan'),
        ]);

        return back()->with('success', 'Preferences de notification mises a jour.');
    }

    // Configure les clés API FedaPay pour les paiements
    public function paiement(Request $request)
    {
        $validated = $request->validate([
            'fedapay_public_key' => 'nullable|string|max:255',
            'fedapay_secret_key' => 'nullable|string|max:255',
            'fedapay_active' => 'boolean',
        ]);

        Auth::user()->update([
            'fedapay_public_key' => $validated['fedapay_public_key'] ?: null,
            'fedapay_secret_key' => $validated['fedapay_secret_key'] ?: null,
            'fedapay_active' => $request->boolean('fedapay_active'),
        ]);

        return back()->with('success', 'Configuration FedaPay mise a jour.');
    }

    // Configure le code d'accès scan global
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'code_acces_scan' => 'nullable|string|min:4|max:50',
        ]);

        Auth::user()->update([
            'code_acces_scan' => $validated['code_acces_scan'] ?: null,
        ]);

        return back()->with('success', 'Code d\' acces scan mis a jour.');
    }

    // Supprime définitivement le compte utilisateur (impossible avec des événements)
    public function supprimerCompte(Request $request)
    {
        $validated = $request->validate([
            'confirmation' => 'required|in:supprimer',
        ]);

        $user = Auth::user();

        if ($user->evenements()->exists()) {
            return back()->withErrors(['confirmation' => 'Impossible de supprimer un compte avec des evenements. Supprimez-les d\'abord.']); // Contrainte d'intégrité
        }

        Auth::logout();
        $user->delete();

        return redirect('/login')->with('success', 'Votre compte a ete supprime.');
    }
}
