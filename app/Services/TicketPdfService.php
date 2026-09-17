<?php

namespace App\Services;

use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Support\Facades\Storage;

class TicketPdfService
{
    // Génère le PDF du ticket à une taille fixe : 8 × 13 cm.
    public static function generer(Ticket $ticket, string $qrCodeDataUri, string $logoDataUri): DomPdfWrapper
    {
        // dompdf attend des points : 1 cm = 28.3465 pt
        $largeur = 8 * 28.3465;   // 226.772 pt
        $hauteur = 13 * 28.3465;  // 368.5045 pt

        $eventImageDataUri = self::eventImageDataUri($ticket);
        $faviconDataUri = self::faviconDataUri();

        // Sur le fond sombre du bas, on utilise le logo blanc pour rester lisible.
        if ($eventImageDataUri) {
            $logoDataUri = Ticket::logoBlancDataUri();
        }

        $pdf = Pdf::loadView('tickets.pdf.ticket', compact(
            'ticket',
            'qrCodeDataUri',
            'logoDataUri',
            'eventImageDataUri',
            'faviconDataUri'
        ));
        $pdf->setPaper([0, 0, $largeur, $hauteur], 'portrait');
        $pdf->render();

        return $pdf;
    }

    // Image importée par l'organisateur pour l'événement, en data-URI (ou null).
    protected static function eventImageDataUri(Ticket $ticket): ?string
    {
        $path = $ticket->evenement?->image;

        if (!$path) {
            return null;
        }

        $abs = Storage::disk('public')->path($path);
        if (!is_file($abs)) {
            return null;
        }

        $mime = @mime_content_type($abs) ?: 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($abs));
    }

    // Favicon PaxEvent (incrusté au centre du QR code), en data-URI (ou null).
    protected static function faviconDataUri(): ?string
    {
        foreach (['favicon.png', 'images/paxevent_icone1.png'] as $rel) {
            $abs = public_path($rel);
            if (is_file($abs)) {
                return 'data:image/png;base64,' . base64_encode((string) file_get_contents($abs));
            }
        }

        return null;
    }
}
