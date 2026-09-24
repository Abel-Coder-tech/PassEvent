<?php

namespace Tests\Unit;

use App\Services\ReconciliationService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReconciliationStatutTest extends TestCase
{
    private ReconciliationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(ReconciliationService::class);
    }

    public function test_est_statut_echec(): void
    {
        foreach (['declined', 'canceled', 'cancelled', 'cancel', 'expired'] as $statut) {
            $this->assertTrue($this->service->estStatutEchec($statut), "{$statut} doit être un échec");
        }

        $this->assertFalse($this->service->estStatutEchec('pending'));
        $this->assertFalse($this->service->estStatutEchec('approved'));
        $this->assertFalse($this->service->estStatutEchec(null));
    }

    public function test_pending_abandonne_apres_expiration_de_la_reservation(): void
    {
        $depasse = Carbon::now()->subMinutes(1);

        $this->assertTrue($this->service->estPendingAbandonne('pending', $depasse));
        $this->assertFalse($this->service->estPendingAbandonne('approved', $depasse));
        $this->assertFalse($this->service->estPendingAbandonne('expired', $depasse));
        $this->assertFalse($this->service->estPendingAbandonne('pending', null));
    }
}