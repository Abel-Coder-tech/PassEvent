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

    // Gouttière (zone de découpe) entre les tickets : la ligne de coupe est au milieu
    public const GOUTTIERE = 1; // mm

    // Bracelet : le code pass vertical est dans la zone, à droite du QR
    // (mêmes valeurs que le code sous le QR des autres formats, tournées)
    public const CODE_V_ECART = self::PAX_GAP; // mm  entre le QR et le code
    public const CODE_V_LARGEUR = self::PAX_LINE_HEIGHT; // mm

    // Marges internes de la zone blanche qui entoure le QR : haut 0,25 — gauche/droite
    // = marge du haut (0,2 au minimum). Le code pass est dans la zone, sous le QR :
    // écarté de 0,1 mm, avec 1,65 mm en dessous avant le bord bas de la zone.
    public const QR_TOP = 0.25; // mm  marge entre le bord haut de la zone et le QR
    public const QR_SIDE = 0.2; // mm   marge gauche/droite entre le bord de la zone et le QR
    public const PAX_GAP = 0.1; // mm   écart entre le bas du QR et le code pass
    public const PAX_BOTTOM = 1.65; // mm   marge entre le code pass et le bord bas de la zone

    // Taille du texte du code pass : 11,34 px = 3 mm à l'impression (96 px = 1 pouce)
    public const PAX_FONT = 11.34; // px

    // Hauteur de ligne du texte du code pass (assez haute pour rester lisible dans DomPDF)
    public const PAX_LINE_HEIGHT = 3.3; // mm

    // Hauteur minimale de la zone blanche : 2 cm. Un QR plus petit est agrandi
    // automatiquement pour que la zone fasse au moins 20 mm de haut (voir qrMin()).
    public const ZONE_MIN = 20; // mm

    // Bornes du zoom de l'image du template (70 % → 150 %)
    public const ZOOM_MIN = 70;
    public const ZOOM_MAX = 150;

    /**
     * Marge gauche/droite de la zone blanche : jamais plus petite que la marge du haut.
     */
    public static function padX(): float
    {
        return round(max(self::QR_SIDE, self::QR_TOP), 2);
    }

    /**
     * Plus petit QR qui remplit exactement la hauteur minimale de la zone (20 mm)
     * en gardant le code pass à l'intérieur. Un QR plus petit est agrandi
     * automatiquement à cette taille.
     */
    public static function qrMin(): float
    {
        return round(self::ZONE_MIN - (self::QR_TOP + self::PAX_GAP + self::PAX_LINE_HEIGHT + self::PAX_BOTTOM), 2);
    }

    /**
     * Plus grand QR dont la zone blanche (avec le code pass) tient dans le ticket.
     */
    public static function qrMaxPour(float $slotW, float $slotH): float
    {
        $parHauteur = $slotH - (self::QR_TOP + self::PAX_GAP + self::PAX_LINE_HEIGHT + self::PAX_BOTTOM);
        $parLargeur = $slotW - 2 * self::padX();

        return round(max(2, min($parHauteur, $parLargeur)), 1);
    }

    /**
     * Géométrie (mm, relative au ticket) de la zone blanche et du code pass.
     *
     * La zone entoure le QR avec des marges identiques (0,25 mm) et le code pass
     * est imprimé dedans, sous le QR. La zone est donc plus haute que large.
     * Les positions sont bornées pour que la zone reste dans le ticket.
     *
     * @param  float|null  $qrSize  taille demandée (agrandie au minimum si trop petite)
     * @param  float|null  $qrX  position du QR, ou null pour centrer
     * @param  float|null  $qrY  position du QR, ou null pour centrer
     * @return array<string, float>
     */
    public static function geometry(?float $qrSize, ?float $qrX, ?float $qrY, float $slotW, float $slotH, ?array $format = null): array
    {
        if (! empty($format['zone_fixe'])) {
            return self::geometryZoneFixe($format, $qrX, $qrY, $slotW, $slotH);
        }

        $padX = self::padX();
        $padTop = self::QR_TOP;
        $gap = self::PAX_GAP;

        // QR entièrement personnalisable : on conserve la taille choisie, avec un plancher minimal.
        if ($qrSize === null) {
            $qrSize = self::qrMin();
        }
        $qrSize = round(max((float) $qrSize, 2), 2);
        // La zone blanche (QR + marges + code pass) ne peut pas dépasser le ticket
        $qrSize = min($qrSize, self::qrMaxPour($slotW, $slotH));

        $zoneW = round($qrSize + 2 * $padX, 2);
        $bandH = round(self::PAX_LINE_HEIGHT + self::PAX_BOTTOM, 2);
        $zoneH = round($qrSize + $padTop + $gap + $bandH, 2);

        $zoneX = min(max($qrX - $padX, 0.0), max($slotW - $zoneW, 0.0));
        $zoneY = min(max($qrY - $padTop, 0.0), max($slotH - $zoneH, 0.0));

        return [
            'qrSize' => $qrSize,
            'padX' => $padX,
            'padTop' => $padTop,
            'zoneX' => $zoneX,
            'zoneY' => $zoneY,
            'zoneW' => $zoneW,
            'zoneH' => $zoneH,
            'gap' => $gap,
            // Position relative au haut de la zone : le code est à l'intérieur.
            'bandTop' => round($padTop + $qrSize + $gap, 2),
            'paxBandH' => $bandH,
            'paxLineH' => self::PAX_LINE_HEIGHT,
            'paxFont' => self::PAX_FONT,
            'paxBottom' => self::PAX_BOTTOM,
        ];
    }

    /**
     * Géométrie d'un format à zone QR fixe (bracelet) : la zone standard tournée,
     * QR à gauche et code pass vertical à l'intérieur, à droite du QR.
     */
    private static function geometryZoneFixe(array $format, ?float $qrX, ?float $qrY, float $slotW, float $slotH): array
    {
        $zoneW = (float) $format['zone_fixe']['largeur'];
        $zoneH = (float) $format['zone_fixe']['hauteur'];
        $qrSize = (float) $format['qr_defaut'];
        $padTop = round(($zoneH - $qrSize) / 2, 2);
        $padX = $padTop; // même marge à gauche qu'en haut et en bas

        $codeV = ! empty($format['code_vertical']);
        $encombrement = $zoneW;

        [$defX, $defY] = LotPhysique::qrPositionDefaut($format, $qrSize);
        $zoneX = min(max(($qrX ?? $defX) - $padX, 0.0), max($slotW - $encombrement, 0.0));
        $zoneY = min(max(($qrY ?? $defY) - $padTop, 0.0), max($slotH - $zoneH, 0.0));

        return [
            'qrSize' => $qrSize,
            'padX' => $padX,
            'padTop' => $padTop,
            'zoneX' => round($zoneX, 2),
            'zoneY' => round($zoneY, 2),
            'zoneW' => $zoneW,
            'zoneH' => $zoneH,
            'gap' => 0.0,
            'bandTop' => $zoneH,
            'paxBandH' => 0.0,
            'paxLineH' => self::PAX_LINE_HEIGHT,
            'paxFont' => self::PAX_FONT,
            'paxBottom' => 0.0,
            'codeVertical' => $codeV,
            'codeX' => round($zoneX + $padX + $qrSize + self::CODE_V_ECART, 2),
            'codeY' => round($zoneY, 2),
            'codeW' => self::CODE_V_LARGEUR,
            'codeH' => $zoneH,
        ];
    }

    /**
     * Code pass écrit à la verticale (lu de bas en haut) en PNG transparent :
     * DomPDF gère mal le texte pivoté, on le dessine donc avec GD.
     */
    public static function codeVerticalDataUri(string $code, float $wMm, float $hMm): ?string
    {
        $font = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        if (! function_exists('imagettftext') || ! is_file($font)) {
            return null;
        }

        $dpi = 600;
        $w = (int) round($wMm / 25.4 * $dpi);
        $h = (int) round($hMm / 25.4 * $dpi);

        // Taille de police ajustée pour que le texte tienne dans la bande
        $box = imagettfbbox(100, 0, $font, $code);
        $longueur = abs($box[2] - $box[0]);
        $epaisseur = abs($box[1] - $box[7]);
        $taille = 100 * min(($h * 0.9) / $longueur, ($w * 0.72) / $epaisseur);

        $box = imagettfbbox($taille, 0, $font, $code);
        $longueur = abs($box[2] - $box[0]);
        $epaisseur = abs($box[1] - $box[7]);

        $im = imagecreatetruecolor($w, $h);
        imagesavealpha($im, true);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        // Angle 90 : la ligne de base est verticale, le texte monte de bas en haut
        $x = (int) round(($w + $epaisseur) / 2 - abs($box[1]));
        $y = (int) round(($h + $longueur) / 2);
        imagettftext($im, $taille, 90, $x, $y, imagecolorallocate($im, 0, 0, 0), $font, $code);

        ob_start();
        imagepng($im);
        $png = ob_get_clean();

        return 'data:image/png;base64,'.base64_encode($png);
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
        // L'orientation de la page peut différer de celle du ticket : un ticket
        // A5 paysage se pose sur une page A4 portrait (2 tickets empilés).
        $ticketOrientation = $format['orientation'] ?? 'landscape';
        $orientation = $format['page_orientation'] ?? $ticketOrientation;

        $pageW = $orientation === 'landscape' ? 297 : 210;
        $pageH = $orientation === 'landscape' ? 210 : 297;

        // Le slot peut être plus large que le visuel (bracelet : bande blanche de pliage à droite)
        $slotW = $format['largeur_totale'] ?? $format['largeur'];
        $slotH = $format['hauteur'];
        $cols = $format['colonnes'];
        $rows = $format['lignes'];

        $blockW = $cols * $slotW + ($cols - 1) * self::GOUTTIERE;
        $blockH = $rows * $slotH + ($rows - 1) * self::GOUTTIERE;
        $startX = ($format['alignement'] ?? null) === 'gauche' ? self::MARGE : ($pageW - $blockW) / 2;
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
            'orientation' => $orientation, // orientation de la page (pour le PDF)
            'ticket_orientation' => $ticketOrientation,
            'page_largeur' => $pageW,
            'page_hauteur' => $pageH,
            'slot_largeur' => $slotW,
            'visuel_largeur' => $format['largeur'], // partie imprimée (image + QR)
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
            // Étendue des lignes de coupe horizontales : le bloc, ou toute la largeur de la feuille
            'coupe_gauche' => ! empty($format['coupe_pleine_largeur']) ? 0 : round($startX, 2),
            'coupe_largeur' => ! empty($format['coupe_pleine_largeur']) ? $pageW : round($blockW, 2),
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
            $t->id => QrCodeService::generateDataUri($t->code_unique, 300, 'H'),
        ]);

        $templateUrl = self::templateToDataUri($lot);
        [$templateW, $templateH] = self::templateSize($lot);
        $zoom = self::zoomEffectif($lot);

        $qrSize = $lot->qr_size ?? $format['qr_defaut'];
        [$defX, $defY] = LotPhysique::qrPositionDefaut($format, (float) $qrSize);
        $qrX = $lot->qr_x ?? $defX;
        $qrY = $lot->qr_y ?? $defY;
        $geo = self::geometry($qrSize, $qrX, $qrY, $layout['visuel_largeur'], $layout['slot_hauteur'], $format);

        // Bracelet : code pass vertical dessiné en image pour chaque ticket
        $codesVerticaux = ! empty($geo['codeVertical'])
            ? $tickets->mapWithKeys(fn (Ticket $t) => [$t->id => self::codeVerticalDataUri($t->code_unique, $geo['codeW'], $geo['codeH'])])
            : collect();

        // Favicon PaxEvent incrusté au centre du QR (même principe que le ticket en ligne).
        $faviconDataUri = TicketPdfService::faviconDataUri();
        $faviconSize = round($geo['qrSize'] * 0.20, 2);

        // Image : taille = slot × zoom, centrée, recadrée par le débordement caché
        $imgW = $layout['visuel_largeur'] * $zoom / 100;
        $imgH = $templateW > 0 ? $imgW * $templateH / $templateW : $layout['slot_hauteur'] * $zoom / 100;
        $imgLeft = ($layout['visuel_largeur'] - $imgW) / 2;
        $imgTop = ($layout['slot_hauteur'] - $imgH) / 2;

        $pageLargeur = $layout['page_largeur'];
        $pageHauteur = $layout['page_hauteur'];

        // Signature PaxEvent dans la marge : texte plus petit si la marge basse est réduite
        $signBottom = $layout['marge_bas'] >= 8 ? 2.0 : 0.5;
        $signFont = $layout['marge_bas'] >= 8 ? 9 : 6.5;

        // La résolution des images de fond DomPDF dépend uniquement de l'option `dpi`
        // (voir config/dompdf.php). À 96 dpi l'image est sous-échantillonnée et pixelise
        // à l'impression : on rend à 300 dpi. Les tailles en px des textes deviennent
        // alors trop petites, on les exprime donc en points (1 px = 0,75 pt).
        $paxFontPt = round(self::PAX_FONT * 0.75, 3);
        $signFontPt = round($signFont * 0.75, 3);

        $pages = $tickets->chunk($layout['par_page'])->values();

        $pdf = Pdf::loadView('tickets.pdf.template', array_merge(compact(
            'lot', 'pages', 'qrs', 'templateUrl',
            'layout', 'pageLargeur', 'pageHauteur', 'format',
            'signBottom', 'signFont', 'signFontPt', 'paxFontPt', 'zoom',
            'imgW', 'imgH', 'imgLeft', 'imgTop',
            'faviconDataUri', 'faviconSize', 'codesVerticaux'
        ), $geo));
        $pdf->setPaper('a4', $layout['orientation']);
        $pdf->setOption('dpi', 300);
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

        $qrDataUri = QrCodeService::generateDataUri($ticket->code_unique, 300, 'H');
        $templateUrl = self::templateToDataUri($lot);
        [$templateW, $templateH] = self::templateSize($lot);
        $zoom = self::zoomEffectif($lot);

        $format = self::formatDetails($lot);
        // Ticket complet (bracelet : visuel + bande blanche de pliage) et partie visuelle
        $slotW = $format['largeur_totale'] ?? $format['largeur'];
        $visuelW = $format['largeur'];
        $slotH = $format['hauteur'];

        // Image : taille = visuel × zoom, centrée sur le visuel, recadrée par le débordement caché
        $imgW = $visuelW * $zoom / 100;
        $imgH = $templateW > 0 ? $imgW * $templateH / $templateW : $slotH * $zoom / 100;
        $imgLeft = ($visuelW - $imgW) / 2;
        $imgTop = ($slotH - $imgH) / 2;

        $qrSize = $lot->qr_size ?? $format['qr_defaut'];
        [$defX, $defY] = LotPhysique::qrPositionDefaut($format, (float) $qrSize);
        $qrX = $lot->qr_x ?? $defX;
        $qrY = $lot->qr_y ?? $defY;
        $geo = self::geometry($qrSize, $qrX, $qrY, $visuelW, $slotH, $format);
        $codeVerticalUri = ! empty($geo['codeVertical'])
            ? self::codeVerticalDataUri($ticket->code_unique, $geo['codeW'], $geo['codeH'])
            : null;

        $faviconDataUri = TicketPdfService::faviconDataUri();
        $faviconSize = round($geo['qrSize'] * 0.20, 2);

        $html = view('tickets.pdf.ticket-preview', array_merge(compact(
            'templateUrl', 'qrDataUri', 'slotW', 'visuelW', 'slotH', 'zoom',
            'imgW', 'imgH', 'imgLeft', 'imgTop',
            'faviconDataUri', 'faviconSize', 'codeVerticalUri'
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
        // volée à ~300 dpi pour l'impression d'un ticket (max 2400 px de côté).
        $maxSide = 2400;
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