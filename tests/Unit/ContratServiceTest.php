<?php

namespace Tests\Unit;

use App\Models\Evenement;
use App\Models\User;
use App\Services\ContratService;
use Tests\TestCase;

class ContratServiceTest extends TestCase
{
    public function test_contract_renders_the_current_dashboard_and_physical_commission_rates(): void
    {
        $user = new User([
            'nom' => 'Doe',
            'prenom' => 'John',
            'email' => 'john@example.com',
            'commission_pourcentage' => 7.5,
            'commission_physique' => 8.5,
        ]);

        $data = app(ContratService::class)->viewData($user);

        $this->assertSame(7.5, $data['commission_eticket']);
        $this->assertSame(8.5, $data['commission_physique']);

        $html = app(ContratService::class)->render($user);

        $this->assertStringContainsString('7,5 %', $html);
        $this->assertStringContainsString('8,5 %', $html);
        $this->assertStringNotContainsString('5% à 10%', $html);
    }

    public function test_contract_reflects_a_changed_physical_commission_rate(): void
    {
        $user = new User([
            'nom' => 'Doe',
            'prenom' => 'John',
            'email' => 'john@example.com',
            'commission_pourcentage' => 10,
            'commission_physique' => 6.5,
        ]);

        $html = app(ContratService::class)->render($user);

        $this->assertStringContainsString('6,5 %', $html);
        $this->assertStringNotContainsString('5% à 10%', $html);
    }

    public function test_contract_uses_default_commission_when_no_dashboard_rate_is_set(): void
    {
        $user = new User([
            'nom' => 'Doe',
            'prenom' => 'John',
            'email' => 'john@example.com',
            'commission_physique' => 5,
        ]);

        $data = app(ContratService::class)->viewData($user);

        $this->assertSame(10.0, $data['commission_eticket']);
    }

    public function test_event_specific_commission_does_not_change_the_contract_rate(): void
    {
        $user = new User([
            'nom' => 'Doe',
            'prenom' => 'John',
            'email' => 'john@example.com',
            'commission_pourcentage' => 8,
            'commission_physique' => 5,
        ]);

        $evenement = new Evenement([
            'titre' => 'Événement avec taux spécifique',
            'commission_pourcentage' => 5,
        ]);
        $user->setRelation('evenements', collect([$evenement]));

        $data = app(ContratService::class)->viewData($user);

        $this->assertSame(8.0, $data['commission_eticket']);
        $this->assertSame(8.0, $user->commissionPourcentage());
    }
}
