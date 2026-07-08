<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationAdminNotification;
use App\Mail\RegistrationPending;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    private function checkIncomplet()
    {
        $user = auth()->user();
        if (!$user || $user->statut !== 'incomplet') {
            return false;
        }
        return $user;
    }

    public function step2()
    {
        $user = $this->checkIncomplet();
        if (!$user) {
            return redirect()->route('dashboard');
        }
        return view('admin.profil.etape2', [
            'type' => old('type', session('profil.type')),
            'data' => session('profil', []),
        ]);
    }

    public function postStep2(Request $request)
    {
        $user = $this->checkIncomplet();
        if (!$user) {
            return redirect()->route('dashboard');
        }

        $rules = [
            'type' => 'required|in:universitaire,particulier,organisation',
            'document_justificatif' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'signature' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];

        if ($request->type === 'universitaire' || $request->type === 'organisation') {
            $rules['organisation'] = 'required|string|max:255';
        }

        if ($request->type === 'organisation') {
            $rules['type_detail'] = 'required|in:entreprise,association,club';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('document_justificatif')) {
            $validated['document_justificatif'] = $request->file('document_justificatif')
                ->store('justificatifs', 'public');
        }

        if ($request->hasFile('signature')) {
            $validated['signature'] = $request->file('signature')
                ->store('signatures', 'public');
        }

        session(['profil' => array_merge(session('profil', []), $validated)]);

        return redirect()->route('profil.recap');
    }

    public function recap()
    {
        $user = $this->checkIncomplet();
        if (!$user) {
            return redirect()->route('dashboard');
        }

        $data = session('profil', []);
        if (empty($data['type']) || empty($data['document_justificatif']) || empty($data['signature'])) {
            return redirect()->route('profil.step2');
        }

        return view('admin.profil.etape3', [
            'user' => $user,
            'data' => $data,
        ]);
    }

    public function submit(Request $request)
    {
        $user = $this->checkIncomplet();
        if (!$user) {
            return redirect()->route('dashboard');
        }

        $data = session('profil', []);
        if (empty($data['type']) || empty($data['document_justificatif']) || empty($data['signature'])) {
            return redirect()->route('profil.step2');
        }

        $updateData = [
            'type' => $data['type'],
            'document_justificatif' => $data['document_justificatif'],
            'signature' => $data['signature'],
            'statut' => 'en_attente',
        ];

        if ($data['type'] === 'universitaire' || $data['type'] === 'organisation') {
            $updateData['organisation'] = $data['organisation'];
        }

        if ($data['type'] === 'organisation') {
            $updateData['type_detail'] = $data['type_detail'];
        }

        $user->update($updateData);

        session()->forget('profil');

        Mail::to($user->email)->send(new RegistrationPending($user));

        $superAdmins = User::where('role', 'super_admin')->get();
        foreach ($superAdmins as $sa) {
            Mail::to($sa->email)->send(new RegistrationAdminNotification($user));
        }

        return redirect()->route('dashboard')->with('success', 'Votre profil a été soumis pour validation.');
    }
}
