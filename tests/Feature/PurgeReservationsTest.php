<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\Tarif;
use App\Models\Ticket;
use App\Models\User;
use App\Services\FedapayService;
use App\Services\WaitlistPromotionService;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class PurgeReservationsTest extends TestCase
{
    private array $idsCrees = ['tickets' => [], 'evenements' => [], 'tarifs' => [], 'users' => []];

    protected function tablesDisponibles(): bool
    {
        return Schema::hasTable('users')
            && Schema::hasTable('evenement')
            && Schema::hasTable('tarifs')
            && Schema::hasTable('ticket');
    }

    public function test_reservation_expiree_est_liberee_et_place_restauree(): void
    {
        if (! $this->tablesDisponibles()) {
            $this->markTestSkipped('Tables indisponibles sur ce driver.');
        }

        $this->simulerFedaPay('expired');

        $evenement = $this->creerEvenement(quotaVendu: 1);
        $ticket = $this->creerTicket($evenement, 'T-EXPIR', now()->subMinutes(30));

        $this->artisan('tickets:purger-en-attente')->assertSuccessful();

        $ticket->refresh();
        $this->assertSame('échoué', $ticket->statut_paiement);
        $this->assertNull($ticket->reservation_expire_le);
        $this->assertSame(0, $evenement->refresh()->quota_vendu);
    }

    public function test_pending_ancient_est_liberee_apres_la_grace(): void
    {
        if (! $this->tablesDisponibles()) {
            $this->markTestSkipped('Tables indisponibles sur ce driver.');
        }

        $this->simulerFedaPay('pending');

        $evenement = $this->creerEvenement(quotaVendu: 1);
        $ticket = $this->creerTicket($evenement, 'T-PEND-1', now()->subHour());

        $this->artisan('tickets:purger-en-attente')->assertSuccessful();

        $ticket->refresh();
        $this->assertSame('échoué', $ticket->statut_paiement);
        $this->assertNull($ticket->reservation_expire_le);
        $this->assertSame(0, $evenement->refresh()->quota_vendu);
    }

    public function test_pending_expiree_est_liberee_des_lexpiration(): void
    {
        if (! $this->tablesDisponibles()) {
            $this->markTestSkipped('Tables indisponibles sur ce driver.');
        }

        $this->simulerFedaPay('pending');

        $evenement = $this->creerEvenement(quotaVendu: 1);
        $ticket = $this->creerTicket($evenement, 'T-PEND-2', now()->subMinutes(10));

        $this->artisan('tickets:purger-en-attente')->assertSuccessful();

        $ticket->refresh();
        $this->assertSame('échoué', $ticket->statut_paiement);
        $this->assertNull($ticket->reservation_expire_le);
        $this->assertSame(0, $evenement->refresh()->quota_vendu);
    }

    // ---------- Aides ----------

    private function simulerFedaPay(string $statut): void
    {
        $fedapay = Mockery::mock(FedapayService::class);
        $fedapay->shouldReceive('getTransaction')->andReturn(['status' => $statut]);
        $this->app->instance(FedapayService::class, $fedapay);

        $promotion = Mockery::mock(WaitlistPromotionService::class);
        $promotion->shouldReceive('promouvoirEvenement')->andReturn(0);
        $this->app->instance(WaitlistPromotionService::class, $promotion);
    }

    private function creerEvenement(int $quotaVendu): Evenement
    {
        $user = User::create([
            'nom' => 'Organisateur Test',
            'email' => 'org-'.uniqid().'@paxevent.test',
            'mot_de_passe' => bcrypt('secret'),
            'role' => 'admin',
        ]);
        $this->idsCrees['users'][] = $user->id;

        $evenement = Evenement::create([
            'user_id' => $user->id,
            'titre' => 'Evenement Test '.uniqid(),
            'date_event' => now()->addDays(1),
            'lieu' => 'Cotonou',
            'capacite' => 10,
            'quota_vendu' => $quotaVendu,
            'statut' => 'publié',
        ]);
        $this->idsCrees['evenements'][] = $evenement->id;

        $tarif = Tarif::create([
            'evenement_id' => $evenement->id,
            'categorie' => 'externe',
            'nom' => 'Standard',
            'prix' => 1000,
            'quantite_disponible' => 10,
            'quantite_vendue' => 0,
            'statut' => 'actif',
        ]);
        $this->idsCrees['tarifs'][] = $tarif->id;

        return $evenement;
    }

    private function creerTicket(Evenement $evenement, string $refFedaPay, $reservationExpireLe): Ticket
    {
        $tarif = Tarif::where('evenement_id', $evenement->id)->first();
        $ticket = Ticket::create([
            'evenement_id' => $evenement->id,
            'tarif_id' => $tarif->id,
            'source' => 'site',
            'code_unique' => 'TMP',
            'qr_signature' => hash_hmac('sha256', (string) uniqid(), config('app.key') ?? 'fallback'),
            'email_acheteur' => 'client-'.uniqid().'@paxevent.test',
            'whatsapp_acheteur' => '+22901000000',
            'nom_acheteur' => 'Client Test',
            'nom_tarif' => $tarif->nom,
            'montant' => 1000,
            'montant_reduction' => 0,
            'quantite' => 1,
            'statut_paiement' => 'en_attente',
            'transaction_id' => 'GRP-TEST-'.uniqid(),
            'fedapay_transaction_id' => $refFedaPay,
            'reservation_expire_le' => $reservationExpireLe,
            'date_achat' => now()->subHour(),
        ]);
        $this->idsCrees['tickets'][] = $ticket->id;

        return $ticket;
    }

    protected function tearDown(): void
    {
        if ($this->idsCrees['evenements'] && Schema::hasTable('evenement')) {
            Evenement::whereIn('id', $this->idsCrees['evenements'])->delete();
        }
        if ($this->idsCrees['users'] && Schema::hasTable('users')) {
            User::whereIn('id', $this->idsCrees['users'])->delete();
        }

        parent::tearDown();
    }
}