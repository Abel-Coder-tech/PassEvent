<?php

namespace Tests\Feature;

use App\Models\DemandeModificationTarif;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Modification d'un événement : même assistant que la création, prérempli,
 * avec les verrous (tarifs vendus, jours occupés) et sans casser les
 * anciens événements.
 */
class EvenementModificationAssistantTest extends TestCase
{
    protected User $organisateur;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        $this->createTables();
        Schema::create((new DemandeModificationTarif)->getTable(), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tarif_id');
            $table->string('statut')->default('en_attente');
            $table->timestamps();
        });
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('evenement_id')->nullable();
            $table->string('nom_complet')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('objet')->nullable();
            $table->text('message')->nullable();
            $table->boolean('lu')->default(false);
            $table->timestamps();
        });
        Schema::create('event_waitlist', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->string('nom_acheteur')->nullable();
            $table->string('email_acheteur');
            $table->string('telephone_acheteur')->nullable();
            $table->integer('quantite')->default(1);
            $table->string('code_promo_utilise')->nullable();
            $table->unsignedInteger('montant_unitaire')->default(0);
            $table->unsignedInteger('montant_reduction')->default(0);
            $table->string('statut')->default('en_attente');
            $table->dateTime('place_offerte_le')->nullable();
            $table->dateTime('expire_le')->nullable();
            $table->timestamps();
        });
        $this->organisateur = $this->creerOrganisateur();
    }

    public function test_la_page_de_modification_affiche_l_assistant_prerempli(): void
    {
        $evenement = $this->creerMono();

        $this->actingAs($this->organisateur)->get(route('admin.evenements.edit', $evenement))
            ->assertOk()
            ->assertSee('id="wz-form"', false)
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('Festival test')
            ->assertSee('Modifier l');
    }

    public function test_modifier_un_evenement_d_un_jour_met_a_jour_ajoute_et_supprime_des_tarifs(): void
    {
        $evenement = $this->creerMono();
        [$standard, $etudiant] = $evenement->tarifs()->orderBy('id')->get()->all();

        $this->modifier($evenement, [
            'duree' => 'un',
            'date_event' => $this->date(5, '21:00'),
            'capacite' => 400,
            'tarifs' => [
                ['id' => $standard->id, 'nom' => 'Standard', 'prix' => 6000, 'qte' => 250],
                ['nom' => 'Prévente', 'prix' => 3000, 'qte' => 50],
            ],
        ])->assertRedirect(route('admin.evenements.index'));

        $evenement->refresh();
        $this->assertSame($this->date(5, '21:00'), $evenement->date_event->format('Y-m-d\TH:i'));
        $this->assertSame(400, (int) $evenement->capacite);
        $this->assertSame(
            [['Standard', 6000.0, 250], ['Prévente', 3000.0, 50]],
            $evenement->tarifs()->orderBy('id')->get()->map(fn ($t) => [$t->nom, (float) $t->prix, $t->quantite_disponible])->all()
        );
        $this->assertNull($etudiant->fresh());
    }

    public function test_un_tarif_vendu_change_de_prix_mais_ne_peut_etre_ni_supprime_ni_reduit_sous_ses_ventes(): void
    {
        $evenement = $this->creerMono();
        [$standard, $etudiant] = $evenement->tarifs()->orderBy('id')->get()->all();
        $standard->update(['quantite_vendue' => 30]);

        // Le prix d'un tarif vendu reste modifiable (les billets émis gardent leur montant).
        $this->modifier($evenement, [
            'duree' => 'un',
            'date_event' => $this->date(5, '20:00'),
            'capacite' => 300,
            'tarifs' => [
                ['id' => $standard->id, 'nom' => 'Standard', 'prix' => 1, 'qte' => 180],
                ['id' => $etudiant->id, 'nom' => 'Étudiant', 'prix' => 2500, 'qte' => 100],
            ],
        ])->assertRedirect(route('admin.evenements.index'));
        $this->assertSame([1.0, 180], [(float) $standard->fresh()->prix, $standard->fresh()->quantite_disponible]);

        $this->modifier($evenement, [
            'duree' => 'un', 'date_event' => $this->date(5, '20:00'), 'capacite' => 300,
            'tarifs' => [
                ['id' => $standard->id, 'nom' => 'Standard', 'prix' => 5000, 'qte' => 10],
                ['id' => $etudiant->id, 'nom' => 'Étudiant', 'prix' => 2500, 'qte' => 100],
            ],
        ])->assertSessionHasErrors('tarifs.0.qte');

        $this->modifier($evenement, [
            'duree' => 'un', 'date_event' => $this->date(5, '20:00'), 'capacite' => 300,
            'tarifs' => [['id' => $etudiant->id, 'nom' => 'Étudiant', 'prix' => 2500, 'qte' => 100]],
        ])->assertSessionHasErrors('tarifs');
        $this->assertNotNull($standard->fresh());
        $this->assertSame(180, $standard->fresh()->quantite_disponible);
    }

    public function test_modifier_un_evenement_multijours_jauges_et_packs(): void
    {
        $evenement = $this->creerMulti();
        $jours = $evenement->dates()->reorder('date_debut')->get();
        $tarifs = $evenement->tarifs()->orderBy('id')->get();

        $this->modifier($evenement, [
            'duree' => 'plusieurs',
            'jours' => $jours->map(fn ($j) => $j->date_debut->format('Y-m-d\TH:i'))->all(),
            'capacite' => 500,
            'meme_capacite' => 0,
            'capacites_jours' => ['', 250],
            'tarifs' => [
                ['id' => $tarifs[0]->id, 'nom' => 'Jour 1', 'prix' => 0, 'qte' => 100, 'jour' => '0'],
                ['id' => $tarifs[1]->id, 'nom' => 'Jour 2', 'prix' => 7000, 'qte' => 100, 'jour' => '1'],
                ['nom' => 'Pack VIP', 'prix' => 15000, 'qte' => 20, 'jour' => 'pack'],
            ],
        ])->assertRedirect(route('admin.evenements.index'));

        $this->assertSame([null, 250], $evenement->dates()->reorder('date_debut')->pluck('capacite')->all());
        $this->assertSame(7000.0, (float) $tarifs[1]->fresh()->prix);
        $this->assertTrue($evenement->tarifs()->whereNull('evenement_date_id')->where('nom', 'Pack VIP')->exists());

        // Un deuxième tarif sur un jour est refusé.
        $this->modifier($evenement, [
            'duree' => 'plusieurs',
            'jours' => $jours->map(fn ($j) => $j->date_debut->format('Y-m-d\TH:i'))->all(),
            'capacite' => 500, 'meme_capacite' => 1,
            'tarifs' => array_merge($this->tarifsActuels($evenement), [['nom' => 'Jour', 'prix' => 1000, 'qte' => 5, 'jour' => '0']]),
        ])->assertSessionHasErrors('tarifs');
    }

    public function test_un_ancien_evenement_vendu_en_packs_se_modifie_sans_tarif_par_jour(): void
    {
        $evenement = $this->creerMulti();
        // Ancien modèle : un seul « Pass complet » valable tous les jours.
        $evenement->tarifs()->delete();
        $pass = $evenement->tarifs()->create(['nom' => 'Pass complet', 'prix' => 9000, 'quantite_disponible' => 100, 'quantite_vendue' => 0, 'statut' => 'actif']);
        $jours = $evenement->dates()->reorder('date_debut')->get();

        $this->actingAs($this->organisateur)->get(route('admin.evenements.edit', $evenement))
            ->assertOk()->assertSee('"sansTarifJour":true', false);

        $this->modifier($evenement, [
            'duree' => 'plusieurs',
            'jours' => $jours->map(fn ($j) => $j->date_debut->format('Y-m-d\TH:i'))->all(),
            'capacite' => 500, 'meme_capacite' => 1,
            'tarifs' => [['id' => $pass->id, 'nom' => 'Pass complet', 'prix' => 9500, 'qte' => 120, 'jour' => 'pack']],
        ])->assertRedirect(route('admin.evenements.index'));

        $this->assertSame(['Pass complet', 9500.0, 120], [$pass->fresh()->nom, (float) $pass->fresh()->prix, $pass->fresh()->quantite_disponible]);
        $this->assertSame(1, $evenement->tarifs()->count());
    }

    public function test_un_ancien_evenement_vendu_en_packs_peut_recevoir_un_tarif_par_jour(): void
    {
        $evenement = $this->creerMulti();
        $evenement->tarifs()->delete();
        $pass = $evenement->tarifs()->create(['nom' => 'Pass complet', 'prix' => 9000, 'quantite_disponible' => 100, 'quantite_vendue' => 0, 'statut' => 'actif']);
        $jours = $evenement->dates()->reorder('date_debut')->get();

        $this->modifier($evenement, [
            'duree' => 'plusieurs',
            'jours' => $jours->map(fn ($j) => $j->date_debut->format('Y-m-d\TH:i'))->all(),
            'capacite' => 500, 'meme_capacite' => 1,
            'tarifs' => [
                ['id' => $pass->id, 'nom' => 'Pass complet', 'prix' => 9000, 'qte' => 100, 'jour' => 'pack'],
                ['nom' => 'Jour', 'prix' => '', 'qte' => '', 'jour' => '0'],
                ['nom' => 'Jour', 'prix' => 1000, 'qte' => 50, 'jour' => '1'],
            ],
        ])->assertRedirect(route('admin.evenements.index'));

        $gratuit = $evenement->tarifs()->where('evenement_date_id', $jours[0]->id)->first();
        $this->assertNotNull($gratuit);
        $this->assertSame(0.0, (float) $gratuit->prix);
        $payant = $evenement->tarifs()->where('evenement_date_id', $jours[1]->id)->first();
        $this->assertSame(1000.0, (float) $payant->prix);
    }

    protected function tarifsActuels(Evenement $evenement): array
    {
        $index = $evenement->dates()->reorder('date_debut')->pluck('id')->flip();

        return $evenement->tarifs()->orderBy('id')->get()->map(fn ($t) => [
            'id' => $t->id, 'nom' => $t->nom, 'prix' => (int) $t->prix, 'qte' => $t->quantite_disponible,
            'jour' => $t->evenement_date_id === null ? 'pack' : (string) $index[$t->evenement_date_id],
        ])->all();
    }

    protected function creerMono(): Evenement
    {
        $this->envoyer([
            'duree' => 'un',
            'date_event' => $this->date(5, '20:00'),
            'capacite' => 300,
            'tarifs' => [
                ['nom' => 'Standard', 'prix' => 5000, 'qte' => 200],
                ['nom' => 'Étudiant', 'prix' => 2500, 'qte' => 100],
            ],
        ])->assertRedirect(route('admin.evenements.index'));

        return Evenement::latest('id')->firstOrFail();
    }

    protected function creerMulti(): Evenement
    {
        $this->envoyer([
            'duree' => 'plusieurs',
            'jours' => [$this->date(4, '18:00'), $this->date(5, '18:00')],
            'capacite' => 500,
            'meme_capacite' => 1,
            'tarifs' => [
                ['nom' => 'Jour', 'prix' => 0, 'qte' => 100, 'jour' => '0'],
                ['nom' => 'Jour', 'prix' => 5000, 'qte' => 100, 'jour' => '1'],
            ],
        ])->assertRedirect(route('admin.evenements.index'));

        return Evenement::latest('id')->firstOrFail();
    }

    protected function modifier(Evenement $evenement, array $donnees)
    {
        return $this->actingAs($this->organisateur)->put(route('admin.evenements.update', $evenement), array_merge([
            'type_evenement' => 'spectacle',
            'categorie' => 'Festival',
            'titre' => 'Festival test',
            'description' => 'Description',
            'gratuit' => 0,
            'lieu' => 'Cotonou',
            'statut' => 'publié',
        ], $donnees));
    }

    protected function envoyer(array $donnees)
    {
        return $this->actingAs($this->organisateur)->post(route('admin.evenements.store'), array_merge([
            'type_evenement' => 'spectacle',
            'categorie' => 'Festival',
            'titre' => 'Festival test',
            'description' => 'Description',
            'gratuit' => 0,
            'lieu' => 'Cotonou',
            'statut' => 'publié',
        ], $donnees));
    }

    protected function date(int $jours, string $heure): string
    {
        return now()->addDays($jours)->format('Y-m-d').'T'.$heure;
    }

    protected function creerOrganisateur(): User
    {
        $id = DB::table('users')->insertGetId([
            'nom' => 'Organisateur',
            'email' => 'orga-'.uniqid().'@example.test',
            'mot_de_passe' => bcrypt('secret'),
            'role' => 'admin',
            'statut' => 'actif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::findOrFail($id);
    }

    protected function createTables(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('email')->unique();
            $table->string('mot_de_passe');
            $table->string('role')->default('admin');
            $table->string('statut')->default('actif');
            $table->decimal('commission_pourcentage', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('evenement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('titre');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->dateTime('date_event')->nullable();
            $table->string('lieu')->nullable();
            $table->string('categorie')->nullable();
            $table->unsignedInteger('capacite')->default(0);
            $table->unsignedInteger('quota_vendu')->default(0);
            $table->string('image')->nullable();
            $table->string('statut')->default('publié');
            $table->string('type_evenement')->default('spectacle');
            $table->boolean('gratuit')->default(false);
            $table->boolean('ventes_fermees')->default(false);
            $table->boolean('billetterie_ouverte')->default(true);
            $table->dateTime('billetterie_ouvre_le')->nullable();
            $table->boolean('co_parrainage_actif')->default(false);
            $table->decimal('co_parrainage_pourcentage', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('evenement_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->dateTime('date_debut')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->unsignedInteger('capacite')->nullable();
            $table->timestamps();
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

        Schema::create('ticket', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->unsignedBigInteger('evenement_date_id')->nullable();
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->unsignedInteger('quantite')->default(1);
            $table->string('statut_paiement')->default('payé');
            $table->string('email_acheteur')->nullable();
            $table->boolean('annule')->nullable();
            $table->dateTime('expire_at')->nullable();
            $table->dateTime('reservation_expire_le')->nullable();
            $table->timestamps();
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->string('type_operation');
            $table->json('details')->nullable();
            $table->dateTime('created_at');
        });

    }
}
