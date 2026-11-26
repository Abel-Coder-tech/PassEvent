<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// La billetterie reste ouverte le jour même : elle se ferme 24 h après le début.
class BilletterieOuverteJourMemeTest extends TestCase
{
    use RefreshDatabase;

    private function evenement(\DateTimeInterface $debut): Evenement
    {
        $user = User::factory()->create(['role' => 'admin', 'statut' => 'actif']);

        return Evenement::create([
            'user_id' => $user->id, 'titre' => 'Concert', 'date_event' => $debut,
            'lieu' => 'Cotonou', 'capacite' => 100, 'statut' => 'publié', 'gratuit' => false,
        ]);
    }

    public function test_un_evenement_commence_depuis_2h_reste_en_vente(): void
    {
        $e = $this->evenement(now()->subHours(2));
        $this->assertFalse($e->evenementPasse());
        $this->assertNotSame('event_ended', $e->raisonBlocageVente());
        $this->assertTrue(Evenement::withUpcomingDate()->whereKey($e->id)->exists());
    }

    public function test_la_vente_se_ferme_24h_apres_le_debut(): void
    {
        $e = $this->evenement(now()->subHours(25));
        $this->assertTrue($e->evenementPasse());
        $this->assertFalse(Evenement::withUpcomingDate()->whereKey($e->id)->exists());
    }

    public function test_la_commande_ne_termine_l_evenement_qu_apres_24h(): void
    {
        $encours = $this->evenement(now()->subHours(10));
        $fini = $this->evenement(now()->subHours(30));

        $this->artisan('evenements:terminer')->assertSuccessful();

        $this->assertSame('publié', $encours->fresh()->statut);
        $this->assertSame('terminé', $fini->fresh()->statut);
    }
}
