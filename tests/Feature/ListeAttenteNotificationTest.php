<?php

namespace Tests\Feature;

use App\Mail\ListeAttentePlacesDisponibles;
use App\Models\Evenement;
use App\Models\EventWaitlist;
use App\Models\Message;
use Illuminate\Support\Facades\Mail;

/**
 * Liste d'attente : l'organisateur est prévenu à chaque demande, puis prévient
 * en une fois tous les inscrits d'un tarif après avoir ajouté des places.
 */
class ListeAttenteNotificationTest extends EvenementModificationAssistantTest
{
    public function test_chaque_demande_notifie_l_organisateur_et_l_envoi_previent_tous_les_inscrits(): void
    {
        Mail::fake();
        $evenement = $this->creerMono();
        $standard = $evenement->tarifs()->where('nom', 'Standard')->firstOrFail();
        $standard->update(['quantite_disponible' => 200, 'quantite_vendue' => 200]);

        foreach (['a@example.test' => 3, 'b@example.test' => 5] as $email => $places) {
            $this->postJson(route('evenements.liste-attente', $evenement), [
                'tarif_id' => $standard->id, 'email' => $email, 'quantite' => $places,
            ])->assertOk();
        }

        // Tableau de bord : un message par demande, avec le total demandé.
        $messages = Message::where('user_id', $this->organisateur->id)->orderBy('id')->get();
        $this->assertCount(2, $messages);
        $this->assertStringContainsString('8 place(s) demandée(s) par 2 personne(s)', $messages->last()->message);

        // Gestion des tarifs : le total est affiché avec le bouton d'envoi.
        $this->actingAs($this->organisateur)->get(route('admin.tarifs.index', $evenement))
            ->assertOk()
            ->assertSee('8 place(s) demandée(s) · 2 personne(s)')
            ->assertSee('Envoyer la notification (2)');

        $notifier = fn () => $this->actingAs($this->organisateur)
            ->post(route('admin.tarifs.liste-attente.notifier', [$evenement, $standard]));

        // Toujours complet : rien n'est envoyé.
        $notifier()->assertSessionHas('error');
        Mail::assertNothingQueued();

        // L'organisateur ajoute des places, puis prévient tout le monde d'un coup.
        $standard->update(['quantite_disponible' => 205]);
        $notifier()->assertSessionHas('success');

        Mail::assertQueued(ListeAttentePlacesDisponibles::class, 2);
        Mail::assertQueued(ListeAttentePlacesDisponibles::class, fn ($mail) => $mail->hasTo('a@example.test')
            && str_contains($mail->lienEvenement, $evenement->slug));
        $this->assertSame(0, EventWaitlist::where('statut', 'en_attente')->count());
        $this->assertSame(2, EventWaitlist::where('statut', 'place_offerte')->whereNotNull('place_offerte_le')->count());

        // Plus personne en attente : le bouton disparaît.
        $this->actingAs($this->organisateur)->get(route('admin.tarifs.index', $evenement))
            ->assertDontSee('Envoyer la notification');
    }

    public function test_un_autre_organisateur_ne_peut_pas_notifier(): void
    {
        $evenement = $this->creerMono();
        $tarif = $evenement->tarifs()->firstOrFail();
        $intrus = $this->creerOrganisateur();

        $this->actingAs($intrus)
            ->post(route('admin.tarifs.liste-attente.notifier', [$evenement, $tarif]))
            ->assertForbidden();
    }
}
