<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InscriptionController extends Controller
{
    protected OtpService $otp;

    public function __construct(OtpService $otp)
    {
        $this->otp = $otp;
    }

    // Réinitialise la session d'inscription
    private function regen(): void
    {
        session()->forget('registration');
    }

    // Récupère les données d'inscription depuis la session
    private function getReg()
    {
        return session('registration', []);
    }

    // Fusionne des données dans la session d'inscription
    private function putReg(array $data): void
    {
        $reg = session('registration', []);
        foreach ($data as $k => $v) {
            $reg[$k] = $v;
        }
        session(['registration' => $reg]);
    }

    // Étape 0 : Formulaire de saisie de l'email
    public function step0()
    {
        return view('auth.register.step0');
    }

    // Envoie un code OTP par email pour vérification
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|max:255']);

        $exists = User::where('email', $request->email)->exists();
        if ($exists) {
            return back()->withErrors(['email' => 'Un compte existe déjà avec cet email. Connectez-vous.'])->withInput();
        }

        $this->otp->generateAndSend($request->email);

        $this->putReg(['email' => $request->email, 'email_verified' => false, 'step' => 1]);

        return redirect()->route('inscriptions.verify');
    }

    // Affiche la page de vérification OTP
    public function showVerify()
    {
        $reg = $this->getReg();
        if (empty($reg['email'])) {
            return redirect()->route('inscriptions.organisateur');
        }
        return view('auth.register.verify', ['email' => $reg['email']]);
    }

    // Vérifie le code OTP saisi par l'utilisateur
    public function verifyOtp(Request $request)
    {
        $reg = $this->getReg();
        if (empty($reg['email'])) {
            return redirect()->route('inscriptions.organisateur');
        }

        $request->validate(['code' => 'required|string|size:4']);

        $result = $this->otp->verify($reg['email'], $request->code);

        if ($result === 'invalide') {
            return back()->withErrors(['code' => 'Code invalide. Vérifiez votre email ou demandez un nouveau code.']);
        }
        if ($result === 'expire') {
            return back()->withErrors(['code' => 'Ce code a expiré. Cliquez sur Renvoyer le code pour en recevoir un nouveau.']);
        }

        $this->putReg(['email_verified' => true, 'from_google' => $request->has('from_google')]);

        return redirect()->route('inscriptions.identity');
    }

    // Renvoie un nouveau code OTP
    public function resendOtp(Request $request)
    {
        $reg = $this->getReg();
        if (empty($reg['email'])) {
            return $request->expectsJson()
                ? response()->json(['success' => false])
                : redirect()->route('inscriptions.organisateur');
        }

        $this->otp->generateAndSend($reg['email']);

        return $request->expectsJson()
            ? response()->json(['success' => true])
            : back()->with('success', 'Un nouveau code vous a été envoyé.');
    }

    // Étape 1 : Formulaire d'identité (nom, téléphone, mot de passe)
    public function step1()
    {
        $reg = $this->getReg();
        if (empty($reg['email']) || empty($reg['email_verified'])) {
            return redirect()->route('inscriptions.organisateur');
        }
        return view('auth.register.step1', [
            'from_google' => $reg['from_google'] ?? false,
            'data' => $reg['identity'] ?? [],
        ]);
    }

    // Traite le formulaire d'identité et crée le compte utilisateur
    public function postStep1(Request $request)
    {
        $reg = $this->getReg();
        if (empty($reg['email']) || empty($reg['email_verified'])) {
            return redirect()->route('inscriptions.organisateur');
        }

        $rules = [
            'nom' => 'required|string|max:255',
            'telephone' => 'required|string|max:30',
            'avatar' => 'nullable|image|max:2048',
        ];

        if (!($reg['from_google'] ?? false)) {
            $rules['mot_de_passe'] = 'required|string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $userData = [
            'nom' => $validated['nom'],
            'email' => $reg['email'],
            'telephone' => $validated['telephone'],
            'avatar' => $validated['avatar'] ?? null,
            'role' => 'admin',
            'statut' => 'incomplet', // Nécessite complétion du profil
        ];

        if ($reg['from_google'] ?? false) {
            $userData['mot_de_passe'] = Hash::make(Str::random(32)); // Mot de passe aléatoire pour les comptes Google
        } else {
            $userData['mot_de_passe'] = Hash::make($validated['mot_de_passe']);
        }

        $user = User::create($userData);

        $this->regen();

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    // Permet de resoumettre un profil rejeté ou nécessitant des corrections
    public function resubmit(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->statut, ['corrections_demandees', 'rejete'])) {
            return redirect()->route('dashboard')->with('error', 'Action non autorisée.');
        }

        $user->update(['statut' => 'en_attente']);

        $superAdmins = User::where('role', 'super_admin')->get();
        foreach ($superAdmins as $sa) {
            Mail::to($sa->email)->send(new RegistrationAdminNotification($user));
        }

        return redirect()->route('dashboard')->with('success', 'Votre profil a été soumis à nouveau pour validation.');
    }
}