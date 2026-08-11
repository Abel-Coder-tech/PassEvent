<?php

namespace App\Services;

use App\Models\LotPhysique;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Support\Collection;

class LotPhysiquePdfService
{
    public const PAR_PAGE = 36;

    // Génère le PDF d'une planche de tickets physiques (A4 portrait, 6x6 QR de 2,5 cm).
    public static function generer(LotPhysique $lot, Collection $tickets, int $parPage = self::PAR_PAGE): DomPdfWrapper
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

    // Génère un PDF unique regroupant plusieurs lots (chaque lot commence sur une nouvelle page).
    public static function genererPlusieurs(Collection $lots, int $parPage = self::PAR_PAGE): DomPdfWrapper
    {
        $groupes = collect();
        $qrsArray = [];

        foreach ($lots as $lot) {
            $tickets = $lot->tickets()->where('annule', false)->orderBy('code_unique')->get();
            if ($tickets->isEmpty()) {
                continue;
            }

            $groupes->push((object) [
                'lot' => $lot,
                'pages' => $tickets->chunk($parPage)->values(),
            ]);

            foreach ($tickets as $ticket) {
                $qrsArray[$ticket->id] = QrCodeService::generateDataUri($ticket->code_unique);
            }
        }

        $qrs = collect($qrsArray);
        $logoDataUri = Ticket::logoBlancDataUri();

        $pdf = Pdf::loadView('tickets.pdf.planches', compact('groupes', 'qrs', 'logoDataUri'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->render();

        return $pdf;
    }
}
