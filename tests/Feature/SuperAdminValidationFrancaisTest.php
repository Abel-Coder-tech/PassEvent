<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminValidationFrancaisTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_locale_par_defaut_est_le_francais(): void
    {
        $this->assertSame('fr', app()->getLocale());
        $this->assertSame('fr', config('app.locale'));
        $this->assertSame('fr', config('app.fallback_locale'));
    }

    public function test_la_creation_d_un_organisateur_renvoie_des_erreurs_en_francais(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'super_admin',
            'statut' => 'actif',
        ]);

        $response = $this->actingAs($superadmin, 'superadmin')
            ->post(route('superadmin.organisateurs.creer'), [
                'nom' => '',
                'email' => 'pas-un-email',
                'mot_de_passe' => '123',
                'telephone' => str_repeat('9', 30),
            ]);

        $response->assertSessionHasErrors(['nom', 'email', 'mot_de_passe', 'telephone']);

        $erreurs = session('errors');

        $this->assertSame(
            'Le champ nom est obligatoire.',
            $erreurs->first('nom')
        );
        $this->assertSame(
            'Le champ adresse e-mail doit être une adresse e-mail valide.',
            $erreurs->first('email')
        );
        $this->assertSame(
            'Le texte de mot de passe doit contenir au moins 8 caractères.',
            $erreurs->first('mot_de_passe')
        );
        $this->assertSame(
            'Le texte de téléphone ne peut pas contenir plus de 20 caractères.',
            $erreurs->first('telephone')
        );
    }
}
