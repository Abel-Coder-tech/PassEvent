<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AgentVente;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * L'aide de l'application affiche les coordonnées de l'organisateur :
 * elles doivent être présentes dans les deux tableaux de bord API.
 */
class AgentContactOrganisateurTest extends TestCase
{
    use RefreshDatabase;

    public function test_les_tableaux_de_bord_exposent_nom_email_et_telephone(): void
    {
        $organisateur = User::factory()->create([
            'role' => 'admin',
            'statut' => 'actif',
            'prenom' => 'Awa',
            'nom' => 'Dossou',
            'email' => 'orga-contact@example.test',
            'telephone' => '+229 01 23 45 67 89',
        ]);

        $evenement = Evenement::create([
            'user_id' => $organisateur->id,
            'titre' => 'Concert contact',
            'slug' => 'concert-contact',
            'lieu' => 'Palais des Sports, Cotonou',
            'statut' => 'publié',
            'date_event' => now()->addDays(10),
            'capacite' => 100,
            'quota_vendu' => 0,
            'gratuit' => false,
            'ventes_fermees' => false,
            'billetterie_ouverte' => true,
            'ventes_especes' => 'toujours',
        ]);

        $scan = Agent::create([
            'nom' => 'Agent scan',
            'email' => 'scan-contact@example.test',
            'password' => bcrypt('secret'),
            'code_acces' => bcrypt('1234'),
            'evenement_id' => $evenement->id,
            'actif' => true,
        ]);
        $vente = AgentVente::create([
            'nom' => 'Agent vente',
            'email' => 'vente-contact@example.test',
            'password' => bcrypt('secret'),
            'evenement_id' => $evenement->id,
            'actif' => true,
        ]);

        $this->withToken($scan->createToken('contact-test', ['agent:read'])->plainTextToken)
            ->getJson('/api/agent/dashboard')
            ->assertOk()
            ->assertJsonPath('organisateur.nom', 'Awa Dossou')
            ->assertJsonPath('organisateur.email', 'orga-contact@example.test')
            ->assertJsonPath('organisateur.telephone', '+229 01 23 45 67 89');

        // Deux comptes de types différents dans le même test : le guard Sanctum
        // garde le premier agent en cache, il faut le libérer.
        $this->app['auth']->forgetGuards();

        $this->withToken($vente->createToken('contact-test', ['agent_vente:read'])->plainTextToken)
            ->getJson('/api/agent-vente/dashboard')
            ->assertOk()
            ->assertJsonPath('organisateur.nom', 'Awa Dossou')
            ->assertJsonPath('organisateur.email', 'orga-contact@example.test')
            ->assertJsonPath('organisateur.telephone', '+229 01 23 45 67 89');
    }
}
