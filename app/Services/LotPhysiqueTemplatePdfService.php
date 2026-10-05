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

    // Marges internes du carré blanc qui entoure le QR : haut 0,25 — gauche/droite
    // = marge du haut (0,2 au minimum). Le code pass n'est plus dans le carré :
    // il est imprimé sous la zone, écarté de 0,1 mm, avec 1,65 mm en dessous.
    public const QR_TOP = 0.25; // mm  marge entre le bord haut de la zone et le QR
    public const QR_SIDE = 0.2; // mm   marge gauche/droite entre le bord de la zone et le QR
    public const PAX_GAP = 0.1; // mm  écart entre le bas de la zone et le code pass
    public const PAX_BOTTOM = 1.65; // mm   marge sous le code pass

    // Taille du texte du code pass : 11,34 px = 3 mm à l'impression (96 px = 1 pouce)
    public const PAX_FONT = 11.34; // px

    // Hauteur de ligne du texte du code pass (assez haute pour rester lisible dans DomPDF)
    public const PAX_LINE_HEIGHT = 3.3; // mm

    // Côté minimal du carré blanc : 2 cm → carré 20×20. Un QR plus petit est
    // agrandi automatiquement pour remplir le carré (voir qrMin()).
    public const ZONE_MIN = 20; // mm

    // Bornes du zoom de l'image du template (70 % → 150 %)
    public const ZOOM_MIN = 70;
    public const ZOOM_MAX = 150;

    /**
     * Marge gauche/droite du carré blanc : jamais plus petite que la marge du haut.
     */
    public static function padX(): float
    {
        return round(max(self::QR_SIDE, self::QR_TOP), 2);
    }

    /**
     * Plus petit QR qui remplit exactement le carré de ZONE_MIN de côté (20 mm).
     * Un QR plus petit est automatiquement agrandi à cette taille.
     */
    public static function qrMin(): float
    {
        return round(self::ZONE_MIN - 2 * self::padX(), 2);
    }

    /**
     * Géométrie (mm, relative au ticket) du carré blanc et du code pass.
     *
     * Le carré entoure le QR avec des marges identiques (0,25 mm) ; le code pass
     * est imprimé sous le carré, jamais dedans. Les positions sont bornées pour
     * que le carré et le code restent dans le ticket.
     *
     * @param  float|null  $qrSize  taille demandée (agrandie au minimum si trop petite)
     * @param  float|null  $qrX  position du QR, ou null pour centrer
     * @param  float|null  $qrY  position du QR, ou null pour centrer
     * @return array<string, float>
     */
    public static function geometry(?float $qrSize, ?float $qrX, ?float $qrY, float $slotW, float $slotH): array
    {
        $padX = self::padX();
        $padTop = self::QR_TOP;
        $gap = self::PAX_GAP;

        // Un QR plus petit que qrMin() est agrandi : il remplit alors le carré de 2 cm.
        $qrSize = round(max($qrSize, self::qrMin()), 2);

        // Carré blanc : côté = QR + 2 marges (haut = côtés).
        $zoneW = round($qrSize + 2 * $padX, 2);
        $zoneH = $zoneW;
        $paxBandH = round($gap + self::PAX_LINE_HEIGHT + self::PAX_BOTTOM, 2);

        $zoneX = min(max($qrX - $padX, 0.0), max($slotW - $zoneW, 0.0));
        $zoneY = min(max($qrY - $padTop, 0.0), max($slotH - ($zoneH + $paxBandH), 0.0));

        return [
            'qrSize' => $qrSize,
            'padX' => $padX,
            'padTop' => $padTop,
            'zoneX' => $zoneX,
            'zoneY' => $zoneY,
            'zoneW' => $zoneW,
            'zoneH' => $zoneH,
            'gap' => $gap,
            'bandTop' => round($zoneY + $zoneH, 2),
            'paxBandH' => $paxBandH,
            'paxLineH' => self::PAX_LINE_HEIGHT,
            'paxFont' => self::PAX_FONT,
            'paxBottom' => self::PAX_BOTTOM,
        ];
    }

    /**
     * Détails du format d'un lot (prédéfini ou sur mesure, sinon format par défaut).
     */
    public static function formatDetails(LotPhysique $lot): array
    {
        return $lot->formatDetails();
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
        // Les grandes planches (template en raster + 150 QR) dépassent la limite par
        // défaut : on autorise plus de mémoire le temps du rendu dompdf.
        @ini_set('memory_limit', '1024M');

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
        $geo = self::geometry($qrSize, $qrX, $qrY, $layout['slot_largeur'], $layout['slot_hauteur']);

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

        $pdf = Pdf::loadView('tickets.pdf.template', array_merge(compact(
            'lot', 'pages', 'qrs', 'templateUrl',
            'layout', 'pageLargeur', 'pageHauteur', 'format',
            'signBottom', 'signFont', 'zoom',
            'imgW', 'imgH', 'imgLeft', 'imgTop'
        ), $geo));
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
        @ini_set('memory_limit', '1024M');

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
        $geo = self::geometry($qrSize, $qrX, $qrY, $slotW, $slotH);

        $html = view('tickets.pdf.ticket-preview', array_merge(compact(
            'templateUrl', 'qrDataUri', 'slotW', 'slotH', 'zoom',
            'imgW', 'imgH', 'imgLeft', 'imgTop'
        ), $geo))->with('codeUnique', $ticket->code_unique)->render();

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

        // Images trop grandes (ex. PNG 2400×2400) : dompdf décode l'image entière
        // et fait exploser la mémoire sur de grandes planches. On réduit donc à la
        // volée à ~300 dpi pour l'impression d'un ticket (max 1600 px de côté).
        $maxSide = 1600;
        $info = @getimagesize($path);
        if ($info && max($info[0], $info[1]) > $maxSide && function_exists('imagecreatefromstring')) {
            $src = @imagecreatefromstring($raw);
            if ($src) {
                $w = imagesx($src);
                $h = imagesy($src);
                $scale = $maxSide / max($w, $h);
                $nw = (int) round($w * $scale);
                $nh = (int) round($h * $scale);
                $dst = imagecreatetruecolor($nw, $nh);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                ob_start();
                imagepng($dst);
                $resized = ob_get_clean();
                imagedestroy($src);
                imagedestroy($dst);
                return 'data:image/png;base64,'.base64_encode($resized);
            }
        }

        return 'data:'.$mime.';base64,'.base64_encode($raw);
    }
}