<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AgentVente;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApiAuthenticationSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        RateLimiter::clear('login-ip:127.0.0.1');
    }

    public function test_jwt_shaped_bearer_tokens_are_rejected_on_every_protected_api_route(): void
    {
        $routes = $this->protectedApiRoutes();
        $this->assertNotEmpty($routes);

        foreach ($this->forgedTokens() as $scenario => $token) {
            foreach ($routes as [$method, $uri]) {
                $response = $this->call($method, $uri, [], [], [], [
                    'HTTP_ACCEPT' => 'application/json',
                    'HTTP_AUTHORIZATION' => 'Bearer '.$token,
                ]);

                $this->assertContains(
                    $response->getStatusCode(),
                    [401, 403],
                    "{$scenario} must not authenticate on {$method} {$uri}."
                );
            }
        }
    }

    public function test_missing_bearer_tokens_are_rejected_on_every_protected_api_route(): void
    {
        $routes = $this->protectedApiRoutes();
        $this->assertNotEmpty($routes);

        foreach ($routes as [$method, $uri]) {
            $response = $this->call($method, $uri, [], [], [], [
                'HTTP_ACCEPT' => 'application/json',
            ]);

            $this->assertContains(
                $response->getStatusCode(),
                [401, 403],
                "A missing token must not authenticate on {$method} {$uri}."
            );
        }
    }

    public function test_agent_login_has_a_combined_account_and_ip_limit_and_uniform_credentials_errors(): void
    {
        DB::table('agents')->insert([
            'nom' => 'Agent',
            'email' => 'known@example.test',
            'password' => bcrypt('correct-password'),
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $unknown = $this->postJson('/api/agent/login', [
            'email' => 'unknown@example.test',
            'password' => 'wrong-password',
        ]);
        $wrongPassword = $this->postJson('/api/agent/login', [
            'email' => 'known@example.test',
            'password' => 'wrong-password',
        ]);

        $unknown->assertUnauthorized();
        $wrongPassword->assertUnauthorized();
        $this->assertSame($unknown->json(), $wrongPassword->json());

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->postJson('/api/agent/login', [
                'email' => 'known@example.test',
                'password' => 'wrong-password',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/agent/login', [
            'email' => 'known@example.test',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_agent_login_issues_a_token_that_expires_after_fourteen_days(): void
    {
        DB::table('agents')->insert([
            'nom' => 'Agent',
            'email' => 'token-expiry@example.test',
            'password' => bcrypt('correct-password'),
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/agent/login', [
            'email' => 'token-expiry@example.test',
            'password' => 'correct-password',
        ])->assertOk();

        $expiresAt = DB::table('personal_access_tokens')->value('expires_at');
        $this->assertNotNull($expiresAt);
        $this->assertEqualsWithDelta(
            14 * 24 * 60,
            now()->diffInMinutes(\Illuminate\Support\Carbon::parse($expiresAt)),
            1
        );
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_agent_vente_cannot_access_another_agents_ticket_actions(): void
    {
        $this->insertEvent(1);
        $this->insertEvent(2);
        $owner = $this->createSalesAgent(1, 'owner@example.test');
        $otherAgent = $this->createSalesAgent(2, 'other@example.test');
        $ticketId = DB::table('ticket')->insertGetId([
            'evenement_id' => 1,
            'agent_vente_id' => $owner->id,
            'code_unique' => 'PAX-OWNER',
            'statut_paiement' => 'en_attente',
            'annule' => false,
            'date_achat' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $token = $otherAgent->createToken(
            'security-test',
            ['agent_vente:read', 'agent_vente:sell']
        )->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->withHeaders($headers)->getJson("/api/agent-vente/sales/{$ticketId}")->assertForbidden();
        $this->withHeaders($headers)->get("/api/agent-vente/sales/{$ticketId}/pdf")->assertForbidden();
        $this->withHeaders($headers)->postJson("/api/agent-vente/sales/{$ticketId}/payment")->assertForbidden();
        $this->withHeaders($headers)->postJson("/api/agent-vente/sales/{$ticketId}/confirm", [
            'transaction_id' => 'forged-transaction',
        ])->assertForbidden();
        $this->withHeaders($headers)->postJson("/api/agent-vente/sales/{$ticketId}/sync")->assertForbidden();
        $this->withHeaders($headers)->postJson("/api/agent-vente/sales/{$ticketId}/resend")->assertForbidden();
        $this->withHeaders($headers)->postJson("/api/agent-vente/sales/{$ticketId}/cancel")->assertForbidden();
    }

    public function test_agent_vente_cannot_mark_another_agents_notification_read(): void
    {
        $this->insertEvent(1);
        $this->insertEvent(2);
        $owner = $this->createSalesAgent(1, 'notification-owner@example.test');
        $otherAgent = $this->createSalesAgent(2, 'notification-other@example.test');
        $notificationId = DB::table('agent_vente_notifications')->insertGetId([
            'agent_vente_id' => $owner->id,
            'evenement_id' => 1,
            'titre' => 'Private notification',
            'message' => 'Private message',
            'type' => 'info',
            'destinataire_type' => 'agent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $token = $otherAgent->createToken('security-test', ['agent_vente:read'])->plainTextToken;

        $this->withToken($token)
            ->postJson("/api/agent-vente/notifications/{$notificationId}/read")
            ->assertNotFound();

        $this->assertNull(DB::table('agent_vente_notifications')->where('id', $notificationId)->value('lu_at'));
    }

    public function test_scan_agent_cannot_scan_a_ticket_from_another_event(): void
    {
        $this->insertEvent(1);
        $this->insertEvent(2);
        $agentId = DB::table('agents')->insertGetId([
            'nom' => 'Scan agent',
            'email' => 'scan-agent@example.test',
            'password' => bcrypt('test-password'),
            'evenement_id' => 1,
            'code_acces' => '123456',
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('ticket')->insert([
            'evenement_id' => 2,
            'code_unique' => 'PAX-OTHER-EVENT',
            'statut_paiement' => 'payé',
            'annule' => false,
            'date_achat' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $agent = Agent::findOrFail($agentId);
        $token = $agent->createToken('scan-test', ['agent:scan'])->plainTextToken;

        $this->withToken($token)->postJson('/api/agent/scan/verify', [
            'code' => 'PAX-OTHER-EVENT',
        ])->assertOk()
            ->assertJsonPath('resultat', 'invalide')
            ->assertJsonPath('ticket', null);
    }

    public function test_sanctum_does_not_read_tokens_from_query_parameters(): void
    {
        $id = DB::table('agents')->insertGetId([
            'nom' => 'Agent',
            'email' => 'query-token@example.test',
            'password' => bcrypt('test-password'),
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $agent = Agent::findOrFail($id);
        $token = $agent->createToken('query-test', ['agent:read'])->plainTextToken;

        $this->getJson('/api/agent/me?token='.urlencode($token))->assertUnauthorized();
    }

    public function test_personal_access_tokens_expire_after_fourteen_days(): void
    {
        $this->assertSame(14 * 24 * 60, config('sanctum.expiration'));
    }

    public function test_expired_personal_access_token_is_rejected(): void
    {
        $id = DB::table('agents')->insertGetId([
            'nom' => 'Agent',
            'email' => 'expired@example.test',
            'password' => bcrypt('test-password'),
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $agent = Agent::findOrFail($id);
        $token = $agent->createToken('expired-test', ['agent:read'])->plainTextToken;
        DB::table('personal_access_tokens')
            ->where('tokenable_type', Agent::class)
            ->where('tokenable_id', $agent->id)
            ->update([
                'created_at' => now()->subDays(15),
                'expires_at' => null,
            ]);

        $this->withToken($token)->getJson('/api/agent/me')->assertUnauthorized();
    }

    public function test_logout_all_revokes_every_agent_token(): void
    {
        $id = DB::table('agents')->insertGetId([
            'nom' => 'Agent',
            'email' => 'logout-all@example.test',
            'password' => bcrypt('test-password'),
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $agent = Agent::findOrFail($id);
        $token = $agent->createToken('current-device', ['agent:read'])->plainTextToken;
        $agent->createToken('other-device', ['agent:read']);

        $this->withToken($token)
            ->postJson('/api/agent/logout-all')
            ->assertOk()
            ->assertJsonPath('message', 'logged_out_all');

        $this->assertSame(0, $agent->tokens()->count());
    }

    public function test_logout_all_revokes_every_sales_agent_token(): void
    {
        $this->insertEvent(1);
        $agent = $this->createSalesAgent(1, 'sales-logout-all@example.test');
        $token = $agent->createToken('current-device', ['agent_vente:read'])->plainTextToken;
        $agent->createToken('other-device', ['agent_vente:read']);

        $this->withToken($token)
            ->postJson('/api/agent-vente/logout-all')
            ->assertOk()
            ->assertJsonPath('message', 'logged_out_all');

        $this->assertSame(0, $agent->tokens()->count());
    }

    public function test_sales_token_refresh_keeps_the_session_cap(): void
    {
        Carbon::setTestNow('2026-11-10 10:00:00');

        config([
            'sanctum.expiration' => 20160,
            'sanctum.absolute_expiration' => 60,
        ]);

        $this->insertEvent(1);
        $this->createSalesAgent(1, 'absolute-session@example.test');

        $login = $this->postJson('/api/agent-vente/login', [
            'email' => 'absolute-session@example.test',
            'password' => 'test-password',
        ])->assertOk();

        Carbon::setTestNow('2026-11-10 10:30:00');

        $refresh = $this->withToken($login->json('token'))
            ->postJson('/api/agent-vente/refresh-token')
            ->assertOk();

        // Le token glissant est plafonné à la fin absolue de session : 11h00.
        $this->assertTrue(
            Carbon::parse($refresh->json('expires_at'))->equalTo(Carbon::parse('2026-11-10 11:00:00')),
            'Le token rafraîchi ne doit pas dépasser la fin absolue de session.'
        );
        $this->assertTrue(
            Carbon::parse($refresh->json('absolute_expires_at'))->equalTo(Carbon::parse('2026-11-10 11:00:00'))
        );

        // Le nouveau token conserve le début de session d'origine.
        $abilities = DB::table('personal_access_tokens')
            ->where('name', 'agent-vente-app')
            ->value('abilities');

        $this->assertStringContainsString('session_started_at:2026-11-10T10:00:00+01:00', $abilities);
    }

    public function test_expired_absolute_session_is_rejected_and_revoked(): void
    {
        Carbon::setTestNow('2026-11-10 11:00:01');

        config([
            'sanctum.expiration' => 20160,
            'sanctum.absolute_expiration' => 60,
        ]);

        $this->insertEvent(1);
        $agent = $this->createSalesAgent(1, 'expired-session@example.test');

        // Token encore valide (expires_at 12h00) mais session ouverte à 10h00 :
        // la limite absolue (11h00) est dépassée.
        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => AgentVente::class,
            'tokenable_id' => $agent->id,
            'name' => 'agent-vente-app',
            'token' => hash('sha256', 'plain-absolute-token'),
            'abilities' => json_encode([
                'agent_vente:read',
                'agent_vente:sell',
                'session_started_at:2026-11-10T10:00:00+01:00',
            ]),
            'expires_at' => '2026-11-10 12:00:00',
            'created_at' => '2026-11-10 10:00:00',
            'updated_at' => '2026-11-10 10:00:00',
        ]);

        $this->withToken('plain-absolute-token')
            ->postJson('/api/agent-vente/refresh-token')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'session_expired');

        $this->assertSame(0, DB::table('personal_access_tokens')->where('name', 'agent-vente-app')->count());
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    private function protectedApiRoutes(): array
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/')) {
                continue;
            }

            if (! in_array('auth:sanctum', $route->gatherMiddleware(), true)) {
                continue;
            }

            $uri = '/'.preg_replace('/\{[^}]+\??\}/', '1', $route->uri());
            foreach ($route->methods() as $method) {
                if (in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                    $routes[$method.' '.$uri] = [$method, $uri];
                }
            }
        }

        return array_values($routes);
    }

    /**
     * @return array<string, string>
     */
    private function forgedTokens(): array
    {
        $payload = $this->base64UrlEncode(json_encode([
            'sub' => '1',
            'iss' => config('app.url'),
            'aud' => 'paxevent-api',
            'iat' => time(),
            'nbf' => time() - 1,
            'exp' => time() + 300,
        ], JSON_THROW_ON_ERROR));

        $tokens = [];
        foreach (['none', 'None', 'NONE'] as $algorithm) {
            $header = $this->base64UrlEncode(json_encode([
                'alg' => $algorithm,
                'typ' => 'JWT',
            ], JSON_THROW_ON_ERROR));
            $tokens['alg_'.$algorithm] = $header.'.'.$payload.'.';
        }

        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));
        $tokens['empty_signature'] = $header.'.'.$payload.'.';
        $publicKey = "-----BEGIN PUBLIC KEY-----\nTest-only public key material\n-----END PUBLIC KEY-----";
        $signature = $this->base64UrlEncode(hash_hmac('sha256', $header.'.'.$payload, $publicKey, true));
        $tokens['hs256_with_public_key_material'] = $header.'.'.$payload.'.'.$signature;

        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
            'kid' => '../../untrusted-key',
            'jku' => 'https://invalid.example/jwks.json',
            'x5u' => 'https://invalid.example/certificate.pem',
            'jwk' => ['kty' => 'oct', 'k' => $this->base64UrlEncode('attacker-key')],
        ], JSON_THROW_ON_ERROR));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', $header.'.'.$payload, 'attacker-key', true));
        $tokens['injected_key_headers'] = $header.'.'.$payload.'.'.$signature;

        return $tokens;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function createSalesAgent(int $eventId, string $email): AgentVente
    {
        $id = DB::table('agents_vente')->insertGetId([
            'nom' => 'Agent',
            'email' => $email,
            'password' => bcrypt('test-password'),
            'evenement_id' => $eventId,
            'code_vente' => '123456',
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return AgentVente::findOrFail($id);
    }

    private function insertEvent(int $id): void
    {
        DB::table('evenement')->insert([
            'id' => $id,
            'titre' => 'Test event '.$id,
            'slug' => 'test-event-'.$id,
            'statut' => 'publie',
            'date_event' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createTables(): void
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->text('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('agents')) {
            Schema::create('agents', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('email')->unique();
                $table->string('password');
                $table->unsignedBigInteger('evenement_id')->nullable();
                $table->string('code_acces')->nullable();
                $table->boolean('actif')->default(true);
                $table->unsignedInteger('tentatives_code')->default(0);
                $table->timestamp('bloque_jusqua')->nullable();
                $table->timestamp('dernier_acces')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('agents_vente')) {
            Schema::create('agents_vente', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('email')->unique();
                $table->string('password');
                $table->unsignedBigInteger('evenement_id');
                $table->string('code_vente', 6)->nullable();
                $table->boolean('actif')->default(true);
                $table->unsignedInteger('tickets_count')->default(0);
                $table->decimal('montant_total', 12, 2)->default(0);
                $table->timestamp('dernier_acces')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('evenement')) {
            Schema::create('evenement', function (Blueprint $table) {
                $table->id();
                $table->string('titre');
                $table->string('slug')->unique();
                $table->string('statut')->default('publie');
                $table->dateTime('date_event')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('evenement_dates')) {
            Schema::create('evenement_dates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('evenement_id');
                $table->dateTime('date_debut')->nullable();
                $table->unsignedInteger('ordre')->default(0);
                $table->unsignedInteger('capacite')->nullable();
            });
        }

        if (! Schema::hasTable('ticket')) {
            Schema::create('ticket', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('evenement_id');
                $table->unsignedBigInteger('evenement_date_id')->nullable();
                $table->unsignedBigInteger('agent_vente_id')->nullable();
                $table->string('code_unique')->unique();
                $table->string('statut_paiement')->default('payé');
                $table->boolean('annule')->default(false);
                $table->dateTime('expire_at')->nullable();
                $table->dateTime('date_achat')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('agent_vente_notifications')) {
            Schema::create('agent_vente_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agent_vente_id');
                $table->unsignedBigInteger('evenement_id')->nullable();
                $table->string('titre');
                $table->text('message');
                $table->string('type')->nullable();
                $table->string('destinataire_type')->nullable();
                $table->json('data')->nullable();
                $table->timestamp('lu_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('logs')) {
            Schema::create('logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id')->nullable();
                $table->unsignedBigInteger('agent_id')->nullable();
                $table->string('type_operation');
                $table->json('details')->nullable();
                $table->string('ip')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }
    }
}
