<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Ajouter un numéro qui recevra les retraits exige le mot de passe
class NumeroRetraitMotDePasseTest extends TestCase
{
    use RefreshDatabase;

    private function organisateur(): User
    {
        return User::factory()->create(['role' => 'admin', 'statut' => 'actif', 'mot_de_passe' => bcrypt('bon-mot-de-passe')]);
    }

    private function donnees(array $extra = []): array
    {
        return ['operateur' => 'mtn', 'nom' => 'Awa Kone', 'mobile' => '0197000000'] + $extra;
    }

    public function test_sans_mot_de_passe_ou_mauvais_mot_de_passe_rien_n_est_ajoute(): void
    {
        $org = $this->organisateur();

        $this->actingAs($org)->post(route('parametres.numeros-retrait.store'), $this->donnees())
            ->assertSessionHasErrors('password_numero');
        $this->actingAs($org)->post(route('parametres.numeros-retrait.store'), $this->donnees(['password_numero' => 'faux']))
            ->assertSessionHasErrors('password_numero');

        $this->assertSame(0, $org->numerosRetrait()->count());
    }

    public function test_le_bon_mot_de_passe_ajoute_le_numero(): void
    {
        $org = $this->organisateur();

        $this->actingAs($org)->post(route('parametres.numeros-retrait.store'), $this->donnees(['password_numero' => 'bon-mot-de-passe']))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, $org->numerosRetrait()->count());
    }
}
