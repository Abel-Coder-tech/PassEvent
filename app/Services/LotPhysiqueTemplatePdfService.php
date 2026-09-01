<?php

namespace App\Services;

use App\Models\LotPhysique;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Support\Collection;

class LotPhysiqueTemplatePdfService
{
    // Marge externe autour du bloc de tickets
    public const MARGE = 4; // mm

    // Gouttière (zone de découpe) entre les tickets
    public const GOUTTIERE = 2; // mm

    // Padding blanc autour du QR code (quiet zone)
    public const QR_PADDING = 2; // mm

    // Bornes du zoom de l'image du template (70 % → 150 %)
    public const ZOOM_MIN = 70;
    public const ZOOM_MAX = 150;

    /**
     * Détails du format d'un lot (ou du format par défaut).
     */
    public static function formatDetails(LotPhysique $lot): array
    {
        $nom = $lot->format && isset(LotPhysique::FORMATS[$lot->format])
            ? $lot->format
            : 's1';

        return LotPhysique::FORMATS[$nom];
    }

    /**
     * Génère les positions (x, y en mm) et les lignes de découpe d'une page.
     */
    public static function layoutPage(array $format): array
    {
        $orientation = $format['orientation'];
        $pageW = $orientation === 'landscape' ? 297 : 210;
        $pageH = $orientation === 'landscape' ? 210 : 297;

        $slotW = $format['largeur'];
        $slotH = $format['hauteur'];
        $cols = $format['colonnes'];
        $rows = $format['lignes'];

        $blockW = $cols * $slotW + ($cols - 1) * self::GOUTTIERE;
        $blockH = $rows * $slotH + ($rows - 1) * self::GOUTTIERE;
        $startX = ($pageW - $blockW) / 2;
        $startY = max(($pageH - $blockH) / 2, self::MARGE);

        $positions = [];
        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                $positions[] = [
                    'x' => round($startX + $c * ($slotW + self::GOUTTIERE), 2),
                    'y' => round($startY + $r * ($slotH + self::GOUTTIERE), 2),
                ];
            }
        }

        $coupesH = [];
        for ($r = 1; $r < $rows; $r++) {
            $coupesH[] = round($startY + $r * ($slotH + self::GOUTTIERE) - self::GOUTTIERE / 2, 2);
        }
        $coupesV = [];
        for ($c = 1; $c < $cols; $c++) {
            $coupesV[] = round($startX + $c * ($slotW + self::GOUTTIERE) - self::GOUTTIERE / 2, 2);
        }

        return [
            'orientation' => $orientation,
            'page_largeur' => $pageW,
            'page_hauteur' => $pageH,
            'slot_largeur' => $slotW,
            'slot_hauteur' => $slotH,
            'colonnes' => $cols,
            'lignes' => $rows,
            'par_page' => $cols * $rows,
            'positions' => $positions,
            'coupes_h' => $coupesH,
            'coupes_v' => $coupesV,
            'bloc_gauche' => round($startX, 2),
            'bloc_haut' => round($startY, 2),
            'bloc_largeur' => round($blockW, 2),
            'bloc_hauteur' => round($blockH, 2),
            'marge_gauche' => round($startX, 2),
            'marge_bas' => round($pageH - ($startY + $blockH), 2),
        ];
    }

    /**
     * Génère le PDF avec template image + QR codes positionnés.
     */
    public static function generer(LotPhysique $lot, Collection $tickets): DomPdfWrapper
    {
        $format = self::formatDetails($lot);
        $layout = self::layoutPage($format);

        $qrs = $tickets->mapWithKeys(fn (Ticket $t) => [
            $t->id => QrCodeService::generateDataUri($t->code_unique, 300),
        ]);

        $templateUrl = self::templateToDataUri($lot);
        [$templateW, $templateH] = self::templateSize($lot);
        $zoom = self::zoomEffectif($lot);

        $qrSize = $lot->qr_size ?? $format['qr_defaut'];
        $qrX = $lot->qr_x ?? round(($layout['slot_largeur'] - $qrSize) / 2);
        $qrY = $lot->qr_y ?? round(($layout['slot_hauteur'] - $qrSize) / 2);
        $qrPadding = self::QR_PADDING;

        // Image : taille = slot × zoom, centrée, recadrée par le débordement caché
        $imgW = $layout['slot_largeur'] * $zoom / 100;
        $imgH = $templateW > 0 ? $imgW * $templateH / $templateW : $layout['slot_hauteur'] * $zoom / 100;
        $imgLeft = ($layout['slot_largeur'] - $imgW) / 2;
        $imgTop = ($layout['slot_hauteur'] - $imgH) / 2;

        $pageLargeur = $layout['page_largeur'];
        $pageHauteur = $layout['page_hauteur'];

        // Signature PaxEvent dans la marge : texte plus petit si la marge basse est réduite
        $signBottom = $layout['marge_bas'] >= 8 ? 2.0 : 0.5;
        $signFont = $layout['marge_bas'] >= 8 ? 9 : 6.5;

        $pages = $tickets->chunk($layout['par_page'])->values();

        $pdf = Pdf::loadView('tickets.pdf.template', compact(
            'lot', 'pages', 'qrs', 'templateUrl',
            'qrX', 'qrY', 'qrSize', 'qrPadding',
            'layout', 'pageLargeur', 'pageHauteur', 'format',
            'signBottom', 'signFont', 'zoom',
            'imgW', 'imgH', 'imgLeft', 'imgTop'
        ));
        $pdf->setPaper('a4', $layout['orientation']);
        $pdf->render();

        return $pdf;
    }

    /**
     * Génère un seul ticket composité (template + QR) en PDF.
     * Utile pour l'aperçu en temps réel (nouvel onglet).
     */
    public static function apercuTicket(LotPhysique $lot, Ticket $ticket)
    {
        $qrDataUri = QrCodeService::generateDataUri($ticket->code_unique, 300);
        $templateUrl = self::templateToDataUri($lot);
        [$templateW, $templateH] = self::templateSize($lot);
        $zoom = self::zoomEffectif($lot);

        $format = self::formatDetails($lot);
        $slotW = $format['largeur'];
        $slotH = $format['hauteur'];

        // Image : taille = slot × zoom, centrée, recadrée par le débordement caché
        $imgW = $slotW * $zoom / 100;
        $imgH = $templateW > 0 ? $imgW * $templateH / $templateW : $slotH * $zoom / 100;
        $imgLeft = ($slotW - $imgW) / 2;
        $imgTop = ($slotH - $imgH) / 2;

        $qrSize = $lot->qr_size ?? $format['qr_defaut'];
        $qrX = $lot->qr_x ?? round(($slotW - $qrSize) / 2);
        $qrY = $lot->qr_y ?? round(($slotH - $qrSize) / 2);
        $qrPadding = self::QR_PADDING;

        $html = view('tickets.pdf.ticket-preview', compact(
            'templateUrl', 'qrDataUri', 'qrX', 'qrY', 'qrSize',
            'slotW', 'slotH', 'qrPadding', 'zoom',
            'imgW', 'imgH', 'imgLeft', 'imgTop'
        ))->with('codeUnique', $ticket->code_unique)->render();

        $pdf = Pdf::loadHtml($html);
        $pdf->setPaper([0, 0, $slotW * 2.835, $slotH * 2.835], 'portrait');

        return $pdf->stream('Apercu-'.$ticket->code_unique.'.pdf');
    }

    /**
     * Zoom effectif de l'image du template (borne 70 % → 150 %).
     */
    public static function zoomEffectif(LotPhysique $lot): int
    {
        $zoom = (int) ($lot->template_zoom ?? 100);

        return max(self::ZOOM_MIN, min(self::ZOOM_MAX, $zoom));
    }

    /**
     * Taille intrinsèque (px) de l'image du template, [0, 0] si absente.
     */
    private static function templateSize(LotPhysique $lot): array
    {
        if (! $lot->template_path) {
            return [0, 0];
        }

        $path = storage_path("app/public/{$lot->template_path}");
        if (! is_file($path)) {
            return [0, 0];
        }

        $info = @getimagesize($path);

        return $info !== false ? [$info[0], $info[1]] : [0, 0];
    }

    /**
     * Convertit l'image du template en data URI base64 pour DomPDF.
     * Retourne null si aucune image enregistrée.
     */
    private static function templateToDataUri(LotPhysique $lot): ?string
    {
        if (! $lot->template_path) {
            return null;
        }

        $path = storage_path("app/public/{$lot->template_path}");
        if (! is_file($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($raw);
    }
}