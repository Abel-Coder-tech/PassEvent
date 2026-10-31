<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\Tarif;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TarifCapacityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('email')->unique();
                $table->string('mot_de_passe');
                $table->string('role')->default('admin');
                $table->string('statut')->default('actif');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('evenement')) {
            Schema::create('evenement', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('titre');
                $table->string('slug')->unique();
                $table->dateTime('date_event');
                $table->string('lieu');
                $table->unsignedInteger('capacite');
                $table->unsignedInteger('quota_vendu')->default(0);
                $table->string('statut')->default('publié');
                $table->string('categorie')->default('General');
                $table->string('type_evenement')->default('spectacle');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tarifs')) {
            Schema::create('tarifs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('evenement_id');
                $table->string('nom');
                $table->decimal('prix', 10, 2);
                $table->unsignedInteger('quantite_disponible')->nullable();
                $table->unsignedInteger('quantite_vendue')->default(0);
                $table->string('statut')->default('actif');
                $table->timestamps();
            });
        }
    }

    public function test_un_tarif_ne_peut_pas_depasser_la_capacite_de_levenement(): void
    {
        [$user, $evenement] = $this->creerEvenement(capacite: 100);

        $this->actingAs($user)
            ->post(route('admin.tarifs.store', $evenement), [
                'nom' => 'VIP',
                'prix' => 10000,
                'quantite_disponible' => 200,
            ])
            ->assertSessionHasErrors('quantite_disponible');

        $this->assertDatabaseCount('tarifs', 0);
    }

    public function test_la_somme_des_quantites_des_tarifs_ne_peut_pas_depasser_la_capacite(): void
    {
        [$user, $evenement] = $this->creerEvenement(capacite: 100);
        $this->creerTarif($evenement, quantiteDisponible: 80);

        $this->actingAs($user)
            ->post(route('admin.tarifs.store', $evenement), [
                'nom' => 'VIP',
                'prix' => 10000,
                'quantite_disponible' => 30,
            ])
            ->assertSessionHasErrors('quantite_disponible');

        $this->assertDatabaseCount('tarifs', 1);
    }

    public function test_la_somme_des_tarifs_peut_atteindre_exactement_la_capacite(): void
    {
        [$user, $evenement] = $this->creerEvenement(capacite: 100);
        $this->creerTarif($evenement, quantiteDisponible: 80);

        $this->actingAs($user)
            ->post(route('admin.tarifs.store', $evenement), [
                'nom' => 'VIP',
                'prix' => 10000,
                'quantite_disponible' => 20,
            ])
            ->assertRedirect(route('admin.tarifs.index', $evenement->id));

        $this->assertDatabaseCount('tarifs', 2);
        $this->assertDatabaseHas('tarifs', [
            'evenement_id' => $evenement->id,
            'nom' => 'VIP',
            'quantite_disponible' => 20,
        ]);
    }

    public function test_un_tarif_existant_ne_peut_pas_etre_modifie_au_dela_de_la_capacite(): void
    {
        [$user, $evenement] = $this->creerEvenement(capacite: 100);
        $tarif = $this->creerTarif($evenement, quantiteDisponible: 80);

        $this->actingAs($user)
            ->put(route('admin.tarifs.update', [$evenement, $tarif]), [
                'nom' => $tarif->nom,
                'prix' => $tarif->prix,
                'quantite_disponible' => 200,
            ])
            ->assertSessionHasErrors('quantite_disponible');

        $this->assertSame(80, (int) $tarif->refresh()->quantite_disponible);
    }

    public function test_la_modification_dun_tarif_ne_peut_pas_creer_un_depassement_de_capacite(): void
    {
        [$user, $evenement] = $this->creerEvenement(capacite: 100);
        $this->creerTarif($evenement, quantiteDisponible: 80);
        $tarif = $evenement->tarifs()->create([
            'nom' => 'VIP',
            'prix' => 10000,
            'quantite_disponible' => 20,
            'quantite_vendue' => 0,
            'statut' => 'actif',
        ]);

        $this->actingAs($user)
            ->put(route('admin.tarifs.update', [$evenement, $tarif]), [
                'nom' => $tarif->nom,
                'prix' => $tarif->prix,
                'quantite_disponible' => 21,
            ])
            ->assertSessionHasErrors('quantite_disponible');

        $this->assertSame(20, (int) $tarif->refresh()->quantite_disponible);
    }

    private function creerEvenement(int $capacite): array
    {
        $user = User::create([
            'nom' => 'Organisateur Test',
            'email' => uniqid('org-').'@paxevent.test',
            'mot_de_passe' => bcrypt('secret'),
            'role' => 'admin',
            'statut' => 'actif',
        ]);

        $evenement = Evenement::create([
            'user_id' => $user->id,
            'titre' => 'Événement Test '.uniqid(),
            'date_event' => now()->addDays(10),
            'lieu' => 'Cotonou',
            'capacite' => $capacite,
            'statut' => 'publié',
        ]);

        return [$user, $evenement];
    }

    private function creerTarif(Evenement $evenement, int $quantiteDisponible): Tarif
    {
        return $evenement->tarifs()->create([
            'nom' => 'Standard',
            'prix' => 5000,
            'quantite_disponible' => $quantiteDisponible,
            'quantite_vendue' => 0,
            'statut' => 'actif',
        ]);
    }
}
