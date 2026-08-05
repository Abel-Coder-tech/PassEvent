<?php

namespace App\Services;

use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;

class TicketPdfService
{
    // Génère le PDF d'un ticket sur une seule page (agrandit la hauteur du papier si le contenu déborde)
    public static function generer(Ticket $ticket, string $qrCodeDataUri, string $logoDataUri): DomPdfWrapper
    {
        $hauteur = $ticket->estimerHauteurPdf();

        for ($essai = 0; $essai < 10; $essai++) {
            $pdf = Pdf::loadView('tickets.pdf.ticket', compact('ticket', 'qrCodeDataUri', 'logoDataUri'));
            $pdf->setPaper([0, 0, 287.43, $hauteur], 'portrait');
            $pdf->render();

            if ($pdf->getDomPDF()->getCanvas()->get_page_count() <= 1) {
                break;
            }

            $hauteur += 30;
        }

        return $pdf;
    }
}
