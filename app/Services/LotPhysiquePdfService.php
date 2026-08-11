<?php

namespace App\Services;

use App\Models\LotPhysique;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Support\Collection;

class LotPhysiquePdfService
{
    // Génère le PDF d'une planche de tickets physiques (A4, plusieurs QR par page).
    public static function generer(LotPhysique $lot, Collection $tickets, int $parPage = 12): DomPdfWrapper
    {
        $pages = $tickets->chunk($parPage)->values();

        $qrs = $tickets->mapWithKeys(fn (Ticket $t) => [
            $t->id => QrCodeService::generateDataUri($t->code_unique),
        ]);

        $logoDataUri = Ticket::logoBlancDataUri();

        $pdf = Pdf::loadView('tickets.pdf.planche', compact('lot', 'pages', 'qrs', 'logoDataUri'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->render();

        return $pdf;
    }
}
