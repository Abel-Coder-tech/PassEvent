<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AcheteurTest extends TestCase
{
    private ?int $userId = null;

    protected function tablesDisponibles(): bool
    {
        return Schema::hasTable('ticket') && Schema::hasTable('users');
    }

    public function test_visiteur_est_redirige_vers_login(): void
    {
        $this->get('/superadmin/acheteurs')->assertRedirect(route('superadmin.login'));
    }

    public function test_superadmin_voit_la_liste_des_acheteurs(): void
    {
        if (! $this->tablesDisponibles()) {
            $this->markTestSkipped('Tables ticket/users indisponibles sur ce driver.');
        }

        $this->creerSuperAdmin();
        $this->actingAs($this->superAdmin(), 'superadmin')
            ->get('/superadmin/acheteurs')
            ->assertOk()
            ->assertSee('Contacts des acheteurs');
    }

    public function test_export_csv_des_acheteurs(): void
    {
        if (! $this->tablesDisponibles()) {
            $this->markTestSkipped('Tables ticket/users indisponibles sur ce driver.');
        }

        $this->creerSuperAdmin();
        $this->actingAs($this->superAdmin(), 'superadmin')
            ->get('/superadmin/acheteurs/export')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_filtre_par_statut_des_acheteurs(): void
    {
        if (! $this->tablesDisponibles()) {
            $this->markTestSkipped('Tables ticket/users indisponibles sur ce driver.');
        }

        $this->creerSuperAdmin();
        $this->actingAs($this->superAdmin(), 'superadmin')
            ->get('/superadmin/acheteurs?statut=échoué')
            ->assertOk()
            ->assertSee('Contacts des acheteurs');
    }

    private function creerSuperAdmin(): void
    {
        if ($this->userId !== null) {
            return;
        }

        $user = User::create([
            'nom' => 'Super Admin Test',
            'email' => 'sa-test-'.uniqid().'@paxevent.test',
            'mot_de_passe' => bcrypt('secret'),
            'role' => 'super_admin',
        ]);

        $this->userId = $user->id;
    }

    private function superAdmin(): User
    {
        return User::find($this->userId);
    }

    protected function tearDown(): void
    {
        if ($this->userId !== null && Schema::hasTable('users')) {
            User::where('id', $this->userId)->delete();
        }

        parent::tearDown();
    }
}