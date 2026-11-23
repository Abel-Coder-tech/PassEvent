<?php

namespace Tests\Feature;

use App\Models\ActionPaxevent;
use App\Models\User;
use App\Services\GestionOrganisateur;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Compte organisateur créé par PaxEvent et mode « Gérer l'organisateur » :
 * accès limité, journal des actions, fin de la prise en main.
 */
class GestionOrganisateurTest extends TestCase
{
    private User $admin;

    private User $organisateur;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        $this->admin = $this->creerUtilisateur('super_admin', 'owner@example.test');
        $this->organisateur = $this->creerUtilisateur('admin', 'orga@example.test');
        DB::table('users')->where('id', $this->organisateur->id)->update(['mandat_actif' => true, 'mandat_mode' => 'organisateur']);
        $this->organisateur = $this->organisateur->fresh();
    }

    public function test_paxevent_cree_un_compte_incomplet_avec_le_mot_de_passe_qu_il_definit(): void
    {
        $this->actingAs($this->admin, 'superadmin')
            ->post(route('superadmin.organisateurs.creer'), [
                'nom' => 'Nouvel Organisateur', 'email' => 'nouveau@example.test', 'mot_de_passe' => 'MotDePasse123',
            ])->assertRedirect();

        $cree = User::where('email', 'nouveau@example.test')->firstOrFail();
        $this->assertSame('incomplet', $cree->statut);
        $this->assertTrue($cree->cree_par_paxevent);
        $this->assertSame('admin', $cree->role);
        $this->assertTrue(password_verify('MotDePasse123', $cree->mot_de_passe));

        // L'organisateur ne gère pas son compte : l'autorisation est accordée dès la création.
        $this->assertTrue($cree->mandat_actif);
        $this->assertSame('creation_paxevent', $cree->mandat_mode);
        $this->assertSame('owner@example.test', $cree->mandat_enregistre_par);
    }

    public function test_gerer_ouvre_le_compte_de_l_organisateur_agit_a_son_nom_et_journalise(): void
    {
        $evenementId = $this->creerEvenement($this->organisateur->id);

        $this->actingAs($this->admin, 'superadmin')
            ->post(route('superadmin.organisateurs.gerer', $this->organisateur))
            ->assertRedirect(route('dashboard'));
        Auth::shouldUse('web');

        $this->assertSame($this->organisateur->id, Auth::guard('web')->id());
        $this->assertSame($this->organisateur->id, session('gestion.org_id'));

        // Action autorisée : fermer la billetterie de l'événement, en son nom.
        $this->post(route('admin.evenements.billetterie', $evenementId), ['action' => 'fermer'])->assertRedirect();
        $this->assertFalse((bool) DB::table('evenement')->where('id', $evenementId)->value('billetterie_ouverte'));

        $action = ActionPaxevent::firstOrFail();
        $this->assertSame($this->organisateur->id, $action->user_id);
        $this->assertSame('owner@example.test', $action->admin_email);
        $this->assertSame('Ouverture ou fermeture de la billetterie', $action->resume);

        // Quitter : la session de l'organisateur est fermée, celle du superadmin reste.
        $this->post(route('admin.gestion.quitter'))
            ->assertRedirect(route('superadmin.organisateurs.voir', $this->organisateur->id));
        $this->assertFalse(Auth::guard('web')->check());
        $this->assertNull(session('gestion'));
        $this->assertTrue(Auth::guard('superadmin')->check());
    }

    public function test_les_actions_reservees_a_l_organisateur_sont_refusees(): void
    {
        $this->actingAs($this->admin, 'superadmin')
            ->post(route('superadmin.organisateurs.gerer', $this->organisateur));
        Auth::shouldUse('web');

        foreach ([
            ['post', route('admin.retraits.store')],
            ['post', route('ventes-manuelles.store')],
            ['post', route('admin.lots-physiques.commander')],
            ['put', route('parametres.securite.update')],
            ['post', route('parametres.compte.delete')],
        ] as [$methode, $url]) {
            $this->$methode($url)->assertRedirect(route('dashboard'))->assertSessionHas('error');
        }

        $this->assertSame(0, ActionPaxevent::count());
    }

    public function test_un_membre_sans_la_permission_ne_peut_pas_gerer(): void
    {
        $equipe = $this->creerUtilisateur('equipe', 'equipe@example.test');

        $reponse = $this->actingAs($equipe, 'superadmin')
            ->post(route('superadmin.organisateurs.gerer', $this->organisateur));

        $this->assertNotSame(200, $reponse->getStatusCode());
        $this->assertFalse(Auth::guard('web')->check());
        $this->assertNull(session('gestion'));
    }

    public function test_la_prise_en_main_expire_apres_une_heure_d_inactivite(): void
    {
        $this->actingAs($this->admin, 'superadmin')
            ->post(route('superadmin.organisateurs.gerer', $this->organisateur));

        session(['gestion.activite' => now()->subHours(2)->timestamp]);

        $this->get(route('admin.gestion.journal'))->assertRedirect(route('superadmin.login'));
        $this->assertFalse(Auth::guard('web')->check());
    }

    public function test_completer_le_profil_en_mode_gerer_finalise_le_compte_sans_email(): void
    {
        Mail::fake();
        $incomplet = $this->creerUtilisateur('admin', 'incomplet@example.test');
        DB::table('users')->where('id', $incomplet->id)->update(['statut' => 'incomplet', 'mandat_actif' => true]);

        $this->actingAs($this->admin, 'superadmin')->post(route('superadmin.organisateurs.gerer', $incomplet));
        Auth::shouldUse('web');

        $this->withSession(['profil' => ['type' => 'particulier', 'document_justificatif' => 'docs/cni.jpg', 'signature' => 'signatures/s.png']])
            ->post(route('profil.submit'))
            ->assertRedirect(route('dashboard'));

        $this->assertSame('actif', $incomplet->fresh()->statut);
        $this->assertSame('docs/cni.jpg', $incomplet->fresh()->document_justificatif);
        Mail::assertNothingQueued();
        Mail::assertNothingSent();
        $this->assertSame('Finalisation du profil', ActionPaxevent::firstOrFail()->resume);
    }

    public function test_la_transmission_du_contrat_est_enregistree_et_le_contrat_signe_conserve(): void
    {
        Storage::fake('local');
        DB::table('users')->where('id', $this->organisateur->id)->update(['contrat_chemin' => 'contrats/1/contrat.pdf']);

        $this->actingAs($this->admin, 'superadmin')
            ->post(route('superadmin.organisateurs.contrat.transmis', $this->organisateur), ['canal' => 'whatsapp'])
            ->assertSessionHas('success');

        $transmis = $this->organisateur->fresh();
        $this->assertSame('whatsapp', $transmis->contrat_transmis_canal);
        $this->assertSame('owner@example.test', $transmis->contrat_transmis_par);
        $this->assertNotNull($transmis->contrat_transmis_le);

        $this->actingAs($this->admin, 'superadmin')
            ->post(route('superadmin.organisateurs.contrat.signe', $this->organisateur), [
                'contrat_signe' => \Illuminate\Http\UploadedFile::fake()->create('signe.pdf', 100, 'application/pdf'),
            ])->assertSessionHas('success');

        Storage::disk('local')->assertExists($this->organisateur->fresh()->contrat_signe_chemin);
    }

    public function test_le_contrat_est_fige_et_cette_copie_reste_servie(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('L\'extension GD (requise par dompdf) n\'est pas installée ici.');
        }

        Storage::fake('local');
        $service = app(\App\Services\ContratService::class);
        DB::table('users')->where('id', $this->organisateur->id)->update(['commission_physique' => 5]);

        $chemin = $service->figer($this->organisateur->fresh());

        Storage::disk('local')->assertExists($chemin);
        $this->assertSame($chemin, $service->cheminFige($this->organisateur->fresh()));
        $this->assertNotNull($this->organisateur->fresh()->contrat_genere_le);
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($chemin));
    }

    public function test_la_transmission_exige_un_contrat_genere(): void
    {
        $this->actingAs($this->admin, 'superadmin')
            ->post(route('superadmin.organisateurs.contrat.transmis', $this->organisateur), ['canal' => 'email'])
            ->assertSessionHas('error');

        $this->assertNull($this->organisateur->fresh()->contrat_transmis_le);
    }

    public function test_paxevent_supprime_un_evenement_sans_ticket_mais_pas_avec_des_tickets(): void
    {
        $vide = $this->creerEvenement($this->organisateur->id);
        $vendu = $this->creerEvenement($this->organisateur->id);
        Schema::create('ticket', function (Blueprint $table) {
            $table->id();
            $table->timestamp('expire_at')->nullable();
            $table->unsignedBigInteger('evenement_id');
            $table->string('statut_paiement')->default('en_attente');
            $table->timestamps();
        });
        DB::table('ticket')->insert(['evenement_id' => $vendu, 'statut_paiement' => 'payé', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($this->admin, 'superadmin')
            ->post(route('superadmin.organisateurs.gerer', $this->organisateur));
        Auth::shouldUse('web');

        // Avec des tickets : refusé, et rien n'est consigné au journal.
        $this->delete(route('admin.evenements.destroy', $vendu))->assertSessionHas('error');
        $this->assertSame(1, DB::table('evenement')->where('id', $vendu)->count());
        $this->assertSame(0, ActionPaxevent::count());

        // Sans ticket : supprimé, et consigné.
        $this->delete(route('admin.evenements.destroy', $vide))->assertSessionHas('success');
        $this->assertSame(0, DB::table('evenement')->where('id', $vide)->count());
        $this->assertSame('Suppression d\'un événement', ActionPaxevent::firstOrFail()->resume);
    }

    public function test_sans_autorisation_gerer_est_refuse(): void
    {
        $sansAccord = $this->creerUtilisateur('admin', 'sansaccord@example.test');

        $this->actingAs($this->admin, 'superadmin')
            ->post(route('superadmin.organisateurs.gerer', $sansAccord))
            ->assertSessionHas('error');

        $this->assertFalse(Auth::guard('web')->check());
        $this->assertNull(session('gestion'));
    }

    public function test_l_organisateur_active_l_autorisation_en_un_clic_puis_la_retire(): void
    {
        $sansAccord = $this->creerUtilisateur('admin', 'unclic@example.test');
        Storage::fake('local');

        $this->actingAs($sansAccord)->post(route('admin.mandat.accorder'))->assertSessionHas('success');
        $apres = $sansAccord->fresh();
        $this->assertTrue($apres->mandat_actif);
        $this->assertSame('organisateur', $apres->mandat_mode);
        $this->assertNotNull($apres->mandat_accorde_le);

        $this->actingAs($sansAccord)->post(route('admin.mandat.retirer'))->assertSessionHas('success');
        $retire = $sansAccord->fresh();
        $this->assertFalse($retire->mandat_actif);
        $this->assertSame('organisateur', $retire->mandat_retire_par);
        $this->assertNotNull($retire->mandat_retire_le);
    }

    public function test_le_retrait_de_l_autorisation_interrompt_la_prise_en_main(): void
    {
        $this->actingAs($this->admin, 'superadmin')
            ->post(route('superadmin.organisateurs.gerer', $this->organisateur));
        Auth::shouldUse('web');

        // L'organisateur retire son autorisation pendant que PaxEvent le gère.
        DB::table('users')->where('id', $this->organisateur->id)->update(['mandat_actif' => false]);
        // En production chaque requête recharge l'utilisateur ; ici on oublie celui gardé en mémoire.
        app('auth')->forgetGuards();
        Auth::shouldUse('web');

        $this->get(route('admin.gestion.journal'))->assertRedirect(route('superadmin.login'));
        $this->assertFalse(Auth::guard('web')->check());
    }

    public function test_paxevent_ne_peut_pas_s_accorder_l_autorisation_en_mode_gerer(): void
    {
        $this->actingAs($this->admin, 'superadmin')
            ->post(route('superadmin.organisateurs.gerer', $this->organisateur));
        Auth::shouldUse('web');

        $this->post(route('admin.mandat.retirer'))->assertRedirect(route('dashboard'))->assertSessionHas('error');
        $this->post(route('admin.mandat.accorder'))->assertRedirect(route('dashboard'))->assertSessionHas('error');
        $this->assertTrue($this->organisateur->fresh()->mandat_actif);
    }

    public function test_paxevent_enregistre_le_justificatif_signe_recu_de_l_organisateur(): void
    {
        Storage::fake('local');
        $sansAccord = $this->creerUtilisateur('admin', 'papier@example.test');

        // Sans justificatif : refusé.
        $this->actingAs($this->admin, 'superadmin')
            ->post(route('superadmin.organisateurs.mandat.enregistrer', $sansAccord))
            ->assertSessionHasErrors('justificatif');
        $this->assertFalse($sansAccord->fresh()->mandat_actif);

        $this->actingAs($this->admin, 'superadmin')
            ->post(route('superadmin.organisateurs.mandat.enregistrer', $sansAccord), [
                'justificatif' => \Illuminate\Http\UploadedFile::fake()->create('declaration-signee.pdf', 100, 'application/pdf'),
            ])->assertSessionHas('success');

        $apres = $sansAccord->fresh();
        $this->assertTrue($apres->mandat_actif);
        $this->assertSame('paxevent_recueilli', $apres->mandat_mode);
        $this->assertSame('owner@example.test', $apres->mandat_enregistre_par);
        Storage::disk('local')->assertExists($apres->mandat_justificatif_chemin);
    }

    public function test_les_regles_d_acces_du_mode_gerer(): void
    {
        $autorise = GestionOrganisateur::AUTORISE;
        $refuse = GestionOrganisateur::REFUSE;
        $lecture = GestionOrganisateur::LECTURE;

        $this->assertSame($autorise, GestionOrganisateur::decision('admin.evenements.store', 'POST'));
        $this->assertSame($autorise, GestionOrganisateur::decision('admin.agents-vente.store', 'POST'));
        $this->assertSame($autorise, GestionOrganisateur::decision('profil.submit', 'POST'));
        $this->assertSame($autorise, GestionOrganisateur::decision('admin.evenements.destroy', 'DELETE'));
        $this->assertSame($refuse, GestionOrganisateur::decision('admin.retraits.store', 'POST'));
        $this->assertSame($lecture, GestionOrganisateur::decision('admin.retraits.index', 'GET'));
        $this->assertSame($refuse, GestionOrganisateur::decision('statistiques.index', 'POST'));
        $this->assertSame($refuse, GestionOrganisateur::decision('route.inconnue', 'GET'));
        $this->assertSame($refuse, GestionOrganisateur::decision(null, 'GET'));
    }

    private function creerUtilisateur(string $role, string $email): User
    {
        $id = DB::table('users')->insertGetId([
            'nom' => ucfirst($role), 'email' => $email, 'mot_de_passe' => bcrypt('secret'),
            'role' => $role, 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
        ]);

        return User::findOrFail($id);
    }

    private function creerEvenement(int $userId): int
    {
        return DB::table('evenement')->insertGetId([
            'user_id' => $userId, 'titre' => 'Concert', 'slug' => 'concert-'.uniqid(), 'statut' => 'publié',
            'date_event' => now()->addDays(20), 'capacite' => 100, 'quota_vendu' => 0, 'gratuit' => false,
            'ventes_fermees' => false, 'billetterie_ouverte' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function createTables(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->string('statut_avant_suppression', 30)->nullable();
            $table->string('supprime_par')->nullable();
            $table->string('nom');
            $table->string('email')->unique();
            $table->string('telephone')->nullable();
            $table->string('organisation')->nullable();
            $table->string('mot_de_passe');
            $table->string('role')->default('admin');
            $table->string('statut')->default('actif');
            $table->text('permissions')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->boolean('cree_par_paxevent')->default(false);
            $table->string('type')->nullable();
            $table->string('document_justificatif')->nullable();
            $table->string('document_cip')->nullable();
            $table->string('type_detail')->nullable();
            $table->string('fonction')->nullable();
            $table->string('nom_representant')->nullable();
            $table->string('numero_rc')->nullable();
            $table->string('numero_cip')->nullable();
            $table->string('signature')->nullable();
            $table->timestamp('contrat_genere_le')->nullable();
            $table->string('contrat_chemin')->nullable();
            $table->timestamp('contrat_transmis_le')->nullable();
            $table->string('contrat_transmis_canal')->nullable();
            $table->string('contrat_transmis_par')->nullable();
            $table->string('contrat_signe_chemin')->nullable();
            $table->boolean('mandat_actif')->default(false);
            $table->timestamp('mandat_accorde_le')->nullable();
            $table->string('mandat_mode', 30)->nullable();
            $table->string('mandat_pdf_chemin')->nullable();
            $table->string('mandat_justificatif_chemin')->nullable();
            $table->string('mandat_enregistre_par')->nullable();
            $table->timestamp('mandat_retire_le')->nullable();
            $table->string('mandat_retire_par')->nullable();
            $table->decimal('commission_pourcentage', 5, 2)->nullable();
            $table->decimal('commission_physique', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('evenement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('titre');
            $table->string('slug')->unique();
            $table->dateTime('date_event')->nullable();
            $table->string('statut')->default('brouillon');
            $table->unsignedInteger('capacite')->default(0);
            $table->unsignedInteger('quota_vendu')->default(0);
            $table->boolean('gratuit')->default(false);
            $table->boolean('ventes_fermees')->default(false);
            $table->boolean('billetterie_ouverte')->default(true);
            $table->dateTime('billetterie_ouvre_le')->nullable();
            $table->timestamps();
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->string('type_operation');
            $table->text('details')->nullable();
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('actions_paxevent', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('admin_email');
            $table->string('methode', 10);
            $table->string('route')->nullable();
            $table->string('resume');
            $table->string('ip')->nullable();
            $table->timestamps();
        });
    }
}
