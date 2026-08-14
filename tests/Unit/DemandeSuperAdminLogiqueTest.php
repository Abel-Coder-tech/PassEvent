<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\DemandeSuperAdminController;
use PHPUnit\Framework\TestCase;

class DemandeSuperAdminLogiqueTest extends TestCase
{
    public function test_sans_tarifs_le_message_est_inchange(): void
    {
        $resultat = DemandeSuperAdminController::formaterMessage(
            'Ticket physique (QR Code)',
            'Bonjour, j\'aimerais commander des tickets physiques.',
            null,
            ['quantites' => [1 => 5]],
        );

        $this->assertStringContainsString('Bonjour, j\'aimerais commander des tickets physiques.', $resultat);
        $this->assertStringNotContainsString('Quantités demandées', $resultat);
    }

    public function test_quantites_par_tarif_prefixent_le_message(): void
    {
        $resultat = DemandeSuperAdminController::formaterMessage(
            'Ticket physique (QR Code)',
            'Pour mon guichet.',
            ['1' => 'Plein tarif', '2' => 'Tarif réduit'],
            ['quantites' => [1 => 5, 2 => 3, 99 => 2]],
        );

        $this->assertStringContainsString('• Plein tarif : 5 ticket(s)', $resultat);
        $this->assertStringContainsString('• Tarif réduit : 3 ticket(s)', $resultat);
        // Un tarif inconnu (99) est ignoré
        $this->assertStringNotContainsString('99', $resultat);
        $this->assertStringContainsString('Pour mon guichet.', $resultat);
    }

    public function test_quantites_zero_ou_vides_ignorees(): void
    {
        $resultat = DemandeSuperAdminController::formaterMessage(
            'Ticket physique (QR Code)',
            'Message libre.',
            ['1' => 'Plein tarif'],
            ['quantites' => [1 => 0, 2 => null]],
        );

        $this->assertStringNotContainsString('Quantités demandées', $resultat);
        $this->assertSame('Message libre.', $resultat);
    }

    public function test_commission_demandee_prefixe_le_message(): void
    {
        $resultat = DemandeSuperAdminController::formaterMessage(
            'Réduction Commission',
            'Merci d\'étudier ma demande.',
            null,
            ['commission_pourcentage' => 5],
        );

        $this->assertStringContainsString('Commission demandée : 5 %', $resultat);
        $this->assertStringContainsString('Merci d\'étudier ma demande.', $resultat);
    }

    public function test_commission_vide_n_ajoute_pas_de_prefixe(): void
    {
        $resultat = DemandeSuperAdminController::formaterMessage(
            'Réduction Commission',
            'Merci d\'étudier ma demande.',
            null,
            ['commission_pourcentage' => null],
        );

        $this->assertSame('Merci d\'étudier ma demande.', $resultat);
    }

    public function test_objet_non_pertinent_sans_prefixe(): void
    {
        $resultat = DemandeSuperAdminController::formaterMessage(
            'Problème technique',
            'La page ne charge pas.',
            null,
            ['quantites' => [1 => 5], 'commission_pourcentage' => 3],
        );

        $this->assertSame('La page ne charge pas.', $resultat);
    }
}
