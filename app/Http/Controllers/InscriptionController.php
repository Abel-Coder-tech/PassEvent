<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationAdminNotification;
use App\Mail\RegistrationPending;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
            return back()->withErrors(['email' => 'Un compte existe d&eacute;j&agrave; avec cet email. Connectez-vous.'])->withInput();
        }

        $this->otp->generateAndSend($request->email);

        $this->putReg(['email' => $request->email, 'email_verified' => false, 'step' => 1]);

        return redirect()->route('inscriptions.verify');
    }

    public function showVerify()
    {
        $reg = $this->getReg();
        if (empty($reg['email'])) {
            return redirect()->route('inscriptions.create');
        }
        return view('auth.register.verify', ['email' => $reg['email']]);
    }

    public function verifyOtp(Request $request)
    {
        $reg = $this->getReg();
        if (empty($reg['email'])) {
            return redirect()->route('inscriptions.create');
        }

        $request->validate(['code' => 'required|string|size:4']);

        $result = $this->otp->verify($reg['email'], $request->code);

        if ($result === 'invalide') {
            return back()->withErrors(['code' => 'Code invalide. V&eacute;rifiez votre email ou demandez un nouveau code.']);
        }
        if ($result === 'expire') {
            return back()->withErrors(['code' => 'Ce code a expir&eacute;. Cliquez sur Renvoyer le code pour en recevoir un nouveau.']);
        }

        $this->putReg(['email_verified' => true]);

        return redirect()->route('inscriptions.type');
    }

    public function resendOtp(Request $request)
    {
        $reg = $this->getReg();
        if (empty($reg['email'])) {
            return $request->expectsJson()
                ? response()->json(['success' => false])
                : redirect()->route('inscriptions.create');
        }

        $this->otp->generateAndSend($reg['email']);

        return $request->expectsJson()
            ? response()->json(['success' => true])
            : back()->with('success', 'Un nouveau code vous a &eacute;t&eacute; envoy&eacute;.');
    }

    public function step1()
    {
        $reg = $this->getReg();
        if (empty($reg['email']) || empty($reg['email_verified'])) {
            return redirect()->route('inscriptions.create');
        }
        return view('auth.register.step1');
    }

    public function postType(Request $request)
    {
        $reg = $this->getReg();
        if (empty($reg['email']) || empty($reg['email_verified'])) {
            return redirect()->route('inscriptions.create');
        }

        $request->validate(['type' => 'required|in:universitaire,particulier,organisation']);

        $this->putReg(['type' => $request->type, 'step' => 2, 'data' => []]);

        return redirect()->route('inscriptions.infos');
    }

    public function step2()
    {
        $reg = $this->getReg();
        if (empty($reg['type'])) {
            return redirect()->route('inscriptions.type');
        }
        return view('auth.register.step2', ['type' => $reg['type'], 'data' => $reg['data'] ?? []]);
    }

    public function postInfos(Request $request)
    {
        $reg = $this->getReg();
        if (empty($reg['type'])) {
            return redirect()->route('inscriptions.type');
        }

        $type = $reg['type'];

        $rules = [
            'telephone' => 'required|string|max:30',
            'mot_de_passe' => 'required|string|min:8|confirmed',
        ];

        if ($type === 'universitaire') {
            $rules['organisation'] = 'required|string|max:255';
            $rules['nom'] = 'required|string|max:255';
            $rules['avatar'] = 'nullable|image|max:2048';
        } elseif ($type === 'particulier') {
            $rules['nom'] = 'required|string|max:255';
            $rules['avatar'] = 'nullable|image|max:2048';
        } elseif ($type === 'organisation') {
            $rules['nom'] = 'required|string|max:255';
            $rules['organisation'] = 'required|string|max:255';
            $rules['type_detail'] = 'required|in:entreprise,association,club';
            $rules['avatar'] = 'nullable|image|max:2048';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $this->putReg(['data' => $validated, 'step' => 3]);

        return redirect()->route('inscriptions.recap');
    }

    public function step3()
    {
        $reg = $this->getReg();
        if (empty($reg['data'])) {
            return redirect()->route('inscriptions.infos');
        }
        return view('auth.register.step3', ['reg' => $reg]);
    }

    public function confirm(Request $request)
    {
        $reg = $this->getReg();
        if (empty($reg['data']) || empty($reg['type']) || empty($reg['email'])) {
            return redirect()->route('inscriptions.create');
        }

        $request->validate(['cgu' => 'accepted']);

        $data = $reg['data'];

        $userData = [
            'nom' => $data['nom'],
            'email' => $reg['email'],
            'telephone' => $data['telephone'],
            'mot_de_passe' => Hash::make($data['mot_de_passe']),
            'type' => $reg['type'],
            'avatar' => $data['avatar'] ?? null,
            'role' => 'admin',
            'statut' => 'en_attente',
        ];

        if ($reg['type'] === 'universitaire' || $reg['type'] === 'organisation') {
            $userData['organisation'] = $data['organisation'];
        }

        if ($reg['type'] === 'organisation') {
            $userData['type_detail'] = $data['type_detail'];
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
            return redirect()->route('inscriptions.create');
        }

        $email = $reg['email'] ?? '';
        $this->regen();

        return view('auth.register.confirmation', ['email' => $email]);
    }

    public function previous($step)
    {
        $reg = $this->getReg();
        $currentStep = $reg['step'] ?? 0;

        if ($step == 0) {
            $this->regen();
            return redirect()->route('inscriptions.create');
        }
        if ($step == 1 && $currentStep >= 1) {
            return redirect()->route('inscriptions.type');
        }
        if ($step == 2 && $currentStep >= 2) {
            return redirect()->route('inscriptions.infos');
        }
        if ($step == 3 && $currentStep >= 3) {
            return redirect()->route('inscriptions.recap');
        }

        return redirect()->route('inscriptions.create');
    }
}
