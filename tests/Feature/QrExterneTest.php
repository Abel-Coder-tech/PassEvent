<?php

namespace Tests\Feature;

use App\Mail\AgentAssigned;
use App\Models\Agent;
use App\Models\Evenement;
use App\Models\LotPhysique;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// Événements internes du super admin et génération de leurs QR codes (hors plateforme)
class QrExterneTest extends TestCase
{
    use RefreshDatabase;

    // Vrai PNG : les services PDF lisent le fichier via storage_path
    private function png(): UploadedFile
    {
        $chemin = tempnam(sys_get_temp_dir(), 'qx').'.png';
        copy(public_path('images/Paxagent-cercle.png'), $chemin);

        return new UploadedFile($chemin, 'ticket.png', 'image/png', null, true);
    }

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'super_admin', 'statut' => 'actif']);
    }

    private function creerEvenementInterne(User $sa, array $tarifs = [['nom' => 'VIP', 'prix' => '20000'], ['nom' => 'Standard', 'prix' => '']]): Evenement
    {
        $this->actingAs($sa, 'superadmin')->post(route('superadmin.evenements-internes.store'), [
            'titre' => 'Gala privé UPAO',
            'auteur_prenom' => 'Awa',
            'auteur_nom' => 'Dossou',
            'lieu' => 'Porto-Novo',
            'capacite' => 300,
            'dates' => [now()->addWeeks(2)->format('Y-m-d\TH:i'), now()->addWeeks(2)->addDay()->format('Y-m-d\TH:i')],
            'tarifs' => $tarifs,
        ])->assertSessionHasNoErrors();

        return Evenement::where('interne', true)->latest('id')->firstOrFail();
    }

    public function test_creation_d_un_evenement_interne(): void
    {
        $sa = $this->superadmin();
        $this->actingAs($sa, 'superadmin')->get(route('superadmin.evenements-internes.creer'))->assertOk();

        $evenement = $this->creerEvenementInterne($sa);

        $this->assertTrue($evenement->interne);
        $this->assertSame('Awa Dossou', $evenement->auteurComplet());
        $this->assertSame(2, $evenement->dates()->count());
        $this->assertSame(['VIP' => 20000.0, 'Standard' => 0.0], $evenement->tarifs()->pluck('prix', 'nom')->map(fn ($p) => (float) $p)->all());
        $this->assertTrue($evenement->user->interne);

        $this->actingAs($sa, 'superadmin')->get(route('superadmin.evenements-internes'))->assertOk()->assertSee('Gala privé UPAO');
        $this->actingAs($sa, 'superadmin')->get(route('superadmin.evenements-internes.voir', $evenement))->assertOk()->assertSee('Awa Dossou');
    }

    public function test_un_evenement_interne_est_masque_du_public(): void
    {
        $evenement = $this->creerEvenementInterne($this->superadmin());

        $this->get(route('evenements.public'))->assertOk()->assertDontSee('Gala privé UPAO');
        $this->get(route('accueil'))->assertOk()->assertDontSee('Gala privé UPAO');
        $this->get(route('evenements.public.show', $evenement))->assertNotFound();
        $this->post(route('evenements.achat', $evenement), [])->assertNotFound();
    }

    public function test_le_compte_interne_ne_peut_pas_se_connecter_ni_apparaitre_comme_organisateur(): void
    {
        $sa = $this->superadmin();
        $this->creerEvenementInterne($sa);
        $interne = User::where('interne', true)->firstOrFail();

        $this->post(route('login'), ['email' => $interne->email, 'mot_de_passe' => 'peu-importe'])
            ->assertSessionHasErrors('email');
        $this->assertGuest('web');

        // Session neuve : le formulaire de connexion ne pré-remplit plus l'email tapé
        $this->flushSession();
        $this->actingAs($sa, 'superadmin')->get(route('superadmin.organisateurs'))
            ->assertOk()->assertDontSee($interne->email);
    }

    public function test_generation_des_qr_par_tarif_cree_de_vrais_billets_sans_montant(): void
    {
        $sa = $this->superadmin();
        $evenement = $this->creerEvenementInterne($sa);
        [$vip, $standard] = $evenement->tarifs()->orderBy('id')->get()->all();

        $this->actingAs($sa, 'superadmin')->get(route('superadmin.qr-externes', ['evenement' => $evenement->id]))
            ->assertOk()->assertSee('Gala privé UPAO');

        $reponse = $this->actingAs($sa, 'superadmin')->post(route('superadmin.qr-externes.store'), [
            'evenement_id' => $evenement->id,
            'quantites' => [$vip->id => 3, $standard->id => 5],
        ]);

        $lots = LotPhysique::where('evenement_id', $evenement->id)->orderBy('id')->get();
        $this->assertCount(2, $lots);
        $reponse->assertRedirect(route('superadmin.tickets-physiques.template', $lots[0]));

        $tickets = Ticket::where('evenement_id', $evenement->id)->get();
        $this->assertCount(8, $tickets);
        $this->assertSame(3, $tickets->where('tarif_id', $vip->id)->count());
        $this->assertSame(0.0, (float) $tickets->sum('montant'));
        $this->assertSame(8, $tickets->pluck('code_unique')->unique()->count());

        // Ces lots restent hors de la liste des tickets physiques des organisateurs
        $this->actingAs($sa, 'superadmin')->get(route('superadmin.tickets-physiques'))
            ->assertOk()->assertDontSee('Gala privé UPAO');
    }

    public function test_un_billet_genere_est_valide_au_scan(): void
    {
        Mail::fake();
        $sa = $this->superadmin();
        $evenement = $this->creerEvenementInterne($sa);
        $tarif = $evenement->tarifs()->first();

        $this->actingAs($sa, 'superadmin')->post(route('superadmin.qr-externes.store'), [
            'evenement_id' => $evenement->id,
            'quantites' => [$tarif->id => 1],
        ]);
        $this->actingAs($sa, 'superadmin')->post(route('superadmin.evenements-internes.agents', $evenement), [
            'nom' => 'Agent Porte', 'email' => 'porte@example.test',
        ]);

        $agent = Agent::where('email', 'porte@example.test')->firstOrFail();
        $code = Ticket::where('evenement_id', $evenement->id)->value('code_unique');
        $token = $agent->createToken('scan-test', ['agent:scan'])->plainTextToken;

        // Avant l'événement : le billet est reconnu valide, mais l'entrée n'est pas encore ouverte
        $this->withToken($token)->postJson('/api/agent/scan/verify', ['code' => $code])
            ->assertOk()->assertJsonPath('resultat', 'pas_encore_ouvert');

        // Le jour J, l'entrée est acceptée
        $this->travelTo($evenement->date_event->copy()->addMinutes(30));
        $this->withToken($token)->postJson('/api/agent/scan/verify', ['code' => $code])
            ->assertOk()->assertJsonPath('success', true);

        // Un second passage est détecté
        $this->withToken($token)->postJson('/api/agent/scan/verify', ['code' => $code])
            ->assertOk()->assertJsonPath('success', false);

        // Le super admin voit l'agent et l'historique de ses scans sur la fiche de l'événement
        $this->travelBack();
        $this->actingAs($sa, 'superadmin')->get(route('superadmin.evenements-internes.voir', $evenement))
            ->assertOk()
            ->assertSee('Agent Porte')
            ->assertSee($code)
            ->assertSee('Entrée validée')
            ->assertSee('Déjà utilisé')
            ->assertSee('1 / 1 billets entrés');
    }

    public function test_creation_d_un_agent_de_scan_envoie_ses_identifiants(): void
    {
        Mail::fake();
        $sa = $this->superadmin();
        $evenement = $this->creerEvenementInterne($sa);

        $this->actingAs($sa, 'superadmin')->post(route('superadmin.evenements-internes.agents', $evenement), [
            'nom' => 'Agent Scan', 'email' => 'scan@example.test',
        ])->assertSessionHas('success');

        Mail::assertSent(AgentAssigned::class, fn ($m) => $m->hasTo('scan@example.test'));
        $this->assertSame(1, $evenement->agents()->count());
        $this->assertSame(0, $evenement->agentsVentes()->count());

        // Le même email ne peut pas servir deux fois
        $this->actingAs($sa, 'superadmin')->post(route('superadmin.evenements-internes.agents', $evenement), [
            'nom' => 'Doublon', 'email' => 'scan@example.test',
        ])->assertSessionHas('error');

        // Désactivation
        $agent = $evenement->agents()->first();
        $this->actingAs($sa, 'superadmin')->post(route('superadmin.evenements-internes.agents.basculer', [$evenement, $agent->id]));
        $this->assertFalse($agent->fresh()->actif);
    }

    public function test_les_billets_internes_n_apparaissent_dans_aucune_liste_du_superadmin(): void
    {
        $sa = $this->superadmin();
        $evenement = $this->creerEvenementInterne($sa);
        $this->actingAs($sa, 'superadmin')->post(route('superadmin.qr-externes.store'), [
            'evenement_id' => $evenement->id,
            'quantites' => [$evenement->tarifs()->first()->id => 2],
        ]);
        $code = Ticket::where('evenement_id', $evenement->id)->value('code_unique');

        foreach (['superadmin.tickets', 'superadmin.transactions', 'superadmin.evenements', 'superadmin.acheteurs', 'superadmin.dashboard'] as $route) {
            $this->actingAs($sa, 'superadmin')->get(route($route))
                ->assertOk()->assertDontSee($code)->assertDontSee('Gala privé UPAO');
        }

        $this->assertSame(0, Ticket::horsEvenementsInternes()->where('evenement_id', $evenement->id)->count());
    }

    // Parcours complet de l'application PaxAgent, avec les identifiants reçus par email
    public function test_l_agent_scanne_depuis_l_application_mobile(): void
    {
        Mail::fake();
        $sa = $this->superadmin();
        $evenement = $this->creerEvenementInterne($sa);
        $this->actingAs($sa, 'superadmin')->post(route('superadmin.qr-externes.store'), [
            'evenement_id' => $evenement->id,
            'quantites' => [$evenement->tarifs()->first()->id => 2],
        ]);
        $this->actingAs($sa, 'superadmin')->post(route('superadmin.evenements-internes.agents', $evenement), [
            'nom' => 'Agent Mobile', 'email' => 'mobile@example.test',
        ]);

        // Identifiants tels qu'envoyés par email
        $motDePasse = null;
        Mail::assertSent(AgentAssigned::class, function ($mail) use (&$motDePasse) {
            $motDePasse = $mail->motDePasse;

            return true;
        });
        $agent = Agent::where('email', 'mobile@example.test')->firstOrFail();
        $codes = Ticket::where('evenement_id', $evenement->id)->pluck('code_unique');
        $this->app['auth']->forgetGuards();

        // L'agent ouvre l'application le jour de l'événement
        $this->travelTo($evenement->date_event->copy()->addMinutes(15));

        // 1. Connexion
        $connexion = $this->postJson('/api/agent/login', ['email' => 'mobile@example.test', 'password' => $motDePasse]);
        $connexion->assertOk();
        $jeton = $connexion->json('token') ?? $connexion->json('access_token') ?? $connexion->json('data.token');
        $this->assertNotEmpty($jeton, 'Jeton de connexion absent : '.$connexion->getContent());

        // 2. Profil et tableau de bord
        $this->withToken($jeton)->getJson('/api/agent/me')->assertOk()->assertJsonFragment(['email' => 'mobile@example.test']);
        $this->withToken($jeton)->getJson('/api/agent/dashboard')->assertOk();

        // 3. Code PIN (mauvais puis bon) → jeton de scan
        $this->withToken($jeton)->postJson('/api/agent/scan/pin', ['code_acces' => $agent->code_acces === '0000' ? '1111' : '0000'])->assertStatus(422);
        $pin = $this->withToken($jeton)->postJson('/api/agent/scan/pin', ['code_acces' => $agent->code_acces]);
        $pin->assertOk();
        $jetonScan = $pin->json('scan_token');
        $this->assertNotEmpty($jetonScan);
        $this->app['auth']->forgetGuards();

        // 4. Scans
        $this->withToken($jetonScan)->postJson('/api/agent/scan/verify', ['code' => $codes[0]])
            ->assertOk()->assertJsonPath('resultat', 'valide');
        $this->withToken($jetonScan)->postJson('/api/agent/scan/verify', ['code' => $codes[0]])
            ->assertOk()->assertJsonPath('resultat', 'deja_utilise');
        $this->withToken($jetonScan)->postJson('/api/agent/scan/verify', ['code' => 'PAX-FAUX99'])
            ->assertOk()->assertJsonPath('resultat', 'invalide');
        $this->withToken($jetonScan)->postJson('/api/agent/scan/verify', ['code' => $codes[1]])
            ->assertOk()->assertJsonPath('resultat', 'valide');

        // 5. Historique puis sortie du mode scan
        $historique = $this->withToken($jetonScan)->getJson('/api/agent/scan/history');
        $historique->assertOk();
        $this->assertStringContainsString($codes[0], $historique->getContent());
        $this->withToken($jetonScan)->postJson('/api/agent/scan/exit')->assertOk();
    }

    public function test_placement_du_qr_puis_pdf(): void
    {
        $sa = $this->superadmin();
        $evenement = $this->creerEvenementInterne($sa);
        $this->actingAs($sa, 'superadmin')->post(route('superadmin.qr-externes.store'), [
            'evenement_id' => $evenement->id,
            'quantites' => [$evenement->tarifs()->first()->id => 3],
        ]);
        $lot = LotPhysique::where('evenement_id', $evenement->id)->firstOrFail();

        $this->actingAs($sa, 'superadmin')->get(route('superadmin.tickets-physiques.template', $lot))->assertOk();

        if (! extension_loaded('gd')) {
            $this->markTestSkipped('dompdf a besoin de l\'extension GD pour intégrer le visuel.');
        }

        $this->actingAs($sa, 'superadmin')->post(route('superadmin.tickets-physiques.template.save', $lot), [
            'template_image' => $this->png(), 'format' => 's1', 'qr_x' => 10, 'qr_y' => 8, 'qr_size' => 30,
        ])->assertSessionHasNoErrors();
        $lot->refresh();

        try {
            $this->assertTrue($lot->aUnTemplate());
            $this->actingAs($sa, 'superadmin')->get(route('superadmin.tickets-physiques.template.preview', $lot))->assertOk();

            foreach ([route('superadmin.tickets-physiques.planche', $lot), route('superadmin.qr-externes.qr-pdf', $lot)] as $url) {
                $pdf = $this->actingAs($sa, 'superadmin')->get($url);
                $pdf->assertOk();
                $this->assertStringStartsWith('%PDF', $pdf->getContent());
            }
        } finally {
            Storage::disk('public')->delete($lot->template_path);
        }
    }

    public function test_evenement_et_quantite_obligatoires(): void
    {
        $this->actingAs($this->superadmin(), 'superadmin')
            ->post(route('superadmin.qr-externes.store'), [])
            ->assertSessionHasErrors(['evenement_id', 'quantites']);
    }
}
