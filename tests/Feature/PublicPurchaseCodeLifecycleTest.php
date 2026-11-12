<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\Ticket;
use App\Services\PaymentMonitoringService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Parcours public réel (EvenementPublicController::achat) : une réservation
 * n'obtient son code PAX- qu'après confirmation. Tant qu'elle est en attente,
 * elle ne porte qu'un code TMP- non scannable.
 */
class PublicPurchaseCodeLifecycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        Queue::fake();
        Mail::fake();
        $this->spy(PaymentMonitoringService::class);
    }

    public function test_un_evenement_gratuit_emet_un_code_definitif_immediatement(): void
    {
        $evenement = $this->creerEvenement(gratuit: true);
        $this->creerTarif($evenement->id, 'Gratuit', 0);

        $response = $this->post(route('evenements.achat', $evenement), [
            'nom_acheteur' => 'Awa Kone',
            'email_acheteur' => 'awa@example.test',
            'whatsapp_acheteur' => '9700000000',
            'quantite' => 1,
        ]);

        $ticket = Ticket::firstOrFail();

        $response->assertRedirect();
        $this->assertSame('payé', $ticket->statut_paiement);
        $this->assertStringStartsWith('PAX-', $ticket->code_unique);
        $this->assertTrue($ticket->aUnCodeDefinitif());
        $this->assertTrue($ticket->estDelivrable());
    }

    public function test_une_reservation_payante_reste_non_scannable_jusqu_a_confirmation(): void
    {
        $evenement = $this->creerEvenement(gratuit: false);
        $tarifId = $this->creerTarif($evenement->id, 'Standard', 5000);

        $response = $this->post(route('evenements.achat', $evenement), [
            'nom_acheteur' => 'Awa Kone',
            'email_acheteur' => 'awa@example.test',
            'whatsapp_acheteur' => '9700000000',
            'quantite' => 1,
            'tarif_id' => $tarifId,
        ]);

        $ticket = Ticket::firstOrFail();

        $response->assertRedirect(route('paiement.show', $ticket->id));
        $this->assertSame('en_attente', $ticket->statut_paiement);
        $this->assertStringStartsWith(Ticket::PREFIXE_RESERVATION, $ticket->code_unique);
        $this->assertFalse($ticket->aUnCodeDefinitif());
        $this->assertFalse($ticket->estDelivrable());
        $this->assertNull(Ticket::trouverParCodeSaisi($ticket->code_unique));

        // Confirmation du paiement (callback/webhook FedaPay) : le code définitif
        // est émis par TicketObserver, le billet devient délivrable et scannable.
        $ticket->update([
            'statut_paiement' => 'payé',
            'reservation_expire_le' => null,
        ]);
        $ticket->refresh();

        $this->assertStringStartsWith('PAX-', $ticket->code_unique);
        $this->assertTrue($ticket->aUnCodeDefinitif());
        $this->assertTrue($ticket->estDelivrable());
        $this->assertNotNull(Ticket::trouverParCodeSaisi($ticket->code_unique));
    }

    private function creerEvenement(bool $gratuit): Evenement
    {
        $id = DB::table('evenement')->insertGetId([
            'titre' => 'Concert public',
            'slug' => 'concert-public-'.uniqid(),
            'date_event' => now()->addMonth(),
            'lieu' => 'Cotonou',
            'statut' => 'publié',
            'capacite' => 100,
            'quota_vendu' => 0,
            'gratuit' => $gratuit,
            'ventes_fermees' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('evenement_dates')->insert([
            'evenement_id' => $id,
            'date_debut' => now()->addMonth(),
            'ordre' => 1,
        ]);

        return Evenement::findOrFail($id);
    }

    private function creerTarif(int $evenementId, string $nom, float $prix): int
    {
        return DB::table('tarifs')->insertGetId([
            'evenement_id' => $evenementId,
            'nom' => $nom,
            'prix' => $prix,
            'statut' => 'actif',
            'quantite_disponible' => 100,
            'quantite_vendue' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createTables(): void
    {
        foreach (['ticket', 'evenement_dates', 'evenement', 'tarifs', 'affiliate_commissions', 'event_waitlist'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('evenement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('titre');
            $table->string('slug')->unique();
            $table->dateTime('date_event')->nullable();
            $table->string('lieu')->nullable();
            $table->string('statut')->default('publié');
            $table->unsignedInteger('capacite')->default(0);
            $table->unsignedInteger('quota_vendu')->default(0);
            $table->boolean('gratuit')->default(false);
            $table->boolean('ventes_fermees')->default(false);
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
            $table->string('statut')->default('actif');
            $table->unsignedInteger('quantite_disponible')->default(0);
            $table->unsignedInteger('quantite_vendue')->default(0);
            $table->timestamps();
        });

        Schema::create('event_waitlist', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->string('nom_acheteur');
            $table->string('email_acheteur');
            $table->string('whatsapp_acheteur')->nullable();
            $table->unsignedInteger('quantite')->default(1);
            $table->string('statut')->default('en_attente');
            $table->timestamps();
        });

        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->unsignedBigInteger('affiliate_id')->nullable();
            $table->string('type')->nullable();
            $table->string('statut')->default('en_attente');
            $table->unsignedBigInteger('affiliate_payout_id')->nullable();
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
            $table->string('fedapay_transaction_id')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->string('methode_paiement')->nullable();
            $table->string('type_paiement')->nullable();
            $table->string('code_promo_utilise')->nullable();
            $table->dateTime('reservation_expire_le')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->boolean('utilise')->default(false);
            $table->boolean('annule')->default(false);
            $table->dateTime('annule_at')->nullable();
            $table->dateTime('expire_at')->nullable();
            $table->dateTime('date_achat')->nullable();
            $table->dateTime('email_envoye_at')->nullable();
            $table->unsignedInteger('email_tentatives')->default(0);
            $table->text('email_derniere_erreur')->nullable();
            $table->dateTime('dernier_renvoi_at')->nullable();
            $table->unsignedInteger('renvoi_count')->default(0);
            $table->timestamps();
        });
    }
}
