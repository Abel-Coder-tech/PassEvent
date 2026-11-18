<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\LotPhysique;
use App\Models\Tarif;
use App\Models\Ticket;
use App\Models\User;
use App\Services\LotAutoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// Parcours « Générer mes QR codes » côté organisateur
class LotPhysiqueOrganisateurTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function organisateur(): User
    {
        return User::create([
            'nom' => 'Organisateur QR',
            'email' => uniqid('org-').'@paxevent.test',
            'mot_de_passe' => bcrypt('secret'),
            'role' => 'admin',
            'statut' => 'actif',
        ]);
    }

    private function evenement(User $user, float $prix = 5000, string $titre = 'Concert Test'): array
    {
        $evenement = Evenement::create([
            'user_id' => $user->id,
            'titre' => $titre,
            'date_event' => now()->addWeek(),
            'lieu' => 'Cotonou',
            'capacite' => 500,
            'statut' => 'publié',
            'gratuit' => $prix <= 0,
        ]);
        $tarif = Tarif::create([
            'evenement_id' => $evenement->id,
            'nom' => 'Standard',
            'prix' => $prix,
            'quantite' => 500,
            'statut' => 'actif',
        ]);

        return [$evenement, $tarif];
    }

    private function png(): UploadedFile
    {
        $chemin = tempnam(sys_get_temp_dir(), 'lot').'.png';
        copy(public_path('images/Paxagent-cercle.png'), $chemin);

        return new UploadedFile($chemin, 'ticket.png', 'image/png', null, true);
    }

    // Commande payante confirmée comme le ferait le callback FedaPay
    private function commandePayee(User $user, Tarif $tarif, int $quantite = 5): LotPhysique
    {
        $this->actingAs($user)->post(route('admin.lots-physiques.commander'), [
            'evenement_id' => $tarif->evenement_id,
            'quantites' => [$tarif->id => $quantite],
        ]);
        $lots = LotPhysique::where('user_id', $user->id)->get();
        LotAutoService::confirmerLots($lots, 'TEST-TX');

        return $lots->first()->fresh();
    }

    public function test_la_page_s_affiche_avec_un_lot_externe_en_base(): void
    {
        LotPhysique::create(['externe' => true, 'nom' => 'Externe', 'quantite' => 1, 'statut' => 'genere', 'auto_genere' => true]);
        $user = $this->organisateur();
        $this->evenement($user);

        $this->actingAs($user)->get(route('admin.lots-physiques.index'))
            ->assertOk()
            ->assertDontSee('Externe');
    }

    public function test_commande_payante_redirige_vers_le_paiement_sans_creer_de_tickets(): void
    {
        $user = $this->organisateur();
        [$evenement, $tarif] = $this->evenement($user);

        $reponse = $this->actingAs($user)->post(route('admin.lots-physiques.commander'), [
            'evenement_id' => $evenement->id,
            'quantites' => [$tarif->id => 10],
        ]);

        $lot = LotPhysique::where('user_id', $user->id)->firstOrFail();
        $reponse->assertRedirect(route('admin.lots-physiques.checkout', $lot->reference_paiement));
        $this->assertSame(LotPhysique::STATUT_ATTENTE_PAIEMENT, $lot->statut);
        $this->assertSame(0, Ticket::where('lot_physique_id', $lot->id)->count());

        $this->actingAs($user)->get(route('admin.lots-physiques.checkout', $lot->reference_paiement))->assertOk();
    }

    public function test_confirmation_du_paiement_genere_les_tickets_et_transmet_le_lot(): void
    {
        $user = $this->organisateur();
        [, $tarif] = $this->evenement($user);

        $lot = $this->commandePayee($user, $tarif, 7);

        $this->assertSame(LotPhysique::STATUT_TRANSMIS, $lot->statut);
        $this->assertSame(7, Ticket::where('lot_physique_id', $lot->id)->count());
        $this->assertSame(7, Ticket::where('lot_physique_id', $lot->id)->distinct()->count('code_unique'));

        // Une seconde confirmation (callback + webhook) ne double pas les tickets
        LotAutoService::confirmerLots(collect([$lot]), 'TEST-TX');
        $this->assertSame(7, Ticket::where('lot_physique_id', $lot->id)->count());
    }

    public function test_evenement_gratuit_genere_immediatement(): void
    {
        $user = $this->organisateur();
        [$evenement, $tarif] = $this->evenement($user, 0);

        $this->actingAs($user)->post(route('admin.lots-physiques.commander'), [
            'evenement_id' => $evenement->id,
            'quantites' => [$tarif->id => 4],
        ])->assertRedirect(route('admin.lots-physiques.index'));

        $lot = LotPhysique::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(LotPhysique::STATUT_TRANSMIS, $lot->statut);
        $this->assertSame(4, $lot->tickets()->count());
    }

    public function test_telechargement_de_la_planche_sans_visuel_et_limite(): void
    {
        $user = $this->organisateur();
        [, $tarif] = $this->evenement($user, 5000, 'Gala 2026/2027');
        $lot = $this->commandePayee($user, $tarif);

        for ($i = 0; $i < config('app.max_downloads'); $i++) {
            $pdf = $this->actingAs($user)->get(route('admin.lots-physiques.download', $lot));
            $pdf->assertOk();
            $this->assertStringStartsWith('%PDF', $pdf->getContent());
        }

        $this->actingAs($user)->get(route('admin.lots-physiques.download', $lot))
            ->assertRedirect()->assertSessionHas('error');
    }

    public function test_visuel_placement_apercu_et_planche_avec_visuel(): void
    {
        $user = $this->organisateur();
        [, $tarif] = $this->evenement($user);
        $lot = $this->commandePayee($user, $tarif, 3);

        $this->actingAs($user)->get(route('admin.lots-physiques.template', $lot))->assertOk();

        $this->actingAs($user)->post(route('admin.lots-physiques.template.save', $lot), [
            'template_image' => $this->png(), 'format' => 's1', 'qr_x' => 10, 'qr_y' => 8, 'qr_size' => 30,
        ])->assertSessionHasNoErrors();
        $lot->refresh();

        try {
            $this->assertTrue($lot->aUnTemplate());

            $this->actingAs($user)->get(route('admin.lots-physiques.template.preview', $lot))->assertOk();

            $pdf = $this->actingAs($user)->get(route('admin.lots-physiques.download', $lot));
            $pdf->assertOk();
            $this->assertStringStartsWith('%PDF', $pdf->getContent());
        } finally {
            // Le visuel est écrit dans le vrai stockage (lu via storage_path par les services PDF)
            Storage::disk('public')->delete($lot->template_path);
        }
    }

    public function test_un_autre_organisateur_n_a_pas_acces_au_lot(): void
    {
        $user = $this->organisateur();
        [, $tarif] = $this->evenement($user);
        $lot = $this->commandePayee($user, $tarif, 2);
        $intrus = $this->organisateur();

        $this->actingAs($intrus)->get(route('admin.lots-physiques.download', $lot))->assertForbidden();
        $this->actingAs($intrus)->get(route('admin.lots-physiques.template', $lot))->assertForbidden();
        $this->actingAs($intrus)->delete(route('admin.lots-physiques.destroy', $lot))->assertForbidden();
    }

    public function test_suppression_bloquee_si_un_ticket_est_scanne(): void
    {
        $user = $this->organisateur();
        [, $tarif] = $this->evenement($user);
        $lot = $this->commandePayee($user, $tarif, 2);

        $lot->tickets()->first()->update(['utilise' => true]);
        $this->actingAs($user)->delete(route('admin.lots-physiques.destroy', $lot))->assertSessionHas('error');
        $this->assertDatabaseHas('lots_physiques', ['id' => $lot->id]);

        $lot->tickets()->update(['utilise' => false]);
        $this->actingAs($user)->delete(route('admin.lots-physiques.destroy', $lot))->assertSessionHas('success');
        $this->assertDatabaseMissing('lots_physiques', ['id' => $lot->id]);
        $this->assertSame(0, Ticket::where('lot_physique_id', $lot->id)->count());
    }
}
