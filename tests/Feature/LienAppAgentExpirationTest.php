<?php

namespace Tests\Feature;

use App\Http\Controllers\AppReleaseController;
use Tests\TestCase;

// Lien « Télécharger PaxAgent » des emails agents : jusqu'à la fin de l'événement, 7 jours minimum
class LienAppAgentExpirationTest extends TestCase
{
    public function test_agent_designe_plus_d_un_mois_avant_le_lien_dure_jusqu_a_la_fin_de_l_evenement(): void
    {
        $fin = now()->addDays(40)->setTime(21, 0);

        $this->assertTrue(AppReleaseController::expirationLienAgent($fin)->equalTo($fin));
    }

    public function test_evenement_proche_ou_inconnu_le_lien_dure_au_moins_7_jours(): void
    {
        $this->assertTrue(AppReleaseController::expirationLienAgent(now()->addDays(2))->gte(now()->addDays(7)->subMinute()));
        $this->assertTrue(AppReleaseController::expirationLienAgent(null)->gte(now()->addDays(7)->subMinute()));
    }
}
