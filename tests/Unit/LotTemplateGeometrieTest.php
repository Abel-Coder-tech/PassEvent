<?php

namespace Tests\Unit;

use App\Services\LotPhysiqueTemplatePdfService as S;
use PHPUnit\Framework\TestCase;

// Regression : la zone blanche autour du QR est un carre aux marges identiques
// (haut = gauche = droite), et le code pass est imprime sous le carre, jamais
// dedans. Avant, la zone etait plus haute que large et le code pass etait
// dans le carre, ce qui laissait 2 mm de blanc de chaque cote du QR.
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

    public function test_zone_carree(): void
    {
        foreach ([19.5, 20.0, 30.0, 40.0, 60.0, 80.0] as $qr) {
            $geo = S::geometry($qr, null, null, self::SLOT_W, self::SLOT_H);

            $this->assertEqualsWithDelta($geo['zoneW'], $geo['zoneH'], 0.005, "Zone carree pour un QR de {$qr} mm.");
        }
    }

    public function test_code_pass_imprime_sous_le_carre(): void
    {
        $geo = S::geometry(30.0, 54.0, 10.0, self::SLOT_W, self::SLOT_H);

        // Le bandeau du code commence exactement sous le carre, pas dedans.
        $this->assertEqualsWithDelta($geo['zoneY'] + $geo['zoneH'], $geo['bandTop'], 0.005);

        // Hauteur du bandeau : ecart + ligne + marge basse.
        $this->assertEqualsWithDelta(
            S::PAX_GAP + S::PAX_LINE_HEIGHT + S::PAX_BOTTOM,
            $geo['paxBandH'],
            0.005
        );
    }

    public function test_qr_minimum_remplit_le_carre_de_2_cm(): void
    {
        $this->assertEqualsWithDelta(S::ZONE_MIN, S::qrMin() + 2 * S::padX(), 0.005);

        // Un QR trop petit est agrandi pour remplir le carre de 2 cm.
        $geo = S::geometry(10.0, null, null, self::SLOT_W, self::SLOT_H);
        $this->assertEquals(S::qrMin(), $geo['qrSize']);
        $this->assertEquals(S::ZONE_MIN, $geo['zoneW']);

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

    public function test_carre_et_code_pass_restent_dans_le_ticket(): void
    {
        // QR colle en bas a droite : le groupe est remonte pour rester entier.
        $geo = S::geometry(30.0, self::SLOT_W, self::SLOT_H, self::SLOT_W, self::SLOT_H);

        $this->assertLessThanOrEqual(self::SLOT_W, $geo['zoneX'] + $geo['zoneW']);
        $this->assertLessThanOrEqual(self::SLOT_H, $geo['bandTop'] + $geo['paxBandH']);

        // QR colle en haut a gauche : aucune marge forcee.
        $geo = S::geometry(30.0, 0.0, 0.0, self::SLOT_W, self::SLOT_H);
        $this->assertEquals(0.0, $geo['zoneX']);
        $this->assertEquals(0.0, $geo['zoneY']);
    }
}
