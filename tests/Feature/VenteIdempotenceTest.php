<?php

namespace Tests\Feature;

use App\Models\AgentVente;
use App\Services\AgentVenteSaleService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VenteIdempotenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        Queue::fake();
    }

    public function test_rejouer_la_meme_cle_ne_cree_qu_une_vente(): void
    {
        $agent = $this->creerAgent();
        $service = app(AgentVenteSaleService::class);

        $data = [
            'nom_acheteur' => 'Awa Kone',
            'email_acheteur' => 'awa@example.test',
            'quantite' => 2,
            'idempotency_key' => 'vente-unique-1',
        ];

        $premiere = $service->create($agent, $data);
        $seconde = $service->create($agent, $data);

        $this->assertFalse($premiere['idempotent']);
        $this->assertTrue($seconde['idempotent']);
        $this->assertSame(2, DB::table('ticket')->count());
        $this->assertSame(
            $premiere['tickets']->pluck('id')->all(),
            $seconde['tickets']->pluck('id')->all()
        );
        $this->assertSame($premiere['transaction_id'], $seconde['transaction_id']);
        $this->assertSame(1, DB::table('vente_idempotences')->count());
    }

    public function test_reutiliser_une_cle_pour_une_autre_vente_est_refuse(): void
    {
        $agent = $this->creerAgent();
        $service = app(AgentVenteSaleService::class);

        $service->create($agent, [
            'nom_acheteur' => 'Awa Kone',
            'email_acheteur' => 'awa@example.test',
            'quantite' => 1,
            'idempotency_key' => 'vente-unique-2',
        ]);

        try {
            $service->create($agent, [
                'nom_acheteur' => 'Autre Acheteur',
                'email_acheteur' => 'awa@example.test',
                'quantite' => 1,
                'idempotency_key' => 'vente-unique-2',
            ]);

            $this->fail('Une clé réutilisée pour un autre contenu doit être refusée.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('idempotency_key', $exception->errors());
        }

        $this->assertSame(1, DB::table('ticket')->count());
    }

    public function test_la_base_interdit_deux_ventes_avec_la_meme_cle(): void
    {
        $agent = $this->creerAgent();

        $ligne = [
            'agent_vente_id' => $agent->id,
            'cle' => 'vente-unique-3',
            'empreinte' => str_repeat('a', 64),
            'transaction_id' => 'AGENT-TEST0001',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('vente_idempotences')->insert($ligne);

        $this->expectException(UniqueConstraintViolationException::class);

        DB::table('vente_idempotences')->insert([
            ...$ligne,
            'transaction_id' => 'AGENT-TEST0002',
        ]);
    }

    public function test_une_vente_sans_cle_n_ecrit_pas_de_trace_d_idempotence(): void
    {
        $agent = $this->creerAgent();
        $service = app(AgentVenteSaleService::class);

        $service->create($agent, [
            'nom_acheteur' => 'Awa Kone',
            'email_acheteur' => 'awa@example.test',
            'quantite' => 1,
        ]);

        $this->assertSame(1, DB::table('ticket')->count());
        $this->assertSame(0, DB::table('vente_idempotences')->count());
    }

    private function creerAgent(): AgentVente
    {
        $evenementId = DB::table('evenement')->insertGetId([
            'titre' => 'Concert test',
            'slug' => 'concert-test',
            'statut' => 'publie',
            'date_event' => now()->addDays(30),
            'capacite' => 100,
            'quota_vendu' => 0,
            'gratuit' => true,
            'ventes_fermees' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tarifs')->insert([
            'evenement_id' => $evenementId,
            'nom' => 'Gratuit',
            'prix' => 0,
            'quantite_disponible' => null,
            'quantite_vendue' => 0,
            'statut' => 'actif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $agentId = DB::table('agents_vente')->insertGetId([
            'nom' => 'Agent Vente',
            'email' => 'agent-vente@example.test',
            'password' => bcrypt('secret'),
            'evenement_id' => $evenementId,
            'actif' => true,
            'tickets_count' => 0,
            'montant_total' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return AgentVente::findOrFail($agentId);
    }

    private function createTables(): void
    {
        Schema::create('evenement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('titre');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->dateTime('date_event')->nullable();
            $table->string('lieu')->nullable();
            $table->string('statut')->default('publie');
            $table->unsignedInteger('capacite')->default(0);
            $table->unsignedInteger('quota_vendu')->default(0);
            $table->boolean('gratuit')->default(false);
            $table->boolean('ventes_fermees')->default(false);
            $table->boolean('ventes_especes')->default(true);
            $table->timestamps();
        });

        Schema::create('evenement_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->dateTime('date_debut')->nullable();
            $table->unsignedInteger('ordre')->default(0);
        });

        Schema::create('tarifs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->string('nom');
            $table->decimal('prix', 10, 2)->default(0);
            $table->unsignedInteger('quantite_disponible')->nullable();
            $table->unsignedInteger('quantite_vendue')->default(0);
            $table->string('statut')->default('actif');
            $table->timestamps();
        });

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

        Schema::create('ticket', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->unsignedBigInteger('agent_vente_id')->nullable();
            $table->unsignedBigInteger('lot_physique_id')->nullable();
            $table->string('source')->nullable();
            $table->string('code_unique')->unique();
            $table->string('qr_signature')->nullable();
            $table->string('email_acheteur')->nullable();
            $table->string('whatsapp_acheteur')->nullable();
            $table->string('nom_acheteur')->nullable();
            $table->string('nom_tarif')->nullable();
            $table->decimal('montant', 10, 2)->default(0);
            $table->decimal('montant_reduction', 10, 2)->default(0);
            $table->string('code_promo_utilise')->nullable();
            $table->unsignedInteger('quantite')->default(1);
            $table->string('statut_paiement')->default('payé');
            $table->string('methode_paiement')->nullable();
            $table->string('type_paiement')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('idempotency_key', 100)->nullable();
            $table->boolean('utilise')->default(false);
            $table->boolean('annule')->default(false);
            $table->dateTime('date_achat')->nullable();
            $table->dateTime('expire_at')->nullable();
            $table->timestamps();
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->string('type_operation', 50);
            $table->json('details')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('journal_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->unsignedBigInteger('agent_vente_id')->nullable();
            $table->unsignedBigInteger('evenement_id')->nullable();
            $table->string('action', 50);
            $table->string('statut_avant', 30)->nullable();
            $table->string('statut_apres', 30)->nullable();
            $table->string('reference', 255)->nullable();
            $table->json('details')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at');
        });

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

        Schema::create('vente_idempotences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_vente_id');
            $table->string('cle', 100);
            $table->char('empreinte', 64);
            $table->string('transaction_id', 100);
            $table->timestamps();

            $table->unique(['agent_vente_id', 'cle'], 'vente_idempotence_agent_cle_unique');
            $table->index('transaction_id');
        });
    }
}
