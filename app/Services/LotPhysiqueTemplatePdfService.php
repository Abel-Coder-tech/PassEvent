<?php

namespace App\Services;

use App\Models\LotPhysique;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Support\Collection;

class LotPhysiqueTemplatePdfService
{
    // Dimensions A4 utiles pour 4 tickets (2×2) avec marges 10mm
    public const A4_LARGEUR = 210; // mm
    public const A4_HAUTEUR = 297; // mm
    public const MARGE = 10; // mm

    // Dimensions d'un ticket dans la grille 2×2
    public const TICKET_LARGEUR = 95; // mm
    public const TICKET_HAUTEUR = 138; // mm

    // Padding blanc autour du QR code
    public const QR_PADDING = 4; // mm

    /**
     * Génère le PDF avec template image + QR codes positionnés.
     * 4 tickets par page A4 (2×2).
     */
    public static function generer(LotPhysique $lot, Collection $tickets): DomPdfWrapper
    {
        $parPage = $lot->pdf_par_page ?? 4;
        $pages = $tickets->chunk($parPage)->values();

        // Pré-génère tous les QR codes en data URI SVG
        $qrs = $tickets->mapWithKeys(fn (Ticket $t) => [
            $t->id => QrCodeService::generateDataUri($t->code_unique, 300),
        ]);

        // Convertit l'image en data URI base64 pour DomPDF (enable_remote=false)
        $templateUrl = self::templateToDataUri($lot);
        $qrX = $lot->qr_x ?? 0;
        $qrY = $lot->qr_y ?? 0;
        $qrSize = $lot->qr_size ?? 40;
        $ticketLargeur = self::TICKET_LARGEUR;
        $ticketHauteur = self::TICKET_HAUTEUR;
        $qrPadding = self::QR_PADDING;

        $pdf = Pdf::loadView('tickets.pdf.template', compact(
            'lot', 'pages', 'qrs', 'templateUrl',
            'qrX', 'qrY', 'qrSize', 'ticketLargeur', 'ticketHauteur', 'qrPadding'
        ));
        $pdf->setPaper('a4', 'portrait');
        $pdf->render();

        return $pdf;
    }

    /**
     * Génère un seul ticket composité (template + QR) en image base64.
     * Utile pour l'aperçu en temps réel.
     */
    public static function apercuTicket(LotPhysique $lot, Ticket $ticket): string
    {
        $qrDataUri = QrCodeService::generateDataUri($ticket->code_unique, 300);
        $templateUrl = self::templateToDataUri($lot);
        $qrX = $lot->qr_x ?? 0;
        $qrY = $lot->qr_y ?? 0;
        $qrSize = $lot->qr_size ?? 40;
        $ticketLargeur = self::TICKET_LARGEUR;
        $ticketHauteur = self::TICKET_HAUTEUR;
        $qrPadding = self::QR_PADDING;

        $html = view('tickets.pdf.ticket-preview', compact(
            'templateUrl', 'qrDataUri', 'qrX', 'qrY', 'qrSize',
            'ticketLargeur', 'ticketHauteur', 'qrPadding'
        ))->render();

        $pdf = Pdf::loadHtml($html);
        $pdf->setPaper([0, 0, $ticketLargeur * 2.835, $ticketHauteur * 2.835], 'portrait');

        return $pdf->inline();
    }

    /**
     * Convertit l'image du template en data URI base64 pour DomPDF.
     */
    private static function templateToDataUri(LotPhysique $lot): string
    {
        $path = storage_path("app/public/{$lot->template_path}");
        $raw = file_get_contents($path);
        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($raw);
    }
}
