<?php

namespace Tests\Feature;

use App\Jobs\SendTicketEmailJob;
use App\Models\Ticket;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Un billet non payé (en attente, échoué, remboursé), annulé ou expiré ne doit
 * jamais être envoyé par email, ni marqué comme envoyé.
 */
class TicketDeliverabilityTest extends TestCase
{
    private int $evenementId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        $this->evenementId = $this->creerEvenement();
    }

    public function test_est_delivrable_refuse_tous_les_statuts_non_payes(): void
    {
        $this->assertFalse($this->ticket('en_attente')->estDelivrable());
        $this->assertFalse($this->ticket('échoué')->estDelivrable());
        $this->assertFalse($this->ticket('remboursé')->estDelivrable());
        $this->assertFalse($this->ticket('payé', annule: true)->estDelivrable());
        $this->assertFalse($this->ticket('payé', expireAt: now()->subMinute())->estDelivrable());
        $this->assertTrue($this->ticket('payé')->estDelivrable());
    }

    public function test_le_job_ignore_les_billets_non_payes_et_ne_les_marque_pas_envoyes(): void
    {
        Mail::fake();

        $nonPayes = collect([
            $this->ticket('en_attente'),
            $this->ticket('échoué'),
            $this->ticket('remboursé'),
        ]);

        (new SendTicketEmailJob($nonPayes->pluck('id')->all(), 'acheteur@example.test'))->handle();

        Mail::assertNothingSent();

        foreach ($nonPayes as $ticket) {
            $this->assertNull($ticket->fresh()->email_envoye_at);
            $this->assertSame(0, $ticket->fresh()->email_tentatives);
        }
    }

    public function test_le_job_n_envoie_que_les_billets_delivrables_du_lot(): void
    {
        Mail::fake();

        $paye = $this->ticket('payé');
        $enAttente = $this->ticket('en_attente');

        (new SendTicketEmailJob([$paye->id, $enAttente->id], 'acheteur@example.test'))->handle();

        $this->assertNotNull($paye->fresh()->email_envoye_at);
        $this->assertNull($enAttente->fresh()->email_envoye_at);
    }

    public function test_le_job_ne_marque_rien_si_tout_le_lot_est_non_delivrable(): void
    {
        Mail::fake();

        $annule = $this->ticket('payé', annule: true);

        (new SendTicketEmailJob([$annule->id], 'acheteur@example.test'))->handle();

        Mail::assertNothingSent();
        $this->assertNull($annule->fresh()->email_envoye_at);
    }

    private function ticket(
        string $statut,
        bool $annule = false,
        ?\DateTimeInterface $expireAt = null
    ): Ticket {
        $id = DB::table('ticket')->insertGetId([
            'evenement_id' => $this->evenementId,
            'code_unique' => 'PAX-'.uniqid(),
            'nom_acheteur' => 'Awa Kone',
            'email_acheteur' => 'awa@example.test',
            'nom_tarif' => 'Standard',
            'montant' => 5000,
            'quantite' => 1,
            'statut_paiement' => $statut,
            'transaction_id' => 'TX-'.uniqid(),
            'annule' => $annule,
            'expire_at' => $expireAt,
            'date_achat' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Ticket::findOrFail($id);
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
        foreach (['ticket', 'evenement_dates', 'evenement', 'tarif'] as $table) {
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

        Schema::create('tarif', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->string('nom');
            $table->decimal('prix', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('ticket', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->unsignedBigInteger('tarif_id')->nullable();
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
            $table->dateTime('email_envoye_at')->nullable();
            $table->unsignedInteger('email_tentatives')->default(0);
            $table->text('email_derniere_erreur')->nullable();
            $table->timestamps();
        });
    }
}
