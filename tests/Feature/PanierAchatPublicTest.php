<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\EventWaitlist;
use App\Models\Ticket;
use App\Services\PaymentMonitoringService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Achat public avec panier : plusieurs tarifs (jours et packs) dans une même
 * commande, réservés ensemble sous verrou, payés en une fois.
 */
class PanierAchatPublicTest extends TestCase
{
    private Evenement $evenement;

    /** @var array<string, int> */
    private array $tarifs = [];

    /** @var array<int, int> */
    private array $jours = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        Queue::fake();
        Mail::fake();
        $this->spy(PaymentMonitoringService::class);

        // Festival sur 2 jours : Jour 1 gratuit, Jour 2 à 5 000 F, Pack Simple à 8 000 F.
        $this->evenement = $this->creerEvenement(capacite: 100);
        $this->jours = [
            $this->creerJour(now()->addDays(10)->setTime(18, 0), 0, null),
            $this->creerJour(now()->addDays(11)->setTime(18, 0), 1, 3),
        ];
        $this->tarifs = [
            'jour1' => $this->creerTarif('Jour 1', 0, $this->jours[0]),
            'jour2' => $this->creerTarif('Jour 2', 5000, $this->jours[1]),
            'pack' => $this->creerTarif('Pack Simple', 8000, null),
        ];
    }

    public function test_l_achat_est_refuse_tant_que_la_billetterie_n_est_pas_ouverte(): void
    {
        $this->evenement->update(['billetterie_ouverte' => false, 'billetterie_ouvre_le' => null]);

        $this->acheter([$this->tarifs['jour2'] => 1])->assertSessionHas('error');
        $this->assertSame(0, Ticket::count());

        // Date d'ouverture dans le futur : toujours fermée ; passée : l'ouverture est automatique.
        $this->evenement->update(['billetterie_ouvre_le' => now()->addDay()]);
        $this->assertTrue($this->evenement->fresh()->billetteriePasOuverte());
        $this->evenement->update(['billetterie_ouvre_le' => now()->subMinute()]);
        $this->assertFalse($this->evenement->fresh()->billetteriePasOuverte());

        $this->acheter([$this->tarifs['jour2'] => 1])->assertSessionMissing('error');
        $this->assertGreaterThan(0, Ticket::count());
    }

    public function test_un_panier_multijours_cree_une_seule_commande(): void
    {
        $reponse = $this->acheter([
            $this->tarifs['jour1'] => 1,
            $this->tarifs['jour2'] => 2,
            $this->tarifs['pack'] => 1,
        ]);

        $tickets = Ticket::orderBy('id')->get();
        $reponse->assertRedirect(route('paiement.show', $tickets->first()->id));

        $this->assertCount(4, $tickets);
        $this->assertCount(1, $tickets->pluck('transaction_id')->unique());
        $this->assertStringStartsWith('GRP-', $tickets->first()->transaction_id);
        $this->assertSame(18000.0, (float) $tickets->sum('montant'));
        $this->assertTrue($tickets->every(fn ($t) => $t->statut_paiement === 'en_attente'));

        // Chaque billet porte le jour de son tarif (pack : tous les jours).
        $this->assertSame(
            [$this->jours[0], $this->jours[1], $this->jours[1], null],
            $tickets->pluck('evenement_date_id')->map(fn ($id) => $id === null ? null : (int) $id)->all()
        );
        $this->assertSame(4, (int) $this->evenement->fresh()->quota_vendu);
    }

    public function test_une_commande_avec_un_jour_gratuit_n_est_pas_confirmee_gratuitement(): void
    {
        $this->acheter([$this->tarifs['jour1'] => 1, $this->tarifs['jour2'] => 1]);
        $gratuit = Ticket::where('tarif_id', $this->tarifs['jour1'])->firstOrFail();

        // La page de paiement du billet à 0 F ne doit pas valider toute la commande.
        $this->withSession(['paiement_tickets' => Ticket::pluck('id')->all()])
            ->get(route('paiement.show', $gratuit->id));

        $this->assertSame(['en_attente'], Ticket::pluck('statut_paiement')->unique()->values()->all());
    }

    public function test_un_panier_entierement_gratuit_est_confirme_aussitot(): void
    {
        $reponse = $this->acheter([$this->tarifs['jour1'] => 2]);

        $tickets = Ticket::all();
        $reponse->assertRedirect(route('confirmation.show', $tickets->first()->id));
        $this->assertCount(2, $tickets);
        $this->assertTrue($tickets->every(fn ($t) => $t->statut_paiement === 'payé'));
        $this->assertSame(2, (int) DB::table('tarifs')->where('id', $this->tarifs['jour1'])->value('quantite_vendue'));
    }

    public function test_une_ligne_sans_place_annule_tout_le_panier(): void
    {
        // Jour 2 : jauge de 3 places, on en demande 4 avec un pack.
        $reponse = $this->acheter([
            $this->tarifs['pack'] => 1,
            $this->tarifs['jour2'] => 4,
        ]);

        $reponse->assertRedirect();
        $reponse->assertSessionHas('error');
        $this->assertSame(0, Ticket::count(), 'Aucun billet ne doit rester d\'un panier refusé.');
        $this->assertSame(0, (int) $this->evenement->fresh()->quota_vendu);
        $this->assertSame(0, EventWaitlist::count());
    }

    public function test_le_pack_occupe_une_place_chaque_jour(): void
    {
        // Jour 2 : 3 places. 2 packs + 1 billet Jour 2 = 3 : accepté.
        $this->acheter([$this->tarifs['pack'] => 2, $this->tarifs['jour2'] => 1])->assertSessionHasNoErrors();
        $this->assertSame(3, Ticket::count());

        // Plus aucune place le Jour 2 : un nouveau pack est refusé.
        $this->acheter([$this->tarifs['pack'] => 1]);
        $this->assertSame(3, Ticket::count());
    }

    public function test_inscription_sur_liste_d_attente_depuis_la_fenetre(): void
    {
        $url = route('evenements.liste-attente', $this->evenement);

        $this->postJson($url, [
            'tarif_id' => $this->tarifs['jour2'],
            'email' => 'Awa@Example.test',
            'quantite' => 35,
        ])->assertOk()->assertJson(['ok' => true]);

        // Même email et même tarif : la demande est mise à jour, pas dupliquée.
        $this->postJson($url, [
            'tarif_id' => $this->tarifs['jour2'],
            'email' => 'awa@example.test',
            'nom' => 'Awa Kone',
            'whatsapp' => '+229 97 00 00 00',
            'quantite' => 40,
        ])->assertOk();

        $this->assertSame(0, Ticket::count());
        $this->assertSame(1, DB::table('event_waitlist')->count());
        $this->assertDatabaseHas('event_waitlist', [
            'tarif_id' => $this->tarifs['jour2'],
            'email_acheteur' => 'awa@example.test',
            'nom_acheteur' => 'Awa Kone',
            'telephone_acheteur' => '+229 97 00 00 00',
            'quantite' => 40,
            'statut' => 'en_attente',
        ]);

        $this->postJson($url, ['tarif_id' => $this->tarifs['jour2'], 'quantite' => 1])->assertJsonValidationErrors('email');
        $this->postJson($url, ['tarif_id' => 999999, 'email' => 'x@example.test', 'quantite' => 1])->assertJsonValidationErrors('tarif_id');
    }

    public function test_le_formulaire_d_achat_n_inscrit_plus_sur_liste_d_attente(): void
    {
        $this->post(route('evenements.achat', $this->evenement), [
            'nom_acheteur' => 'Awa Kone',
            'email_acheteur' => 'awa@example.test',
            'whatsapp_acheteur' => '9700000000',
            'attente_tarif_id' => $this->tarifs['jour2'],
        ])->assertSessionHas('error');

        $this->assertSame(0, DB::table('event_waitlist')->count());
        $this->assertSame(0, Ticket::count());
    }

    public function test_un_panier_vide_est_refuse(): void
    {
        $this->acheter([$this->tarifs['jour2'] => 0])->assertSessionHas('error');
        $this->assertSame(0, Ticket::count());
    }

    private function acheter(array $lignes)
    {
        return $this->post(route('evenements.achat', $this->evenement), [
            'nom_acheteur' => 'Awa Kone',
            'email_acheteur' => 'awa@example.test',
            'whatsapp_acheteur' => '9700000000',
            'lignes' => $lignes,
        ]);
    }

    private function creerEvenement(int $capacite): Evenement
    {
        $id = DB::table('evenement')->insertGetId([
            'titre' => 'Festival',
            'slug' => 'festival-'.uniqid(),
            'date_event' => now()->addDays(10),
            'lieu' => 'Cotonou',
            'statut' => 'publié',
            'capacite' => $capacite,
            'quota_vendu' => 0,
            'gratuit' => false,
            'ventes_fermees' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Evenement::findOrFail($id);
    }

    private function creerJour($date, int $ordre, ?int $capacite): int
    {
        return DB::table('evenement_dates')->insertGetId([
            'evenement_id' => $this->evenement->id,
            'date_debut' => $date,
            'ordre' => $ordre,
            'capacite' => $capacite,
        ]);
    }

    private function creerTarif(string $nom, float $prix, ?int $jourId): int
    {
        return DB::table('tarifs')->insertGetId([
            'evenement_id' => $this->evenement->id,
            'evenement_date_id' => $jourId,
            'nom' => $nom,
            'prix' => $prix,
            'statut' => 'actif',
            'quantite_disponible' => 50,
            'quantite_vendue' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createTables(): void
    {
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
            $table->boolean('billetterie_ouverte')->default(true);
            $table->dateTime('billetterie_ouvre_le')->nullable();
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
            $table->unsignedInteger('quantite_disponible')->nullable();
            $table->unsignedInteger('quantite_vendue')->default(0);
            $table->timestamps();
        });

        Schema::create('event_waitlist', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->string('nom_acheteur')->nullable();
            $table->string('email_acheteur');
            $table->string('telephone_acheteur')->nullable();
            $table->unsignedInteger('quantite')->default(1);
            $table->string('code_promo_utilise')->nullable();
            $table->integer('montant_unitaire')->default(0);
            $table->integer('montant_reduction')->default(0);
            $table->string('statut')->default('en_attente');
            $table->dateTime('place_offerte_le')->nullable();
            $table->dateTime('expire_le')->nullable();
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
