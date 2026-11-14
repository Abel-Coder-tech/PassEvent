<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\Tarif;
use App\Models\Ticket;
use App\Services\AffiliationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Compteurs de places : les billets physiques n'occupent pas la jauge en
 * ligne et la commande de recalcul remet quota_vendu / quantite_vendue d'aplomb.
 */
class CompteursCapaciteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('evenement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('titre');
            $table->string('slug')->unique();
            $table->dateTime('date_event')->nullable();
            $table->unsignedInteger('capacite')->default(0);
            $table->unsignedInteger('quota_vendu')->default(0);
            $table->boolean('gratuit')->default(false);
            $table->string('statut')->default('publié');
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
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->unsignedBigInteger('evenement_date_id')->nullable();
            $table->unsignedBigInteger('agent_vente_id')->nullable();
            $table->unsignedBigInteger('lot_physique_id')->nullable();
            $table->string('source')->nullable();
            $table->string('code_unique')->nullable();
            $table->unsignedInteger('quantite')->default(1);
            $table->string('statut_paiement')->default('payé');
            $table->boolean('annule')->default(false);
            $table->timestamp('annule_at')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->timestamp('reservation_expire_le')->nullable();
            $table->timestamps();
        });

        $this->mock(AffiliationService::class);
    }

    public function test_les_billets_physiques_sont_exclus_du_filtre_de_capacite(): void
    {
        [$evenement, $tarif] = $this->creerEvenement();

        $this->creerBillet($evenement->id, $tarif->id, 2);
        $this->creerBillet($evenement->id, $tarif->id, 5, ['lot_physique_id' => 9]);
        $this->creerBillet($evenement->id, $tarif->id, 4, ['source' => 'physique']);

        $this->assertSame(2, (int) Ticket::where('evenement_id', $evenement->id)->actifsPourCapacite()->sum('quantite'));
    }

    public function test_la_commande_recalcule_les_compteurs(): void
    {
        [$evenement, $tarif] = $this->creerEvenement(['quota_vendu' => 99]);
        $tarif->update(['quantite_vendue' => 50]);

        $this->creerBillet($evenement->id, $tarif->id, 3);
        $this->creerBillet($evenement->id, $tarif->id, 2, ['statut_paiement' => 'en_attente', 'reservation_expire_le' => now()->addMinutes(10)]);
        $this->creerBillet($evenement->id, $tarif->id, 7, ['annule' => true]);
        $this->creerBillet($evenement->id, $tarif->id, 6, ['statut_paiement' => 'échoué']);
        $this->creerBillet($evenement->id, $tarif->id, 8, ['lot_physique_id' => 1]);

        $this->artisan('evenements:recalculer-compteurs', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame(99, (int) $evenement->fresh()->quota_vendu);

        $this->artisan('evenements:recalculer-compteurs')->assertSuccessful();

        // Quota : payé (3) + réservation en cours (2). Tarif : payés seulement (3).
        $this->assertSame(5, (int) $evenement->fresh()->quota_vendu);
        $this->assertSame(3, (int) $tarif->fresh()->quantite_vendue);
    }

    private function creerEvenement(array $attributs = []): array
    {
        $id = DB::table('evenement')->insertGetId(array_merge([
            'titre' => 'Concert', 'slug' => 'concert', 'date_event' => now()->addWeek(),
            'capacite' => 100, 'created_at' => now(), 'updated_at' => now(),
        ], $attributs));
        $tarifId = DB::table('tarifs')->insertGetId([
            'evenement_id' => $id, 'nom' => 'Standard', 'prix' => 1000,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return [Evenement::find($id), Tarif::find($tarifId)];
    }

    private function creerBillet(int $evenementId, int $tarifId, int $quantite, array $overrides = []): int
    {
        return DB::table('ticket')->insertGetId(array_merge([
            'evenement_id' => $evenementId, 'tarif_id' => $tarifId,
            'code_unique' => 'PAX-'.uniqid(), 'quantite' => $quantite,
            'statut_paiement' => 'payé', 'annule' => false,
            'created_at' => now(), 'updated_at' => now(),
        ], $overrides));
    }
}
