<?php

namespace Tests\Feature;

use App\Models\AgentVente;
use App\Services\AgentVentePanierService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Panier multi-tarifs côté agent : plusieurs tarifs dans une seule vente,
 * une seule transaction groupée, un code promo appliqué à son tarif.
 */
class AgentVentePanierTest extends TestCase
{
    private int $evenementId;

    private int $standardId;

    private int $vipId;

    private AgentVente $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        Queue::fake();

        $this->evenementId = DB::table('evenement')->insertGetId([
            'titre' => 'Concert panier',
            'slug' => 'concert-panier',
            'statut' => 'publie',
            'date_event' => now()->addDays(30),
            'capacite' => 100,
            'quota_vendu' => 0,
            'gratuit' => false,
            'ventes_fermees' => false,
            'billetterie_ouverte' => true,
            'ventes_especes' => 'toujours',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->standardId = $this->creerTarif('Standard', 5000);
        $this->vipId = $this->creerTarif('VIP', 15000);

        $this->agent = AgentVente::findOrFail(DB::table('agents_vente')->insertGetId([
            'nom' => 'Agent Panier',
            'email' => 'panier@example.test',
            'password' => bcrypt('secret'),
            'evenement_id' => $this->evenementId,
            'actif' => true,
            'tickets_count' => 0,
            'montant_total' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    public function test_un_panier_cree_plusieurs_tarifs_dans_une_seule_vente(): void
    {
        $resultat = $this->service()->create($this->agent, [
            'nom_acheteur' => 'Awa Kone',
            'email_acheteur' => 'awa@example.test',
            'methode_paiement' => 'cash',
            'lignes' => [$this->standardId => 1, $this->vipId => 2],
        ]);

        $this->assertSame('payé', $resultat['statut']);
        $this->assertCount(3, $resultat['tickets']);
        $this->assertSame(35000.0, (float) $resultat['montant_total']);

        // Tous les billets partagent la même transaction groupée.
        $this->assertSame(
            1,
            DB::table('ticket')->distinct()->count('transaction_id')
        );
        $this->assertSame(3, DB::table('ticket')->count());

        $this->assertSame(1, (int) DB::table('tarifs')->where('id', $this->standardId)->value('quantite_vendue'));
        $this->assertSame(2, (int) DB::table('tarifs')->where('id', $this->vipId)->value('quantite_vendue'));
        $this->assertSame(3, (int) DB::table('evenement')->where('id', $this->evenementId)->value('quota_vendu'));

        $agent = $this->agent->fresh();
        $this->assertSame(3, (int) $agent->tickets_count);
        $this->assertSame(35000.0, (float) $agent->montant_total);

    }

    public function test_le_code_promo_s_applique_uniquement_a_son_tarif(): void
    {
        DB::table('codes_promos')->insert([
            'evenement_id' => $this->evenementId,
            'tarif_id' => $this->vipId,
            'code' => 'VIP10',
            'type_reduction' => 'pourcentage',
            'valeur_reduction' => 10,
            'max_utilisations' => null,
            'nb_utilisations' => 0,
            'date_expiration' => null,
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resultat = $this->service()->create($this->agent, [
            'nom_acheteur' => 'Awa Kone',
            'email_acheteur' => 'awa@example.test',
            'methode_paiement' => 'cash',
            'code_promo' => 'vip10',
            'lignes' => [$this->standardId => 1, $this->vipId => 2],
        ]);

        // 5 000 + (15 000 − 10 %) × 2 = 32 000.
        $this->assertSame(32000.0, (float) $resultat['montant_total']);
        $this->assertSame(1, (int) DB::table('codes_promos')->value('nb_utilisations'));

        $standard = DB::table('ticket')->where('tarif_id', $this->standardId)->first();
        $vip = DB::table('ticket')->where('tarif_id', $this->vipId)->first();

        $this->assertSame(5000.0, (float) $standard->montant);
        $this->assertNull($standard->code_promo_utilise);
        $this->assertSame(13500.0, (float) $vip->montant);
        $this->assertSame(1500.0, (float) $vip->montant_reduction);
        $this->assertSame('VIP10', $vip->code_promo_utilise);
    }

    public function test_un_code_promo_hors_panier_est_refuse(): void
    {
        DB::table('codes_promos')->insert([
            'evenement_id' => $this->evenementId,
            'tarif_id' => $this->vipId,
            'code' => 'VIP10',
            'type_reduction' => 'pourcentage',
            'valeur_reduction' => 10,
            'max_utilisations' => null,
            'nb_utilisations' => 0,
            'date_expiration' => null,
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->service()->create($this->agent, [
                'nom_acheteur' => 'Awa Kone',
                'email_acheteur' => 'awa@example.test',
                'methode_paiement' => 'cash',
                'code_promo' => 'VIP10',
                'lignes' => [$this->standardId => 1],
            ]);

            $this->fail('Un code promo lié à un tarif absent du panier doit être refusé.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('code_promo', $exception->errors());
        }

        $this->assertSame(0, DB::table('ticket')->count());
    }

    public function test_rejouer_la_meme_cle_ne_cree_pas_un_second_panier(): void
    {
        $donnees = [
            'nom_acheteur' => 'Awa Kone',
            'email_acheteur' => 'awa@example.test',
            'methode_paiement' => 'cash',
            'idempotency_key' => 'panier-unique-1',
            'lignes' => [$this->standardId => 1, $this->vipId => 1],
        ];

        $premiere = $this->service()->create($this->agent, $donnees);
        $seconde = $this->service()->create($this->agent, $donnees);

        $this->assertFalse($premiere['idempotent']);
        $this->assertTrue($seconde['idempotent']);
        $this->assertSame(2, DB::table('ticket')->count());
        $this->assertSame(
            $premiere['tickets']->pluck('id')->all(),
            $seconde['tickets']->pluck('id')->all()
        );
    }

    public function test_le_panier_est_limite_a_vingt_billets(): void
    {
        try {
            $this->service()->create($this->agent, [
                'nom_acheteur' => 'Awa Kone',
                'email_acheteur' => 'awa@example.test',
                'methode_paiement' => 'cash',
                'lignes' => [$this->standardId => 15, $this->vipId => 6],
            ]);

            $this->fail('Un panier de plus de 20 billets doit être refusé.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('lignes', $exception->errors());
        }

        $this->assertSame(0, DB::table('ticket')->count());
    }

    private function service(): AgentVentePanierService
    {
        return app(AgentVentePanierService::class);
    }

    private function creerTarif(string $nom, int $prix): int
    {
        return DB::table('tarifs')->insertGetId([
            'evenement_id' => $this->evenementId,
            'evenement_date_id' => null,
            'nom' => $nom,
            'prix' => $prix,
            'quantite_disponible' => 50,
            'quantite_vendue' => 0,
            'statut' => 'actif',
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
            $table->text('description')->nullable();
            $table->dateTime('date_event')->nullable();
            $table->string('lieu')->nullable();
            $table->string('statut')->default('publie');
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
            $table->unsignedInteger('quantite_disponible')->nullable();
            $table->unsignedInteger('quantite_vendue')->default(0);
            $table->string('statut')->default('actif');
            $table->timestamps();
        });

        Schema::create('codes_promos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->unsignedBigInteger('tarif_id');
            $table->string('code');
            $table->string('type_reduction')->default('pourcentage');
            $table->decimal('valeur_reduction', 10, 2)->default(0);
            $table->unsignedInteger('max_utilisations')->nullable();
            $table->unsignedInteger('nb_utilisations')->default(0);
            $table->dateTime('date_expiration')->nullable();
            $table->boolean('actif')->default(true);
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
            $table->string('source')->nullable();
            $table->string('code_unique')->unique();
            $table->string('qr_signature')->nullable();
            $table->string('email_acheteur')->nullable();
            $table->string('whatsapp_acheteur')->nullable();
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
            $table->dateTime('reservation_expire_le')->nullable();
            $table->boolean('utilise')->default(false);
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

        Schema::create('vente_idempotences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_vente_id');
            $table->string('cle', 100);
            $table->char('empreinte', 64);
            $table->string('transaction_id', 100);
            $table->timestamps();

            $table->unique(['agent_vente_id', 'cle'], 'vente_idempotence_agent_cle_unique');
            $table->index('transaction_id');
        });
    }
}
