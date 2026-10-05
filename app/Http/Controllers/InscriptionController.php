<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationAdminNotification;
use App\Models\Message;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

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

    /**
     * Traduit un échec technique d'envoi en message compréhensible.
     * Sans cela, l'utilisateur reçoit une page 404/500 sans savoir si le code
     * a été envoyé ou non.
     */
    private function messageEnvoiEchoue(Throwable $e): string
    {
        // Message volontairement generique pour l'utilisateur : aucun detail
        // technique ne doit lui exposer. La cause reelle est conservee dans
        // les logs via report($e) et reste consultable par l'administrateur.
        Log::warning('Echec envoi du code OTP : ' . $e->getMessage());

        return "Échec d'envoi du code de vérification.";
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

        // L'envoi peut échouer (SMTP, file d'attente) : on le dit explicitement
        // plutot que de renvoyer une page d'erreur technique.
        try {
            $this->otp->generateAndSend($request->email);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['email' => $this->messageEnvoiEchoue($e)])
                ->withInput();
        }

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

        // from_google n'est jamais choisi par le client : uniquement Google
        // le positionne dans la session. Le recopier depuis la requete
        // permettrait de contourner la saisie du mot de passe.
        $this->putReg(['email_verified' => true]);

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

        try {
            $this->otp->generateAndSend($reg['email']);
        } catch (Throwable $e) {
            report($e);

            $message = $this->messageEnvoiEchoue($e);

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message])
                : back()->withErrors(['code' => $message]);
        }

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
            $rules['mot_de_passe'] = 'required|string|min:8|max:255|confirmed';
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

        // Filet de securite : l'email a pu etre enregistre entre-temps
        // (ex. inscription Google concurrente) sinon on leve une 500.
        if (User::where('email', $reg['email'])->exists()) {
            $this->regen();

            return redirect()->route('login')->withErrors([
                'email' => 'Un compte existe déjà avec cet email. Connectez-vous.',
            ]);
        }

        $user = User::create($userData);

        $this->regen();

        Auth::login($user);
        $request->session()->regenerate(); // Anti fixation de session

        return redirect()->route('dashboard');
    }

    // Permet de resoumettre un profil rejeté ou nécessitant des corrections
    public function resubmit(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->statut, ['corrections_demandees', 'rejete'])) {
            return redirect()->route('dashboard')->with('error', 'Action non autorisée.');
        }

        $etaitEnCorrections = $user->statut === 'corrections_demandees';

        $user->update(['statut' => $etaitEnCorrections ? 'corrections_apportees' : 'en_attente']);

        $superAdmins = User::where('role', 'super_admin')->get();
        foreach ($superAdmins as $sa) {
            Mail::to($sa->email)->queue(new RegistrationAdminNotification($user));
            if ($etaitEnCorrections) {
                Message::create([
                    'user_id' => null,
                    'nom_complet' => $user->nom,
                    'email' => $user->email,
                    'objet' => 'Corrections apportées - ' . ($user->nom ?? 'organisateur'),
                    'message' => "{$user->nom} a apporté les corrections demandées à son profil et attend une nouvelle validation.\n\nConnectez-vous pour valider son compte.",
                    'lu' => false,
                ]);
            }
        }

        return redirect()->route('dashboard')->with('success', 'Votre profil a été soumis à nouveau pour validation.');
    }
}