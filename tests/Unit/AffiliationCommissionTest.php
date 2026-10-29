<?php

namespace Tests\Unit;

use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliateEventLink;
use App\Models\AffiliatePayout;
use App\Models\AffiliateReferral;
use App\Models\Evenement;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AffiliationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AffiliationCommissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('affiliate_commissions');
        Schema::dropIfExists('affiliate_payouts');
        Schema::dropIfExists('affiliate_event_links');
        Schema::dropIfExists('affiliate_referrals');
        Schema::dropIfExists('affiliates');
        Schema::dropIfExists('ticket');
        Schema::dropIfExists('evenement');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->string('email')->unique();
            $table->string('mot_de_passe')->nullable();
            $table->string('role')->default('admin');
            $table->string('statut')->default('complet');
            $table->float('commission_pourcentage')->default(10);
            $table->timestamps();
        });

        Schema::create('evenement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('titre');
            $table->string('slug')->nullable();
            $table->dateTime('date_event')->nullable();
            $table->enum('statut', ['brouillon', 'publié', 'terminé', 'annulé'])->default('brouillon');
            $table->boolean('gratuit')->default(false);
            $table->float('commission_pourcentage')->nullable();
            $table->boolean('co_parrainage_actif')->default(false);
            $table->float('co_parrainage_pourcentage')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('evenement')->cascadeOnDelete();
            $table->foreignId('tarif_id')->nullable();
            $table->string('code_unique')->nullable();
            $table->string('qr_signature')->nullable();
            $table->string('nom_acheteur')->nullable();
            $table->string('email_acheteur')->nullable();
            $table->string('nom_tarif')->nullable();
            $table->decimal('montant', 10, 2)->default(0);
            $table->integer('quantite')->default(1);
            $table->enum('statut_paiement', ['en_attente', 'payé', 'annulé', 'remboursé'])->default('en_attente');
            $table->boolean('annule')->default(false);
            $table->foreignId('lot_physique_id')->nullable();
            $table->foreignId('affiliate_event_link_id')->nullable();
            $table->timestamps();
        });

        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->string('email')->unique();
            $table->string('pays', 2)->default('BJ');
            $table->string('password');
            $table->string('code_parrainage', 20)->unique();
            $table->unsignedInteger('clics_parrainage')->default(0);
            $table->string('operateur_paiement')->nullable();
            $table->string('numero_paiement')->nullable();
            $table->string('titulaire_paiement')->nullable();
            $table->decimal('solde_en_attente', 12, 2)->default(0);
            $table->decimal('solde_disponible', 12, 2)->default(0);
            $table->decimal('total_gagne', 12, 2)->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('affiliate_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->foreignId('organisateur_id')->constrained('users')->cascadeOnDelete();
            $table->string('code_parrainage')->nullable();
            $table->foreignId('premier_evenement_id')->nullable()->constrained('evenement')->nullOnDelete();
            $table->enum('statut', ['en_attente', 'valide', 'annule'])->default('en_attente');
            $table->timestamp('valide_le')->nullable();
            $table->timestamps();
            $table->unique('organisateur_id');
        });

        Schema::create('affiliate_event_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('evenement')->cascadeOnDelete();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->decimal('pourcentage', 5, 2)->default(0);
            $table->boolean('actif')->default(true);
            $table->integer('clics')->default(0);
            $table->timestamps();
            $table->unique(['evenement_id', 'affiliate_id']);
        });

        Schema::create('affiliate_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->decimal('montant', 12, 2);
            $table->string('operateur')->nullable();
            $table->string('numero_paiement')->nullable();
            $table->string('titulaire')->nullable();
            $table->enum('statut', ['demande', 'en_cours', 'paye', 'refuse'])->default('demande');
            $table->text('note')->nullable();
            $table->timestamp('traite_le')->nullable();
            $table->timestamps();
        });

        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->foreignId('evenement_id')->constrained('evenement')->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained('ticket')->cascadeOnDelete();
            $table->foreignId('affiliate_event_link_id')->nullable()->constrained('affiliate_event_links')->nullOnDelete();
            $table->enum('type', ['parrainage_direct', 'co_parrainage']);
            $table->decimal('base_montant', 12, 2)->default(0);
            $table->decimal('pourcentage', 5, 2)->default(0);
            $table->decimal('montant', 12, 2)->default(0);
            $table->enum('statut', ['en_attente', 'disponible', 'paye', 'annule'])->default('en_attente');
            $table->timestamp('disponible_le')->nullable();
            $table->timestamp('paye_le')->nullable();
            $table->foreignId('affiliate_payout_id')->nullable()->constrained('affiliate_payouts')->nullOnDelete();
            $table->timestamps();

            $table->unique(['ticket_id', 'type']);
        });
    }

    public function test_mecanisme_b_cree_commission_sur_billet_paye_avec_lien_affilie(): void
    {
        $organisateur = User::create([
            'nom' => 'Org',
            'email' => 'org@ex.com',
            'mot_de_passe' => 'x',
            'role' => 'admin',
            'statut' => 'complet',
            'commission_pourcentage' => 10,
        ]);

        $evenement = Evenement::create([
            'user_id' => $organisateur->id,
            'titre' => 'Concert',
            'date_event' => now()->addDay(),
            'statut' => 'publié',
            'gratuit' => false,
            'co_parrainage_actif' => true,
            'co_parrainage_pourcentage' => 15,
        ]);

        $affiliate = Affiliate::create([
            'nom' => 'Aff',
            'email' => 'aff@ex.com',
            'password' => 'x',
            'code_parrainage' => 'PAXAFF-ABC123',
            'actif' => true,
        ]);

        $lien = AffiliateEventLink::create([
            'evenement_id' => $evenement->id,
            'affiliate_id' => $affiliate->id,
            'code' => 'LIEN123',
            'pourcentage' => 15,
            'actif' => true,
        ]);

        $ticket = Ticket::create([
            'evenement_id' => $evenement->id,
            'code_unique' => 'T1',
            'nom_tarif' => 'Standard',
            'montant' => 10000,
            'statut_paiement' => 'payé',
            'affiliate_event_link_id' => $lien->id,
        ]);

        $this->assertDatabaseHas('affiliate_commissions', [
            'ticket_id' => $ticket->id,
            'affiliate_id' => $affiliate->id,
            'type' => AffiliateCommission::TYPE_CO_PARRAINAGE,
            'montant' => 1500,
            'statut' => 'en_attente',
        ]);

        $affiliate->refresh();
        $this->assertEquals(1500, (float) $affiliate->solde_en_attente);
    }

    public function test_parrainage_direct_historique_ne_cree_pas_de_nouvelle_commission(): void
    {
        $organisateur = User::create([
            'nom' => 'Org',
            'email' => 'org2@ex.com',
            'mot_de_passe' => 'x',
            'role' => 'admin',
            'statut' => 'complet',
            'commission_pourcentage' => 10,
        ]);

        $evenement = Evenement::create([
            'user_id' => $organisateur->id,
            'titre' => 'Premier',
            'date_event' => now()->addDay(),
            'statut' => 'publié',
            'gratuit' => false,
        ]);

        $affiliate = Affiliate::create([
            'nom' => 'Aff2',
            'email' => 'aff2@ex.com',
            'password' => 'x',
            'code_parrainage' => 'PAXAFF-DEF456',
            'actif' => true,
        ]);

        AffiliateReferral::create([
            'affiliate_id' => $affiliate->id,
            'organisateur_id' => $organisateur->id,
            'code_parrainage' => $affiliate->code_parrainage,
            'statut' => 'en_attente',
        ]);

        $ticket = Ticket::create([
            'evenement_id' => $evenement->id,
            'code_unique' => 'T2',
            'nom_tarif' => 'Standard',
            'montant' => 10000,
            'statut_paiement' => 'payé',
        ]);

        $this->assertDatabaseMissing('affiliate_commissions', [
            'ticket_id' => $ticket->id,
            'type' => AffiliateCommission::TYPE_PARRAINAGE,
        ]);
        $this->assertDatabaseHas('affiliate_referrals', [
            'organisateur_id' => $organisateur->id,
            'premier_evenement_id' => null,
            'statut' => 'en_attente',
        ]);
    }

    public function test_billets_gratuits_et_lots_physiques_exclus(): void
    {
        $organisateur = User::create([
            'nom' => 'Org',
            'email' => 'org3@ex.com',
            'mot_de_passe' => 'x',
            'role' => 'admin',
            'statut' => 'complet',
            'commission_pourcentage' => 10,
        ]);

        $gratuit = Evenement::create([
            'user_id' => $organisateur->id,
            'titre' => 'Gratuit',
            'date_event' => now()->addDay(),
            'statut' => 'publié',
            'gratuit' => true,
        ]);

        $payant = Evenement::create([
            'user_id' => $organisateur->id,
            'titre' => 'Payant',
            'date_event' => now()->addDay(),
            'statut' => 'publié',
            'gratuit' => false,
        ]);

        $affiliate = Affiliate::create([
            'nom' => 'Aff3',
            'email' => 'aff3@ex.com',
            'password' => 'x',
            'code_parrainage' => 'PAXAFF-GHI789',
            'actif' => true,
        ]);

        $lien = AffiliateEventLink::create([
            'evenement_id' => $payant->id,
            'affiliate_id' => $affiliate->id,
            'code' => 'LIEN999',
            'pourcentage' => 10,
            'actif' => true,
        ]);

        AffiliateReferral::create([
            'affiliate_id' => $affiliate->id,
            'organisateur_id' => $organisateur->id,
            'code_parrainage' => $affiliate->code_parrainage,
        ]);

        // Événement gratuit → aucune commission
        Ticket::create([
            'evenement_id' => $gratuit->id,
            'code_unique' => 'TG1',
            'nom_tarif' => 'Gratuit',
            'montant' => 5000,
            'statut_paiement' => 'payé',
            'affiliate_event_link_id' => $lien->id,
        ]);

        // Lot physique → aucune commission
        Ticket::create([
            'evenement_id' => $payant->id,
            'code_unique' => 'TP1',
            'nom_tarif' => 'Physique',
            'montant' => 5000,
            'statut_paiement' => 'payé',
            'lot_physique_id' => 1,
            'affiliate_event_link_id' => $lien->id,
        ]);

        $this->assertDatabaseCount('affiliate_commissions', 0);
    }

    public function test_payout_lifecycle_allocate_et_confirme(): void
    {
        $service = app(AffiliationService::class);

        $affiliate = Affiliate::create([
            'nom' => 'Aff4',
            'email' => 'aff4@ex.com',
            'password' => 'x',
            'code_parrainage' => 'PAXAFF-JKL012',
            'actif' => true,
            'operateur_paiement' => 'MTN',
            'numero_paiement' => '97000000',
            'solde_disponible' => 0,
        ]);

        // Créer des commissions disponibles
        $organisateur = User::create([
            'nom' => 'Org',
            'email' => 'org4@ex.com',
            'mot_de_passe' => 'x',
            'role' => 'admin',
            'statut' => 'complet',
        ]);
        $evenement = Evenement::create([
            'user_id' => $organisateur->id,
            'titre' => 'E',
            'date_event' => now()->subDay(),
            'statut' => 'terminé',
            'gratuit' => false,
        ]);
        $ticket = Ticket::create([
            'evenement_id' => $evenement->id,
            'code_unique' => 'TX',
            'nom_tarif' => 'X',
            'montant' => 1000,
            'statut_paiement' => 'payé',
        ]);

        $c1 = AffiliateCommission::create([
            'affiliate_id' => $affiliate->id,
            'evenement_id' => $evenement->id,
            'ticket_id' => $ticket->id,
            'type' => AffiliateCommission::TYPE_CO_PARRAINAGE,
            'base_montant' => 1000,
            'pourcentage' => 10,
            'montant' => 3000,
            'statut' => 'disponible',
        ]);

        $ticket2 = Ticket::create([
            'evenement_id' => $evenement->id,
            'code_unique' => 'TY',
            'nom_tarif' => 'X',
            'montant' => 1000,
            'statut_paiement' => 'payé',
        ]);
        $c2 = AffiliateCommission::create([
            'affiliate_id' => $affiliate->id,
            'evenement_id' => $evenement->id,
            'ticket_id' => $ticket2->id,
            'type' => AffiliateCommission::TYPE_CO_PARRAINAGE,
            'base_montant' => 1000,
            'pourcentage' => 10,
            'montant' => 4000,
            'statut' => 'disponible',
        ]);

        $service->recalculerSoldes($affiliate);
        $affiliate->refresh();
        $this->assertEquals(7000, (float) $affiliate->solde_disponible);

        $payout = $service->creerDemandeReversement($affiliate, 5000);
        $this->assertEquals('demande', $payout->statut);
        $this->assertEquals(7000, (float) $payout->montant);
        $this->assertEquals(2, $payout->commissions()->count());

        $affiliate->refresh();
        $this->assertEquals(0, (float) $affiliate->solde_disponible);

        $this->assertTrue($service->approuverReversement($payout));
        $this->assertTrue($service->confirmerReversement($payout->fresh()));

        $c1->refresh(); $c2->refresh();
        $this->assertEquals('paye', $c1->statut);
        $this->assertEquals('paye', $c2->statut);
    }

    public function test_liberation_a_maturation_et_rejet_du_payout(): void
    {
        $service = app(AffiliationService::class);

        $organisateur = User::create([
            'nom' => 'Org',
            'email' => 'org5@ex.com',
            'mot_de_passe' => 'x',
            'role' => 'admin',
            'statut' => 'complet',
        ]);
        $evenement = Evenement::create([
            'user_id' => $organisateur->id,
            'titre' => 'Termine',
            'date_event' => now()->subDay(),
            'statut' => 'terminé',
            'gratuit' => false,
        ]);
        $affiliate = Affiliate::create([
            'nom' => 'Aff5',
            'email' => 'aff5@ex.com',
            'password' => 'x',
            'code_parrainage' => 'PAXAFF-MNO345',
            'actif' => true,
            'operateur_paiement' => 'Moov',
            'numero_paiement' => '95000000',
        ]);
        $ticket = Ticket::create([
            'evenement_id' => $evenement->id,
            'code_unique' => 'TZ',
            'nom_tarif' => 'X',
            'montant' => 2000,
            'statut_paiement' => 'payé',
        ]);

        $commission = AffiliateCommission::create([
            'affiliate_id' => $affiliate->id,
            'evenement_id' => $evenement->id,
            'ticket_id' => $ticket->id,
            'type' => AffiliateCommission::TYPE_CO_PARRAINAGE,
            'base_montant' => 2000,
            'pourcentage' => 10,
            'montant' => 200,
            'statut' => 'en_attente',
            'disponible_le' => now()->subMinute(),
        ]);

        $this->assertSame(1, $service->libererEcheues());
        $commission->refresh();
        $this->assertEquals('disponible', $commission->statut);

        $affiliate->refresh();
        $this->assertEquals(200, (float) $affiliate->solde_disponible);
        $this->assertEquals(0, (float) $affiliate->solde_en_attente);

        $payout = $service->creerDemandeReversement($affiliate, 200);
        $this->assertEquals(200, (float) $payout->montant);
        $this->assertTrue($service->rejeterReversement($payout, 'Coordonnees invalides'));

        $payout->refresh();
        $commission->refresh();
        $affiliate->refresh();
        $this->assertEquals('refuse', $payout->statut);
        $this->assertEquals('Coordonnees invalides', $payout->note);
        $this->assertEquals('disponible', $commission->statut);
        $this->assertNull($commission->affiliate_payout_id);
        $this->assertEquals(200, (float) $affiliate->solde_disponible);
    }

    public function test_remboursement_annule_la_commission(): void
    {
        $organisateur = User::create([
            'nom' => 'Org',
            'email' => 'org6@ex.com',
            'mot_de_passe' => 'x',
            'role' => 'admin',
            'statut' => 'complet',
            'commission_pourcentage' => 10,
        ]);
        $evenement = Evenement::create([
            'user_id' => $organisateur->id,
            'titre' => 'Rembourse',
            'date_event' => now()->addDay(),
            'statut' => 'publié',
            'gratuit' => false,
            'co_parrainage_actif' => true,
            'co_parrainage_pourcentage' => 10,
        ]);
        $affiliate = Affiliate::create([
            'nom' => 'Aff6',
            'email' => 'aff6@ex.com',
            'password' => 'x',
            'code_parrainage' => 'PAXAFF-PQR678',
            'actif' => true,
        ]);

        $lien = AffiliateEventLink::create([
            'evenement_id' => $evenement->id,
            'affiliate_id' => $affiliate->id,
            'code' => 'LIEN-RMB',
            'pourcentage' => 10,
        ]);

        $ticket = Ticket::create([
            'evenement_id' => $evenement->id,
            'code_unique' => 'TR',
            'nom_tarif' => 'X',
            'montant' => 5000,
            'statut_paiement' => 'payé',
            'affiliate_event_link_id' => $lien->id,
        ]);

        $this->assertDatabaseHas('affiliate_commissions', [
            'ticket_id' => $ticket->id,
            'statut' => 'en_attente',
        ]);

        $ticket->update(['statut_paiement' => 'remboursé']);

        $this->assertDatabaseHas('affiliate_commissions', [
            'ticket_id' => $ticket->id,
            'statut' => 'annule',
        ]);

        $affiliate->refresh();
        $this->assertEquals(0, (float) $affiliate->solde_en_attente);
        $this->assertEquals(0, (float) $affiliate->total_gagne);
    }

    public function test_commission_non_dupliquee_si_le_billet_est_repaye(): void
    {
        $organisateur = User::create([
            'nom' => 'Org',
            'email' => 'org7@ex.com',
            'mot_de_passe' => 'x',
            'role' => 'admin',
            'statut' => 'complet',
            'commission_pourcentage' => 10,
        ]);
        $evenement = Evenement::create([
            'user_id' => $organisateur->id,
            'titre' => 'Double',
            'date_event' => now()->addDay(),
            'statut' => 'publié',
            'gratuit' => false,
            'co_parrainage_actif' => true,
            'co_parrainage_pourcentage' => 10,
        ]);
        $affiliate = Affiliate::create([
            'nom' => 'Aff7',
            'email' => 'aff7@ex.com',
            'password' => 'x',
            'code_parrainage' => 'PAXAFF-STU901',
            'actif' => true,
        ]);
        $lien = AffiliateEventLink::create([
            'evenement_id' => $evenement->id,
            'affiliate_id' => $affiliate->id,
            'code' => 'LIEN-DBL',
            'pourcentage' => 10,
        ]);
        $ticket = Ticket::create([
            'evenement_id' => $evenement->id,
            'code_unique' => 'TD',
            'nom_tarif' => 'X',
            'montant' => 1000,
            'statut_paiement' => 'payé',
            'affiliate_event_link_id' => $lien->id,
        ]);

        $ticket->update(['statut_paiement' => 'en_attente']);
        $ticket->update(['statut_paiement' => 'payé']);

        $this->assertSame(1, AffiliateCommission::where('ticket_id', $ticket->id)->count());
    }
}
