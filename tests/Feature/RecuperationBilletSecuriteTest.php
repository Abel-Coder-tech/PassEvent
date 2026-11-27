<?php

namespace Tests\Feature;

use App\Jobs\SendTicketEmailJob;
use App\Models\Evenement;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

// « Récupérer mon billet » : ID de transaction (numérique FedaPay, devinable)
// + téléphone de l'acheteur ; envoi possible sur un autre email.
class RecuperationBilletSecuriteTest extends TestCase
{
    use RefreshDatabase;

    private function billet(array $extra = []): Ticket
    {
        $org = User::factory()->create(['role' => 'admin', 'statut' => 'actif']);
        $e = Evenement::create(['user_id' => $org->id, 'titre' => 'Concert', 'date_event' => now()->addWeek(),
            'lieu' => 'Cotonou', 'capacite' => 100, 'statut' => 'publié', 'gratuit' => false]);

        return Ticket::create($extra + [
            'evenement_id' => $e->id, 'code_unique' => 'PAX-SECRET123', 'qr_signature' => 'sig',
            'nom_acheteur' => 'Awa Kone', 'email_acheteur' => 'awa@example.test', 'whatsapp_acheteur' => '0166398823',
            'montant' => 5000, 'quantite' => 1, 'statut_paiement' => 'payé', 'methode_paiement' => 'mtn',
            'transaction_id' => '412345678', 'date_achat' => now(),
        ]);
    }

    private function recuperer(array $donnees)
    {
        return $this->from(route('tickets.recuperer'))->post(route('tickets.rechercher'), $donnees + ['email' => 'nouvel@example.test']);
    }

    public function test_id_et_telephone_corrects_envoient_sur_l_email_choisi_sans_reveler_l_acheteur(): void
    {
        Bus::fake();
        $this->billet();

        // Le téléphone peut être saisi avec l'indicatif ou des espaces
        $this->recuperer(['transaction_id' => '412345678', 'telephone' => '+229 01 66 39 88 23'])
            ->assertOk()
            ->assertDontSee('Awa Kone')
            ->assertDontSee('awa@example.test')
            ->assertDontSee('PAX-SECRET123');

        Bus::assertDispatchedAfterResponse(SendTicketEmailJob::class);
    }

    public function test_un_id_devine_sans_le_bon_telephone_ne_donne_rien(): void
    {
        Bus::fake();
        $this->billet();

        $this->recuperer(['transaction_id' => '412345678', 'telephone' => '0197000000'])->assertSessionHas('error');
        $this->recuperer(['transaction_id' => '412345678', 'telephone' => '123'])->assertSessionHas('error');
        $this->recuperer(['transaction_id' => '412345679', 'telephone' => '0166398823'])->assertSessionHas('error');

        // 4e essai dans la minute : bloqué (3 essais / minute)
        $this->recuperer(['transaction_id' => '412345678', 'telephone' => '0166398823'])->assertStatus(429);

        Bus::assertNotDispatched(SendTicketEmailJob::class);
    }

    public function test_un_billet_gratuit_n_est_jamais_recuperable(): void
    {
        Bus::fake();
        $this->billet(['montant' => 0]);

        $this->recuperer(['transaction_id' => '412345678', 'telephone' => '0166398823'])->assertSessionHas('error');

        Bus::assertNotDispatched(SendTicketEmailJob::class);
    }
}
