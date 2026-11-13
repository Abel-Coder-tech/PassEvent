<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\Tarif;
use App\Models\Ticket;
use App\Http\Controllers\EvenementController;
use App\Services\AffiliationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Comportement multijours : chaque jour possède sa jauge, un billet jour
 * n'occupe que son jour, un pack occupe tous les jours.
 */
class MultiDayEvenementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        $this->mock(AffiliationService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_un_billet_herite_du_jour_du_tarif(): void
    {
        $evenement = $this->creerEvenement(20);
        $jour2 = $this->creerJour($evenement->id, now()->addDay(), 1);
        $tarif = $this->creerTarif($evenement->id, $jour2);

        $ticket = Ticket::create([
            'evenement_id' => $evenement->id,
            'tarif_id' => $tarif->id,
            'code_unique' => 'PAX-HERITE',
            'quantite' => 1,
            'statut_paiement' => 'en_attente',
        ]);

        $this->assertSame($jour2->id, (int) $ticket->evenement_date_id);
    }

    public function test_un_billet_jour_n_occupe_que_son_jour(): void
    {
        $evenement = $this->creerEvenement(10);
        $jour1 = $this->creerJour($evenement->id, now(), 0, 5);
        $jour2 = $this->creerJour($evenement->id, now()->addDay(), 1, 5);
        $tarifJour1 = $this->creerTarif($evenement->id, $jour1);
        $tarifJour2 = $this->creerTarif($evenement->id, $jour2);

        $this->creerBillet($evenement->id, $jour1, $tarifJour1, 2);

        $this->assertSame(2, $evenement->occupationJour($jour1));
        $this->assertSame(0, $evenement->occupationJour($jour2));
        $this->assertSame(3, $evenement->placesRestantesPourTarif($tarifJour1));
        $this->assertSame(5, $evenement->placesRestantesPourTarif($tarifJour2));
    }

    public function test_un_pack_occupe_tous_les_jours(): void
    {
        $evenement = $this->creerEvenement(10);
        $jour1 = $this->creerJour($evenement->id, now(), 0, 5);
        $jour2 = $this->creerJour($evenement->id, now()->addDay(), 1, 5);
        $tarifJour2 = $this->creerTarif($evenement->id, $jour2);
        $pack = $this->creerTarif($evenement->id, null);

        $this->creerBillet($evenement->id, null, $pack, 3);

        $this->assertSame(3, $evenement->occupationJour($jour1));
        $this->assertSame(3, $evenement->occupationJour($jour2));
        $this->assertSame(2, $evenement->placesRestantesPourTarif($tarifJour2));

        // Un nouveau pack est limité par le jour le plus contraint.
        $this->assertSame(2, $evenement->placesRestantesPourTarif($pack));
    }

    public function test_le_pack_est_limite_par_le_jour_le_plus_contraint(): void
    {
        $evenement = $this->creerEvenement(10);
        $jour1 = $this->creerJour($evenement->id, now(), 0, 3);
        $jour2 = $this->creerJour($evenement->id, now()->addDay(), 1, 8);
        $pack = $this->creerTarif($evenement->id, null);

        $this->creerBillet($evenement->id, $jour1, null, 2);

        $this->assertSame(1, $evenement->placesRestantesPourTarif($pack));
    }

    public function test_les_billets_inactifs_ne_prennent_pas_de_place(): void
    {
        $evenement = $this->creerEvenement(10);
        $jour = $this->creerJour($evenement->id, now(), 0, 5);

        $this->creerBillet($evenement->id, $jour, null, 1, ['annule' => true]);
        $this->creerBillet($evenement->id, $jour, null, 1, ['expire_at' => now()->subMinute()]);
        $this->creerBillet($evenement->id, $jour, null, 1, ['statut_paiement' => 'échoué']);
        $this->creerBillet($evenement->id, $jour, null, 1, ['statut_paiement' => 'remboursé']);

        $this->assertSame(0, $evenement->occupationJour($jour));
    }

    public function test_une_reservation_en_attente_non_expiree_occupe_la_place(): void
    {
        $evenement = $this->creerEvenement(10);
        $jour = $this->creerJour($evenement->id, now(), 0, 5);

        $this->creerBillet($evenement->id, $jour, null, 1, [
            'statut_paiement' => 'en_attente',
            'reservation_expire_le' => now()->addMinutes(20),
        ]);
        $this->creerBillet($evenement->id, $jour, null, 1, [
            'statut_paiement' => 'en_attente',
            'reservation_expire_le' => now()->subMinute(),
        ]);

        $this->assertSame(1, $evenement->occupationJour($jour));
    }

    public function test_un_tarif_jour_est_vendable_jusqu_a_minuit_de_son_jour(): void
    {
        Carbon::setTestNow('2026-11-20 10:00');
        $evenement = $this->creerEvenement(10);
        $jour1 = $this->creerJour($evenement->id, Carbon::parse('2026-11-20 08:00'), 0, 5);
        $this->creerJour($evenement->id, Carbon::parse('2026-11-21 08:00'), 1, 5);
        $tarifJour1 = $this->creerTarif($evenement->id, $jour1);

        // Le jour a commencé mais il reste vendable jusqu'à minuit.
        Carbon::setTestNow('2026-11-20 23:30');
        $this->assertTrue($tarifJour1->fresh()->estEncoreVendable());

        Carbon::setTestNow('2026-11-21 00:01');
        $this->assertFalse($tarifJour1->fresh()->estEncoreVendable());
    }

    public function test_le_pack_n_est_plus_vendable_apres_minuit_du_premier_jour_payant(): void
    {
        Carbon::setTestNow('2026-11-20 09:00');
        $evenement = $this->creerEvenement(10);
        $jour1 = $this->creerJour($evenement->id, Carbon::parse('2026-11-20 10:00'), 0, 5);
        $jour2 = $this->creerJour($evenement->id, Carbon::parse('2026-11-21 10:00'), 1, 5);
        $this->creerTarif($evenement->id, $jour1, prix: 5000);
        $this->creerTarif($evenement->id, $jour2, prix: 5000);
        $pack = $this->creerTarif($evenement->id, null, prix: 8000);

        $this->assertSame($jour1->id, $evenement->premierJourPayant()?->id);

        Carbon::setTestNow('2026-11-20 23:59');
        $this->assertTrue($pack->fresh()->estEncoreVendable());

        Carbon::setTestNow('2026-11-21 00:01');
        $this->assertFalse($pack->fresh()->estEncoreVendable());
    }

    public function test_un_premier_jour_gratuit_ne_ferme_pas_la_vente_des_packs(): void
    {
        Carbon::setTestNow('2026-11-20 09:00');
        $evenement = $this->creerEvenement(10);
        $jour1 = $this->creerJour($evenement->id, Carbon::parse('2026-11-20 10:00'), 0, 5);
        $jour2 = $this->creerJour($evenement->id, Carbon::parse('2026-11-21 10:00'), 1, 5);
        $this->creerJour($evenement->id, Carbon::parse('2026-11-22 10:00'), 2, 5);
        $this->creerTarif($evenement->id, $jour1, prix: 0);
        $this->creerTarif($evenement->id, $jour2, prix: 5000);
        $pack = $this->creerTarif($evenement->id, null, prix: 8000);

        $this->assertSame($jour2->id, $evenement->premierJourPayant()?->id);

        // Lendemain du jour gratuit : le pack est toujours en vente.
        Carbon::setTestNow('2026-11-21 15:00');
        $this->assertTrue($pack->fresh()->estEncoreVendable());

        // Minuit du premier jour payant passé : le pack est fermé.
        Carbon::setTestNow('2026-11-22 00:01');
        $this->assertFalse($pack->fresh()->estEncoreVendable());
    }

    public function test_un_jour_sans_tarif_propre_est_considere_payant(): void
    {
        $evenement = $this->creerEvenement(10);
        $jour1 = $this->creerJour($evenement->id, Carbon::parse('2026-11-20 10:00'), 0, 5);
        $this->creerJour($evenement->id, Carbon::parse('2026-11-21 10:00'), 1, 5);
        $this->creerTarif($evenement->id, null, prix: 8000);

        $this->assertSame($jour1->id, $evenement->premierJourPayant()?->id);
    }

    public function test_un_jour_passe_ne_bloque_pas_les_places_du_pack(): void
    {
        Carbon::setTestNow('2026-11-21 09:00');
        $evenement = $this->creerEvenement(10);
        $jour1 = $this->creerJour($evenement->id, Carbon::parse('2026-11-20 10:00'), 0, 3);
        $this->creerJour($evenement->id, Carbon::parse('2026-11-21 10:00'), 1, 6);
        $pack = $this->creerTarif($evenement->id, null);

        // Jour 1 terminé et complet.
        $this->creerBillet($evenement->id, $jour1, null, 3);

        $this->assertSame(6, $evenement->placesRestantesPourTarif($pack));
        // Le total affiché ne compte que les jours à venir.
        $this->assertSame(6, $evenement->placesRestantesTotales());
    }

    public function test_un_evenement_multijours_n_est_complet_que_si_aucun_tarif_n_a_de_place(): void
    {
        $evenement = $this->creerEvenement(10);
        $jour1 = $this->creerJour($evenement->id, now()->addDay(), 0, 2);
        $jour2 = $this->creerJour($evenement->id, now()->addDays(2), 1, 2);
        $tarifJour1 = $this->creerTarif($evenement->id, $jour1);
        $tarifJour2 = $this->creerTarif($evenement->id, $jour2);

        $this->creerBillet($evenement->id, $jour1, $tarifJour1, 2);
        $evenement->resetCachesCapacite();

        $this->assertFalse($evenement->estComplet());
        $this->assertNotSame('event_full', $evenement->raisonBlocageVente());

        $this->creerBillet($evenement->id, $jour2, $tarifJour2, 2);
        $evenement->resetCachesCapacite();

        $this->assertTrue($evenement->estComplet());
        $this->assertSame('event_full', $evenement->raisonBlocageVente());
    }

    public function test_le_taux_de_remplissage_multijours_se_base_sur_les_jauges(): void
    {
        $evenement = $this->creerEvenement(10, quotaVendu: 999);
        $jour1 = $this->creerJour($evenement->id, now()->addDay(), 0, 10);
        $this->creerJour($evenement->id, now()->addDays(2), 1, 10);

        $this->creerBillet($evenement->id, $jour1, null, 5);

        // 5 places occupées sur 20 : quota_vendu n'intervient pas.
        $this->assertSame(25.0, $evenement->tauxRemplissage());
    }

    public function test_le_stock_d_un_tarif_jour_compte_les_packs(): void
    {
        $evenement = $this->creerEvenement(100);
        $jour1 = $this->creerJour($evenement->id, now()->addDay(), 0, 10);
        $this->creerJour($evenement->id, now()->addDays(2), 1, 10);
        $this->creerTarif($evenement->id, null, quantite: 4);
        $this->creerTarif($evenement->id, $jour1, quantite: 5);

        // 5 (jour) + 4 (packs) + 1 = 10 : accepté.
        $this->validerCapaciteTotale($evenement, 1, $jour1->id);

        // 5 + 4 + 2 = 11 > 10 : refusé.
        $this->expectException(ValidationException::class);
        $this->validerCapaciteTotale($evenement, 2, $jour1->id);
    }

    public function test_le_stock_d_un_pack_est_controle_sur_chaque_jour(): void
    {
        $evenement = $this->creerEvenement(100);
        $this->creerJour($evenement->id, now()->addDay(), 0, 10);
        $jour2 = $this->creerJour($evenement->id, now()->addDays(2), 1, 6);
        $this->creerTarif($evenement->id, $jour2, quantite: 4);

        // Jour 2 : 4 billets du jour + 3 packs = 7 > 6.
        $this->expectException(ValidationException::class);
        $this->validerCapaciteTotale($evenement, 3, null);
    }

    public function test_la_jauge_d_un_jour_ne_peut_pas_descendre_sous_les_stocks(): void
    {
        $user = $this->creerUtilisateur();
        $evenement = $this->creerEvenement(10, userId: $user->id);
        $jour = $this->creerJour($evenement->id, now()->addDay(), 0, 10);
        $this->creerJour($evenement->id, now()->addDays(2), 1, 10);
        $this->creerTarif($evenement->id, $jour, quantite: 5);
        $this->creerTarif($evenement->id, null, quantite: 3);

        $this->actingAs($user)
            ->post(route('admin.evenements.jours.capacite', [$evenement->id, $jour->id]), ['capacite' => 7])
            ->assertSessionHasErrors('capacite');

        $this->assertSame(10, $jour->fresh()->capacite);
    }

    public function test_les_billets_vendus_avant_l_ajout_d_un_jour_occupent_chaque_jour(): void
    {
        $evenement = $this->creerEvenement(100, quotaVendu: 40);
        $jour1 = $this->creerJour($evenement->id, Carbon::parse('2026-12-01 10:00'), 0, null);
        $this->creerBillet($evenement->id, null, null, 40);

        $this->assertFalse($evenement->estMultiJour());
        $this->assertSame(60, $evenement->placesRestantesPourTarif(null));

        // L'organisateur ajoute un second jour.
        $this->enregistrerDates($evenement, '2026-12-01 10:00', ['2026-12-02 10:00']);
        $jour2 = $evenement->dates()->get()->last();

        $this->assertTrue($evenement->estMultiJour());
        $this->assertSame(40, $evenement->occupationJour($jour1));
        $this->assertSame(40, $evenement->occupationJour($jour2));
        $this->assertSame(60, $evenement->placesRestantesPourTarif(null));
    }

    public function test_revenir_a_un_seul_jour_reprend_le_calcul_historique(): void
    {
        $evenement = $this->creerEvenement(100, quotaVendu: 30);
        $this->creerJour($evenement->id, Carbon::parse('2026-12-01 10:00'), 0, null);
        $this->creerJour($evenement->id, Carbon::parse('2026-12-02 10:00'), 1, null);
        $pack = $this->creerTarif($evenement->id, null);
        $this->creerBillet($evenement->id, null, $pack, 30);

        $this->assertTrue($evenement->estMultiJour());
        $this->assertSame(70, $evenement->placesRestantesPourTarif($pack));

        // Seulement des packs : le second jour peut être retiré.
        $this->enregistrerDates($evenement, '2026-12-01 10:00', []);

        $this->assertFalse($evenement->estMultiJour());
        $this->assertSame(70, $evenement->placesRestantesPourTarif($pack));
    }

    public function test_un_tarif_d_evenement_gratuit_reste_valable_tous_les_jours(): void
    {
        $user = $this->creerUtilisateur();
        $evenement = $this->creerEvenement(10, userId: $user->id, gratuit: true);
        $jour1 = $this->creerJour($evenement->id, now()->addDay(), 0, 10);
        $this->creerJour($evenement->id, now()->addDays(2), 1, 10);

        $this->actingAs($user)->post(route('admin.tarifs.store', $evenement->id), [
            'nom' => 'Entrée',
            'prix' => 0,
            'evenement_date_id' => $jour1->id,
        ]);

        $this->assertDatabaseHas('tarifs', ['evenement_id' => $evenement->id, 'nom' => 'Entrée', 'evenement_date_id' => null]);
    }

    public function test_la_jauge_du_jour_retombe_sur_la_capacite_evenement(): void
    {
        $evenement = $this->creerEvenement(12);
        $jour = $this->creerJour($evenement->id, now(), 0, null);

        $this->assertSame(12, $jour->capaciteEffective());
    }

    public function test_un_evenement_mono_jour_garde_le_comportement_historique(): void
    {
        $evenement = $this->creerEvenement(10, quotaVendu: 4);
        $this->creerJour($evenement->id, now(), 0, 5);

        $this->assertFalse($evenement->estMultiJour());
        $this->assertSame(6, $evenement->placesRestantesPourTarif(null));
    }

    public function test_les_jours_avec_tarifs_ou_billets_sont_signales(): void
    {
        $evenement = $this->creerEvenement(10);
        $jour1 = $this->creerJour($evenement->id, now(), 0, 5);
        $jour2 = $this->creerJour($evenement->id, now()->addDay(), 1, 5);
        $this->creerJour($evenement->id, now()->addDays(2), 2, 5);

        $this->creerTarif($evenement->id, $jour1);
        $this->creerBillet($evenement->id, $jour2, null, 1);

        $dates = $evenement->joursAvecTarifsOuBillets();

        $this->assertCount(2, $dates);
        $this->assertTrue($dates->contains($jour1->date_debut->toDateString()));
        $this->assertTrue($dates->contains($jour2->date_debut->toDateString()));
    }

    public function test_enregistrer_dates_conserve_les_ids_existants(): void
    {
        $evenement = $this->creerEvenement(10);
        $jour1 = $this->creerJour($evenement->id, Carbon::parse('2026-11-09 10:00'), 0);
        $jour2 = $this->creerJour($evenement->id, Carbon::parse('2026-11-10 10:00'), 1);

        // Même jour calendaire, heure décalée : l'identifiant est conservé.
        $this->enregistrerDates($evenement, '2026-11-09 18:00', ['2026-11-10 10:00']);

        $jours = $evenement->dates()->get();

        $this->assertSame([$jour1->id, $jour2->id], $jours->pluck('id')->all());
        $this->assertSame('2026-11-09 18:00', $jours->first()->date_debut->format('Y-m-d H:i'));
    }

    public function test_enregistrer_dates_supprime_un_jour_sans_dependance(): void
    {
        $evenement = $this->creerEvenement(10);
        $jour1 = $this->creerJour($evenement->id, Carbon::parse('2026-11-09 10:00'), 0);
        $jour2 = $this->creerJour($evenement->id, Carbon::parse('2026-11-10 10:00'), 1);

        $this->enregistrerDates($evenement, '2026-11-09 10:00', ['2026-11-11 10:00']);

        // La nouvelle date doit exister (elle était supprimée par erreur).
        $this->assertSame(
            ['2026-11-09 10:00', '2026-11-11 10:00'],
            $evenement->dates()->get()->map(fn ($jour) => $jour->date_debut->format('Y-m-d H:i'))->all()
        );

        $ids = $evenement->dates()->get()->pluck('id')->all();
        $this->assertContains($jour1->id, $ids);
        $this->assertNotContains($jour2->id, $ids);
        $this->assertDatabaseMissing('evenement_dates', ['id' => $jour2->id]);
    }

    public function test_enregistrer_dates_refuse_de_supprimer_un_jour_avec_tarif_ou_billet(): void
    {
        $evenement = $this->creerEvenement(10);
        $jour1 = $this->creerJour($evenement->id, Carbon::parse('2026-11-09 10:00'), 0);
        $jour2 = $this->creerJour($evenement->id, Carbon::parse('2026-11-10 10:00'), 1);
        $this->creerTarif($evenement->id, $jour2);

        $this->expectException(ValidationException::class);

        try {
            $this->enregistrerDates($evenement, '2026-11-09 10:00', []);
        } finally {
            $this->assertDatabaseHas('evenement_dates', ['id' => $jour1->id]);
            $this->assertDatabaseHas('evenement_dates', ['id' => $jour2->id]);
        }
    }

    public function test_mettre_a_jour_la_jauge_d_un_jour(): void
    {
        $user = $this->creerUtilisateur();
        $evenement = $this->creerEvenement(10, userId: $user->id);
        $jour = $this->creerJour($evenement->id, now(), 0, null);

        $this->actingAs($user)
            ->post(route('admin.evenements.jours.capacite', [$evenement->id, $jour->id]), ['capacite' => 7])
            ->assertRedirect();

        $this->assertSame(7, $jour->fresh()->capacite);
    }

    public function test_la_jauge_d_un_jour_ne_peut_pas_descendre_sous_l_occupation(): void
    {
        $user = $this->creerUtilisateur();
        $evenement = $this->creerEvenement(10, userId: $user->id);
        $jour = $this->creerJour($evenement->id, now(), 0, 5);
        $this->creerBillet($evenement->id, $jour, null, 3);

        $this->actingAs($user)
            ->post(route('admin.evenements.jours.capacite', [$evenement->id, $jour->id]), ['capacite' => 2])
            ->assertSessionHasErrors('capacite');

        $this->assertSame(5, $jour->fresh()->capacite);
    }

    public function test_la_jauge_d_un_jour_peut_revenir_au_defaut(): void
    {
        $user = $this->creerUtilisateur();
        $evenement = $this->creerEvenement(10, userId: $user->id);
        $jour = $this->creerJour($evenement->id, now(), 0, 5);

        $this->actingAs($user)
            ->post(route('admin.evenements.jours.capacite', [$evenement->id, $jour->id]), ['capacite' => ''])
            ->assertRedirect();

        $this->assertNull($jour->fresh()->capacite);
        $this->assertSame(10, $jour->fresh()->capaciteEffective());
    }

    public function test_la_jauge_d_un_jour_refuse_un_jour_d_un_autre_evenement(): void
    {
        $user = $this->creerUtilisateur();
        $evenement = $this->creerEvenement(10, userId: $user->id);
        $autre = $this->creerEvenement(10, userId: $user->id);
        $jourAutre = $this->creerJour($autre->id, now(), 0, 5);

        $this->actingAs($user)
            ->post(route('admin.evenements.jours.capacite', [$evenement->id, $jourAutre->id]), ['capacite' => 8])
            ->assertNotFound();

        $this->assertSame(5, $jourAutre->fresh()->capacite);
    }

    public function test_une_vente_agent_occupe_la_jauge_du_jour(): void
    {
        $evenement = $this->creerEvenement(10);
        $jour = $this->creerJour($evenement->id, now(), 0, 5);
        $jour2 = $this->creerJour($evenement->id, now()->addDay(), 1, 6);
        $tarif = $this->creerTarif($evenement->id, $jour);

        $this->mock(AffiliationService::class)
            ->shouldReceive('enregistrerPourTicket')
            ->once();

        $ticket = Ticket::create([
            'evenement_id' => $evenement->id,
            'tarif_id' => $tarif->id,
            'agent_vente_id' => 42,
            'source' => 'agent_vente',
            'code_unique' => 'PAX-AGENT-'.uniqid(),
            'quantite' => 1,
            'statut_paiement' => 'payé',
            'annule' => false,
        ]);

        $this->assertSame($jour->id, (int) $ticket->evenement_date_id);
        $this->assertSame(1, $evenement->occupationJour($jour));
        $this->assertSame(0, $evenement->occupationJour($jour2));
        $this->assertSame(4, $evenement->placesRestantesPourTarif($tarif));

        // La limite d'un pack reste le jour le plus contraint (minimum).
        $this->assertSame(4, $evenement->placesRestantesPourTarif(null));

        // Le compteur global affiché doit néanmoins baisser de 11 à 10 places.
        $this->assertSame(10, $evenement->placesRestantesTotales());

        // Une vente sur un jour non limitant ne doit plus laisser le compteur
        // global intact (jour 2 : 6 places, jour 1 : 5 places).
        $this->creerBillet($evenement->id, $jour2, $tarif, 1, [
            'agent_vente_id' => 42,
            'source' => 'agent_vente',
        ]);
        $evenement->resetCachesCapacite();
        $this->assertSame(9, $evenement->placesRestantesTotales());
    }

    private function enregistrerDates(Evenement $evenement, string $premiereDate, array $datesSupplementaires): void
    {
        $methode = new \ReflectionMethod(EvenementController::class, 'enregistrerDates');
        $methode->setAccessible(true);
        $methode->invoke(new EvenementController(), $evenement, $premiereDate, $datesSupplementaires);
    }

    public function test_un_ancien_evenement_sans_tarif_jour_vend_ses_packs_jusqu_au_bout(): void
    {
        // Événement créé avant les tarifs par jour : seulement des tarifs « tous les jours ».
        Carbon::setTestNow('2026-11-20 09:00');
        $evenement = $this->creerEvenement(10);
        $this->creerJour($evenement->id, Carbon::parse('2026-11-20 10:00'), 0, 5);
        $this->creerJour($evenement->id, Carbon::parse('2026-11-22 10:00'), 1, 5);
        $ancienTarif = $this->creerTarif($evenement->id, null, prix: 5000);

        $this->assertNull($evenement->finVentePacks());

        // Le lendemain du Jour 1, le tarif est toujours en vente.
        Carbon::setTestNow('2026-11-21 12:00');
        $this->assertTrue($ancienTarif->fresh()->estEncoreVendable());
    }

    public function test_l_economie_du_pack_simple_compare_la_somme_des_jours(): void
    {
        $evenement = $this->creerEvenement(100);
        $jour1 = $this->creerJour($evenement->id, now()->addDay(), 0, 50);
        $jour2 = $this->creerJour($evenement->id, now()->addDays(2), 1, 50);
        $jour3 = $this->creerJour($evenement->id, now()->addDays(3), 2, 50);
        $this->creerTarif($evenement->id, $jour1, prix: 0);
        $this->creerTarif($evenement->id, $jour2, prix: 5000);
        $this->creerTarif($evenement->id, $jour3, prix: 5000);
        $packSimple = $this->creerTarif($evenement->id, null, prix: 8000);
        $packVip = $this->creerTarif($evenement->id, null, prix: 18000);
        DB::table('tarifs')->where('id', $packSimple->id)->update(['nom' => 'Pack Simple']);
        DB::table('tarifs')->where('id', $packVip->id)->update(['nom' => 'Pack VIP']);

        $this->assertSame(2000, $evenement->economiePack($packSimple->fresh()));
        // Pas de billet VIP à la journée : aucune économie affichée pour le Pack VIP.
        $this->assertTrue(Evenement::estPackVip($packVip->fresh()));
        $this->assertNull($evenement->economiePack($packVip->fresh()));
    }

    public function test_pas_d_economie_si_un_jour_n_a_pas_de_tarif(): void
    {
        $evenement = $this->creerEvenement(100);
        $jour1 = $this->creerJour($evenement->id, now()->addDay(), 0, 50);
        $this->creerJour($evenement->id, now()->addDays(2), 1, 50);
        $this->creerTarif($evenement->id, $jour1, prix: 5000);
        $pack = $this->creerTarif($evenement->id, null, prix: 1000);

        $this->assertNull($evenement->economiePack($pack));
    }

    public function test_un_second_tarif_sur_le_meme_jour_est_refuse(): void
    {
        $user = $this->creerUtilisateur();
        $evenement = $this->creerEvenement(100, userId: $user->id);
        $jour1 = $this->creerJour($evenement->id, now()->addDay(), 0, 50);
        $this->creerJour($evenement->id, now()->addDays(2), 1, 50);
        $this->creerTarif($evenement->id, $jour1, quantite: 10);

        $this->actingAs($user)->post(route('admin.tarifs.store', $evenement->id), [
            'nom' => 'VIP',
            'prix' => 10000,
            'evenement_date_id' => $jour1->id,
            'quantite_disponible' => 5,
        ])->assertSessionHasErrors('evenement_date_id');

        $this->assertSame(1, DB::table('tarifs')->where('evenement_date_id', $jour1->id)->count());
    }

    private function validerCapaciteTotale(Evenement $evenement, ?int $quantite, ?int $jourId): void
    {
        $methode = new \ReflectionMethod(\App\Http\Controllers\TarifController::class, 'validerCapaciteTotale');
        $methode->setAccessible(true);
        $methode->invoke(new \App\Http\Controllers\TarifController(), $evenement, $quantite, null, $jourId);
    }

    private function creerUtilisateur(): \App\Models\User
    {
        $id = DB::table('users')->insertGetId([
            'nom' => 'Organisateur '.uniqid(),
            'email' => 'orga-'.uniqid().'@example.test',
            'mot_de_passe' => bcrypt('secret'),
            'role' => 'admin',
            'statut' => 'actif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return \App\Models\User::findOrFail($id);
    }

    private function creerEvenement(int $capacite, int $quotaVendu = 0, ?int $userId = null, bool $gratuit = false): Evenement
    {
        $id = DB::table('evenement')->insertGetId([
            'user_id' => $userId,
            'titre' => 'Festival '.uniqid(),
            'slug' => 'festival-'.uniqid(),
            'date_event' => now(),
            'capacite' => $capacite,
            'quota_vendu' => $quotaVendu,
            'gratuit' => $gratuit,
            'statut' => 'publié',
            'ventes_fermees' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Evenement::findOrFail($id);
    }

    private function creerJour(int $evenementId, Carbon $dateDebut, int $ordre, ?int $capacite = 5): \App\Models\EvenementDate
    {
        $id = DB::table('evenement_dates')->insertGetId([
            'evenement_id' => $evenementId,
            'date_debut' => $dateDebut,
            'ordre' => $ordre,
            'capacite' => $capacite,
        ]);

        return \App\Models\EvenementDate::findOrFail($id);
    }

    private function creerTarif(int $evenementId, int|\App\Models\EvenementDate|null $jour, int $quantite = 10, float $prix = 5000): Tarif
    {
        $jourId = $jour instanceof \App\Models\EvenementDate ? $jour->id : $jour;

        $id = DB::table('tarifs')->insertGetId([
            'evenement_id' => $evenementId,
            'evenement_date_id' => $jourId,
            'nom' => 'Tarif '.uniqid(),
            'prix' => $prix,
            'quantite_disponible' => $quantite,
            'quantite_vendue' => 0,
            'statut' => 'actif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Tarif::findOrFail($id);
    }

    private function creerBillet(
        int $evenementId,
        int|\App\Models\EvenementDate|null $jour,
        int|Tarif|null $tarif,
        int $quantite = 1,
        array $overrides = []
    ): int {
        $jourId = $jour instanceof \App\Models\EvenementDate ? $jour->id : $jour;
        $tarifId = $tarif instanceof Tarif ? $tarif->id : $tarif;

        return DB::table('ticket')->insertGetId(array_merge([
            'evenement_id' => $evenementId,
            'tarif_id' => $tarifId,
            'evenement_date_id' => $jourId,
            'code_unique' => 'PAX-'.uniqid(),
            'quantite' => $quantite,
            'statut_paiement' => 'payé',
            'annule' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
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
            $table->timestamps();
        });

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
    }
}
