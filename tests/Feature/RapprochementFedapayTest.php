<?php

namespace Tests\Feature;

use App\Mail\TicketEmail;
use App\Models\Log;
use App\Models\Ticket;
use App\Services\FedapayService;
use App\Services\ReconciliationService;
use App\Services\WaitlistPromotionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

/**
 * Filet de sécurité paiements : un paiement approuvé chez FedaPay ne doit
 * jamais être perdu parce que le webhook ou le retour navigateur n'a pas
 * transmis l'ID de transaction.
 */
class RapprochementFedapayTest extends TestCase
{
    private int $evenementId;

    private int $tarifId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        Queue::fake();
        config([
            'services.fedapay.webhook_secret' => null,
            'services.fedapay.secret_key' => 'sk_test_rapprochement',
        ]);

        $this->evenementId = DB::table('evenement')->insertGetId([
            'titre' => 'Evenement rapprochement',
            'slug' => 'evenement-rapprochement-'.uniqid(),
            'date_event' => now()->addDay(),
            'lieu' => 'Cotonou',
            'capacite' => 10,
            'quota_vendu' => 0,
            'statut' => 'publié',
            'gratuit' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->tarifId = DB::table('tarifs')->insertGetId([
            'evenement_id' => $this->evenementId,
            'nom' => 'Standard',
            'prix' => 1000,
            'quantite_disponible' => 10,
            'quantite_vendue' => 0,
            'statut' => 'actif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_un_paiement_approuve_confirme_un_billet_echoue_sans_id(): void
    {
        $ticket = $this->creerTicket('échoué', null, null);

        $this->simulerTransactionsApprouvees([
            $this->transactionApprouvee('114238512', $ticket, 1000),
        ]);

        $this->artisan('paiements:rapprocher-fedapay')->assertSuccessful();

        $ticket->refresh();

        $this->assertSame('payé', $ticket->statut_paiement);
        $this->assertSame('114238512', (string) $ticket->fedapay_transaction_id);
        $this->assertSame(1, (int) DB::table('evenement')->where('id', $this->evenementId)->value('quota_vendu'));
        $this->assertSame(1, (int) DB::table('tarifs')->where('id', $this->tarifId)->value('quantite_vendue'));
        $this->assertTrue($this->logExiste('paiement_rapproche'));
    }

    public function test_un_billet_confirme_par_rapprochement_recoit_son_email_et_est_trace(): void
    {
        Mail::fake();

        $ticket = $this->creerTicket('échoué', null, null, 'acheteur@example.com');

        $this->simulerTransactionsApprouvees([
            $this->transactionApprouvee('114238512', $ticket, 1000),
        ]);

        $this->artisan('paiements:rapprocher-fedapay')->assertSuccessful();

        $ticket->refresh();

        $this->assertSame('payé', $ticket->statut_paiement);
        $this->assertNotNull($ticket->email_envoye_at);
        $this->assertNull($ticket->email_derniere_erreur);

        Mail::assertSent(TicketEmail::class, fn (TicketEmail $mail) => $mail->hasTo('acheteur@example.com'));
    }

    public function test_renvoyer_email_reutilise_le_billet_sans_toucher_aux_compteurs(): void
    {
        Mail::fake();

        $ticket = $this->creerTicket('payé', '114238512', null, 'acheteur@example.com');
        $code = $ticket->code_unique;

        DB::table('evenement')->where('id', $this->evenementId)->update(['quota_vendu' => 1]);
        DB::table('tarifs')->where('id', $this->tarifId)->update(['quantite_vendue' => 1]);

        $resultat = app(ReconciliationService::class)->renvoyerEmail($ticket);

        $this->assertTrue($resultat['success']);
        Mail::assertSent(TicketEmail::class, fn (TicketEmail $mail) => $mail->hasTo('acheteur@example.com'));

        $ticket->refresh();

        $this->assertSame($code, $ticket->code_unique);
        $this->assertNotNull($ticket->email_envoye_at);
        $this->assertSame(1, DB::table('ticket')->count());
        $this->assertSame(1, (int) DB::table('evenement')->where('id', $this->evenementId)->value('quota_vendu'));
        $this->assertSame(1, (int) DB::table('tarifs')->where('id', $this->tarifId)->value('quantite_vendue'));
        $this->assertTrue($this->logExiste('renvoi_email'));
    }

    public function test_le_mode_dry_run_ne_modifie_aucun_billet(): void
    {
        $ticket = $this->creerTicket('échoué', null, null);

        $this->simulerTransactionsApprouvees([
            $this->transactionApprouvee('114238512', $ticket, 1000),
        ]);

        $this->artisan('paiements:rapprocher-fedapay', ['--dry-run' => true])->assertSuccessful();

        $ticket->refresh();

        $this->assertSame('échoué', $ticket->statut_paiement);
        $this->assertNull($ticket->fedapay_transaction_id);
        $this->assertSame(0, (int) DB::table('evenement')->where('id', $this->evenementId)->value('quota_vendu'));
    }

    public function test_un_paiement_sans_billet_est_journalise_sans_creation_automatique(): void
    {
        $this->simulerTransactionsApprouvees([
            [
                'id' => '99887766',
                'status' => 'approved',
                'amount' => 15000,
                'external_id' => 'INCONNU',
                'custom_metadata' => [],
                'mode' => 'mtn_open',
                'created_at' => now()->toIso8601String(),
            ],
        ]);

        $this->artisan('paiements:rapprocher-fedapay')->assertSuccessful();

        $this->assertSame(0, DB::table('ticket')->count());
        $this->assertTrue($this->logExiste('paiement_approuve_sans_billet'));
    }

    public function test_la_purge_verifie_fedapay_avant_de_liberer_une_reservation_sans_id(): void
    {
        // La place est déjà comptée : c'est une réservation en cours d'expiration.
        DB::table('evenement')->where('id', $this->evenementId)->update(['quota_vendu' => 1]);

        $ticket = $this->creerTicket('en_attente', null, now()->subMinute());

        $this->simulerTransactionsApprouvees([
            $this->transactionApprouvee('114238512', $ticket, 1000),
        ]);

        $promotion = Mockery::mock(WaitlistPromotionService::class);
        $promotion->shouldReceive('promouvoirEvenement')->andReturn(0);
        $this->app->instance(WaitlistPromotionService::class, $promotion);

        $this->artisan('tickets:purger-en-attente')->assertSuccessful();

        $ticket->refresh();

        $this->assertSame('payé', $ticket->statut_paiement);
        $this->assertSame('114238512', (string) $ticket->fedapay_transaction_id);
        $this->assertNull($ticket->reservation_expire_le);
        // La place réservée n'a été ni libérée ni recomptée.
        $this->assertSame(1, (int) DB::table('evenement')->where('id', $this->evenementId)->value('quota_vendu'));
        $this->assertSame(1, (int) DB::table('tarifs')->where('id', $this->tarifId)->value('quantite_vendue'));
        $this->assertTrue($this->logExiste('confirmation_ticket'));
    }

    public function test_un_lot_de_qr_codes_n_est_pas_signale_comme_orphelin(): void
    {
        $this->simulerTransactionsApprouvees([
            [
                'id' => '55443322',
                'status' => 'approved',
                'amount' => 5000,
                'external_id' => 'LOTAUTO-ABC123',
                'custom_metadata' => ['type' => 'lot_physique', 'reference' => 'LOTAUTO-ABC123'],
                'mode' => 'bank_card',
                'created_at' => now()->toIso8601String(),
            ],
        ]);

        $this->artisan('paiements:rapprocher-fedapay')->assertSuccessful();

        $this->assertSame(0, DB::table('ticket')->count());
        $this->assertFalse($this->logExiste('paiement_approuve_sans_billet'));
    }

    public function test_la_purge_conserve_la_reservation_si_fedapay_est_injoignable(): void
    {
        DB::table('evenement')->where('id', $this->evenementId)->update(['quota_vendu' => 1]);

        $ticket = $this->creerTicket('en_attente', null, now()->subMinute());

        $fedapay = Mockery::mock(FedapayService::class)->makePartial();
        $fedapay->shouldReceive('transactionsApprouvees')->andReturn([]);
        $fedapay->shouldReceive('rechercheIndisponible')->andReturn(true);
        $this->app->instance(FedapayService::class, $fedapay);

        $promotion = Mockery::mock(WaitlistPromotionService::class);
        $promotion->shouldReceive('promouvoirEvenement')->andReturn(0);
        $this->app->instance(WaitlistPromotionService::class, $promotion);

        $this->artisan('tickets:purger-en-attente')->assertSuccessful();

        $ticket->refresh();

        // Faute d'information fiable, la place reste réservée.
        $this->assertSame('en_attente', $ticket->statut_paiement);
        $this->assertNotNull($ticket->reservation_expire_le);
        $this->assertSame(1, (int) DB::table('evenement')->where('id', $this->evenementId)->value('quota_vendu'));
    }

    public function test_le_webhook_journalise_chaque_entree_meme_invalide(): void
    {
        $response = $this->postJson('/paiement/webhook', []);

        $response->assertStatus(400);

        $this->assertTrue($this->logExiste('webhook_recu'));
    }

    public function test_le_webhook_journalise_le_succes_de_confirmation(): void
    {
        $ticket = $this->creerTicket('en_attente', null, null);
        $groupe = (string) $ticket->transaction_id;

        $fedapay = Mockery::mock(FedapayService::class);
        $fedapay->shouldReceive('getTransaction')
            ->once()
            ->with('114238512')
            ->andReturn([
                'id' => '114238512',
                'status' => 'approved',
                'amount' => 1000,
                'currency' => ['iso' => 'XOF'],
                'mode' => 'mtn_open',
                'custom_metadata' => [
                    'ticket_id' => (string) $ticket->id,
                    'group_transaction_id' => $groupe,
                ],
            ]);
        $this->app->instance(FedapayService::class, $fedapay);

        $response = $this->postJson('/paiement/webhook', [
            'name' => 'transaction.updated',
            'data' => [
                'transaction' => [
                    'id' => '114238512',
                    'status' => 'approved',
                    'amount' => 1000,
                    'external_id' => (string) $ticket->id,
                    'mode' => 'mtn_open',
                    'custom_metadata' => [
                        'ticket_id' => (string) $ticket->id,
                        'group_transaction_id' => $groupe,
                    ],
                ],
            ],
        ]);

        $response->assertOk()->assertJson(['status' => 'ok']);

        $ticket->refresh();
        $this->assertSame('payé', $ticket->statut_paiement);
        $this->assertTrue($this->logExiste('webhook_recu'));
        $this->assertTrue($this->logExiste('webhook_paiement_confirme'));
    }

    // ---------- Aides ----------

    private function transactionApprouvee(string $transactionId, Ticket $ticket, float $montant): array
    {
        return [
            'id' => $transactionId,
            'status' => 'approved',
            'amount' => $montant,
            'external_id' => (string) $ticket->id,
            'custom_metadata' => [
                'ticket_id' => (string) $ticket->id,
                'group_transaction_id' => (string) $ticket->transaction_id,
            ],
            'mode' => 'mtn_open',
            'phone' => '+22901000000',
            'created_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $transactions
     */
    private function simulerTransactionsApprouvees(array $transactions): void
    {
        $fedapay = Mockery::mock(FedapayService::class)->makePartial();
        $fedapay->shouldReceive('transactionsApprouvees')->andReturn($transactions);
        $this->app->instance(FedapayService::class, $fedapay);
    }

    private function creerTicket(string $statut, ?string $fedapayId, $reservationExpireLe, ?string $email = null): Ticket
    {
        return Ticket::create([
            'evenement_id' => $this->evenementId,
            'tarif_id' => $this->tarifId,
            'source' => 'site',
            'code_unique' => Ticket::genererCodeReservation(),
            'email_acheteur' => $email,
            'nom_acheteur' => 'Client Test',
            'nom_tarif' => 'Standard',
            'montant' => 1000,
            'montant_reduction' => 0,
            'quantite' => 1,
            'statut_paiement' => $statut,
            'transaction_id' => 'GRP-'.strtoupper(uniqid()),
            'fedapay_transaction_id' => $fedapayId,
            'reservation_expire_le' => $reservationExpireLe,
            'annule' => false,
            'date_achat' => now()->subHour(),
        ]);
    }

    private function logExiste(string $action): bool
    {
        return Log::query()->get()->contains(
            fn (Log $log) => ($log->details['action'] ?? null) === $action
        );
    }

    private function createTables(): void
    {
        foreach (['logs', 'affiliate_commissions', 'ticket', 'tarifs', 'evenement_dates', 'evenement'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('evenement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('titre');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->dateTime('date_event')->nullable();
            $table->string('lieu')->nullable();
            $table->unsignedInteger('capacite')->default(0);
            $table->unsignedInteger('quota_vendu')->default(0);
            $table->string('statut')->default('publié');
            $table->boolean('gratuit')->default(false);
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

        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->unsignedBigInteger('affiliate_id')->nullable();
            $table->unsignedBigInteger('evenement_id')->nullable();
            $table->unsignedBigInteger('affiliate_event_link_id')->nullable();
            $table->string('type')->nullable();
            $table->decimal('base_montant', 10, 2)->default(0);
            $table->decimal('montant', 10, 2)->default(0);
            $table->decimal('pourcentage', 5, 2)->default(0);
            $table->string('statut')->default('en_attente');
            $table->dateTime('disponible_le')->nullable();
            $table->unsignedBigInteger('affiliate_payout_id')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->unsignedBigInteger('evenement_date_id')->nullable();
            $table->unsignedBigInteger('agent_vente_id')->nullable();
            $table->string('source')->nullable();
            $table->string('code_unique')->unique();
            $table->string('qr_signature')->nullable();
            $table->string('email_acheteur')->nullable();
            $table->string('telephone_acheteur')->nullable();
            $table->string('whatsapp_acheteur')->nullable();
            $table->string('telephone_paiement')->nullable();
            $table->string('nom_acheteur')->nullable();
            $table->string('nom_tarif')->nullable();
            $table->decimal('montant', 10, 2)->default(0);
            $table->decimal('montant_reduction', 10, 2)->default(0);
            $table->unsignedInteger('quantite')->default(1);
            $table->string('statut_paiement')->default('en_attente');
            $table->string('transaction_id')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->dateTime('reservation_expire_le')->nullable();
            $table->string('fedapay_transaction_id')->nullable();
            $table->string('methode_paiement')->nullable();
            $table->string('type_paiement')->nullable();
            $table->boolean('utilise')->default(false);
            $table->dateTime('date_achat')->nullable();
            $table->dateTime('email_envoye_at')->nullable();
            $table->unsignedInteger('email_tentatives')->default(0);
            $table->text('email_derniere_erreur')->nullable();
            $table->string('code_promo_utilise')->nullable();
            $table->unsignedBigInteger('lot_physique_id')->nullable();
            $table->unsignedBigInteger('affiliate_event_link_id')->nullable();
            $table->boolean('annule')->default(false);
            $table->dateTime('annule_at')->nullable();
            $table->dateTime('expire_at')->nullable();
            $table->dateTime('dernier_renvoi_at')->nullable();
            $table->unsignedInteger('renvoi_count')->default(0);
            $table->timestamps();
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->string('type_operation');
            $table->text('details')->nullable();
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }
}
