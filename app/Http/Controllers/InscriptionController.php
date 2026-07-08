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

    private function regen(): void
    {
        session()->forget('registration');
    }

    private function getReg()
    {
        return session('registration', []);
    }

    private function putReg(array $data): void
    {
        $reg = session('registration', []);
        foreach ($data as $k => $v) {
            $reg[$k] = $v;
        }
        session(['registration' => $reg]);
    }

    public function step0()
    {
        return view('auth.register.step0');
    }

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

    public function showVerify()
    {
        $reg = $this->getReg();
        if (empty($reg['email'])) {
            return redirect()->route('inscriptions.organisateur');
        }
        return view('auth.register.verify', ['email' => $reg['email']]);
    }

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
            'statut' => 'incomplet',
        ];

        if ($reg['from_google'] ?? false) {
            $userData['mot_de_passe'] = Hash::make(Str::random(32));
        } else {
            $userData['mot_de_passe'] = Hash::make($validated['mot_de_passe']);
        }

        $user = User::create($userData);

        $this->regen();

        Auth::login($user);

        return redirect()->route('dashboard');
    }

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