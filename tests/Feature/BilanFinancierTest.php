<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\LotPhysique;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\BilanFinancier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

// Bilan super admin : en ligne, espèces et physiques séparés ; solde à verser
class BilanFinancierTest extends TestCase
{
    use RefreshDatabase;

    private function billet(Evenement $e, float $montant, string $methode, string $statut = 'payé', ?int $lot = null): void
    {
        Ticket::create([
            'evenement_id' => $e->id, 'code_unique' => 'PAX-'.Str::random(8), 'qr_signature' => Str::random(20),
            'nom_acheteur' => 'Test', 'email_acheteur' => 't@t.test', 'montant' => $montant, 'quantite' => 1,
            'statut_paiement' => $statut, 'methode_paiement' => $methode, 'type_paiement' => $methode,
            'lot_physique_id' => $lot, 'source' => $lot ? 'physique' : null, 'transaction_id' => Str::random(10),
            'date_achat' => now(),
        ]);
    }

    public function test_les_billets_physiques_ne_sont_pas_comptes_comme_ventes(): void
    {
        $org = User::factory()->create(['role' => 'admin', 'statut' => 'actif']);
        $e = Evenement::create(['user_id' => $org->id, 'titre' => 'Festival', 'date_event' => now()->addWeek(),
            'lieu' => 'Abomey', 'capacite' => 5000, 'statut' => 'publié', 'gratuit' => false]);
        $lot = LotPhysique::create(['user_id' => $org->id, 'evenement_id' => $e->id, 'nom' => 'Lot', 'quantite' => 2, 'statut' => 'transmis', 'auto_genere' => false]);

        $this->billet($e, 2500, 'mtn');
        $this->billet($e, 2500, 'mtn');
        $this->billet($e, 1000, 'mtn', 'échoué');          // jamais payé : ignoré
        $this->billet($e, 1000, 'especes');                // agent de vente
        $this->billet($e, 1000, 'especes', 'payé', $lot->id); // physique
        $this->billet($e, 1000, 'especes', 'payé', $lot->id);

        $b = app(BilanFinancier::class)->pourEvenements(collect([$e]));

        $this->assertSame([2, 5000.0], [$b['mobile_nb'], (float) $b['mobile']]);
        $this->assertSame([1, 1000.0], [$b['especes_nb'], (float) $b['especes']]);
        $this->assertSame([2, 2000.0], [$b['physique_nb'], (float) $b['physique']]);
        $this->assertSame(8000.0, (float) $b['montant_genere']);

        Withdrawal::create(['user_id' => $org->id, 'montant' => 1000, 'nom' => 'Org', 'mobile' => '0100000000', 'reseau' => 'mtn', 'status' => 'payé']);
        $solde = $org->fresh()->soldeRetrait();
        $this->assertSame(1000.0, (float) $solde['retraitsPayes']);
        $this->assertEqualsWithDelta(
            $solde['mobileRecettes'] - $solde['commissionImputee'] - $solde['commissionAffiliation'] - 1000,
            $solde['soldeDisponible'], 0.01
        );
    }

    public function test_le_super_admin_telecharge_la_fiche_excel_des_retraits(): void
    {
        $sa = User::factory()->create(['role' => 'super_admin', 'statut' => 'actif']);
        $org = User::factory()->create(['role' => 'admin', 'statut' => 'actif', 'organisation' => 'Abomey Events']);
        Withdrawal::create(['user_id' => $org->id, 'montant' => 50000, 'nom' => 'Org', 'mobile' => '0100000000', 'reseau' => 'mtn', 'status' => 'payé']);
        Withdrawal::create(['user_id' => $org->id, 'montant' => 20000, 'nom' => 'Org', 'mobile' => '0100000000', 'reseau' => 'moov', 'status' => 'rejeté']);

        $reponse = $this->actingAs($sa, 'superadmin')->get(route('superadmin.organisateurs.retraits-excel', $org));
        $reponse->assertOk();
        $this->assertStringContainsString('Retraits-abomey-events-', $reponse->headers->get('Content-Disposition'));

        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($reponse->getFile()->getPathname()) === true);
        $feuille = $zip->getFromName('xl/worksheets/sheet1.xml');
        $this->assertStringContainsString('Abomey Events', $feuille);
        $this->assertStringContainsString('Payé', $feuille);
        $this->assertStringContainsString('Rejeté', $feuille);
        $this->assertStringContainsString('<v>50000</v>', $feuille);
        $this->assertNotFalse($zip->getFromName('xl/worksheets/sheet3.xml'));
        $zip->close();
    }
}
