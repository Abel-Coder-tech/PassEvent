<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationAdminNotification;
use App\Mail\RegistrationPending;
use App\Models\Message;
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
                'fonction' => $user->fonction,
                'document_justificatif' => $user->document_justificatif,
                'document_cip' => $user->document_cip,
                'signature' => $user->signature,
                'numero_rc' => $user->numero_rc,
                'numero_cip' => $user->numero_cip,
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

        $messages = [
            'type.required' => 'Veuillez choisir un type de compte.',
            'type.in' => 'Le type de compte sélectionné est invalide.',
        ];

        // Libellés lisibles
        $labels = [
            'document_justificatif' => 'le document justificatif (carte CIP ou carte étudiant)',
            'signature' => 'la signature',
            'document_cip' => 'la carte CIP du représentant',
            'numero_cip' => 'le numéro CIP',
            'numero_cip_rep' => 'le numéro CIP du représentant',
            'numero_rc' => 'le numéro du registre de commerce (ou récépissé)',
            'fonction' => 'la fonction du représentant',
            'organisation' => 'le nom de l\'organisation',
            'type_detail' => 'le type de structure',
        ];

        $hasDocs = $user->statut === 'corrections_demandees' && $user->document_justificatif && $user->signature;

        if (!$hasDocs || $request->hasFile('document_justificatif')) {
            $rules['document_justificatif'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
            $messages['document_justificatif.required'] = 'Veuillez joindre ' . $labels['document_justificatif'] . '.';
            $messages['document_justificatif.file'] = 'Le fichier joint est invalide.';
            $messages['document_justificatif.mimes'] = 'Le fichier doit être au format PDF, JPG ou PNG.';
            $messages['document_justificatif.max'] = 'Le fichier ne doit pas dépasser 2 Mo.';
            $messages['document_justificatif.uploaded'] = 'Le fichier n\'a pas pu être téléversé. Vérifiez que sa taille ne dépasse pas la limite du serveur.';
        }
        if (!$hasDocs || $request->hasFile('signature')) {
            $rules['signature'] = 'required|file|mimes:jpg,jpeg,png|max:2048';
            $messages['signature.required'] = 'Veuillez joindre ' . $labels['signature'] . '.';
            $messages['signature.mimes'] = 'La signature doit être au format JPG ou PNG.';
            $messages['signature.max'] = 'La signature ne doit pas dépasser 2 Mo.';
            $messages['signature.uploaded'] = 'La signature n\'a pas pu être téléversée. Vérifiez sa taille.';
        }

        if ($request->type === 'universitaire' || $request->type === 'organisation') {
            $rules['organisation'] = 'required|string|max:255';
            $messages['organisation.required'] = 'Veuillez renseigner ' . $labels['organisation'] . '.';
        }

        if ($request->type === 'organisation') {
            $rules['type_detail'] = 'required|in:entreprise,association';
            $rules['fonction'] = 'required|string|max:255';
            $rules['numero_rc'] = 'required|string|max:100';
            $rules['numero_cip_rep'] = 'required|string|max:100';
            $messages['type_detail.required'] = 'Veuillez choisir ' . $labels['type_detail'] . '.';
            $messages['fonction.required'] = 'Veuillez renseigner ' . $labels['fonction'] . '.';
            $messages['numero_rc.required'] = 'Veuillez renseigner ' . $labels['numero_rc'] . '.';
            $messages['numero_cip_rep.required'] = 'Veuillez renseigner ' . $labels['numero_cip_rep'] . '.';

            $hasCipDoc = $user->statut === 'corrections_demandees' && $user->document_cip;
            if (!$hasCipDoc || $request->hasFile('document_cip')) {
                $rules['document_cip'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
                $messages['document_cip.required'] = 'Veuillez joindre ' . $labels['document_cip'] . '.';
                $messages['document_cip.file'] = 'Le fichier joint est invalide.';
                $messages['document_cip.mimes'] = 'Le fichier doit être au format PDF, JPG ou PNG.';
                $messages['document_cip.max'] = 'Le fichier ne doit pas dépasser 2 Mo.';
                $messages['document_cip.uploaded'] = 'Le fichier n\'a pas pu être téléversé.';
            }
        } else {
            $rules['numero_cip'] = 'required|string|max:100';
            $messages['numero_cip.required'] = 'Veuillez renseigner ' . $labels['numero_cip'] . '.';
        }

        $validated = $request->validate($rules, $messages);

        if ($request->hasFile('document_justificatif')) {
            if ($user->document_justificatif) {
                Storage::disk('public')->delete($user->document_justificatif); // Supprime l'ancien document
            }
            $validated['document_justificatif'] = $request->file('document_justificatif')->store('justificatifs', 'public');
        } elseif ($hasDocs) {
            $validated['document_justificatif'] = $user->document_justificatif; // Conserve l'existant
        }

        if ($request->type === 'organisation') {
            $hasCipDoc = $user->statut === 'corrections_demandees' && $user->document_cip;
            if ($request->hasFile('document_cip')) {
                Storage::disk('public')->delete($user->document_cip);
                $validated['document_cip'] = $request->file('document_cip')->store('justificatifs', 'public');
            } elseif ($hasCipDoc) {
                $validated['document_cip'] = $user->document_cip;
            }
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

        // Si l'organisateur resoumet après des corrections demandées -> corrections_apportees
        $etaitEnCorrections = $user->statut === 'corrections_demandees';

        $updateData = [
            'type' => $data['type'],
            'document_justificatif' => $data['document_justificatif'],
            'signature' => $data['signature'],
            'statut' => $etaitEnCorrections ? 'corrections_apportees' : 'en_attente',
        ];

        if ($data['type'] === 'organisation') {
            $updateData['document_cip'] = $data['document_cip'] ?? null;
        } else {
            $updateData['document_cip'] = null;
        }

        if ($data['type'] === 'universitaire' || $data['type'] === 'organisation') {
            $updateData['organisation'] = $data['organisation'] ?? null;
        } else {
            $updateData['organisation'] = null;
        }

        if ($data['type'] === 'organisation') {
            $updateData['type_detail'] = $data['type_detail'] ?? null;
            $updateData['fonction'] = $data['fonction'] ?? null;
        } else {
            $updateData['type_detail'] = null;
            $updateData['fonction'] = null;
        }

        $updateData['numero_rc'] = $data['numero_rc'] ?? null;
        $updateData['numero_cip'] = $data['numero_cip'] ?? ($data['numero_cip_rep'] ?? null);

        $user->update($updateData);

        session()->forget('profil');

        Mail::to($user->email)->send(new RegistrationPending($user));

        $superAdmins = User::where('role', 'super_admin')->get();
        foreach ($superAdmins as $sa) {
            Mail::to($sa->email)->send(new RegistrationAdminNotification($user));
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

        $message = $etaitEnCorrections
            ? 'Vos corrections ont bien été enregistrées. Elles sont en cours de validation par notre équipe.'
            : 'Votre profil a été soumis pour validation.';

        return redirect()->route('dashboard')->with('success', $message);
    }
}
