<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Evenement;
use App\Models\EvenementDate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Badge « À venir / En cours / Terminé » du tableau de bord des agents :
 * même fenêtre que le scan (DUREE_SCAN_HEURES après chaque début).
 */
class EvenementEtatAgentsTest extends TestCase
{
    use RefreshDatabase;

    private function evenement(array $debuts): Evenement
    {
        $organisateur = User::factory()->create(['role' => 'admin', 'statut' => 'actif']);

        $evenement = Evenement::create([
            'user_id' => $organisateur->id,
            'titre' => 'Concert état',
            'slug' => 'concert-etat-'.uniqid(),
            'lieu' => 'Cotonou',
            'statut' => 'publié',
            'date_event' => $debuts[0],
            'capacite' => 100,
            'quota_vendu' => 0,
            'gratuit' => false,
            'ventes_fermees' => false,
            'billetterie_ouverte' => true,
            'ventes_especes' => 'toujours',
        ]);

        foreach ($debuts as $i => $debut) {
            EvenementDate::create([
                'evenement_id' => $evenement->id,
                'date_debut' => $debut,
                'ordre' => $i,
            ]);
        }

        return $evenement;
    }

    public function test_un_evenement_futur_est_a_venir_avec_sa_date(): void
    {
        $debut = now()->addDays(3)->startOfHour();
        $etat = $this->evenement([$debut])->etatPourAgents();

        $this->assertSame('a_venir', $etat['etat']);
        $this->assertSame($debut->toIso8601String(), $etat['prochain_debut']);
    }

    public function test_une_session_commencee_est_en_cours(): void
    {
        $etat = $this->evenement([now()->subHours(2)])->etatPourAgents();

        $this->assertSame('en_cours', $etat['etat']);
        $this->assertNull($etat['prochain_debut']);
    }

    public function test_apres_la_fenetre_de_scan_l_evenement_est_termine(): void
    {
        $duree = Evenement::DUREE_SCAN_HEURES + 1;
        $etat = $this->evenement([now()->subHours($duree)])->etatPourAgents();

        $this->assertSame('termine', $etat['etat']);
    }

    public function test_entre_deux_jours_l_evenement_redevient_a_venir(): void
    {
        $demain = now()->addDay()->startOfHour();
        $etat = $this->evenement([now()->subDays(2), $demain])->etatPourAgents();

        $this->assertSame('a_venir', $etat['etat']);
        $this->assertSame($demain->toIso8601String(), $etat['prochain_debut']);
    }

    public function test_le_tableau_de_bord_scan_expose_l_etat(): void
    {
        $evenement = $this->evenement([now()->subHour()]);
        $agent = Agent::create([
            'nom' => 'Agent état',
            'email' => 'etat@example.test',
            'password' => bcrypt('secret'),
            'code_acces' => bcrypt('1234'),
            'evenement_id' => $evenement->id,
            'actif' => true,
        ]);

        $this->withToken($agent->createToken('etat', ['agent:read'])->plainTextToken)
            ->getJson('/api/agent/dashboard')
            ->assertOk()
            ->assertJsonPath('event.etat', 'en_cours');
    }
}
