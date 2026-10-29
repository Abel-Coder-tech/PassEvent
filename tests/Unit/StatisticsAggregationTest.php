<?php

namespace Tests\Unit;

use App\Models\Agent;
use App\Models\AgentVente;
use App\Models\Affiliate;
use App\Models\Evenement;
use App\Services\AffiliateStatisticsService;
use App\Services\AgentScanStatisticsService;
use App\Services\AgentVenteStatisticsService;
use App\Services\CodePromoStatisticsService;
use App\Services\EvenementStatisticsService;
use App\Services\TicketStatisticsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StatisticsAggregationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('evenement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('statut');
            $table->unsignedInteger('quota_vendu')->default(0);
        });

        Schema::create('tarifs', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
        });

        Schema::create('ticket', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id')->nullable();
            $table->unsignedBigInteger('agent_vente_id')->nullable();
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->unsignedBigInteger('lot_physique_id')->nullable();
            $table->string('statut_paiement')->nullable();
            $table->boolean('utilise')->default(false);
            $table->boolean('annule')->default(false);
            $table->string('nom_tarif')->nullable();
            $table->string('transaction_id')->nullable();
            $table->decimal('montant', 10, 2)->default(0);
            $table->dateTime('date_achat')->nullable();
            $table->string('methode_paiement')->nullable();
            $table->string('type_paiement')->nullable();
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->string('type_operation');
            $table->json('details')->nullable();
            $table->dateTime('created_at');
        });

        Schema::create('affiliate_event_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id');
            $table->boolean('actif')->default(true);
            $table->unsignedInteger('clics')->default(0);
        });

        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id');
            $table->unsignedBigInteger('evenement_id');
            $table->string('statut');
            $table->decimal('montant', 12, 2);
        });

        Schema::create('codes_promos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evenement_id');
            $table->unsignedInteger('nb_utilisations')->default(0);
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('logs');
        Schema::dropIfExists('affiliate_commissions');
        Schema::dropIfExists('affiliate_event_links');
        Schema::dropIfExists('codes_promos');
        Schema::dropIfExists('ticket');
        Schema::dropIfExists('tarifs');
        Schema::dropIfExists('evenement');

        parent::tearDown();
    }

    public function test_ticket_dashboard_metrics_are_aggregated_using_the_same_filters(): void
    {
        DB::table('ticket')->insert([
            [
                'statut_paiement' => 'payé',
                'utilise' => false,
                'nom_tarif' => 'Externe',
                'transaction_id' => 'FEDAPAY-1',
                'montant' => 100,
            ],
            [
                'statut_paiement' => 'payé',
                'utilise' => true,
                'nom_tarif' => 'Etudiant',
                'transaction_id' => 'MANUEL-1',
                'montant' => 50,
            ],
            [
                'statut_paiement' => 'échoué',
                'utilise' => false,
                'nom_tarif' => 'Etudiant',
                'transaction_id' => 'FEDAPAY-2',
                'montant' => 0,
            ],
            [
                'statut_paiement' => 'remboursé',
                'utilise' => false,
                'nom_tarif' => 'Externe',
                'transaction_id' => null,
                'montant' => 25,
            ],
        ]);

        DB::enableQueryLog();
        DB::flushQueryLog();
        $service = app(TicketStatisticsService::class);
        $stats = $service->organisateur(\App\Models\Ticket::query());

        $this->assertCount(1, DB::getQueryLog());
        $this->assertSame([
            'total_tickets' => 4,
            'valides' => 1,
            'scannes' => 1,
            'etudiants' => 2,
            'annules' => 1,
            'transactions_reussies' => 1,
            'transactions_echouees' => 1,
        ], $stats);
    }

    public function test_event_dashboard_metrics_are_aggregated_by_owner_and_status(): void
    {
        DB::table('evenement')->insert([
            ['user_id' => 8, 'statut' => 'publié', 'quota_vendu' => 5],
            ['user_id' => 8, 'statut' => 'brouillon', 'quota_vendu' => 1],
            ['user_id' => 8, 'statut' => 'terminé', 'quota_vendu' => 2],
            ['user_id' => 8, 'statut' => 'annulé', 'quota_vendu' => 9],
            ['user_id' => 9, 'statut' => 'publié', 'quota_vendu' => 100],
        ]);

        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->assertSame([
            'total_evenements' => 4,
            'en_cours' => 1,
            'a_venir' => 1,
            'termines' => 1,
            'total_billets_vendus' => 17,
        ], app(EvenementStatisticsService::class)->pourOrganisateur(8));
        $this->assertCount(1, DB::getQueryLog());
    }

    public function test_physical_ticket_counters_are_aggregated_in_one_query(): void
    {
        DB::table('ticket')->insert([
            ['evenement_id' => 1, 'lot_physique_id' => 10, 'statut_paiement' => 'payé', 'annule' => true, 'utilise' => true],
            ['evenement_id' => 1, 'lot_physique_id' => 10, 'statut_paiement' => 'payé', 'annule' => false, 'utilise' => false],
            ['evenement_id' => 1, 'lot_physique_id' => null, 'statut_paiement' => 'payé', 'annule' => false, 'utilise' => true],
        ]);

        DB::enableQueryLog();
        DB::flushQueryLog();
        $stats = app(TicketStatisticsService::class)->physiques(
            \App\Models\Ticket::query()
                ->where('evenement_id', 1)
                ->whereNotNull('lot_physique_id')
                ->where('statut_paiement', 'payé')
        );

        $this->assertSame(['total' => 2, 'annules' => 1, 'scannes' => 1], $stats);
        $this->assertCount(1, DB::getQueryLog());
    }

    public function test_organizer_dashboard_ticket_metrics_use_few_aggregate_queries(): void
    {
        $today = today()->setTime(10, 0);
        DB::table('ticket')->insert([
            [
                'evenement_id' => 1,
                'statut_paiement' => 'payé',
                'lot_physique_id' => null,
                'utilise' => false,
                'montant' => 100,
                'methode_paiement' => 'mtn',
                'type_paiement' => 'mobile_money',
                'date_achat' => $today,
            ],
            [
                'evenement_id' => 1,
                'statut_paiement' => 'payé',
                'lot_physique_id' => null,
                'utilise' => false,
                'montant' => 50,
                'methode_paiement' => 'cash',
                'type_paiement' => 'especes',
                'date_achat' => $today->copy()->subDays(6),
            ],
            [
                'evenement_id' => 1,
                'statut_paiement' => 'payé',
                'lot_physique_id' => 10,
                'utilise' => true,
                'montant' => 80,
                'methode_paiement' => 'cash',
                'type_paiement' => 'especes',
                'date_achat' => $today,
            ],
            [
                'evenement_id' => 1,
                'statut_paiement' => 'en_attente',
                'lot_physique_id' => null,
                'utilise' => false,
                'montant' => 30,
                'methode_paiement' => 'moov',
                'type_paiement' => 'mobile_money',
                'date_achat' => $today,
            ],
            [
                'evenement_id' => 2,
                'statut_paiement' => 'payé',
                'lot_physique_id' => null,
                'utilise' => false,
                'montant' => 900,
                'methode_paiement' => 'mtn',
                'type_paiement' => 'mobile_money',
                'date_achat' => $today,
            ],
        ]);
        DB::table('codes_promos')->insert([
            ['evenement_id' => 1, 'nb_utilisations' => 0],
            ['evenement_id' => 1, 'nb_utilisations' => 2],
            ['evenement_id' => 2, 'nb_utilisations' => 1],
        ]);

        $tickets = app(TicketStatisticsService::class);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->assertSame([
            'tickets_vendus' => 2,
            'recettes_totales' => 150.0,
            'recettes_mobile' => 100.0,
            'recettes_especes' => 50.0,
            'tickets_scannes' => 1,
        ], $tickets->tableauDeBord([1]));
        $this->assertCount(1, DB::getQueryLog());

        DB::flushQueryLog();
        $this->assertSame(1, (int) $tickets->ventesParJour([1])->get(today()->toDateString()));
        $this->assertCount(1, DB::getQueryLog());

        DB::flushQueryLog();
        $paiements = $tickets->repartitionPaiements([1]);
        $this->assertSame(1, (int) $paiements['operateurs']->get('mtn')->total);
        $this->assertSame(1, (int) $paiements['moyens']->get('especes')->total);
        $this->assertCount(1, DB::getQueryLog());

        DB::flushQueryLog();
        $this->assertSame(
            ['total' => 2, 'utilises' => 1],
            app(CodePromoStatisticsService::class)->pourEvenements([1])
        );
        $this->assertCount(1, DB::getQueryLog());
    }

    public function test_ticket_alerts_and_event_revenue_use_single_aggregate_queries(): void
    {
        $now = now();
        DB::table('ticket')->insert([
            [
                'evenement_id' => 30,
                'statut_paiement' => 'en_attente',
                'transaction_id' => null,
                'date_achat' => $now->copy()->subMinutes(40),
                'montant' => 0,
            ],
            [
                'evenement_id' => 30,
                'statut_paiement' => 'payé',
                'date_achat' => $now,
                'transaction_id' => null,
                'montant' => 100,
            ],
            [
                'evenement_id' => 30,
                'statut_paiement' => 'payé',
                'date_achat' => $now,
                'transaction_id' => 'TX-1',
                'montant' => 200,
            ],
            [
                'evenement_id' => 30,
                'statut_paiement' => 'échoué',
                'transaction_id' => null,
                'date_achat' => $now,
                'montant' => 0,
            ],
            [
                'evenement_id' => 31,
                'statut_paiement' => 'payé',
                'date_achat' => $now,
                'transaction_id' => 'TX-2',
                'montant' => 900,
            ],
        ]);

        DB::enableQueryLog();
        DB::flushQueryLog();
        $service = app(TicketStatisticsService::class);
        $this->assertSame([
            'en_attente' => 1,
            'sans_transaction' => 1,
            'reussies' => 2,
            'echouees' => 1,
        ], $service->alertes([30]));
        $this->assertCount(1, DB::getQueryLog());

        DB::flushQueryLog();
        $this->assertSame([
            'ventes' => 2,
            'revenus' => 300.0,
        ], $service->ventesEvenement(30));
        $this->assertCount(1, DB::getQueryLog());
    }

    public function test_scan_counters_and_admin_metrics_are_aggregated(): void
    {
        $agent = new Agent();
        $agent->id = 5;
        $today = now();

        DB::table('logs')->insert([
            ['agent_id' => 5, 'ticket_id' => null, 'type_operation' => 'scan', 'details' => json_encode(['resultat' => 'valide']), 'created_at' => $today],
            ['agent_id' => 5, 'ticket_id' => null, 'type_operation' => 'scan', 'details' => json_encode(['resultat' => 'valide']), 'created_at' => $today],
            ['agent_id' => 5, 'ticket_id' => null, 'type_operation' => 'scan', 'details' => json_encode(['resultat' => 'deja_utilise']), 'created_at' => $today],
            ['agent_id' => 5, 'ticket_id' => null, 'type_operation' => 'scan', 'details' => json_encode(['resultat' => 'invalide']), 'created_at' => $today->copy()->subDay()],
            ['agent_id' => 5, 'ticket_id' => null, 'type_operation' => 'achat', 'details' => json_encode(['resultat' => 'valide']), 'created_at' => $today],
        ]);

        DB::enableQueryLog();
        DB::flushQueryLog();
        $service = app(AgentScanStatisticsService::class);

        $this->assertSame([
            'total' => 4,
            'valides' => 2,
            'deja_utilises' => 1,
        ], $service->apiCounters($agent));
        $this->assertCount(1, DB::getQueryLog());
        DB::flushQueryLog();
        $this->assertSame([
            'total_scans' => 4,
            'scans_ajd' => 3,
            'valides' => 2,
            'deja_utilises' => 1,
            'invalides' => 2,
            'dernier_acces' => null,
        ], $service->adminStats($agent));
        $this->assertCount(1, DB::getQueryLog());
    }

    public function test_event_scan_page_metrics_use_one_aggregate_query(): void
    {
        $ticketId = DB::table('ticket')->insertGetId(['evenement_id' => 40]);
        $otherEventTicketId = DB::table('ticket')->insertGetId(['evenement_id' => 41]);
        $today = now();
        DB::table('logs')->insert([
            ['agent_id' => 1, 'ticket_id' => $ticketId, 'type_operation' => 'scan', 'details' => json_encode(['resultat' => 'valide']), 'created_at' => $today],
            ['agent_id' => 2, 'ticket_id' => $ticketId, 'type_operation' => 'scan', 'details' => json_encode(['resultat' => 'invalide']), 'created_at' => $today],
            ['agent_id' => 1, 'ticket_id' => $ticketId, 'type_operation' => 'scan', 'details' => json_encode(['resultat' => 'invalide']), 'created_at' => $today->copy()->subDay()],
            ['agent_id' => 1, 'ticket_id' => $otherEventTicketId, 'type_operation' => 'scan', 'details' => json_encode(['resultat' => 'valide']), 'created_at' => $today],
            ['agent_id' => 1, 'ticket_id' => $ticketId, 'type_operation' => 'achat', 'details' => json_encode(['resultat' => 'valide']), 'created_at' => $today],
        ]);

        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->assertSame([
            'total_scans' => 3,
            'scans_today' => 2,
            'scans_valides' => 1,
            'scans_invalides' => 2,
        ], app(AgentScanStatisticsService::class)->evenement(40));
        $this->assertCount(1, DB::getQueryLog());
    }

    public function test_agent_vente_metrics_share_aggregates_and_keep_grouped_breakdowns(): void
    {
        DB::table('tarifs')->insert([
            ['id' => 1, 'nom' => 'Standard'],
            ['id' => 2, 'nom' => 'VIP'],
        ]);
        DB::table('ticket')->insert([
            [
                'evenement_id' => 30,
                'agent_vente_id' => 12,
                'tarif_id' => 1,
                'statut_paiement' => 'payé',
                'montant' => 100,
                'date_achat' => now(),
                'methode_paiement' => 'mtn',
            ],
            [
                'evenement_id' => 30,
                'agent_vente_id' => 12,
                'tarif_id' => 1,
                'statut_paiement' => 'en_attente',
                'montant' => 20,
                'date_achat' => now(),
                'methode_paiement' => 'mtn',
            ],
            [
                'evenement_id' => 30,
                'agent_vente_id' => 12,
                'tarif_id' => 2,
                'statut_paiement' => 'payé',
                'montant' => 50,
                'date_achat' => now()->subDay(),
                'methode_paiement' => 'cash',
            ],
            [
                'evenement_id' => 30,
                'agent_vente_id' => 13,
                'tarif_id' => 2,
                'statut_paiement' => 'payé',
                'montant' => 500,
                'date_achat' => now(),
                'methode_paiement' => 'cash',
            ],
        ]);

        $agent = new AgentVente();
        $agent->id = 12;
        $service = app(AgentVenteStatisticsService::class);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $stats = $service->pourAgent($agent, 30);

        $this->assertCount(4, DB::getQueryLog());
        $this->assertSame(3, $stats['total_tickets']);
        $this->assertSame(170.0, $stats['montant_total']);
        $this->assertSame(2, $stats['aujourd_hui']);
        $this->assertCount(2, $stats['par_tarif']);
        $this->assertCount(2, $stats['par_methode']);
        DB::flushQueryLog();
        $this->assertSame([
            'aujourd_hui' => 1,
            'montant_ajd' => 100.0,
        ], $service->ventesDuJour($agent));
        $this->assertCount(1, DB::getQueryLog());

        $evenement = new Evenement();
        $evenement->id = 30;
        DB::flushQueryLog();
        $parAgent = $service->pourEvenement($evenement, [12]);
        $this->assertSame(2, (int) $parAgent->get(12)->firstWhere('methode_paiement', 'mtn')->total);
        $this->assertCount(1, DB::getQueryLog());
    }

    public function test_affiliate_link_and_commission_metrics_are_aggregated(): void
    {
        DB::table('affiliate_event_links')->insert([
            ['affiliate_id' => 4, 'actif' => true, 'clics' => 10],
            ['affiliate_id' => 4, 'actif' => false, 'clics' => 3],
            ['affiliate_id' => 5, 'actif' => true, 'clics' => 100],
        ]);
        DB::table('affiliate_commissions')->insert([
            ['affiliate_id' => 4, 'evenement_id' => 30, 'statut' => 'en_attente', 'montant' => 5],
            ['affiliate_id' => 4, 'evenement_id' => 30, 'statut' => 'disponible', 'montant' => 7],
            ['affiliate_id' => 4, 'evenement_id' => 30, 'statut' => 'paye', 'montant' => 11],
            ['affiliate_id' => 4, 'evenement_id' => 30, 'statut' => 'annule', 'montant' => 13],
            ['affiliate_id' => 5, 'evenement_id' => 30, 'statut' => 'paye', 'montant' => 100],
        ]);

        $affiliate = new Affiliate();
        $affiliate->id = 4;
        $service = app(AffiliateStatisticsService::class);

        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->assertSame([
            'liens' => 2,
            'liens_actifs' => 1,
            'clics' => 13,
        ], $service->links($affiliate));
        $this->assertCount(1, DB::getQueryLog());

        DB::flushQueryLog();
        $this->assertSame([
            'nombre' => 3,
            'total' => 23.0,
            'en_attente' => 5.0,
            'disponible' => 7.0,
            'paye' => 11.0,
        ], $service->commissions($affiliate, 30, true));
        $this->assertCount(1, DB::getQueryLog());
    }
}
