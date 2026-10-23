<?php

namespace Tests\Unit;

use App\Models\LotPhysique;
use App\Services\LotPhysiqueTemplatePdfService as Pdf;
use PHPUnit\Framework\TestCase;

/**
 * Verrouille le flux d'integration des templates de tickets physiques :
 *  - chaque format predefini doit tenir entierement sur sa page A4 ;
 *  - le format A5 doit tenir 2 tickets sur un A4 portrait, avec une ligne de
 *    decoupe horizontale et le texte de signature en bas de page ;
 *  - le format personnalise doit reprendre les dimensions de l'image ;
 *  - la taille du QR code choisie par l'utilisateur doit etre conservee.
 */
class LotTemplateIntegrationTest extends TestCase
{
    private const MARGE = 4.0;

    private function formats(): array
    {
        return LotPhysique::FORMATS;
    }

    private function tousLesFormats(): array
    {
        $formats = $this->formats();
        $formats[LotPhysique::FORMAT_CUSTOM] = LotPhysique::formatPersonnalise(150, 100);

        return $formats;
    }

    // --- 1. A5 : definition et placement -------------------------------------

    public function test_a5_existe_et_est_un_ticket_paysage_sur_page_portrait(): void
    {
        $this->assertArrayHasKey('a5', $this->formats());

        $a5 = $this->formats()['a5'];

        $this->assertSame('landscape', $a5['orientation'], 'Le ticket A5 est en paysage.');
        $this->assertSame('portrait', $a5['page_orientation'], 'La page A4 est en portrait.');
        $this->assertSame(1, $a5['colonnes']);
        $this->assertSame(2, $a5['lignes'], 'Deux tickets A5 par page A4.');

        // Le slot doit rester proche du ratio A5 paysage (210 / 148 = 1,419).
        $ratio = $a5['largeur'] / $a5['hauteur'];
        $this->assertEqualsWithDelta(210 / 148, $ratio, 0.02, 'Ratio du slot proche de l A5 paysage.');
    }

    public function test_a5_place_deux_tickets_sur_un_a4_sans_debordement(): void
    {
        $layout = Pdf::layoutPage($this->formats()['a5']);

        $this->assertSame('portrait', $layout['orientation'], 'Page A4 portrait.');
        $this->assertEquals(210, $layout['page_largeur']);
        $this->assertEquals(297, $layout['page_hauteur']);
        $this->assertSame(2, $layout['par_page']);
        $this->assertCount(2, $layout['positions']);

        foreach ($layout['positions'] as $pos) {
            $this->assertGreaterThanOrEqual(0, $pos['x']);
            $this->assertGreaterThanOrEqual(0, $pos['y']);
            $this->assertLessThanOrEqual(
                $layout['page_largeur'],
                $pos['x'] + $layout['slot_largeur'],
                'Le ticket ne depasse pas a droite.'
            );
            $this->assertLessThanOrEqual(
                $layout['page_hauteur'],
                $pos['y'] + $layout['slot_hauteur'],
                'Le ticket ne depasse pas en bas.'
            );
        }

        // Les deux tickets sont empiles : meme X, Y differents.
        [$haut, $bas] = $layout['positions'];
        $this->assertEquals($haut['x'], $bas['x']);
        $this->assertLessThan($bas['y'], $haut['y']);
    }

    public function test_a5_a_une_ligne_de_decoupe_horizontale_et_une_marge_basse(): void
    {
        $layout = Pdf::layoutPage($this->formats()['a5']);

        $this->assertCount(1, $layout['coupes_h'], 'Une seule ligne de decoupe horizontale.');
        $this->assertCount(0, $layout['coupes_v'], 'Aucune decoupe verticale.');

        // La decoupe tombe entre les deux tickets.
        [$haut, $bas] = $layout['positions'];
        $coupe = $layout['coupes_h'][0];
        $this->assertGreaterThan($haut['y'] + $layout['slot_hauteur'], $coupe);
        $this->assertLessThan($bas['y'], $coupe);

        // Le texte de signature s'imprime dans la marge basse : elle doit exister.
        $this->assertGreaterThanOrEqual(self::MARGE - 1, $layout['marge_bas']);

        // Le bloc peut etre centre : marges gauche/droite symetriques.
        $droite = $layout['page_largeur'] - ($layout['bloc_gauche'] + $layout['bloc_largeur']);
        $this->assertEqualsWithDelta($layout['bloc_gauche'], $droite, 0.01, 'Bloc centre horizontalement.');
    }

    // --- 2. Tous les formats tiennent sur la page ----------------------------

    public function test_tous_les_formats_tiennent_entierement_sur_la_page(): void
    {
        foreach ($this->tousLesFormats() as $key => $format) {
            $layout = Pdf::layoutPage($format);

            $this->assertGreaterThan(0, $layout['par_page'], "Le format {$key} doit avoir au moins un ticket par page.");

            foreach ($layout['positions'] as $i => $pos) {
                $this->assertGreaterThanOrEqual(0, $pos['x'], "{$key} ticket {$i} : X negatif.");
                $this->assertGreaterThanOrEqual(0, $pos['y'], "{$key} ticket {$i} : Y negatif.");
                $this->assertLessThanOrEqual(
                    $layout['page_largeur'] + 0.01,
                    $pos['x'] + $layout['slot_largeur'],
                    "{$key} ticket {$i} : debordement a droite."
                );
                $this->assertLessThanOrEqual(
                    $layout['page_hauteur'] + 0.01,
                    $pos['y'] + $layout['slot_hauteur'],
                    "{$key} ticket {$i} : debordement en bas."
                );
            }
        }
    }

    public function test_les_formats_predefinis_conservent_leur_taille_exacte(): void
    {
        // Contrairement au format personnalise, les formats predefinis ne sont
        // jamais redimensionnes depuis l'image : ce sont les dimensions du modele.
        foreach ($this->formats() as $key => $def) {
            $lot = new LotPhysique(['format' => $key]);
            $details = $lot->formatDetails();

            $this->assertSame($def['largeur'], $details['largeur'], "{$key} : largeur non modifiee.");
            $this->assertSame($def['hauteur'], $details['hauteur'], "{$key} : hauteur non modifiee.");
            $this->assertSame($def['colonnes'], $details['colonnes'], "{$key} : colonnes non modifiees.");
            $this->assertSame($def['lignes'], $details['lignes'], "{$key} : lignes non modifiees.");
        }
    }

    // --- 3. Format personnalise : dimensions issues de l'image ---------------

    public function test_format_personnalise_reprend_les_dimensions_de_image(): void
    {
        // Image FITAB : 2480 x 1748 px a 96 dpi = 656 x 462 mm (ratio 1,42).
        $lot = new LotPhysique([
            'format' => LotPhysique::FORMAT_CUSTOM,
            'largeur_personnalisee' => 656,
            'hauteur_personnalisee' => 462,
        ]);

        $details = $lot->formatDetails();

        $this->assertSame(656.0, (float) $details['largeur']);
        $this->assertSame(462.0, (float) $details['hauteur']);
        $this->assertSame('landscape', $details['orientation']);
        $this->assertSame('landscape', $details['page_orientation']);
    }

    public function test_format_personnalise_ne_borne_plus_la_taille(): void
    {
        // Aucune limite arbitraire haute : une grande image doit passer.
        $this->assertNotNull(LotPhysique::formatPersonnalise(1000, 800));
        $this->assertNotNull(LotPhysique::formatPersonnalise(9000, 500));

        // Une dimension nulle ou negative reste refusee.
        $this->assertNull(LotPhysique::formatPersonnalise(0, 100));
        $this->assertNull(LotPhysique::formatPersonnalise(100, 0));
    }

    public function test_format_personnalise_portrait_sur_page_portrait(): void
    {
        $details = LotPhysique::formatPersonnalise(148, 210);

        $this->assertSame('portrait', $details['orientation']);
        $this->assertSame('portrait', $details['page_orientation']);

        $layout = Pdf::layoutPage($details);
        $this->assertSame(210, $layout['page_largeur']);
        $this->assertSame(297, $layout['page_hauteur']);

        foreach ($layout['positions'] as $pos) {
            $this->assertLessThanOrEqual($layout['page_largeur'] + 0.01, $pos['x'] + $layout['slot_largeur']);
        }
    }

    // --- 4. QR code : taille choisie conservee et position valide ------------

    public function test_la_taille_du_qr_choisie_est_conservee(): void
    {
        // Plus d'agrandissement force : la valeur demandee est respectee.
        foreach ([5.0, 12.5, 30.0, 50.0, 90.0] as $taille) {
            $geo = Pdf::geometry($taille, null, null, 202, 143);

            $this->assertEqualsWithDelta($taille, $geo['qrSize'], 0.001, "QR de {$taille} mm conserve.");
        }
    }

    public function test_le_qr_et_sa_zone_restent_dans_le_ticket_a5(): void
    {
        $layout = Pdf::layoutPage($this->formats()['a5']);
        $slotW = $layout['slot_largeur'];
        $slotH = $layout['slot_hauteur'];

        foreach ([5.0, 20.0, 50.0, 90.0] as $taille) {
            // Position collee en bas a droite : la zone doit etre ramenee dans le ticket.
            $geo = Pdf::geometry($taille, $slotW, $slotH, $slotW, $slotH);

            $this->assertGreaterThanOrEqual(0, $geo['zoneX']);
            $this->assertGreaterThanOrEqual(0, $geo['zoneY']);
            $this->assertLessThanOrEqual($slotW + 0.01, $geo['zoneX'] + $geo['zoneW'], "QR {$taille} : zone hors ticket a droite.");
            $this->assertLessThanOrEqual($slotH + 0.01, $geo['zoneY'] + $geo['zoneH'], "QR {$taille} : zone hors ticket en bas.");
            $this->assertLessThanOrEqual($slotH + 0.01, $geo['bandTop'] + $geo['paxBandH'], "QR {$taille} : code pass hors ticket.");
        }
    }

    public function test_le_qr_par_defaut_de_chaque_format_tient_dans_le_slot(): void
    {
        foreach ($this->tousLesFormats() as $key => $format) {
            $layout = Pdf::layoutPage($format);
            $qr = $format['qr_defaut'];

            $geo = Pdf::geometry($qr, ($layout['slot_largeur'] - $qr) / 2, ($layout['slot_hauteur'] - $qr) / 2, $layout['slot_largeur'], $layout['slot_hauteur']);

            $this->assertLessThanOrEqual(
                $layout['slot_largeur'] + 0.01,
                $geo['zoneW'],
                "{$key} : la zone QR est plus large que le ticket."
            );
            $this->assertLessThanOrEqual(
                $layout['slot_hauteur'] + 0.01,
                $geo['zoneH'],
                "{$key} : la zone QR est plus haute que le ticket."
            );
        }
    }

    // --- 5. Coherence JS (vues Blade) <-> PHP --------------------------------

    public function test_le_calcul_js_des_formats_est_coherent_avec_php(): void
    {
        // Le JS des vues utilise FORMATS tel quel : les cles attendues doivent exister.
        foreach ($this->formats() as $key => $def) {
            foreach (['label', 'largeur', 'hauteur', 'orientation', 'colonnes', 'lignes', 'qr_defaut'] as $champ) {
                $this->assertArrayHasKey($champ, $def, "{$key} : champ {$champ} manquant pour le JS.");
            }
        }

        // Le format personnalise calcule en JS doit produire les memes valeurs.
        $w = 656;
        $h = 462;
        $marge = 4;
        $gouttiere = 2;
        $orientation = $w >= $h ? 'landscape' : 'portrait';
        $pageW = $orientation === 'landscape' ? 297 : 210;
        $pageH = $orientation === 'landscape' ? 210 : 297;
        $cols = max(1, (int) floor(($pageW - 2 * $marge + $gouttiere) / ($w + $gouttiere)));
        $rows = max(1, (int) floor(($pageH - 2 * $marge + $gouttiere) / ($h + $gouttiere)));

        $php = LotPhysique::formatPersonnalise($w, $h);

        $this->assertSame($cols, $php['colonnes']);
        $this->assertSame($rows, $php['lignes']);
        $this->assertSame($orientation, $php['orientation']);
    }
}
