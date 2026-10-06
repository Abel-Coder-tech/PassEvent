<?php

namespace Tests\Unit;

use App\Services\LotPhysiqueTemplatePdfService as S;
use PHPUnit\Framework\TestCase;

/**
 * Regression : la zone blanche entoure le QR avec des marges identiques
 * (haut = gauche = droite) et le code pass est imprime DANS la zone, sous le
 * QR. La zone est donc plus haute que large.
 */
class LotTemplateGeometrieTest extends TestCase
{
    private const SLOT_W = 137.0; // format s1 : 137 x 49
    private const SLOT_H = 49.0;

    public function test_marges_laterales_identiques_a_la_marge_du_haut(): void
    {
        $this->assertEquals(S::QR_TOP, S::padX(), 'La marge gauche/droite vaut la marge du haut.');

        $qrX = 54.0;
        $geo = S::geometry(30.0, $qrX, 10.0, self::SLOT_W, self::SLOT_H);

        $this->assertEqualsWithDelta($geo['padX'], $qrX - $geo['zoneX'], 0.005, 'Marge gauche.');
        $this->assertEqualsWithDelta(
            $geo['padX'],
            ($geo['zoneX'] + $geo['zoneW']) - ($qrX + $geo['qrSize']),
            0.005,
            'Marge droite.'
        );

        // Le QR garde exactement la position demandee.
        $this->assertEqualsWithDelta($qrX, $geo['zoneX'] + $geo['padX'], 0.005);
        $this->assertEqualsWithDelta(10.0, $geo['zoneY'] + $geo['padTop'], 0.005);
    }

    public function test_zone_plus_haute_que_large(): void
    {
        foreach ([20.0, 30.0, 40.0, 60.0, 80.0] as $qr) {
            $geo = S::geometry($qr, null, null, self::SLOT_W, self::SLOT_H);

            $this->assertEqualsWithDelta(
                $geo['qrSize'] + $geo['padTop'] + $geo['gap'] + $geo['paxBandH'],
                $geo['zoneH'],
                0.005,
                "Hauteur de zone pour un QR de {$qr} mm."
            );
            $this->assertGreaterThan($geo['zoneW'], $geo['zoneH'], "Zone plus haute que large pour {$qr} mm.");
        }
    }

    public function test_code_pass_imprime_dans_la_zone(): void
    {
        $geo = S::geometry(30.0, 54.0, 10.0, self::SLOT_W, self::SLOT_H);

        // Le bandeau du code commence dans la zone, 0,1 mm sous le QR.
        $this->assertEqualsWithDelta(
            $geo['padTop'] + $geo['qrSize'] + $geo['gap'],
            $geo['bandTop'],
            0.005,
            'Le code commence dans la zone, pas sous elle.'
        );

        // Hauteur du bandeau : hauteur de ligne + marge basse (l'ecart est deja
        // dans bandTop).
        $this->assertEqualsWithDelta(S::PAX_LINE_HEIGHT + S::PAX_BOTTOM, $geo['paxBandH'], 0.005);

        // Le bas du code + sa marge reste dans la zone (+ tolerance flottante).
        $this->assertLessThanOrEqual($geo['zoneH'] + 0.005, $geo['bandTop'] + $geo['paxBandH']);
    }

    public function test_qr_minimum_pour_une_zone_de_2_cm_de_haut(): void
    {
        $this->assertEqualsWithDelta(
            S::ZONE_MIN,
            S::qrMin() + S::QR_TOP + S::PAX_GAP + S::PAX_LINE_HEIGHT + S::PAX_BOTTOM,
            0.005
        );

        // Un QR trop petit est agrandi pour que la zone fasse 2 cm de haut.
        $geo = S::geometry(10.0, null, null, self::SLOT_W, self::SLOT_H);
        $this->assertEquals(S::qrMin(), $geo['qrSize']);
        $this->assertEquals(S::ZONE_MIN, $geo['zoneH']);

        // Au-dessus du minimum, la taille demandee est conservee.
        $this->assertEquals(30.0, S::geometry(30.0, null, null, self::SLOT_W, self::SLOT_H)['qrSize']);
    }

    public function test_texte_du_code_pass_en_3_mm(): void
    {
        // 11,34 px a 96 dpi = 3 mm a l'impression.
        $this->assertEqualsWithDelta(3.0, S::PAX_FONT / 96 * 25.4, 0.01);

        // La hauteur de ligne doit rester superieure a la taille du texte.
        $this->assertGreaterThan(S::PAX_FONT / 96 * 25.4, S::PAX_LINE_HEIGHT);
    }

    public function test_zone_et_code_pass_restent_dans_le_ticket(): void
    {
        // QR colle en bas a droite : la zone est remontee pour rester entiere.
        $geo = S::geometry(30.0, self::SLOT_W, self::SLOT_H, self::SLOT_W, self::SLOT_H);

        $this->assertLessThanOrEqual(self::SLOT_W, $geo['zoneX'] + $geo['zoneW']);
        $this->assertLessThanOrEqual(self::SLOT_H, $geo['zoneY'] + $geo['zoneH']);
        $this->assertLessThanOrEqual(self::SLOT_H, $geo['bandTop'] + $geo['paxBandH']);

        // QR colle en haut a gauche : aucune marge forcee.
        $geo = S::geometry(30.0, 0.0, 0.0, self::SLOT_W, self::SLOT_H);
        $this->assertEquals(0.0, $geo['zoneX']);
        $this->assertEquals(0.0, $geo['zoneY']);
    }

    public function test_geometrie_alignee_avec_les_editeurs_web(): void
    {
        // Meme calcul que zoneDims() dans les vues Blade admin et superadmin.
        foreach ([15.0, 30.0, 48.0] as $qr) {
            $geo = S::geometry($qr, null, null, self::SLOT_W, self::SLOT_H);

            $padX = max(S::QR_SIDE, S::QR_TOP);
            $w = round($qr + $padX * 2, 2);
            $bandH = round(S::PAX_LINE_HEIGHT + S::PAX_BOTTOM, 2);
            $h = max(round($qr + S::QR_TOP + S::PAX_GAP + $bandH, 2), S::ZONE_MIN);

            $this->assertEqualsWithDelta($w, $geo['zoneW'], 0.005, "Largeur JS pour {$qr} mm.");
            $this->assertEqualsWithDelta($h, $geo['zoneH'], 0.005, "Hauteur JS pour {$qr} mm.");
            $this->assertEqualsWithDelta(
                round(S::QR_TOP + $qr + S::PAX_GAP, 2),
                $geo['bandTop'],
                0.005,
                "Position du code JS pour {$qr} mm."
            );
        }
    }
}
