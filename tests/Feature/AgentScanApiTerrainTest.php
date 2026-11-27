<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// Conditions de terrain du scan via l'API mobile : PIN, désactivation, durée du jeton
class AgentScanApiTerrainTest extends TestCase
{
    use RefreshDatabase;

    private Evenement $evenement;

    protected function setUp(): void
    {
        parent::setUp();
        $org = User::factory()->create(['role' => 'admin', 'statut' => 'actif']);
        $this->evenement = Evenement::create(['user_id' => $org->id, 'titre' => 'Concert', 'date_event' => now()->subMinutes(30),
            'lieu' => 'Cotonou', 'capacite' => 100, 'statut' => 'publié', 'gratuit' => true]);
        DB::table('evenement_dates')->where('evenement_id', $this->evenement->id)->delete();
        DB::table('evenement_dates')->insert(['evenement_id' => $this->evenement->id, 'date_debut' => now()->subMinutes(30), 'ordre' => 0]);
    }

    private function agent(string $email, string $pin): Agent
    {
        return Agent::create(['nom' => 'Agent', 'email' => $email, 'password' => Hash::make('Secret@123'),
            'evenement_id' => $this->evenement->id, 'code_acces' => Hash::make($pin), 'actif' => true]);
    }

    // Chaque appel repart d'une authentification vierge (comme deux téléphones distincts)
    public function withToken(string $token, $type = 'Bearer')
    {
        $this->app['auth']->forgetGuards();

        return parent::withToken($token, $type);
    }

    private function connecter(string $email): string
    {
        $r = $this->postJson('/api/agent/login', ['email' => $email, 'password' => 'Secret@123'])->assertOk()->json();

        return $r['token'] ?? $r['access_token'];
    }

    public function test_un_agent_qui_se_trompe_de_pin_ne_bloque_pas_son_collegue_sur_le_meme_wifi(): void
    {
        $this->agent('a1@test.local', '1111');
        $this->agent('a2@test.local', '2222');
        $t1 = $this->connecter('a1@test.local');
        $t2 = $this->connecter('a2@test.local');

        foreach (range(1, 3) as $i) {
            $this->withToken($t1)->postJson('/api/agent/scan/pin', ['code_acces' => '0000'])->assertStatus(422);
        }
        $this->withToken($t1)->postJson('/api/agent/scan/pin', ['code_acces' => '1111'])->assertStatus(429);

        // Même IP, autre agent : non bloqué
        $this->withToken($t2)->postJson('/api/agent/scan/pin', ['code_acces' => '2222'])->assertOk();
    }

    public function test_un_agent_desactive_en_plein_service_est_bloque_immediatement(): void
    {
        $agent = $this->agent('a1@test.local', '1111');
        $scan = $this->withToken($this->connecter('a1@test.local'))
            ->postJson('/api/agent/scan/pin', ['code_acces' => '1111'])->assertOk()->json('scan_token');

        $this->withToken($scan)->postJson('/api/agent/scan/verify', ['code' => 'PAX-INCONNU'])->assertOk();

        $agent->update(['actif' => false]);

        $this->withToken($scan)->postJson('/api/agent/scan/verify', ['code' => 'PAX-INCONNU'])
            ->assertStatus(403)->assertJson(['message' => 'account_disabled']);
    }

    public function test_le_jeton_de_scan_couvre_toute_la_fenetre_de_scan(): void
    {
        $this->agent('a1@test.local', '1111');
        $expire = $this->withToken($this->connecter('a1@test.local'))
            ->postJson('/api/agent/scan/pin', ['code_acces' => '1111'])->assertOk()->json('expires_at');

        $this->assertTrue(\Illuminate\Support\Carbon::parse($expire)->gte(now()->addHours(Evenement::DUREE_SCAN_HEURES)->subMinute()));
    }

    public function test_l_historique_est_pagine_par_10_avec_filtres(): void
    {
        $agent = $this->agent('a1@test.local', '1111');
        $autre = $this->agent('a2@test.local', '2222');
        // 15 scans aujourd'hui (10 valides, 5 refusés), 8 hier, + 3 d'un autre agent
        foreach (range(1, 15) as $i) {
            $this->logScan($agent, $i <= 10 ? 'valide' : 'deja_utilise', now()->subMinutes($i));
        }
        foreach (range(1, 8) as $i) {
            $this->logScan($agent, 'valide', now()->subDay()->subMinutes($i));
        }
        foreach (range(1, 3) as $i) {
            $this->logScan($autre, 'valide', now());
        }
        $token = $this->connecter('a1@test.local');

        $p1 = $this->withToken($token)->getJson('/api/agent/scans')->assertOk();
        $p1->assertJson(['page' => 1, 'par_page' => 10, 'total' => 23, 'pages' => 3]);
        $this->assertCount(10, $p1->json('scans'));
        $this->assertSame([15, 8], array_column($p1->json('jours'), 'total'));

        $p3 = $this->withToken($token)->getJson('/api/agent/scans?page=3')->assertOk();
        $this->assertCount(3, $p3->json('scans'));

        $jour = now()->toDateString();
        $refuses = $this->withToken($token)->getJson("/api/agent/scans?jour={$jour}&filtre=invalid")->assertOk();
        $refuses->assertJson(['total' => 5, 'pages' => 1, 'resume' => ['total' => 15, 'valides' => 10, 'invalides' => 5]]);
    }

    private function logScan(Agent $agent, string $resultat, $quand): void
    {
        $log = \App\Models\Log::create([
            'agent_id' => $agent->id, 'type_operation' => 'scan',
            'details' => ['code' => 'PAX-'.\Illuminate\Support\Str::random(6), 'resultat' => $resultat],
        ]);
        $log->forceFill(['created_at' => $quand])->save();
    }
}
