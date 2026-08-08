<?php

namespace App\Services;

use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;

class TicketPdfService
{
    // Génère le PDF du ticket à une taille fixe : 377.953 px × 529.1342 px (10 × 14 cm).
    public static function generer(Ticket $ticket, string $qrCodeDataUri, string $logoDataUri): DomPdfWrapper
    {
        // dompdf attend des points (1 px = 0.75 pt à 96 dpi)
        $largeur = 377.953 * 0.75;   // 283.46475 pt
        $hauteur = 520.1342 * 0.75;  // 396.85065 pt

        $pdf = Pdf::loadView('tickets.pdf.ticket', compact('ticket', 'qrCodeDataUri', 'logoDataUri'));
        $pdf->setPaper([0, 0, $largeur, $hauteur], 'portrait');
        $pdf->render();

        return $pdf;
    }
}
