<?php

namespace Tests\Feature;

use App\Models\Consentement;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ConsentementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::getConnection()->getDriverName() === 'sqlite' && ! Schema::hasTable('consentements')) {
            Schema::create('consentements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('session_id', 100)->nullable();
                $table->string('statut', 20)->default('accepte');
                $table->text('services')->nullable();
                $table->string('version_politique', 50)->nullable();
                $table->string('ip_visiteur', 45)->nullable();
                $table->timestamps();
            });
        }
    }

    protected function tearDown(): void
    {
        Consentement::where('version_politique', 'test')->delete();

        parent::tearDown();
    }

    public function test_consentement_accepte_est_enregistre(): void
    {
        $response = $this->postJson('/consentement', [
            'statut' => 'accepte',
            'services' => ['googletagmanager'],
            'version' => 'test',
        ]);

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertTrue(Consentement::where('version_politique', 'test')->where('statut', 'accepte')->exists());
    }

    public function test_consentement_statut_invalide_est_rejete(): void
    {
        $this->postJson('/consentement', ['statut' => 'peut_etre', 'services' => [], 'version' => 'test'])
            ->assertStatus(422);
    }

    public function test_consentement_refuse_est_enregistre(): void
    {
        $response = $this->postJson('/consentement', [
            'statut' => 'refuse',
            'services' => [],
            'version' => 'test',
        ]);

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertTrue(Consentement::where('version_politique', 'test')->where('statut', 'refuse')->exists());
    }
}