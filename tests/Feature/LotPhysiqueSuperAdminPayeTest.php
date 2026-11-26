<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\LotPhysique;
use App\Models\Tarif;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Un lot de tickets physiques généré par PaxEvent est considéré comme payé
class LotPhysiqueSuperAdminPayeTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_lot_genere_par_paxevent_demarre_en_paye(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin', 'statut' => 'actif']);
        $organisateur = User::factory()->create(['role' => 'admin', 'statut' => 'actif']);
        $evenement = Evenement::create([
            'user_id' => $organisateur->id,
            'titre' => 'Concert',
            'date_event' => now()->addWeek(),
            'lieu' => 'Cotonou',
            'capacite' => 500,
            'statut' => 'publié',
            'gratuit' => false,
        ]);
        $tarif = Tarif::create([
            'evenement_id' => $evenement->id,
            'nom' => 'Standard',
            'prix' => 5000,
            'quantite' => 500,
            'statut' => 'actif',
        ]);

        $this->actingAs($superadmin, 'superadmin')
            ->post(route('superadmin.tickets-physiques.store'), [
                'user_id' => $organisateur->id,
                'evenement_id' => $evenement->id,
                'tarif_id' => $tarif->id,
                'nom' => 'Lot imprimerie',
                'quantite' => 3,
            ])
            ->assertSessionHasNoErrors()->assertSessionMissing('error');

        $lot = LotPhysique::firstOrFail();
        $this->assertSame(LotPhysique::STATUT_PAYE, $lot->statut);
        $this->assertSame('Payé — QR à générer', $lot->statutLibelle());
        $this->assertSame(3, $lot->tickets()->where('statut_paiement', 'payé')->count());
    }
}
