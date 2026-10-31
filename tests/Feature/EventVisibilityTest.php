<?php

namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\AffiliateEventLink;
use App\Models\Evenement;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EventVisibilityTest extends TestCase
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
                $table->string('categorie')->nullable();
                $table->boolean('gratuit')->default(false);
                $table->boolean('co_parrainage_actif')->default(false);
                $table->decimal('co_parrainage_pourcentage', 5, 2)->nullable();
                $table->timestamps();
            });
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

        if (! Schema::hasTable('affiliates')) {
            Schema::create('affiliates', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('code_parrainage')->unique();
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('affiliate_event_links')) {
            Schema::create('affiliate_event_links', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('evenement_id');
                $table->unsignedBigInteger('affiliate_id');
                $table->string('code')->unique();
                $table->decimal('pourcentage', 5, 2)->default(0);
                $table->boolean('actif')->default(true);
                $table->unsignedInteger('clics')->default(0);
                $table->timestamps();
            });
        }
    }

    public function test_un_evenement_multi_jours_reste_visible_si_une_session_future(): void
    {
        $evenement = $this->creerEvenement(now()->subDay());
        $evenement->dates()->create([
            'date_debut' => now()->addDay(),
            'ordre' => 1,
        ]);

        $this->assertTrue(
            Evenement::withUpcomingDate()->whereKey($evenement->id)->exists()
        );
    }

    public function test_un_evenement_futur_sans_session_enregistree_reste_visible(): void
    {
        $evenement = $this->creerEvenement(now()->addDay());

        $this->assertTrue(
            Evenement::withUpcomingDate()->whereKey($evenement->id)->exists()
        );
    }

    public function test_un_affilie_peut_retirer_un_evenement_de_ses_liens_sans_supprimer_levenement(): void
    {
        $affiliate = Affiliate::create([
            'nom' => 'Affilié Test',
            'email' => uniqid('affiliate-').'@paxevent.test',
            'password' => bcrypt('secret'),
            'code_parrainage' => uniqid('PAXAFF-'),
            'actif' => true,
        ]);
        $evenement = $this->creerEvenement(now()->addDay());
        $link = AffiliateEventLink::create([
            'evenement_id' => $evenement->id,
            'affiliate_id' => $affiliate->id,
            'code' => strtoupper(uniqid('LINK')),
            'pourcentage' => 5,
            'actif' => true,
        ]);

        $this->actingAs($affiliate, 'affiliate')
            ->delete(route('affiliate.liens.destroy', $link))
            ->assertRedirect(route('affiliate.liens'));

        $this->assertDatabaseMissing('affiliate_event_links', ['id' => $link->id]);
        $this->assertDatabaseHas('evenement', ['id' => $evenement->id]);
    }

    private function creerEvenement(\DateTimeInterface $date): Evenement
    {
        return Evenement::create([
            'user_id' => 1,
            'titre' => 'Événement Test '.uniqid(),
            'date_event' => $date,
            'lieu' => 'Cotonou',
            'capacite' => 100,
            'statut' => 'publié',
        ]);
    }
}
