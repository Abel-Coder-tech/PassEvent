<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Services\TicketScanService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Vérifie que le type de billet (nom du tarif : SIMPLE, STANDARD, VIP, ...)
 * est renvoyé dans tous les verdicts où le billet est identifié.
 */
class TicketScanServiceTest extends TestCase
{
    private int $evenementId;

    private Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        $this->evenementId = $this->creerEvenement();
        $this->agent = $this->creerAgent($this->evenementId);
    }

    public function test_un_scan_valide_renvoie_le_type_du_billet(): void
    {
        $ticketId = $this->creerTicket($this->evenementId, 'PAX-VALID', 'VIP');

        $resultat = app(TicketScanService::class)->verifier($this->agent, 'PAX-VALID');

        $this->assertTrue($resultat['success']);
        $this->assertSame('valide', $resultat['resultat']);
        $this->assertSame('VIP', $resultat['ticket']['nom_tarif']);
        $this->assertSame('Awa Kone', $resultat['ticket']['nom']);
        $this->assertSame('awa@example.test', $resultat['ticket']['email']);

        $this->assertDatabaseHas('logs', [
            'ticket_id' => $ticketId,
            'agent_id' => $this->agent->id,
        ]);
    }

    public function test_un_ticket_non_paye_est_traite_comme_introuvable(): void
    {
        $this->creerTicket($this->evenementId, 'PAX-UNPAID', 'SIMPLE', 'en_attente');

        $resultat = app(TicketScanService::class)->verifier($this->agent, 'PAX-UNPAID');

        $this->assertFalse($resultat['success']);
        $this->assertSame('invalide', $resultat['resultat']);
        $this->assertSame('Ticket introuvable', $resultat['raison']);
        $this->assertNull($resultat['ticket']);
    }

    public function test_un_paiement_echoue_est_traite_comme_introuvable(): void
    {
        $this->creerTicket($this->evenementId, 'PAX-FAILED', 'STANDARD', 'échoué');

        $resultat = app(TicketScanService::class)->verifier($this->agent, 'PAX-FAILED');

        $this->assertFalse($resultat['success']);
        $this->assertSame('Ticket introuvable', $resultat['raison']);
        $this->assertNull($resultat['ticket']);
    }

    public function test_un_ticket_rembourse_est_refuse_comme_rembourse(): void
    {
        $this->creerTicket($this->evenementId, 'PAX-REFUND', 'VIP', 'remboursé');

        $resultat = app(TicketScanService::class)->verifier($this->agent, 'PAX-REFUND');

        $this->assertFalse($resultat['success']);
        $this->assertSame('Ticket remboursé', $resultat['raison']);
        $this->assertSame('VIP', $resultat['ticket']['nom_tarif']);
    }

    public function test_un_ticket_annule_renvoie_le_type_du_billet(): void
    {
        $this->creerTicket($this->evenementId, 'PAX-CANCEL', 'STANDARD', 'payé', annule: true);

        $resultat = app(TicketScanService::class)->verifier($this->agent, 'PAX-CANCEL');

        $this->assertFalse($resultat['success']);
        $this->assertSame('Ticket annulé', $resultat['raison']);
        $this->assertSame('STANDARD', $resultat['ticket']['nom_tarif']);
    }

    public function test_un_doublon_renvoie_le_type_du_billet(): void
    {
        $this->creerTicket($this->evenementId, 'PAX-DOUBLE', 'VIP');

        $service = app(TicketScanService::class);
        $premier = $service->verifier($this->agent, 'PAX-DOUBLE');
        $second = $service->verifier($this->agent, 'PAX-DOUBLE');

        $this->assertTrue($premier['success']);
        $this->assertSame('deja_utilise', $second['resultat']);
        $this->assertSame('VIP', $second['ticket']['nom_tarif']);
    }

    public function test_un_billet_sans_nom_de_tarif_retombe_sur_standard(): void
    {
        $this->creerTicket($this->evenementId, 'PAX-NOLABEL', null);

        $resultat = app(TicketScanService::class)->verifier($this->agent, 'PAX-NOLABEL');

        $this->assertTrue($resultat['success']);
        $this->assertSame('Standard', $resultat['ticket']['nom_tarif']);
    }

    public function test_un_billet_d_un_autre_evenement_ne_divulgue_aucune_donnee(): void
    {
        $autreEvenement = $this->creerEvenement('Autre événement');
        $this->creerTicket($autreEvenement, 'PAX-WRONG', 'VIP');

        $resultat = app(TicketScanService::class)->verifier($this->agent, 'PAX-WRONG');

        $this->assertFalse($resultat['success']);
        $this->assertSame('Événement non autorisé', $resultat['raison']);
        $this->assertNull($resultat['ticket']);
    }

    public function test_un_code_inconnu_ne_divulgue_aucune_donnee(): void
    {
        $resultat = app(TicketScanService::class)->verifier($this->agent, 'PAX-UNKNOWN');

        $this->assertFalse($resultat['success']);
        $this->assertSame('Ticket introuvable', $resultat['raison']);
        $this->assertNull($resultat['ticket']);
    }

    public function test_un_billet_du_jour_est_valide_le_jour_prevu(): void
    {
        $jourDuJour = DB::table('evenement_dates')
            ->where('evenement_id', $this->evenementId)
            ->value('id');

        $this->creerTicket($this->evenementId, 'PAX-DAY-TODAY', 'SIMPLE', evenementDateId: $jourDuJour);

        $resultat = app(TicketScanService::class)->verifier($this->agent, 'PAX-DAY-TODAY');

        $this->assertTrue($resultat['success']);
        $this->assertSame('valide', $resultat['resultat']);
    }

    public function test_un_billet_d_un_jour_futur_est_refuse(): void
    {
        $jourFutur = $this->creerJour($this->evenementId, now()->addDay()->setTime(10, 0), 1);
        $this->creerTicket($this->evenementId, 'PAX-DAY-FUTUR', 'SIMPLE', evenementDateId: $jourFutur);

        $resultat = app(TicketScanService::class)->verifier($this->agent, 'PAX-DAY-FUTUR');

        $this->assertFalse($resultat['success']);
        $this->assertSame('invalide', $resultat['resultat']);
        $this->assertSame('Billet valable un autre jour', $resultat['raison']);
        $this->assertStringContainsString('Jour 2', $resultat['message']);
    }

    public function test_un_billet_d_un_jour_passe_est_refuse(): void
    {
        $jourPasse = $this->creerJour($this->evenementId, now()->subDay()->setTime(10, 0), 1);
        $this->creerTicket($this->evenementId, 'PAX-DAY-PASSE', 'SIMPLE', evenementDateId: $jourPasse);

        $resultat = app(TicketScanService::class)->verifier($this->agent, 'PAX-DAY-PASSE');

        $this->assertFalse($resultat['success']);
        $this->assertSame('invalide', $resultat['resultat']);
        $this->assertSame('Billet valable un autre jour', $resultat['raison']);
        $this->assertStringContainsString("n'est plus valable", $resultat['message']);
    }

    public function test_un_billet_d_un_autre_jour_ne_consomme_pas_le_pack(): void
    {
        // Billet jour (demain) refusé aujourd'hui : le ticket n'est pas marqué
        // comme scanné et reste utilisable demain.
        $jourFutur = $this->creerJour($this->evenementId, now()->addDay()->setTime(10, 0), 1);
        $this->creerTicket($this->evenementId, 'PAX-DAY-RETRY', 'SIMPLE', evenementDateId: $jourFutur);

        $service = app(TicketScanService::class);
        $aujourdHui = $service->verifier($this->agent, 'PAX-DAY-RETRY');
        $this->assertFalse($aujourdHui['success']);

        Carbon::setTestNow(now()->addDay()->setTime(10, 30));
        try {
            $demain = $service->verifier($this->agent, 'PAX-DAY-RETRY');
        } finally {
            Carbon::setTestNow();
        }

        $this->assertTrue($demain['success']);
        $this->assertSame('valide', $demain['resultat']);
    }

    public function test_un_pack_est_valable_une_fois_par_jour(): void
    {
        $this->creerJour($this->evenementId, now()->addDay()->setTime(10, 0), 1);
        $this->creerTicket($this->evenementId, 'PAX-PACK-MULTI', 'PACK');

        $service = app(TicketScanService::class);

        $jour1 = $service->verifier($this->agent, 'PAX-PACK-MULTI');
        $this->assertTrue($jour1['success']);

        $doublonJour1 = $service->verifier($this->agent, 'PAX-PACK-MULTI');
        $this->assertFalse($doublonJour1['success']);
        $this->assertSame('deja_utilise', $doublonJour1['resultat']);

        Carbon::setTestNow(now()->addDay()->setTime(10, 30));
        try {
            $jour2 = $service->verifier($this->agent, 'PAX-PACK-MULTI');
            $this->assertTrue($jour2['success']);
            $this->assertSame('valide', $jour2['resultat']);

            $doublonJour2 = $service->verifier($this->agent, 'PAX-PACK-MULTI');
            $this->assertSame('deja_utilise', $doublonJour2['resultat']);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function creerJour(int $evenementId, Carbon $dateDebut, int $ordre): int
    {
        return DB::table('evenement_dates')->insertGetId([
            'evenement_id' => $evenementId,
            'date_debut' => $dateDebut,
            'ordre' => $ordre,
        ]);
    }

    private function creerEvenement(string $titre = 'Concert test'): int
    {
        $evenementId = DB::table('evenement')->insertGetId([
            'titre' => $titre,
            'slug' => 'concert-'.uniqid(),
            'statut' => 'publie',
            'date_event' => now(),
            'gratuit' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('evenement_dates')->insert([
            'evenement_id' => $evenementId,
            'date_debut' => now()->subMinutes(30),
            'ordre' => 0,
        ]);

        return $evenementId;
    }

    private function creerAgent(int $evenementId): Agent
    {
        $agentId = DB::table('agents')->insertGetId([
            'nom' => 'Agent Scan',
            'email' => 'agent-scan-'.uniqid().'@example.test',
            'password' => 'secret',
            'evenement_id' => $evenementId,
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Agent::findOrFail($agentId);
    }

    private function creerTicket(
        int $evenementId,
        string $code,
        ?string $nomTarif,
        string $statut = 'payé',
        bool $annule = false,
        ?int $evenementDateId = null
    ): int {
        return DB::table('ticket')->insertGetId([
            'evenement_id' => $evenementId,
            'evenement_date_id' => $evenementDateId,
            'code_unique' => $code,
            'nom_acheteur' => 'Awa Kone',
            'email_acheteur' => 'awa@example.test',
            'nom_tarif' => $nomTarif,
            'montant' => 0,
            'quantite' => 1,
            'statut_paiement' => $statut,
            'transaction_id' => 'TX-'.uniqid(),
            'annule' => $annule,
            'date_achat' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createTables(): void
    {
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
            $table->unsignedInteger('capacite')->nullable();
        });

        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedBigInteger('evenement_id')->nullable();
            $table->string('code_acces', 6)->nullable();
            $table->boolean('actif')->default(true);
            $table->unsignedInteger('tentatives_code')->default(0);
            $table->timestamp('bloque_jusqua')->nullable();
            $table->timestamp('dernier_acces')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->unsignedBigInteger('evenement_date_id')->nullable();
            $table->string('code_unique')->unique();
            $table->string('email_acheteur')->nullable();
            $table->string('nom_acheteur')->nullable();
            $table->string('nom_tarif')->nullable();
            $table->decimal('montant', 10, 2)->default(0);
            $table->unsignedInteger('quantite')->default(1);
            $table->string('statut_paiement')->default('payé');
            $table->string('transaction_id')->nullable();
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
            $table->timestamp('created_at')->nullable();
        });
    }
}
