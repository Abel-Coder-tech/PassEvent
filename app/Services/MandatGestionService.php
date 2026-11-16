<?php

namespace App\Services;

use App\Models\Log;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Autorisation (mandat) donnée par l'organisateur à PaxEvent d'agir à sa place.
 * Obligatoire pour ouvrir le mode « Gérer ». Trois origines :
 *  - organisateur : il l'active lui-même en un clic (déclaration PDF datée générée) ;
 *  - creation_paxevent : accordée dès la création du compte par PaxEvent ;
 *  - paxevent_recueilli : PaxEvent enregistre le justificatif signé reçu de l'organisateur.
 */
class MandatGestionService
{
    public const MODES = ['organisateur', 'creation_paxevent', 'paxevent_recueilli'];

    public function accorder(User $organisateur, string $mode, ?string $par = null, ?string $ip = null, ?UploadedFile $justificatif = null): void
    {
        $maintenant = now();
        $donnees = [
            'mandat_actif' => true,
            'mandat_accorde_le' => $maintenant,
            'mandat_mode' => $mode,
            'mandat_enregistre_par' => $par,
            'mandat_retire_le' => null,
            'mandat_retire_par' => null,
        ];

        if ($justificatif !== null) {
            $donnees['mandat_justificatif_chemin'] = $justificatif->store('mandats/'.$organisateur->id, 'local');
        }

        // La déclaration PDF est conservée ; son échec n'empêche pas d'enregistrer l'autorisation.
        if ($mode !== 'paxevent_recueilli') {
            try {
                $chemin = 'mandats/'.$organisateur->id.'/declaration-'.$maintenant->format('Ymd-His').'.pdf';
                Storage::disk('local')->put($chemin, $this->pdf($organisateur, $mode, $maintenant, $par, $ip)->output());
                $donnees['mandat_pdf_chemin'] = $chemin;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Déclaration de mandat non générée pour '.$organisateur->id.' : '.$e->getMessage());
            }
        }

        $organisateur->update($donnees);
        $this->journaliser($organisateur, 'mandat_accorde', $mode, $par, $ip);
    }

    public function retirer(User $organisateur, string $par, ?string $ip = null): void
    {
        $organisateur->update([
            'mandat_actif' => false,
            'mandat_retire_le' => now(),
            'mandat_retire_par' => $par,
        ]);
        $this->journaliser($organisateur, 'mandat_retire', null, $par, $ip);
    }

    /**
     * Déclaration PDF. Mode « modele » : version à imprimer et faire signer (non conservée).
     */
    public function pdf(User $organisateur, string $mode, $date = null, ?string $par = null, ?string $ip = null): \Barryvdh\DomPDF\PDF
    {
        $pdf = Pdf::loadHTML(view('pdf.mandat-gestion', [
            'user' => $organisateur,
            'mode' => $mode,
            'date' => $date ?? now(),
            'par' => $par,
            'ip' => $ip,
        ])->render());
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    private function journaliser(User $organisateur, string $action, ?string $mode, ?string $par, ?string $ip): void
    {
        Log::create([
            'type_operation' => 'mandat_gestion',
            'ticket_id' => null,
            'details' => ['action' => $action, 'user_id' => $organisateur->id, 'email' => $organisateur->email, 'mode' => $mode, 'par' => $par],
            'ip' => $ip,
        ]);
    }
}
