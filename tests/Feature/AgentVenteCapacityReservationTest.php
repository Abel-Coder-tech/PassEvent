<?php

namespace Tests\Feature;

use App\Models\AgentVente;
use App\Models\Ticket;
use App\Services\AgentVenteSaleService;
use App\Services\FedapayService;
use App\Services\ReconciliationService;
use App\Services\WaitlistPromotionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

/**
 * Une vente mobile en attente doit réserver sa place dès sa création, comme le
 * flux d'achat public : la capacité est comptée dans quota_vendu et libérée si
 * le paiement n'aboutit pas. Le tarif, lui, n'est compté qu'à la confirmation.
 */
class AgentVenteCapacityReservationTest extends TestCase
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

        $this->evenementId = $this->creerEvenement(10);
        $this->tarifId = $this->creerTarif($this->evenementId, null);
        $this->agent = $this->creerAgent($this->evenementId);

        $this->fedapay = Mockery::mock(FedapayService::class);
        $this->fedapay->shouldReceive('getPublicKey')->andReturn('pk_test_capacity');
        $this->fedapay->shouldReceive('isSandbox')->andReturn(true);
        $this->app->instance(FedapayService::class, $this->fedapay);
    }

    public function test_une_vente_mobile_reserve_la_capacite_des_la_creation(): void
    {
        $resultat = $this->vendreMobile();

        $ticket = $resultat['ticket']->fresh();

        $this->assertSame('en_attente', $ticket->statut_paiement);
        $this->assertNotNull($ticket->reservation_expire_le);
        $this->assertStringStartsWith(Ticket::PREFIXE_RESERVATION, $ticket->code_unique);

        // La place est réservée immédiatement, mais le tarif n'est pas encore vendu.
        $this->assertSame(1, (int) DB::table('evenement')->where('id', $this->evenementId)->value('quota_vendu'));
        $this->assertSame(0, (int) DB::table('tarifs')->where('id', $this->tarifId)->value('quantite_vendue'));
    }

    public function test_confirmer_une_vente_reservee_ne_double_pas_le_quota(): void
    {
        $resultat = $this->vendreMobile();
        $ticket = $resultat['ticket']->fresh();

        $ticket->update(['fedapay_transaction_id' => '9001']);

        $this->fedapay->shouldReceive('getTransaction')
            ->once()
            ->with('9001')
            ->andReturn([
                'id' => 9001,
                'status' => 'approved',
                'amount' => 5000,
                'mode' => 'mtn_open',
                'custom_metadata' => [
                    'ticket_id' => (string) $ticket->id,
                    'group_transaction_id' => (string) $ticket->transaction_id,
                ],
            ]);

        $confirmation = $this->service()->syncMobile($this->agent, $ticket);

        $this->assertTrue($confirmation['confirme']);

        $ticket->refresh();
        $this->assertSame('payé', $ticket->statut_paiement);
        $this->assertNull($ticket->reservation_expire_le);
        $this->assertStringStartsWith('PAX-', $ticket->code_unique);

        // La capacité n'est comptée qu'une seule fois : réservée puis confirmée.
        $this->assertSame(1, (int) DB::table('evenement')->where('id', $this->evenementId)->value('quota_vendu'));
        $this->assertSame(1, (int) DB::table('tarifs')->where('id', $this->tarifId)->value('quantite_vendue'));
    }

    public function test_annuler_une_vente_en_attente_libere_la_place(): void
    {
        $resultat = $this->vendreMobile();
        $ticket = $resultat['ticket']->fresh();

        $this->service()->cancelPending($this->agent, $ticket);

        $ticket->refresh();
        $this->assertTrue((bool) $ticket->annule);
        $this->assertNull($ticket->reservation_expire_le);
        $this->assertSame(0, (int) DB::table('evenement')->where('id', $this->evenementId)->value('quota_vendu'));
    }

    public function test_expirer_une_vente_en_attente_libere_la_place(): void
    {
        $resultat = $this->vendreMobile();
        $ticket = $resultat['ticket']->fresh();

        // Simule une vente créée il y a plus de 30 minutes.
        DB::table('ticket')->where('id', $ticket->id)->update([
            'date_achat' => now()->subMinutes(AgentVenteSaleService::DUREE_RESERVATION_MINUTES + 5),
        ]);

        $expires = $this->service()->expirePending($this->agent->fresh());

        $this->assertSame(1, $expires);

        $ticket->refresh();
        $this->assertNotNull($ticket->expire_at);
        $this->assertNull($ticket->reservation_expire_le);
        $this->assertSame(0, (int) DB::table('evenement')->where('id', $this->evenementId)->value('quota_vendu'));
    }

    public function test_la_capacite_est_bloquee_par_les_ventes_en_attente(): void
    {
        DB::table('evenement')->where('id', $this->evenementId)->update(['capacite' => 2]);

        $this->vendreMobile();
        $this->vendreMobile();

        try {
            $this->vendreMobile();
            $this->fail('Une troisième vente ne doit pas dépasser la capacité de l\'événement.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('evenement', $exception->errors());
            $this->assertStringContainsString('complet', strtolower($exception->errors()['evenement'][0]));
        }

        $this->assertSame(2, (int) DB::table('evenement')->where('id', $this->evenementId)->value('quota_vendu'));
        $this->assertSame(2, DB::table('ticket')->count());
    }

    public function test_le_stock_du_tarif_deduit_les_ventes_en_attente(): void
    {
        DB::table('tarifs')->where('id', $this->tarifId)->update(['quantite_disponible' => 2]);

        $this->vendreMobile();
        $this->vendreMobile();

        try {
            $this->vendreMobile();
            $this->fail('Le stock du tarif doit tenir compte des réservations en attente.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quantite', $exception->errors());
            $this->assertStringContainsString('insuffisante', $exception->errors()['quantite'][0]);
        }

        // Le tarif n'a pourtant encore comptabilisé aucune vente ferme.
        $this->assertSame(0, (int) DB::table('tarifs')->where('id', $this->tarifId)->value('quantite_vendue'));
    }

    public function test_la_purge_confirme_un_paiement_retrouve_sans_doubler_la_capacite(): void
    {
        $resultat = $this->vendreMobile();
        $ticket = $resultat['ticket']->fresh();

        // Réservation arrivée à expiration, mais FedaPay a bien encaissé.
        DB::table('ticket')->where('id', $ticket->id)->update([
            'fedapay_transaction_id' => '7777',
            'reservation_expire_le' => now()->subMinute(),
        ]);

        $reconciliation = Mockery::mock(ReconciliationService::class);
        $reconciliation->shouldReceive('verifier')
            ->once()
            ->with('7777')
            ->andReturn(['ok' => true, 'approuve' => true, 'statut' => 'approved']);
        $this->app->instance(ReconciliationService::class, $reconciliation);

        $promotion = Mockery::mock(WaitlistPromotionService::class);
        $promotion->shouldReceive('promouvoirEvenement')->andReturn(0);
        $this->app->instance(WaitlistPromotionService::class, $promotion);

        $this->artisan('tickets:purger-en-attente')->assertSuccessful();

        $ticket->refresh();
        $this->assertSame('payé', $ticket->statut_paiement);
        $this->assertNull($ticket->reservation_expire_le);

        // Capacité réservée puis confirmée : toujours une seule place.
        $this->assertSame(1, (int) DB::table('evenement')->where('id', $this->evenementId)->value('quota_vendu'));
        $this->assertSame(1, (int) DB::table('tarifs')->where('id', $this->tarifId)->value('quantite_vendue'));
    }

    public function test_la_reconciliation_ne_recompte_pas_une_vente_reservee(): void
    {
        $resultat = $this->vendreMobile();
        $ticket = $resultat['ticket']->fresh();

        // Pas d'email : évite l'envoi réel du billet en fin de confirmation.
        DB::table('ticket')->where('id', $ticket->id)->update(['email_acheteur' => null]);

        $reconciliation = $this->app->make(ReconciliationService::class);
        $resultatConfirmation = $reconciliation->confirmerTickets(
            collect([$ticket->fresh()]),
            'tx-reconciliation',
            'mtn',
            null,
            false
        );

        $this->assertTrue($resultatConfirmation['success']);
        $this->assertSame(1, $resultatConfirmation['confirmes']);

        $ticket->refresh();
        $this->assertSame('payé', $ticket->statut_paiement);
        $this->assertNull($ticket->reservation_expire_le);
        $this->assertSame(1, (int) DB::table('evenement')->where('id', $this->evenementId)->value('quota_vendu'));
        $this->assertSame(1, (int) DB::table('tarifs')->where('id', $this->tarifId)->value('quantite_vendue'));
    }

    private function vendreMobile(): array
    {
        return $this->service()->create($this->agent, [
            'nom_acheteur' => 'Awa Kone',
            'email_acheteur' => 'awa@example.test',
            'whatsapp_acheteur' => '+22990000000',
            'tarif_id' => $this->tarifId,
            'methode_paiement' => 'mobile_money',
            'quantite' => 1,
        ]);
    }

    private function service(): AgentVenteSaleService
    {
        return $this->app->make(AgentVenteSaleService::class);
    }

    private function creerEvenement(int $capacite): int
    {
        return DB::table('evenement')->insertGetId([
            'titre' => 'Concert test',
            'slug' => 'concert-'.uniqid(),
            'statut' => 'publie',
            'date_event' => now()->addDays(30),
            'capacite' => $capacite,
            'quota_vendu' => 0,
            'gratuit' => false,
            'ventes_fermees' => false,
            'ventes_especes' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function creerTarif(int $evenementId, ?int $quantiteDisponible): int
    {
        return DB::table('tarifs')->insertGetId([
            'evenement_id' => $evenementId,
            'nom' => 'Standard',
            'prix' => 5000,
            'quantite_disponible' => $quantiteDisponible,
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
            $table->boolean('ventes_especes')->default(true);
            $table->timestamps();
        });

        Schema::create('evenement_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->dateTime('date_debut')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->unsignedInteger('capacite')->nullable();
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

        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id')->nullable();
            $table->unsignedBigInteger('evenement_id')->nullable();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('affiliate_payout_id')->nullable();
            $table->string('statut')->default('en_attente');
            $table->timestamps();
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
