<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class IdorSessionLogiqueTest extends TestCase
{
    // Reproduit la logique de PaiementController::verifierAccesAcheteur
    private function accesAutorise(int $ticketId, array $autorises): bool
    {
        return in_array($ticketId, $autorises, true);
    }

    public function test_acces_refuse_quand_aucun_ticket_en_session(): void
    {
        $this->assertFalse($this->accesAutorise(5, []));
    }

    public function test_acces_autorise_quand_le_ticket_est_en_session(): void
    {
        $this->assertTrue($this->accesAutorise(5, [5]));
    }

    public function test_acces_refuse_pour_un_autre_ticket_en_session(): void
    {
        $this->assertFalse($this->accesAutorise(6, [5]));
    }

    public function test_acces_refuse_avec_type_strict(): void
    {
        // "5" (string) ne doit pas matcher 5 (int) à cause de in_array strict
        $this->assertFalse($this->accesAutorise(5, ['5']));
    }

    public function test_achat_accumule_les_tickets_sans_doublons(): void
    {
        $sessionTickets = [];
        $nouveaux = [3, 4, 4, 5];

        $sessionTickets = array_values(array_unique(array_merge($sessionTickets, $nouveaux)));

        $this->assertEquals([3, 4, 5], $sessionTickets);
    }
}
