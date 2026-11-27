<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AgentVente;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// Une seule session active par compte : la deuxième connexion, avec les
// mêmes identifiants, est refusée sans déconnecter la première.
class SessionUniqueAgentTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'agent@gmail.com';

    private const PASSWORD = 'Agent.234';

    private Evenement $evenement;

    protected function setUp(): void
    {
        parent::setUp();

        $organisateur = User::factory()->create(['role' => 'admin', 'statut' => 'actif']);
        $this->evenement = Evenement::create([
            'user_id' => $organisateur->id,
            'titre' => 'Concert',
            'date_event' => now()->addDays(2),
            'lieu' => 'Cotonou',
            'capacite' => 100,
            'statut' => 'publié',
            'gratuit' => true,
        ]);
        DB::table('evenement_dates')->insert([
            'evenement_id' => $this->evenement->id,
            'date_debut' => now()->addDays(2),
            'ordre' => 0,
        ]);
    }

    // Chaque changement de jeton simule un nouvel appareil : on vide les
    // gardes en cache pour ne pas réutiliser l'utilisateur du test précédent.
    public function withToken(string $token, $type = 'Bearer')
    {
        $this->app['auth']->forgetGuards();

        return parent::withToken($token, $type);
    }

    public function test_api_scan_refuse_la_deuxieme_connexion_et_garde_la_premiere(): void
    {
        $agent = $this->creerAgentScan();

        $premier = $this->postJson('/api/agent/login', $this->identifiants())->assertOk();
        $cle = $agent->fresh()->session_cle;
        $this->assertNotEmpty($cle);

        $this->flushHeaders();
        $this->postJson('/api/agent/login', $this->identifiants())
            ->assertStatus(409)
            ->assertJsonPath('message', 'deja_connecte');

        $this->assertSame($cle, $agent->fresh()->session_cle);
        $this->withToken($premier->json('token'))->getJson('/api/agent/me')->assertOk();
    }

    public function test_api_scan_autorise_une_nouvelle_connexion_apres_deconnexion(): void
    {
        $agent = $this->creerAgentScan();

        $premier = $this->postJson('/api/agent/login', $this->identifiants())->assertOk()->json('token');
        $this->withToken($premier)->postJson('/api/agent/logout')->assertOk();
        $this->assertNull($agent->fresh()->session_cle);

        $this->flushHeaders();
        $this->postJson('/api/agent/login', $this->identifiants())->assertOk();
    }

    public function test_api_vente_refuse_la_deuxieme_connexion_et_garde_la_premiere(): void
    {
        $agent = $this->creerAgentVente();

        $premier = $this->postJson('/api/agent-vente/login', $this->identifiants())->assertOk();
        $cle = $agent->fresh()->session_cle;
        $this->assertNotEmpty($cle);

        $this->flushHeaders();
        $this->postJson('/api/agent-vente/login', $this->identifiants())
            ->assertStatus(409)
            ->assertJsonPath('message', 'deja_connecte');

        $this->assertSame($cle, $agent->fresh()->session_cle);
        $this->withToken($premier->json('token'))->getJson('/api/agent-vente/me')->assertOk();
    }

    public function test_web_scan_refuse_la_deuxieme_connexion(): void
    {
        $agent = $this->creerAgentScan();

        $this->post('/agent/connexion', $this->identifiants())->assertRedirect();
        $session = $this->app['session']->all();
        $this->assertNotEmpty($agent->fresh()->session_cle);

        // Autre navigateur : même identifiant, session locale vide.
        $this->flushSession();
        $this->post('/agent/connexion', $this->identifiants())
            ->assertSessionHas('error', 'Ce compte est déjà connecté sur un autre appareil. Déconnectez-vous d’abord.');

        $this->withSession($session)->get('/agent/dashboard')->assertOk();
    }

    public function test_web_vente_refuse_la_deuxieme_connexion(): void
    {
        $agent = $this->creerAgentVente();

        $this->post('/vente/connexion', $this->identifiants())->assertRedirect();
        $session = $this->app['session']->all();
        $this->assertNotEmpty($agent->fresh()->session_cle);

        $this->flushSession();
        $this->post('/vente/connexion', $this->identifiants())
            ->assertSessionHas('error', 'Ce compte est déjà connecté sur un autre appareil. Déconnectez-vous d’abord.');

        $this->withSession($session)->get('/vente/dashboard')->assertOk();
    }

    public function test_une_session_api_bloque_la_connexion_web_et_inverse(): void
    {
        $scan = $this->creerAgentScan();
        $vente = $this->creerAgentVente();

        $jetonScan = $this->postJson('/api/agent/login', $this->identifiants())->assertOk()->json('token');
        $this->post('/agent/connexion', $this->identifiants())
            ->assertSessionHas('error', 'Ce compte est déjà connecté sur un autre appareil. Déconnectez-vous d’abord.');
        $this->withToken($jetonScan)->getJson('/api/agent/me')->assertOk();

        $this->flushSession();
        $this->flushHeaders();
        $jetonVente = $this->postJson('/api/agent-vente/login', $this->identifiants())->assertOk()->json('token');
        $this->flushHeaders();
        $this->post('/vente/connexion', $this->identifiants())
            ->assertSessionHas('error', 'Ce compte est déjà connecté sur un autre appareil. Déconnectez-vous d’abord.');
        $this->flushHeaders();
        $this->withToken($jetonVente)->getJson('/api/agent-vente/me')->assertOk();
        $this->assertNotEmpty($vente->fresh()->session_cle);
    }

    public function test_le_logout_web_libere_le_compte(): void
    {
        $this->creerAgentScan();

        $this->post('/agent/connexion', $this->identifiants())->assertRedirect();
        $this->post('/agent/deconnexion')->assertRedirect();
        $this->assertNull(Agent::first()->session_cle);

        $this->flushSession();
        $this->post('/agent/connexion', $this->identifiants())->assertRedirect();
    }

    public function test_la_sortie_du_scan_libere_le_compte(): void
    {
        $agent = $this->creerAgentScan();
        $jeton = $this->postJson('/api/agent/login', $this->identifiants())->assertOk()->json('token');
        $jetonScan = $this->withToken($jeton)
            ->postJson('/api/agent/scan/pin', ['code_acces' => '1234'])
            ->assertOk()
            ->json('scan_token');

        $this->withToken($jetonScan)->postJson('/api/agent/scan/exit')->assertOk();
        $this->assertNull($agent->fresh()->session_cle);

        // Les jetons de l'appareil sont révoqués et le compte peut se reconnecter.
        $this->flushHeaders();
        $this->withToken($jeton)->getJson('/api/agent/me')->assertUnauthorized();
        $this->flushHeaders();
        $this->postJson('/api/agent/login', $this->identifiants())->assertOk();
    }

    public function test_une_session_expiree_ne_bloque_plus_la_connexion(): void
    {
        $agent = $this->creerAgentScan();
        $this->postJson('/api/agent/login', $this->identifiants())->assertOk();

        $agent->forceFill(['session_expire_le' => now()->subMinute()])->save();

        $this->flushHeaders();
        $this->postJson('/api/agent/login', $this->identifiants())->assertOk();
    }

    private function creerAgentScan(): Agent
    {
        return Agent::create([
            'nom' => 'Agent scan',
            'email' => self::EMAIL,
            'password' => Hash::make(self::PASSWORD),
            'evenement_id' => $this->evenement->id,
            'code_acces' => Hash::make('1234'),
            'actif' => true,
        ]);
    }

    private function creerAgentVente(): AgentVente
    {
        return AgentVente::create([
            'nom' => 'Agent vente',
            'email' => self::EMAIL,
            'password' => Hash::make(self::PASSWORD),
            'evenement_id' => $this->evenement->id,
            'actif' => true,
        ]);
    }

    /**
     * @return array{email: string, password: string}
     */
    private function identifiants(): array
    {
        return ['email' => self::EMAIL, 'password' => self::PASSWORD];
    }
}
