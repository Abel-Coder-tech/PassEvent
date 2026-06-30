<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationAdminNotification;
use App\Mail\RegistrationPending;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

        $this->putReg(['identity' => $validated, 'step' => 2]);

        return redirect()->route('inscriptions.org');
    }

    public function step2()
    {
        $reg = $this->getReg();
        if (empty($reg['identity'])) {
            return redirect()->route('inscriptions.identity');
        }
        return view('auth.register.step2', [
            'type' => $reg['org_data']['type'] ?? null,
            'data' => $reg['org_data'] ?? [],
        ]);
    }

    public function postStep2(Request $request)
    {
        $reg = $this->getReg();
        if (empty($reg['identity'])) {
            return redirect()->route('inscriptions.identity');
        }

        $rules = [
            'type' => 'required|in:universitaire,particulier,organisation',
            'description' => 'nullable|string|max:2000',
            'document_justificatif' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];

        $type = $request->type;

        if ($type === 'universitaire' || $type === 'organisation') {
            $rules['organisation'] = 'required|string|max:255';
        }

        if ($type === 'organisation') {
            $rules['type_detail'] = 'required|in:entreprise,association,club';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('document_justificatif')) {
            $validated['document_justificatif'] = $request->file('document_justificatif')
                ->store('justificatifs', 'public');
        }

        $this->putReg(['type' => $type, 'org_data' => $validated, 'step' => 3]);

        return redirect()->route('inscriptions.recap');
    }

    public function step3()
    {
        $reg = $this->getReg();
        if (empty($reg['org_data'])) {
            return redirect()->route('inscriptions.org');
        }
        return view('auth.register.step3', ['reg' => $reg]);
    }

    public function confirm(Request $request)
    {
        $reg = $this->getReg();
        if (empty($reg['org_data']) || empty($reg['type']) || empty($reg['email']) || empty($reg['identity'])) {
            return redirect()->route('inscriptions.organisateur');
        }

        $request->validate(['cgu' => 'accepted']);

        $identity = $reg['identity'];
        $orgData = $reg['org_data'];

        $userData = [
            'nom' => $identity['nom'],
            'email' => $reg['email'],
            'telephone' => $identity['telephone'],
            'type' => $reg['type'],
            'avatar' => $identity['avatar'] ?? null,
            'description' => $orgData['description'] ?? null,
            'document_justificatif' => $orgData['document_justificatif'] ?? null,
            'role' => 'admin',
            'statut' => 'en_attente',
        ];

        if ($reg['from_google'] ?? false) {
            $userData['mot_de_passe'] = Hash::make(\Illuminate\Support\Str::random(32));
        } else {
            $userData['mot_de_passe'] = Hash::make($identity['mot_de_passe']);
        }

        if ($reg['type'] === 'universitaire' || $reg['type'] === 'organisation') {
            $userData['organisation'] = $orgData['organisation'];
        }

        if ($reg['type'] === 'organisation') {
            $userData['type_detail'] = $orgData['type_detail'];
        }

        $user = User::create($userData);

        Mail::to($user->email)->send(new RegistrationPending($user));

        $superAdmins = User::where('role', 'super_admin')->get();
        foreach ($superAdmins as $sa) {
            Mail::to($sa->email)->send(new RegistrationAdminNotification($user));
        }

        $this->putReg(['step' => 'done']);

        return redirect()->route('inscriptions.confirmation');
    }

    public function confirmation()
    {
        $reg = $this->getReg();
        if (($reg['step'] ?? null) !== 'done') {
            return redirect()->route('inscriptions.organisateur');
        }

        $email = $reg['email'] ?? '';
        $this->regen();

        return view('auth.register.confirmation', ['email' => $email]);
    }

    public function resubmit(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->statut !== 'corrections_demandees') {
            return redirect()->route('dashboard')->with('error', 'Action non autorisée.');
        }

        $user->update(['statut' => 'en_attente']);

        $superAdmins = User::where('role', 'super_admin')->get();
        foreach ($superAdmins as $sa) {
            Mail::to($sa->email)->send(new RegistrationAdminNotification($user));
        }

        return redirect()->route('dashboard')->with('success', 'Votre profil a été soumis à nouveau pour validation.');
    }

    public function previous($step)
    {
        $reg = $this->getReg();
        $currentStep = $reg['step'] ?? 0;

        if ($step == 0) {
            $this->regen();
            return redirect()->route('inscriptions.organisateur');
        }
        if ($step == 1 && $currentStep >= 1) {
            return redirect()->route('inscriptions.identity');
        }
        if ($step == 2 && $currentStep >= 2) {
            return redirect()->route('inscriptions.org');
        }
        if ($step == 3 && $currentStep >= 3) {
            return redirect()->route('inscriptions.recap');
        }

        return redirect()->route('inscriptions.organisateur');
    }
}
