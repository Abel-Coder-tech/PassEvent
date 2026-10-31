<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CommissionPhysiqueOrganisateurTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        Setting::query()->delete();
    }

    public function test_le_taux_propre_a_lorganisateur_remplace_le_taux_global(): void
    {
        Setting::set('commission_physique', 5);
        $organisateur = new User(['commission_physique' => 7.5]);

        $this->assertSame(7.5, $organisateur->commissionPhysiquePourcentage());
    }

    public function test_un_taux_non_defini_heritera_du_taux_global(): void
    {
        Setting::set('commission_physique', 6.25);
        $organisateur = new User;

        $this->assertSame(6.25, $organisateur->commissionPhysiquePourcentage());
    }
}