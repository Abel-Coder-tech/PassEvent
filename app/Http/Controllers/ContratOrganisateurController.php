<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ContratService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Contrat d'un organisateur côté PaxEvent : copie figée, transmission, contrat signé retourné.
class ContratOrganisateurController extends Controller
{
    private function autoriser(User $user): void
    {
        /** @var User|null $membre */
        $membre = auth('superadmin')->user();
        abort_unless($membre?->peut('organisateurs.gerer'), 403);
        abort_if($user->role !== 'admin', 404);
    }

    // (Re)génère et fige le contrat avec les taux et les informations actuels.
    public function figer(User $user, ContratService $contrats)
    {
        $this->autoriser($user);
        $contrats->figer($user);

        return back()->with('success', 'Contrat généré et figé. Transmettez-le à l\'organisateur.');
    }

    public function voirFige(User $user, ContratService $contrats)
    {
        $this->autoriser($user);
        $chemin = $contrats->cheminFige($user);
        abort_if($chemin === null, 404, 'Aucun contrat figé pour cet organisateur.');

        return Storage::disk('local')->response($chemin, $contrats->filename($user), ['Content-Type' => 'application/pdf']);
    }

    // Enregistre que le contrat a été transmis (canal, date, membre de l'équipe).
    public function transmis(Request $request, User $user)
    {
        $this->autoriser($user);
        $data = $request->validate(
            [
                'canal' => 'required|in:email,whatsapp,autre'
            ],[
                'canal.required' => 'Choisissez le canal de transmission.',
                'canal.in' => 'Le canal de transmission doit être email, whatsapp ou autre.',
            ]);

        if (! $user->contrat_chemin) {
            return back()->with('error', 'Générez d\'abord le contrat avant de le marquer comme transmis.');
        }

        $user->update([
            'contrat_transmis_le' => now(),
            'contrat_transmis_canal' => $data['canal'],
            'contrat_transmis_par' => auth('superadmin')->user()->email,
        ]);

        return back()->with('success', 'Transmission du contrat enregistrée.');
    }

    // Contrat signé retourné par l'organisateur (facultatif, sert de preuve).
    public function signe(Request $request, User $user)
    {
        $this->autoriser($user);
        $request->validate(['contrat_signe' => 'required|file|mimes:pdf|max:5120'], [
            'contrat_signe.required' => 'Choisissez le contrat signé.',
            'contrat_signe.mimes' => 'Le contrat signé doit être un PDF.',
            'contrat_signe.max' => 'Le fichier ne doit pas dépasser 5 Mo.',
        ]);

        $chemin = $request->file('contrat_signe')->store('contrats/'.$user->id, 'local');
        $user->update(['contrat_signe_chemin' => $chemin]);

        return back()->with('success', 'Contrat signé enregistré.');
    }

    public function voirSigne(User $user)
    {
        $this->autoriser($user);
        abort_if(! $user->contrat_signe_chemin || ! Storage::disk('local')->exists($user->contrat_signe_chemin), 404);

        return Storage::disk('local')->response($user->contrat_signe_chemin);
    }
}
