<?php

namespace App\Services;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class ContratService
{
    /**
     * Génère le contenu HTML du contrat pour un organisateur.
     */
    public function render(User $user): string
    {
        return view('site.contrat-prestation', compact('user'))->render();
    }

    /**
     * Génère l'objet PDF du contrat pour un organisateur (téléchargement ou pièce jointe).
     */
    public function pdf(User $user): \Barryvdh\DomPDF\PDF
    {
        $pdf = Pdf::loadHTML($this->render($user));
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Nom de fichier par défaut du contrat.
     */
    public function filename(User $user): string
    {
        return 'Contrat-Prestation-PaxEvent-' . $user->id . '.pdf';
    }
}