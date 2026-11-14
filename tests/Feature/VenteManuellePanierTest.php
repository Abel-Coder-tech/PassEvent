<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\Ticket;
use App\Models\User;
use App\Services\PaymentMonitoringService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Vente manuelle de l'organisateur avec panier : plusieurs billets (jours,
 * packs) enregistrés en une vente, sous verrou.
 */
class VenteManuellePanierTest extends TestCase
{
    private User $organisateur;

    private Evenement $evenement;

    /** @var array<string, int> */
    private array $tarifs = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        Mail::fake();
        $this->spy(PaymentMonitoringService::class);

        $this->organisateur = User::findOrFail(DB::table('users')->insertGetId([
            'nom' => 'Organisateur',
            'email' => 'orga@example.test',
            'mot_de_passe' => bcrypt('secret'),
            'role' => 'admin',
            'statut' => 'actif',
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        $this->evenement = Evenement::findOrFail(DB::table('evenement')->insertGetId([
            'user_id' => $this->organisateur->id,
            'titre' => 'Festival',
            'slug' => 'festival-'.uniqid(),
            'date_event' => now()->addDays(10),
            'lieu' => 'Cotonou',
            'statut' => 'publié',
            'capacite' => 100,
            'quota_vendu' => 0,
            'gratuit' => false,
            'ventes_especes' => 'toujours',
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        $jour1 = DB::table('evenement_dates')->insertGetId(['evenement_id' => $this->evenement->id, 'date_debut' => now()->addDays(10), 'ordre' => 0, 'capacite' => null]);
        $jour2 = DB::table('evenement_dates')->insertGetId(['evenement_id' => $this->evenement->id, 'date_debut' => now()->addDays(11), 'ordre' => 1, 'capacite' => 2]);

        $this->tarifs = [
            'jour1' => $this->creerTarif('Jour 1', 3000, $jour1),
            'jour2' => $this->creerTarif('Jour 2', 5000, $jour2),
            'pack' => $this->creerTarif('Pack Simple', 7000, null),
        ];
    }

    public function test_une_vente_en_especes_enregistre_tout_le_panier(): void
    {
        $this->vendre('especes', [$this->tarifs['jour1'] => 2, $this->tarifs['pack'] => 1])
            ->assertOk()
            ->assertJson(['success' => true, 'total' => 13000]);

        $tickets = Ticket::all();
        $this->assertCount(3, $tickets);
        $this->assertTrue($tickets->every(fn ($t) => $t->statut_paiement === 'payé' && $t->methode_paiement === 'especes'));
        $this->assertSame(3, (int) $this->evenement->fresh()->quota_vendu);
        $this->assertSame(2, (int) DB::table('tarifs')->where('id', $this->tarifs['jour1'])->value('quantite_vendue'));
        $this->assertSame(1, (int) DB::table('tarifs')->where('id', $this->tarifs['pack'])->value('quantite_vendue'));
    }

    public function test_une_ligne_sans_place_annule_la_vente(): void
    {
        // Jour 2 : jauge de 2 places ; 1 pack + 2 billets Jour 2 = 3 places.
        $this->vendre('especes', [$this->tarifs['pack'] => 1, $this->tarifs['jour2'] => 2])
            ->assertStatus(422)
            ->assertJsonPath('errors.quantite.0', fn ($message) => str_contains($message, 'Places restantes insuffisantes'));

        $this->assertSame(0, Ticket::count());
        $this->assertSame(0, (int) $this->evenement->fresh()->quota_vendu);
    }

    public function test_une_vente_mobile_cree_une_commande_en_attente(): void
    {
        $reponse = $this->vendre('mobile', [$this->tarifs['jour1'] => 1, $this->tarifs['jour2'] => 1])
            ->assertOk()
            ->assertJsonPath('ticket.montant', 8000);

        $tickets = Ticket::all();
        $this->assertCount(2, $tickets);
        $this->assertCount(1, $tickets->pluck('transaction_id')->unique());
        $this->assertSame($reponse->json('ticket.transaction_id'), $tickets->first()->transaction_id);
        $this->assertTrue($tickets->every(fn ($t) => $t->statut_paiement === 'en_attente'));
        // Compté dans quota_vendu seulement à la confirmation du paiement.
        $this->assertSame(0, (int) $this->evenement->fresh()->quota_vendu);
    }

    public function test_l_ancien_format_tarif_et_quantite_reste_accepte(): void
    {
        $this->actingAs($this->organisateur)->postJson(route('ventes-manuelles.store'), [
            'evenement_id' => $this->evenement->id,
            'nom_acheteur' => 'Adja Koné',
            'email' => 'adja@example.test',
            'methode_paiement' => 'especes',
            'tarif_id' => $this->tarifs['jour1'],
            'quantite' => 2,
        ])->assertOk();

        $this->assertSame(2, Ticket::count());
    }

    private function vendre(string $methode, array $lignes)
    {
        return $this->actingAs($this->organisateur)->postJson(route('ventes-manuelles.store'), [
            'evenement_id' => $this->evenement->id,
            'nom_acheteur' => 'Adja Koné',
            'email' => 'adja@example.test',
            'methode_paiement' => $methode,
            'lignes' => $lignes,
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('email')->unique();
            $table->string('mot_de_passe');
            $table->string('role')->default('admin');
            $table->string('statut')->default('actif');
            $table->string('type')->nullable();
            $table->string('ventes_especes')->nullable();
            $table->decimal('commission_pourcentage', 5, 2)->nullable();
            $table->timestamps();
        });

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
            $table->string('ventes_especes')->nullable();
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
            $table->string('whatsapp_acheteur')->nullable();
            $table->string('nom_acheteur')->nullable();
            $table->string('nom_tarif')->nullable();
            $table->decimal('montant', 10, 2)->default(0);
            $table->decimal('montant_reduction', 10, 2)->default(0);
            $table->unsignedInteger('quantite')->default(1);
            $table->string('statut_paiement')->default('en_attente');
            $table->string('transaction_id')->nullable();
            $table->string('methode_paiement')->nullable();
            $table->string('type_paiement')->nullable();
            $table->string('code_promo_utilise')->nullable();
            $table->dateTime('reservation_expire_le')->nullable();
            $table->boolean('utilise')->default(false);
            $table->boolean('annule')->default(false);
            $table->dateTime('annule_at')->nullable();
            $table->dateTime('expire_at')->nullable();
            $table->dateTime('date_achat')->nullable();
            $table->dateTime('email_envoye_at')->nullable();
            $table->timestamps();
        });
    }
}
