<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Création d'un événement depuis l'assistant en étapes : un jour ou
 * plusieurs, billets à la journée, packs et jauges par jour.
 */
class EvenementCreationAssistantTest extends TestCase
{
    private User $organisateur;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        $this->organisateur = $this->creerOrganisateur();
    }

    public function test_un_evenement_payant_d_un_jour_avec_vip_automatique(): void
    {
        $this->envoyer([
            'duree' => 'un',
            'date_event' => $this->date(5, '20:00'),
            'capacite' => 300,
            'tarifs' => [
                ['nom' => 'Standard', 'prix' => 5000, 'qte' => 200],
                ['nom' => 'Étudiant', 'prix' => 2500, 'qte' => 100],
            ],
            'generer_vip' => 1,
        ])->assertRedirect(route('admin.evenements.index'));

        $evenement = Evenement::firstOrFail();
        $this->assertSame(1, $evenement->dates()->count());
        $this->assertSame(
            [['Standard', null, 200], ['Étudiant', null, 100], ['VIP', null, null]],
            $evenement->tarifs()->orderBy('id')->get()->map(fn ($t) => [$t->nom, $t->evenement_date_id, $t->quantite_disponible])->all()
        );
        $this->assertSame(10000.0, (float) $evenement->tarifs()->where('nom', 'VIP')->value('prix'));
    }

    public function test_un_evenement_sur_plusieurs_jours_rattache_billets_et_jauges_au_bon_jour(): void
    {
        // Jours saisis dans le désordre : le serveur les remet dans l'ordre.
        $this->envoyer([
            'duree' => 'plusieurs',
            'jours' => [
                0 => $this->date(6, '16:00'),
                1 => $this->date(4, '18:00'),
                2 => $this->date(5, '18:00'),
            ],
            'capacite' => 500,
            'meme_capacite' => 0,
            'capacites_jours' => [0 => '', 1 => 300, 2 => ''],
            // Un tarif par jour (le nom envoyé est ignoré : « Jour N ») + deux packs
            'tarifs' => [
                ['nom' => 'Jour', 'prix' => 0, 'qte' => 150, 'jour' => '1'],
                ['nom' => 'Jour', 'prix' => 5000, 'qte' => 50, 'jour' => '2'],
                ['nom' => 'Jour', 'prix' => 5000, 'qte' => 50, 'jour' => '0'],
                ['nom' => 'Pack Simple', 'prix' => 8000, 'qte' => 100, 'jour' => 'pack'],
                ['nom' => 'Pack VIP', 'prix' => 18000, 'qte' => 20, 'jour' => 'pack'],
            ],
        ])->assertRedirect(route('admin.evenements.index'));

        $evenement = Evenement::firstOrFail();
        $jours = $evenement->dates()->get();

        $this->assertCount(3, $jours);
        $this->assertSame($this->date(4, '18:00'), $jours[0]->date_debut->format('Y-m-d\TH:i'));
        $this->assertSame($this->date(4, '18:00'), $evenement->date_event->format('Y-m-d\TH:i'));
        // La jauge de 300 saisie pour le 2e jour du formulaire va au premier jour chronologique.
        $this->assertSame([300, null, null], $jours->pluck('capacite')->all());

        // Chaque tarif à la journée est nommé d'après son jour chronologique.
        $tarifs = $evenement->tarifs()->get()->keyBy('nom');
        $this->assertSame($jours[0]->id, $tarifs['Jour 1']->evenement_date_id);
        $this->assertSame(0.0, (float) $tarifs['Jour 1']->prix);
        $this->assertSame($jours[1]->id, $tarifs['Jour 2']->evenement_date_id);
        $this->assertSame($jours[2]->id, $tarifs['Jour 3']->evenement_date_id);
        $this->assertNull($tarifs['Pack Simple']->evenement_date_id);
        $this->assertNull($tarifs['Pack VIP']->evenement_date_id);
    }

    public function test_deux_tarifs_le_meme_jour_sont_refuses(): void
    {
        $this->envoyer([
            'duree' => 'plusieurs',
            'jours' => [$this->date(4, '18:00'), $this->date(5, '18:00')],
            'capacite' => 500,
            'meme_capacite' => 1,
            'tarifs' => [
                ['nom' => 'Jour', 'prix' => 5000, 'qte' => 10, 'jour' => '0'],
                ['nom' => 'Jour', 'prix' => 9000, 'qte' => 10, 'jour' => '0'],
                ['nom' => 'Jour', 'prix' => 5000, 'qte' => 10, 'jour' => '1'],
            ],
        ])->assertSessionHasErrors('tarifs');

        $this->assertSame(0, Evenement::count());
    }

    public function test_chaque_jour_doit_avoir_son_tarif(): void
    {
        $this->envoyer([
            'duree' => 'plusieurs',
            'jours' => [$this->date(4, '18:00'), $this->date(5, '18:00')],
            'capacite' => 500,
            'meme_capacite' => 1,
            'tarifs' => [
                ['nom' => 'Jour', 'prix' => 5000, 'qte' => 10, 'jour' => '0'],
                ['nom' => 'Pack Simple', 'prix' => 8000, 'qte' => 10, 'jour' => 'pack'],
            ],
        ])->assertSessionHasErrors('tarifs');

        $this->assertSame(0, Evenement::count());
    }

    public function test_seuls_pack_simple_et_pack_vip_sont_acceptes(): void
    {
        $this->envoyer([
            'duree' => 'plusieurs',
            'jours' => [$this->date(4, '18:00'), $this->date(5, '18:00')],
            'capacite' => 500,
            'meme_capacite' => 1,
            'tarifs' => [
                ['nom' => 'Jour', 'prix' => 5000, 'qte' => 10, 'jour' => '0'],
                ['nom' => 'Jour', 'prix' => 5000, 'qte' => 10, 'jour' => '1'],
                ['nom' => 'Pass complet', 'prix' => 8000, 'qte' => 10, 'jour' => 'pack'],
            ],
        ])->assertSessionHasErrors('tarifs');

        $this->assertSame(0, Evenement::count());
    }

    public function test_billets_du_jour_et_packs_ne_depassent_pas_la_jauge_du_jour(): void
    {
        $this->envoyer([
            'duree' => 'plusieurs',
            'jours' => [$this->date(4, '18:00'), $this->date(5, '18:00')],
            'capacite' => 500,
            'meme_capacite' => 0,
            'capacites_jours' => [100, ''],
            'tarifs' => [
                ['nom' => 'Jour', 'prix' => 5000, 'qte' => 80, 'jour' => '0'],
                ['nom' => 'Jour', 'prix' => 5000, 'qte' => 80, 'jour' => '1'],
                ['nom' => 'Pack Simple', 'prix' => 8000, 'qte' => 30, 'jour' => 'pack'],
            ],
        ])->assertSessionHasErrors(['tarifs' => 'Jour 1 : les billets du jour (80) et les packs (30) font 110 places, au-delà de la capacité du jour (100 places).']);

        $this->assertSame(0, Evenement::count());
    }

    public function test_les_packs_ne_sont_pas_comptes_deux_fois_sur_le_premier_jour(): void
    {
        // Jour 1 : 70 + packs 30 = 100 = jauge. Accepté.
        $this->envoyer([
            'duree' => 'plusieurs',
            'jours' => [$this->date(4, '18:00'), $this->date(5, '18:00')],
            'capacite' => 500,
            'meme_capacite' => 0,
            'capacites_jours' => [100, ''],
            'tarifs' => [
                ['nom' => 'Jour', 'prix' => 5000, 'qte' => 70, 'jour' => '0'],
                ['nom' => 'Jour', 'prix' => 5000, 'qte' => 70, 'jour' => '1'],
                ['nom' => 'Pack Simple', 'prix' => 8000, 'qte' => 30, 'jour' => 'pack'],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, Evenement::count());
    }

    public function test_un_evenement_gratuit_sur_plusieurs_jours_a_un_seul_billet(): void
    {
        $this->envoyer([
            'gratuit' => 1,
            'duree' => 'plusieurs',
            'jours' => [$this->date(4, '18:00'), $this->date(5, '18:00')],
            'capacite' => 200,
            'meme_capacite' => 1,
            'co_parrainage_actif' => 1,
            'co_parrainage_pourcentage' => 10,
        ])->assertRedirect(route('admin.evenements.index'));

        $evenement = Evenement::firstOrFail();
        $this->assertTrue($evenement->gratuit);
        $this->assertFalse((bool) $evenement->co_parrainage_actif);
        $this->assertSame([['Gratuit', null]], $evenement->tarifs()->get()->map(fn ($t) => [$t->nom, $t->evenement_date_id])->all());
    }

    public function test_un_billet_personnalise_prend_son_nom_libre(): void
    {
        $this->envoyer([
            'duree' => 'un',
            'date_event' => $this->date(5, '20:00'),
            'capacite' => 100,
            'tarifs' => [['nom' => 'Personnalisé', 'nom_perso' => 'Enfant', 'prix' => 1000, 'qte' => 50]],
        ])->assertSessionHasNoErrors();

        $this->assertSame('Enfant', Evenement::firstOrFail()->tarifs()->value('nom'));
    }

    public function test_un_billet_personnalise_sans_nom_est_refuse(): void
    {
        $this->envoyer([
            'duree' => 'un',
            'date_event' => $this->date(5, '20:00'),
            'capacite' => 100,
            'tarifs' => [['nom' => 'Personnalisé', 'nom_perso' => '', 'prix' => 1000, 'qte' => 50]],
        ])->assertSessionHasErrors('tarifs.0.nom_perso');

        $this->assertSame(0, Evenement::count());
    }

    public function test_un_billet_rattache_a_un_jour_inexistant_est_refuse(): void
    {
        $this->envoyer([
            'duree' => 'plusieurs',
            'jours' => [$this->date(4, '18:00'), $this->date(5, '18:00')],
            'capacite' => 100,
            'meme_capacite' => 1,
            'tarifs' => [['nom' => 'Standard', 'prix' => 1000, 'qte' => 10, 'jour' => '7']],
        ])->assertSessionHasErrors('tarifs.0.jour');
    }

    public function test_la_categorie_autre_et_le_brouillon(): void
    {
        $this->envoyer([
            'categorie' => 'Autre',
            'autre_categorie' => 'Salon professionnel',
            'statut' => 'brouillon',
            'duree' => 'un',
            'date_event' => $this->date(5, '20:00'),
            'capacite' => 100,
            'tarifs' => [['nom' => 'Standard', 'prix' => 1000, 'qte' => 100]],
        ])->assertSessionHasNoErrors();

        $evenement = Evenement::firstOrFail();
        $this->assertSame('Salon professionnel', $evenement->categorie);
        $this->assertSame('brouillon', $evenement->statut);
    }

    public function test_un_evenement_sur_plusieurs_jours_exige_deux_jours(): void
    {
        $this->envoyer([
            'duree' => 'plusieurs',
            'jours' => [$this->date(4, '18:00')],
            'capacite' => 100,
            'tarifs' => [['nom' => 'Standard', 'prix' => 1000, 'qte' => 10, 'jour' => '0']],
        ])->assertSessionHasErrors('jours');
    }

    public function test_la_page_de_l_assistant_s_affiche_et_rouvre_l_etape_en_erreur(): void
    {
        // Mise en page minimale : le vrai layout interroge des tables hors sujet ici.
        $dossier = sys_get_temp_dir().'/wz-layout-'.uniqid();
        mkdir($dossier.'/layouts', 0777, true);
        file_put_contents($dossier.'/layouts/app.blade.php', "@yield('styles')@yield('content')@yield('scripts')");
        \Illuminate\Support\Facades\View::getFinder()->prependLocation($dossier);

        $this->actingAs($this->organisateur)
            ->get(route('admin.evenements.create'))
            ->assertOk()
            ->assertSee('id="wz-form"', false)
            ->assertSee('Gratuit ou payant');

        // Après un refus, la page revient avec la saisie et l'étape en erreur.
        $this->from(route('admin.evenements.create'))->envoyer([
            'duree' => 'plusieurs',
            'jours' => [$this->date(4, '18:00'), $this->date(5, '18:00')],
            'capacite' => 10,
            'meme_capacite' => 1,
            'tarifs' => [
                ['nom' => 'Jour', 'prix' => 1000, 'qte' => 50, 'jour' => '0'],
                ['nom' => 'Jour', 'prix' => 1000, 'qte' => 5, 'jour' => '1'],
            ],
        ])->assertRedirect(route('admin.evenements.create'));

        $this->actingAs($this->organisateur)
            ->get(route('admin.evenements.create'))
            ->assertOk()
            ->assertSee('const ETAPE_ERREUR = "billets"', false)
            ->assertSee('au-delà de la capacité du jour');
    }

    private function envoyer(array $donnees)
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

    private function date(int $jours, string $heure): string
    {
        return now()->addDays($jours)->format('Y-m-d').'T'.$heure;
    }

    private function creerOrganisateur(): User
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

    private function createTables(): void
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
            $table->timestamps();
        });
    }
}
