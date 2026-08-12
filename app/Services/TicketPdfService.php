<?php

namespace App\Services;

use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;

class TicketPdfService
{
    // Génère le PDF du ticket à une taille fixe : 8 × 13 cm.
    public static function generer(Ticket $ticket, string $qrCodeDataUri, string $logoDataUri): DomPdfWrapper
    {
        // dompdf attend des points : 1 cm = 28.3465 pt
        $largeur = 8 * 28.3465;   // 226.772 pt
        $hauteur = 13 * 28.3465;  // 368.5045 pt

        $pdf = Pdf::loadView('tickets.pdf.ticket', compact('ticket', 'qrCodeDataUri', 'logoDataUri'));
        $pdf->setPaper([0, 0, $largeur, $hauteur], 'portrait');
        $pdf->render();

        return $pdf;
    }
}
