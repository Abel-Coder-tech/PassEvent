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
    // Vérifie si l'utilisateur a le droit d'accéder à la complétion du profil
    private function checkAccess()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->statut, ['incomplet', 'corrections_demandees'])) {
            return false; // Accès refusé si le profil n'est pas dans le bon état
        }
        return $user;
    }

    // Étape 2 : Formulaire de type d'activité et documents justificatifs
    public function step2()
    {
        $user = $this->checkAccess();
        if (!$user) {
            return redirect()->route('dashboard');
        }

        if ($user->statut === 'corrections_demandees') {
            $data = [
                'type' => $user->type,
                'organisation' => $user->organisation,
                'type_detail' => $user->type_detail,
                'document_justificatif' => $user->document_justificatif,
                'signature' => $user->signature,
            ];
        } else {
            $data = session('profil', []);
        }

        return view('admin.profil.etape2', [
            'type' => old('type', $data['type'] ?? session('profil.type')),
            'data' => $data,
            'existingDocuments' => $user->statut === 'corrections_demandees',
        ]);
    }

    // Traite le formulaire étape 2 avec upload de documents
    public function postStep2(Request $request)
    {
        $user = $this->checkAccess();
        if (!$user) {
            return redirect()->route('dashboard');
        }

        $rules = [
            'type' => 'required|in:universitaire,particulier,organisation',
        ];

        $hasDocs = $user->statut === 'corrections_demandees' && $user->document_justificatif && $user->signature;

        if (!$hasDocs || $request->hasFile('document_justificatif')) {
            $rules['document_justificatif'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
        }
        if (!$hasDocs || $request->hasFile('signature')) {
            $rules['signature'] = 'required|file|mimes:jpg,jpeg,png|max:2048';
        }

        if ($request->type === 'universitaire' || $request->type === 'organisation') {
            $rules['organisation'] = 'required|string|max:255';
        }

        if ($request->type === 'organisation') {
            $rules['type_detail'] = 'required|in:entreprise,association';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('document_justificatif')) {
            if ($user->document_justificatif) {
                Storage::disk('public')->delete($user->document_justificatif); // Supprime l'ancien document
            }
            $validated['document_justificatif'] = $request->file('document_justificatif')->store('justificatifs', 'public');
        } elseif ($hasDocs) {
            $validated['document_justificatif'] = $user->document_justificatif; // Conserve l'existant
        }

        if ($request->hasFile('signature')) {
            if ($user->signature) {
                Storage::disk('public')->delete($user->signature); // Supprime l'ancienne signature
            }
            $validated['signature'] = $request->file('signature')->store('signatures', 'public');
        } elseif ($hasDocs) {
            $validated['signature'] = $user->signature;
        }

        session(['profil' => array_merge(session('profil', []), $validated)]);

        return redirect()->route('profil.recap');
    }

    // Affiche le récapitulatif avant soumission
    public function recap()
    {
        $user = $this->checkAccess();
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

    // Soumet le profil pour validation par le super admin
    public function submit(Request $request)
    {
        $user = $this->checkAccess();
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
            $updateData['organisation'] = $data['organisation'] ?? null;
        } else {
            $updateData['organisation'] = null;
        }

        if ($data['type'] === 'organisation') {
            $updateData['type_detail'] = $data['type_detail'] ?? null;
        } else {
            $updateData['type_detail'] = null;
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
