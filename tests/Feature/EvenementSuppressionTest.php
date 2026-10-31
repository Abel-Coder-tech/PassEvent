<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EvenementSuppressionTest extends TestCase
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
                $table->boolean('gratuit')->default(false);
                $table->timestamps();
            });
        } elseif (! Schema::hasColumn('evenement', 'gratuit')) {
            Schema::table('evenement', fn (Blueprint $table) => $table->boolean('gratuit')->default(false));
        }

        if (! Schema::hasTable('evenement_dates')) {
            Schema::create('evenement_dates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('evenement_id');
                $table->dateTime('date_debut');
                $table->unsignedInteger('ordre')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ticket')) {
            Schema::create('ticket', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('evenement_id');
                $table->string('statut_paiement')->default('en_attente');
                $table->unsignedBigInteger('lot_physique_id')->nullable();
                $table->boolean('annule')->default(false);
                $table->string('source')->nullable();
                $table->timestamps();
            });
        } else {
            foreach ([
                'statut_paiement' => fn (Blueprint $table) => $table->string('statut_paiement')->default('en_attente'),
                'lot_physique_id' => fn (Blueprint $table) => $table->unsignedBigInteger('lot_physique_id')->nullable(),
                'annule' => fn (Blueprint $table) => $table->boolean('annule')->default(false),
                'source' => fn (Blueprint $table) => $table->string('source')->nullable(),
            ] as $column => $addColumn) {
                if (! Schema::hasColumn('ticket', $column)) {
                    Schema::table('ticket', $addColumn);
                }
            }
        }
    }

    public function test_un_evenement_payant_avec_une_vente_en_ligne_ne_peut_pas_etre_supprime_avant_sa_date(): void
    {
        [$user, $evenement] = $this->creerEvenement();
        $this->creerTicket($evenement, lotPhysiqueId: null);
        $evenementCharge = Evenement::with('dates')
            ->withExists(['ventesPayeesHorsBilletsPhysiques as has_successful_non_physical_sales'])
            ->findOrFail($evenement->id);

        $this->assertTrue($evenementCharge->suppressionOrganisateurBloquee());

        $this->actingAs($user)
            ->delete(route('admin.evenements.destroy', $evenement))
            ->assertRedirect(route('admin.evenements.index'))
            ->assertSessionHas('evenement_suppression_bloquee', true);

        $this->assertDatabaseHas('evenement', ['id' => $evenement->id]);
    }

    public function test_une_vente_manuelle_bloque_aussi_la_suppression(): void
    {
        [$user, $evenement] = $this->creerEvenement();
        $this->creerTicket($evenement, lotPhysiqueId: null, source: 'manuel');

        $this->actingAs($user)
            ->delete(route('admin.evenements.destroy', $evenement))
            ->assertRedirect(route('admin.evenements.index'))
            ->assertSessionHas('evenement_suppression_bloquee', true);

        $this->assertDatabaseHas('evenement', ['id' => $evenement->id]);
    }

    public function test_une_vente_de_billet_physique_ne_bloque_pas_la_suppression(): void
    {
        [$user, $evenement] = $this->creerEvenement();
        $this->creerTicket($evenement, lotPhysiqueId: 7, source: 'physique');

        $this->actingAs($user)
            ->delete(route('admin.evenements.destroy', $evenement))
            ->assertRedirect(route('admin.evenements.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('evenement', ['id' => $evenement->id]);
    }

    public function test_une_vente_payee_annulee_ne_bloque_pas_la_suppression(): void
    {
        [$user, $evenement] = $this->creerEvenement();
        $this->creerTicket($evenement, lotPhysiqueId: null, annule: true);

        $this->actingAs($user)
            ->delete(route('admin.evenements.destroy', $evenement))
            ->assertRedirect(route('admin.evenements.index'));

        $this->assertDatabaseMissing('evenement', ['id' => $evenement->id]);
    }

    public function test_un_evenement_dont_la_derniere_session_est_passee_peut_etre_supprime(): void
    {
        [$user, $evenement] = $this->creerEvenement(now()->subDay());
        $this->creerTicket($evenement, lotPhysiqueId: null);
        $evenement->dates()->create([
            'date_debut' => now()->subHour(),
            'ordre' => 1,
        ]);

        $this->actingAs($user)
            ->delete(route('admin.evenements.destroy', $evenement))
            ->assertRedirect(route('admin.evenements.index'));

        $this->assertDatabaseMissing('evenement', ['id' => $evenement->id]);
    }

    public function test_un_evenement_multi_jours_avec_une_session_future_reste_protege(): void
    {
        [$user, $evenement] = $this->creerEvenement(now()->subDay());
        $this->creerTicket($evenement, lotPhysiqueId: null);
        $evenement->dates()->create([
            'date_debut' => now()->addDay(),
            'ordre' => 1,
        ]);

        $this->actingAs($user)
            ->delete(route('admin.evenements.destroy', $evenement))
            ->assertRedirect(route('admin.evenements.index'))
            ->assertSessionHas('evenement_suppression_bloquee', true);

        $this->assertDatabaseHas('evenement', ['id' => $evenement->id]);
    }

    private function creerEvenement(?\DateTimeInterface $date = null): array
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
            'date_event' => $date ?? now()->addDay(),
            'lieu' => 'Cotonou',
            'capacite' => 100,
            'statut' => 'publié',
            'gratuit' => false,
        ]);

        return [$user, $evenement];
    }

    private function creerTicket(
        Evenement $evenement,
        ?int $lotPhysiqueId,
        string $source = 'en_ligne',
        bool $annule = false,
    ): void {
        DB::table('ticket')->insert([
            'evenement_id' => $evenement->id,
            'statut_paiement' => 'payé',
            'lot_physique_id' => $lotPhysiqueId,
            'annule' => $annule,
            'source' => $source,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
