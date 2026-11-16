<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MandatGestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

// Autorisation de gestion (mandat) : côté organisateur et côté PaxEvent.
class MandatGestionController extends Controller
{
    public function __construct(private MandatGestionService $mandats)
    {
    }

    // ---------- Organisateur (son tableau de bord) ----------

    public function accorder(Request $request)
    {
        $user = Auth::user();

        if (! $user->mandat_actif) {
            $this->mandats->accorder($user, 'organisateur', $user->nom, $request->ip());
        }

        return back()->with('success', 'PaxEvent est autorisé à gérer votre compte. La déclaration sur l\'honneur est disponible ci-dessous.');
    }

    public function retirer(Request $request)
    {
        $user = Auth::user();

        if ($user->mandat_actif) {
            $this->mandats->retirer($user, 'organisateur', $request->ip());
        }

        return back()->with('success', 'Vous avez retiré l\'autorisation : PaxEvent ne peut plus agir à votre place.');
    }

    public function declaration()
    {
        $user = Auth::user();
        abort_if(! $user->mandat_pdf_chemin || ! Storage::disk('local')->exists($user->mandat_pdf_chemin), 404);

        return Storage::disk('local')->download($user->mandat_pdf_chemin, 'Declaration-autorisation-PaxEvent.pdf');
    }

    // ---------- PaxEvent (fiche de l'organisateur) ----------

    private function autoriser(User $user): void
    {
        /** @var \App\Models\User|null $membre */
        $membre = auth('superadmin')->user();
        abort_unless($membre?->peut('organisateurs.gerer'), 403);
        abort_if($user->role !== 'admin', 404);
    }

    // Enregistre le justificatif signé reçu de l'organisateur (courrier, WhatsApp, email).
    public function enregistrer(Request $request, User $user)
    {
        $this->autoriser($user);
        $request->validate(['justificatif' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'], [
            'justificatif.required' => 'Joignez la déclaration signée reçue de l\'organisateur.',
            'justificatif.mimes' => 'Le justificatif doit être un PDF, JPG ou PNG.',
            'justificatif.max' => 'Le fichier ne doit pas dépasser 5 Mo.',
        ]);

        $this->mandats->accorder($user, 'paxevent_recueilli', auth('superadmin')->user()->email, $request->ip(), $request->file('justificatif'));

        return back()->with('success', 'Autorisation enregistrée. Vous pouvez gérer cet organisateur.');
    }

    // Retrait demandé par l'organisateur à PaxEvent (message, appel).
    public function retirerPourOrganisateur(Request $request, User $user)
    {
        $this->autoriser($user);
        $this->mandats->retirer($user, auth('superadmin')->user()->email, $request->ip());

        return back()->with('success', 'Retrait de l\'autorisation enregistré.');
    }

    // Modèle à imprimer et faire signer (non conservé).
    public function modele(User $user)
    {
        $this->autoriser($user);

        return $this->mandats->pdf($user, 'modele')->stream('Declaration-autorisation-modele.pdf');
    }

    public function voir(User $user)
    {
        $this->autoriser($user);
        $chemin = $user->mandat_pdf_chemin ?: $user->mandat_justificatif_chemin;
        abort_if(! $chemin || ! Storage::disk('local')->exists($chemin), 404);

        return Storage::disk('local')->response($chemin);
    }

    public function voirJustificatif(User $user)
    {
        $this->autoriser($user);
        abort_if(! $user->mandat_justificatif_chemin || ! Storage::disk('local')->exists($user->mandat_justificatif_chemin), 404);

        return Storage::disk('local')->response($user->mandat_justificatif_chemin);
    }
}
