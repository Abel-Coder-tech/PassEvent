<?php

namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Le net retirable de l'organisateur doit déduire les commissions des affiliés.
class SoldeRetirableAffiliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_les_commissions_affilies_sont_retirees_du_net(): void
    {
        $organisateur = User::factory()->create(['role' => 'admin', 'commission_pourcentage' => 10]);
        $evenementId = DB::table('evenement')->insertGetId([
            'user_id' => $organisateur->id,
            'titre' => 'Concert',
            'slug' => 'concert-'.uniqid(),
            'date_event' => now()->addMonth(),
            'lieu' => 'Cotonou',
            'statut' => 'publié',
            'capacite' => 100,
            'quota_vendu' => 0,
            'gratuit' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 10 billets à 5 000 F payés en mobile money = 50 000 F
        $tickets = collect(range(1, 10))->map(fn () => Ticket::forceCreate([
            'evenement_id' => $evenementId,
            'montant' => 5000,
            'quantite' => 1,
            'statut_paiement' => 'payé',
            'methode_paiement' => 'mobile_money',
            'nom_acheteur' => 'Client',
            'email_acheteur' => 'client@example.test',
            'transaction_id' => 'T-'.uniqid(),
            'code_unique' => strtoupper(uniqid('PX')),
            'qr_signature' => bin2hex(random_bytes(16)),
            'date_achat' => now(),
        ]));

        $affilie = Affiliate::forceCreate([
            'nom' => 'Affilié',
            'email' => 'aff@example.test',
            'code_parrainage' => 'AFF'.uniqid(),
            'password' => bcrypt('secret-123'),
        ]);

        // 5 % de co-parrainage sur chaque billet (250 F), dont une commission annulée
        foreach ($tickets as $i => $ticket) {
            AffiliateCommission::forceCreate([
                'affiliate_id' => $affilie->id,
                'evenement_id' => $evenementId,
                'ticket_id' => $ticket->id,
                'type' => AffiliateCommission::TYPE_CO_PARRAINAGE,
                'base_montant' => 5000,
                'pourcentage' => 5,
                'montant' => 250,
                'statut' => $i === 0 ? 'annule' : 'en_attente',
            ]);
        }

        $stats = $organisateur->fresh()->statsFinancieres();
        $this->assertSame(2250.0, $stats['commissionAffiliation']); // 9 × 250, l'annulée exclue

        // 50 000 − 5 000 (PaxEvent) − 2 250 (affiliés) = 42 750
        $this->actingAs($organisateur)
            ->get(route('admin.retraits.index'))
            ->assertOk()
            ->assertViewHas('soldeDisponible', 42750.0)
            ->assertViewHas('commissionAffiliation', 2250.0);
    }
}
