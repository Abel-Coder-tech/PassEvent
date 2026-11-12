<?php

namespace Tests\Feature;

use App\Models\Evenement;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EvenementModificationLockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('evenement', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date_event');
        });

        Schema::create('evenement_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->dateTime('date_debut');
            $table->integer('ordre')->default(0);
            $table->unsignedInteger('capacite')->nullable();
        });

        Schema::create('ticket', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->unsignedBigInteger('evenement_date_id')->nullable();
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->string('type_operation');
            $table->json('details')->nullable();
            $table->dateTime('created_at');
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Schema::dropIfExists('logs');
        Schema::dropIfExists('ticket');
        Schema::dropIfExists('evenement_dates');
        Schema::dropIfExists('evenement');

        parent::tearDown();
    }

    public function test_a_successful_scan_locks_only_the_scanned_day(): void
    {
        Carbon::setTestNow('2026-11-09 12:00:00');

        $evenement = $this->creerEvenement([
            '2026-11-09 10:00:00',
            '2026-11-10 10:00:00',
            '2026-11-11 10:00:00',
        ]);
        $this->ajouterScan($evenement, '2026-11-09 11:00:00', 'valide');

        $this->assertSame(['2026-11-09'], $evenement->joursAvecScansReussis()->all());
        $this->assertFalse($evenement->modificationGlobaleVerrouillee());
    }

    public function test_invalid_scans_do_not_lock_event_dates(): void
    {
        $evenement = $this->creerEvenement(['2026-11-09 10:00:00']);
        $this->ajouterScan($evenement, '2026-11-09 11:00:00', 'invalide');

        $this->assertTrue($evenement->joursAvecScansReussis()->isEmpty());
        $this->assertFalse($evenement->modificationGlobaleVerrouillee());
    }

    public function test_event_locks_twelve_hours_after_the_last_scan_window_ends(): void
    {
        $evenement = $this->creerEvenement([
            '2026-11-09 10:00:00',
            '2026-11-10 10:00:00',
        ]);
        $this->ajouterScan($evenement, '2026-11-09 11:00:00', 'valide');

        Carbon::setTestNow('2026-11-11 03:59:59');
        $this->assertFalse($evenement->modificationGlobaleVerrouillee());

        Carbon::setTestNow('2026-11-11 04:00:00');
        $this->assertTrue($evenement->modificationGlobaleVerrouillee());
    }

    public function test_event_without_successful_scans_remains_editable_after_its_dates(): void
    {
        $evenement = $this->creerEvenement(['2026-11-09 10:00:00']);
        Carbon::setTestNow('2026-11-12 10:00:00');

        $this->assertFalse($evenement->modificationGlobaleVerrouillee());
    }

    public function test_jour_scan_actuel_couvre_une_session_qui_traverse_minuit(): void
    {
        $evenement = $this->creerEvenement(['2026-11-09 20:00:00']);

        Carbon::setTestNow('2026-11-10 00:30:00');

        $session = $evenement->jourScanActuel();

        $this->assertNotNull($session);
        $this->assertSame('2026-11-09 20:00:00', $session->date_debut->format('Y-m-d H:i:s'));
    }

    public function test_jour_scan_actuel_renvoie_la_prochaine_session_du_jour(): void
    {
        $evenement = $this->creerEvenement(['2026-11-10 20:00:00']);

        Carbon::setTestNow('2026-11-10 10:00:00');

        $session = $evenement->jourScanActuel();

        $this->assertNotNull($session);
        $this->assertTrue($session->date_debut->isFuture());
    }

    public function test_jour_scan_actuel_renvoie_la_session_terminee_du_jour(): void
    {
        $evenement = $this->creerEvenement(['2026-11-10 08:00:00']);

        Carbon::setTestNow('2026-11-10 15:00:00');

        $session = $evenement->jourScanActuel();

        $this->assertNotNull($session);
        $this->assertTrue($session->finFenetreScan()->isPast());
    }

    public function test_jour_scan_actuel_est_null_hors_session(): void
    {
        $evenement = $this->creerEvenement(['2026-11-10 10:00:00']);

        Carbon::setTestNow('2026-11-12 10:00:00');

        $this->assertNull($evenement->jourScanActuel());
    }

    public function test_scan_termine_apres_la_fenetre_et_non_au_debut(): void
    {
        $evenement = $this->creerEvenement(['2026-11-10 20:00:00']);

        Carbon::setTestNow('2026-11-10 22:00:00');
        $this->assertFalse($evenement->scanTermine());

        Carbon::setTestNow('2026-11-11 02:30:00');
        $this->assertTrue($evenement->scanTermine());
    }

    public function test_un_scan_apres_minuit_reste_bloque_pour_la_meme_session(): void
    {
        $evenement = $this->creerEvenement(['2026-11-09 20:00:00']);
        $ticketId = DB::table('ticket')->insertGetId([
            'evenement_id' => $evenement->id,
        ]);
        $ticket = \App\Models\Ticket::findOrFail($ticketId);

        DB::table('logs')->insert([
            'ticket_id' => $ticketId,
            'type_operation' => 'scan',
            'details' => json_encode(['resultat' => 'valide']),
            'created_at' => '2026-11-09 23:45:00',
        ]);

        $session = $evenement->dates()->first();

        $this->assertTrue($ticket->dejaScanneEntre($session->date_debut, $session->finFenetreScan()));
        $this->assertFalse($ticket->dejaScanneEntre($session->date_debut->copy()->subHour(), $session->date_debut));
    }

    private function creerEvenement(array $dates): Evenement
    {
        $evenementId = DB::table('evenement')->insertGetId([
            'date_event' => $dates[0],
        ]);

        foreach ($dates as $ordre => $date) {
            DB::table('evenement_dates')->insert([
                'evenement_id' => $evenementId,
                'date_debut' => $date,
                'ordre' => $ordre,
            ]);
        }

        return Evenement::findOrFail($evenementId);
    }

    private function ajouterScan(Evenement $evenement, string $createdAt, string $resultat): void
    {
        $ticketId = DB::table('ticket')->insertGetId([
            'evenement_id' => $evenement->id,
        ]);

        DB::table('logs')->insert([
            'ticket_id' => $ticketId,
            'type_operation' => 'scan',
            'details' => json_encode(['resultat' => $resultat]),
            'created_at' => $createdAt,
        ]);
    }
}
