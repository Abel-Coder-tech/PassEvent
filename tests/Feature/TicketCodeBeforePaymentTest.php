<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Services\AgentVenteSaleService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Règle métier : aucun code scannable n'existe avant la confirmation du
 * paiement. Une réservation non payée ne porte qu'un code TMP-, et le code
 * PAX- définitif est attribué au passage à « payé ».
 */
class TicketCodeBeforePaymentTest extends TestCase
{
    private int $evenementId;

    private int $tarifId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        $this->evenementId = $this->creerEvenement();
        $this->tarifId = DB::table('tarifs')->insertGetId([
            'evenement_id' => $this->evenementId,
            'nom' => 'Standard',
            'prix' => 5000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_une_reservation_non_payee_ne_porte_pas_de_code_scannable(): void
    {
        $ticket = $this->creerTicket('en_attente');

        $this->assertStringStartsWith(Ticket::PREFIXE_RESERVATION, $ticket->code_unique);
        $this->assertFalse($ticket->aUnCodeDefinitif());
        $this->assertFalse($ticket->estDelivrable());
        $this->assertSame($ticket->transaction_id, $ticket->referenceAffichage());

        // Un code TMP- ne peut jamais être normalisé en code PAX- scannable.
        $this->assertNull(Ticket::trouverParCodeSaisi($ticket->code_unique));
    }

    public function test_le_code_definitif_est_attribue_a_la_confirmation_du_paiement(): void
    {
        $ticket = $this->creerTicket('en_attente');
        $provisoire = $ticket->code_unique;

        $ticket->update(['statut_paiement' => 'payé']);

        $ticket->refresh();

        $this->assertNotSame($provisoire, $ticket->code_unique);
        $this->assertStringStartsWith('PAX-', $ticket->code_unique);
        $this->assertTrue($ticket->aUnCodeDefinitif());
        $this->assertTrue($ticket->estDelivrable());
        $this->assertNotNull(Ticket::trouverParCodeSaisi($ticket->code_unique));
    }

    public function test_un_billet_paye_cree_avec_un_placeholder_recoit_un_vrai_code(): void
    {
        $ticket = $this->creerTicket('payé', 'TMP');

        $this->assertStringStartsWith('PAX-', $ticket->fresh()->code_unique);
        $this->assertTrue($ticket->fresh()->aUnCodeDefinitif());
    }

    public function test_le_payload_agent_masque_le_code_tant_que_le_paiement_n_est_pas_confirme(): void
    {
        $service = app(AgentVenteSaleService::class);

        $enAttente = $this->creerTicket('en_attente');
        $payloadEnAttente = $service->ticketPayload($enAttente);

        $this->assertNull($payloadEnAttente['code_unique']);
        $this->assertSame($enAttente->transaction_id, $payloadEnAttente['reference']);
        $this->assertFalse($payloadEnAttente['paye']);

        $enAttente->update(['statut_paiement' => 'payé']);
        $payloadPaye = $service->ticketPayload($enAttente->fresh());

        $this->assertStringStartsWith('PAX-', $payloadPaye['code_unique']);
        $this->assertSame($payloadPaye['code_unique'], $payloadPaye['reference']);
        $this->assertTrue($payloadPaye['paye']);
    }

    public function test_un_billet_echoue_garde_un_code_non_scannable(): void
    {
        $ticket = $this->creerTicket('en_attente');

        $ticket->update(['statut_paiement' => 'échoué']);
        $ticket->refresh();

        $this->assertStringStartsWith(Ticket::PREFIXE_RESERVATION, $ticket->code_unique);
        $this->assertFalse($ticket->aUnCodeDefinitif());
        $this->assertNull(Ticket::trouverParCodeSaisi($ticket->code_unique));
    }

    private function creerTicket(string $statut, ?string $code = null): Ticket
    {
        return Ticket::create([
            'evenement_id' => $this->evenementId,
            'tarif_id' => $this->tarifId,
            'code_unique' => $code ?? Ticket::genererCodeReservation(),
            'nom_acheteur' => 'Awa Kone',
            'email_acheteur' => 'awa@example.test',
            'nom_tarif' => 'Standard',
            'montant' => 5000,
            'quantite' => 1,
            'statut_paiement' => $statut,
            'transaction_id' => 'GRP-'.strtoupper(uniqid()),
            'date_achat' => now(),
        ]);
    }

    private function creerEvenement(): int
    {
        return DB::table('evenement')->insertGetId([
            'titre' => 'Concert test',
            'slug' => 'concert-'.uniqid(),
            'statut' => 'publie',
            'date_event' => now(),
            'gratuit' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createTables(): void
    {
        foreach (['ticket', 'evenement_dates', 'evenement', 'tarifs', 'affiliate_commissions'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('evenement', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->string('slug')->unique();
            $table->dateTime('date_event')->nullable();
            $table->string('statut')->default('publie');
            $table->boolean('gratuit')->default(false);
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
            $table->unsignedBigInteger('agent_vente_id')->nullable();
            $table->string('code_unique')->unique();
            $table->string('email_acheteur')->nullable();
            $table->string('nom_acheteur')->nullable();
            $table->string('nom_tarif')->nullable();
            $table->decimal('montant', 10, 2)->default(0);
            $table->decimal('montant_reduction', 10, 2)->default(0);
            $table->unsignedInteger('quantite')->default(1);
            $table->string('statut_paiement')->default('payé');
            $table->string('transaction_id')->nullable();
            $table->string('methode_paiement')->nullable();
            $table->string('type_paiement')->nullable();
            $table->string('code_promo_utilise')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->boolean('annule')->default(false);
            $table->dateTime('annule_at')->nullable();
            $table->dateTime('expire_at')->nullable();
            $table->dateTime('date_achat')->nullable();
            $table->dateTime('email_envoye_at')->nullable();
            $table->unsignedInteger('email_tentatives')->default(0);
            $table->text('email_derniere_erreur')->nullable();
            $table->timestamps();
        });
    }
}
