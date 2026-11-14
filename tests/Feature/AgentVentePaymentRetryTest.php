<?php

namespace Tests\Feature;

use App\Models\AgentVente;
use App\Models\Ticket;
use App\Services\AgentVenteSaleService;
use App\Services\FedapayService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

/**
 * FedaPay n'accepte qu'une transaction par `merchant_reference`. Comme la
 * référence utilisée est l'id du premier billet, une reprise du checkout ne
 * doit pas recréer de transaction : elle doit réutiliser celle qui existe.
 */
class AgentVentePaymentRetryTest extends TestCase
{
    private int $evenementId;

    private int $tarifId;

    private AgentVente $agent;

    private FedapayService&Mockery\MockInterface $fedapay;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        Queue::fake();
        config(['services.fedapay.secret_key' => 'sk_test_retry']);

        $this->evenementId = $this->creerEvenement();
        $this->tarifId = $this->creerTarif($this->evenementId);
        $this->agent = $this->creerAgent($this->evenementId);

        $this->fedapay = Mockery::mock(FedapayService::class);
        $this->fedapay->shouldReceive('getPublicKey')->andReturn('pk_test_retry');
        $this->fedapay->shouldReceive('isSandbox')->andReturn(true);
        $this->app->instance(FedapayService::class, $this->fedapay);
    }

    public function test_le_premier_paiement_cree_une_transaction(): void
    {
        $ticket = $this->creerTicket('en_attente');

        $this->fedapay->shouldReceive('findTransactionByExternalId')
            ->once()
            ->with((string) $ticket->id)
            ->andReturnNull();

        $this->fedapay->shouldReceive('createTransaction')
            ->once()
            ->withArgs(fn (array $payload) => $payload['merchant_reference'] === (string) $ticket->id)
            ->andReturn([
                'id' => 4242,
                'payment_url' => 'https://checkout.fedapay.test/premier',
            ]);

        $this->fedapay->shouldNotReceive('generateToken');

        $resultat = $this->service()->createPayment($this->agent, $ticket);

        $this->assertSame('https://checkout.fedapay.test/premier', $resultat['payment_url']);
        $this->assertSame('4242', $resultat['fedapay_transaction_id']);
        $this->assertSame('4242', (string) $ticket->fresh()->fedapay_transaction_id);
    }

    public function test_une_reprise_reutilise_la_transaction_existante(): void
    {
        $ticket = $this->creerTicket('en_attente', '4343');

        $this->fedapay->shouldReceive('getTransaction')
            ->once()
            ->with('4343')
            ->andReturn([
                'id' => 4343,
                'status' => 'pending',
                'amount' => 5000,
                'merchant_reference' => (string) $ticket->id,
                'custom_metadata' => [
                    'ticket_id' => (string) $ticket->id,
                    'group_transaction_id' => (string) $ticket->transaction_id,
                ],
            ]);

        $this->fedapay->shouldReceive('generateToken')
            ->once()
            ->with('4343')
            ->andReturn(['url' => 'https://checkout.fedapay.test/reprise', 'token' => 'tok_4343']);

        // C'est tout l'intérêt du correctif : aucune transaction supplémentaire.
        $this->fedapay->shouldNotReceive('createTransaction');

        $resultat = $this->service()->createPayment($this->agent, $ticket);

        $this->assertSame('https://checkout.fedapay.test/reprise', $resultat['payment_url']);
        $this->assertSame('tok_4343', $resultat['token']);
        $this->assertSame('4343', $resultat['fedapay_transaction_id']);
        $this->assertSame('4343', (string) $ticket->fresh()->fedapay_transaction_id);
    }

    public function test_une_reprise_retrouve_la_transaction_meme_sans_identifiant_en_base(): void
    {
        // Réponse FedaPay perdue avant l'enregistrement de l'id : le billet n'a
        // pas de fedapay_transaction_id, on la retrouve par merchant_reference.
        $ticket = $this->creerTicket('en_attente');

        $this->fedapay->shouldReceive('findTransactionByExternalId')
            ->once()
            ->with((string) $ticket->id)
            ->andReturn([
                'id' => 4444,
                'status' => 'pending',
                'amount' => 5000,
                'merchant_reference' => (string) $ticket->id,
            ]);

        $this->fedapay->shouldReceive('generateToken')
            ->once()
            ->with('4444')
            ->andReturn(['url' => 'https://checkout.fedapay.test/retrouve']);

        $this->fedapay->shouldNotReceive('createTransaction');

        $resultat = $this->service()->createPayment($this->agent, $ticket);

        $this->assertSame('https://checkout.fedapay.test/retrouve', $resultat['payment_url']);
        $this->assertSame('4444', (string) $ticket->fresh()->fedapay_transaction_id);
    }

    public function test_une_transaction_deja_approuvee_est_confirmee_sans_lien(): void
    {
        $ticket = $this->creerTicket('en_attente', '4545');

        $this->fedapay->shouldReceive('getTransaction')
            ->once()
            ->with('4545')
            ->andReturn([
                'id' => 4545,
                'status' => 'approved',
                'amount' => 5000,
                'mode' => 'mtn_open',
                'merchant_reference' => (string) $ticket->id,
                'custom_metadata' => [
                    'ticket_id' => (string) $ticket->id,
                    'group_transaction_id' => (string) $ticket->transaction_id,
                ],
            ]);

        $this->fedapay->shouldNotReceive('createTransaction');
        $this->fedapay->shouldNotReceive('generateToken');

        $resultat = $this->service()->createPayment($this->agent, $ticket);

        $this->assertTrue($resultat['confirme']);
        $this->assertArrayNotHasKey('payment_url', $resultat);

        $ticket->refresh();
        $this->assertSame('payé', $ticket->statut_paiement);
        $this->assertStringStartsWith('PAX-', $ticket->code_unique);
    }

    public function test_une_transaction_echouee_demande_de_relancer_la_vente(): void
    {
        $ticket = $this->creerTicket('en_attente', '4646');

        $this->fedapay->shouldReceive('getTransaction')
            ->once()
            ->with('4646')
            ->andReturn([
                'id' => 4646,
                'status' => 'declined',
                'amount' => 5000,
                'merchant_reference' => (string) $ticket->id,
            ]);

        $this->fedapay->shouldNotReceive('createTransaction');
        $this->fedapay->shouldNotReceive('generateToken');

        try {
            $this->service()->createPayment($this->agent, $ticket);
            $this->fail('Une transaction échouée doit inviter à relancer la vente.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('paiement_mobile', $exception->errors());
            $this->assertStringContainsString('Annulez cette vente', $exception->errors()['paiement_mobile'][0]);
        }

        $this->assertSame('en_attente', $ticket->fresh()->statut_paiement);
    }

    private function service(): AgentVenteSaleService
    {
        return $this->app->make(AgentVenteSaleService::class);
    }

    private function creerTicket(string $statut, ?string $fedapayId = null): Ticket
    {
        return Ticket::create([
            'evenement_id' => $this->evenementId,
            'tarif_id' => $this->tarifId,
            'agent_vente_id' => $this->agent->id,
            'code_unique' => Ticket::genererCodeReservation(),
            'nom_acheteur' => 'Awa Kone',
            'email_acheteur' => 'awa@example.test',
            'whatsapp_acheteur' => '+22990000000',
            'nom_tarif' => 'Standard',
            'montant' => 5000,
            'quantite' => 1,
            'statut_paiement' => $statut,
            'methode_paiement' => 'mobile_money',
            'type_paiement' => 'mobile_money',
            'transaction_id' => 'AGENT-'.strtoupper(uniqid()),
            'idempotency_key' => 'retry-'.uniqid(),
            'fedapay_transaction_id' => $fedapayId,
            'date_achat' => now(),
        ]);
    }

    private function creerEvenement(): int
    {
        return DB::table('evenement')->insertGetId([
            'titre' => 'Concert test',
            'slug' => 'concert-'.uniqid(),
            'statut' => 'publie',
            'date_event' => now()->addDays(30),
            'capacite' => 100,
            'quota_vendu' => 0,
            'gratuit' => false,
            'ventes_fermees' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function creerTarif(int $evenementId): int
    {
        return DB::table('tarifs')->insertGetId([
            'evenement_id' => $evenementId,
            'nom' => 'Standard',
            'prix' => 5000,
            'quantite_disponible' => null,
            'quantite_vendue' => 0,
            'statut' => 'actif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function creerAgent(int $evenementId): AgentVente
    {
        $agentId = DB::table('agents_vente')->insertGetId([
            'nom' => 'Agent Vente',
            'email' => 'agent-vente-'.uniqid().'@example.test',
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
            $table->boolean('billetterie_ouverte')->default(true);
            $table->dateTime('billetterie_ouvre_le')->nullable();
            $table->boolean('ventes_especes')->default(true);
            $table->timestamps();
        });

        Schema::create('tarifs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->unsignedBigInteger('evenement_date_id')->nullable();
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
            $table->unsignedBigInteger('evenement_date_id')->nullable();
            $table->unsignedBigInteger('agent_vente_id')->nullable();
            $table->unsignedBigInteger('lot_physique_id')->nullable();
            $table->unsignedBigInteger('affiliate_event_link_id')->nullable();
            $table->string('source')->nullable();
            $table->string('code_unique')->unique();
            $table->string('qr_signature')->nullable();
            $table->string('email_acheteur')->nullable();
            $table->string('whatsapp_acheteur')->nullable();
            $table->string('telephone_acheteur')->nullable();
            $table->string('telephone_paiement')->nullable();
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
            $table->string('fedapay_transaction_id')->nullable();
            $table->dateTime('reservation_expire_le')->nullable();
            $table->boolean('utilise')->default(false);
            $table->boolean('annule')->default(false);
            $table->dateTime('annule_at')->nullable();
            $table->dateTime('date_achat')->nullable();
            $table->dateTime('expire_at')->nullable();
            $table->dateTime('email_envoye_at')->nullable();
            $table->unsignedInteger('email_tentatives')->default(0);
            $table->text('email_derniere_erreur')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->dateTime('dernier_renvoi_at')->nullable();
            $table->unsignedInteger('renvoi_count')->default(0);
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
    }
}
