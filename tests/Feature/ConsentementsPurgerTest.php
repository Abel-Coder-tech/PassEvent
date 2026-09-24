<?php

namespace Tests\Feature;

use App\Models\Consentement;
use App\Models\ParametreSite;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ConsentementsPurgerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            if (! Schema::hasTable('consentements')) {
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
            if (! Schema::hasTable('parametres_site')) {
                Schema::create('parametres_site', function (Blueprint $table) {
                    $table->id();
                    $table->string('cle')->unique();
                    $table->text('valeur')->nullable();
                });
            }
        }

        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function creerDecision(string $statut): Consentement
    {
        return Consentement::create([
            'statut' => $statut,
            'services' => ['googletagmanager'],
            'version_politique' => '2026-09-23',
        ]);
    }

    public function test_purge_toutes_les_decisions_et_effectue_la_rotation(): void
    {
        $accepte = $this->creerDecision('accepte');
        $this->creerDecision('accepte');
        $refuse = $this->creerDecision('refuse');

        $this->artisan('consentements:purger')->assertSuccessful();

        $this->assertSame(0, Consentement::count());
        $this->assertFalse(Consentement::whereKey($accepte->id)->exists());
        $this->assertFalse(Consentement::whereKey($refuse->id)->exists());
        $this->assertNotNull(ParametreSite::valeur('consentement_cookie'));
        $this->assertSame(date('Y-m-d'), ParametreSite::valeur('consentement_version'));
    }

    public function test_purge_un_seul_statut(): void
    {
        $accepte = $this->creerDecision('accepte');
        $refuse = $this->creerDecision('refuse');

        $this->artisan('consentements:purger', ['--statut' => 'accepte'])->assertSuccessful();

        $this->assertFalse(Consentement::whereKey($accepte->id)->exists());
        $this->assertTrue(Consentement::whereKey($refuse->id)->exists());
    }

    public function test_option_sans_rotation_ne_change_pas_le_cookie(): void
    {
        $avant = ParametreSite::valeur('consentement_cookie');
        $this->creerDecision('accepte');

        $this->artisan('consentements:purger', ['--sans-rotation' => true])->assertSuccessful();

        $this->assertSame(0, Consentement::count());
        $this->assertSame($avant, ParametreSite::valeur('consentement_cookie'));
    }

    public function test_statut_invalide_est_rejetee(): void
    {
        $this->artisan('consentements:purger', ['--statut' => 'peut_etre'])->assertFailed();
    }
}