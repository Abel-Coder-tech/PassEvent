<?php

namespace Tests\Feature;

use App\Mail\OtpEmail;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

/**
 * Couvre les deux methodes d'inscription organisateur :
 *  - email + code OTP
 *  - Google OAuth
 */
class InscriptionOrganisateurTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('prenom')->nullable();
                $table->string('email')->unique();
                $table->string('telephone')->nullable();
                $table->string('avatar')->nullable();
                $table->string('mot_de_passe')->nullable();
                $table->string('role')->default('admin');
                $table->string('statut')->default('incomplet');
                $table->string('type')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('email_verifications')) {
            Schema::create('email_verifications', function (Blueprint $table) {
                $table->id();
                $table->string('email');
                $table->string('code');
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
            });
        }
    }

    // ---------------------------------------------------------------- helpers

    /**
     * Poste /inscription/email et retourne le code envoye.
     * Le code stocke en base est hache : on le recupere via le mail capture
     * (OtpEmail::$code est public), seule facon fiable de le connaitre.
     */
    private function envoyerCode(string $email): ?string
    {
        $code = null;

        $this->post('/inscription/email', ['email' => $email]);

        Mail::assertSent(OtpEmail::class, function (OtpEmail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        $this->codeEnvoye = $code;

        return $code;
    }

    /** Parcours complet jusqu'a l'etape identite. */
    private function allerJusquALIdentite(string $email = 'orga@example.com'): void
    {
        $this->envoyerCode($email);
        $this->post('/inscription/verifier', ['code' => $this->codeEnvoye]);
    }

    private ?string $codeEnvoye = null;

    private function simulerGoogle(string $email, ?string $nom = 'Jean Google'): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getEmail')->andReturn($email);
        $socialiteUser->shouldReceive('getName')->andReturn($nom);
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/a.jpg');

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        $factory = Mockery::mock(SocialiteFactory::class);
        $factory->shouldReceive('driver')->with('google')->andReturn($provider);

        $this->app->instance(SocialiteFactory::class, $factory);
    }

    // ------------------------------------------------- parcours email + OTP

    public function test_parcours_email_complet_cree_le_compte_et_connecte(): void
    {
        Mail::fake();

        $this->get('/inscription')->assertOk();

        $this->assertNotNull($this->envoyerCode('orga@example.com'), 'Aucun email OTP envoye');
        $this->assertDatabaseHas('email_verifications', ['email' => 'orga@example.com']);
        Mail::assertSent(OtpEmail::class);

        $this->get('/inscription/verifier')->assertOk();

        $this->post('/inscription/verifier', ['code' => $this->codeEnvoye])
            ->assertRedirect(route('inscriptions.identity'));

        $this->get('/inscription/identite')->assertOk();

        $this->post('/inscription/identite', [
            'nom' => 'Marie Martin',
            'telephone' => '+229 97 00 00 00',
            'mot_de_passe' => 'Motdepasse1!',
            'mot_de_passe_confirmation' => 'Motdepasse1!',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'orga@example.com', 'nom' => 'Marie Martin']);
        $this->assertAuthenticated();
        $this->assertSame('incomplet', auth()->user()->statut);

        // Le code ne doit plus etre reutilisable
        $this->assertNotNull(EmailVerification::where('email', 'orga@example.com')->first()->used_at);
    }

    public function test_email_deja_utilise_est_refuse_avec_message_clair(): void
    {
        User::create(['nom' => 'Existant', 'email' => 'deja@example.com', 'role' => 'admin', 'statut' => 'actif']);
        Mail::fake();

        $this->from('/inscription')
            ->post('/inscription/email', ['email' => 'deja@example.com'])
            ->assertRedirect('/inscription')
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();
        $this->assertGuest();
    }

    public function test_code_invalide_et_code_expire_sont_refuses(): void
    {
        Mail::fake();

        $code = $this->envoyerCode('test@example.com');

        $this->from('/inscription/verifier')
            ->post('/inscription/verifier', ['code' => '0000' === $code ? '1111' : '0000'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
        $this->assertEmpty(session('registration.email_verified'));

        // Expiration
        EmailVerification::where('email', 'test@example.com')->update(['expires_at' => now()->subMinute()]);
        $this->from('/inscription/verifier')
            ->post('/inscription/verifier', ['code' => $code])
            ->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_echec_smtp_affiche_un_message_simple_sans_detail_technique(): void
    {
        Mail::shouldReceive('to')->once()->andThrow(new TransportException('535 Authentication failed for smtp.ovh.net'));

        $this->from('/inscription')
            ->post('/inscription/email', ['email' => 'panne@example.com'])
            ->assertRedirect('/inscription')
            ->assertSessionHasErrors('email');

        $message = session('errors')->first('email');

        $this->assertSame("Échec d'envoi du code de vérification.", $message);
        // Aucun detail technique ne doit fuiter vers l'utilisateur
        $this->assertStringNotContainsString('SMTP', $message);
        $this->assertStringNotContainsString('535', $message);
        $this->assertStringNotContainsString('authentification', strtolower($message));

        // Aucun code memorise non plus : l'utilisateur ne doit pas pouvoir
        // valider un code alors que l'email n'est jamais parti.
        $this->assertGuest();
        $this->assertNull(session('registration.email'));
    }

    public function test_renvoi_de_code_signale_les_erreurs_en_json(): void
    {
        $this->post('/inscription/email', ['email' => 'renvoi@example.com']);

        Mail::shouldReceive('to')->once()->andThrow(new TransportException('connection refused'));

        $this->postJson('/inscription/renvoyer')
            ->assertStatus(200)
            ->assertJson(['success' => false, 'message' => "Échec d'envoi du code de vérification."]);
    }

    public function test_un_nouvel_otp_invalide_le_precedent(): void
    {
        Mail::fake();

        $premier = $this->envoyerCode('renvoi2@example.com');

        $this->post('/inscription/renvoyer');

        $actifs = EmailVerification::where('email', 'renvoi2@example.com')->whereNull('used_at')->get();
        $tous = EmailVerification::where('email', 'renvoi2@example.com')->orderBy('id')->get();

        // Un seul code valide : l'ancien a du etre invalide
        $this->assertCount(1, $actifs);
        $this->assertCount(2, $tous);
        $this->assertSame($tous->last()->id, $actifs->first()->id);
        $this->assertNotNull($tous->first()->used_at);
    }

    public function test_etape_identite_inaccessible_sans_email_verifie(): void
    {
        $this->get('/inscription/identite')->assertRedirect(route('inscriptions.organisateur'));
        $this->post('/inscription/identite', ['nom' => 'X'])->assertRedirect(route('inscriptions.organisateur'));
        $this->assertGuest();
    }

    // ------------------------------------------------------- parcours Google

    public function test_google_nouveau_compte_va_directement_a_lidentite(): void
    {
        Mail::fake();
        $this->simulerGoogle('google@example.com', 'Jean Google');

        $this->get('/auth/google/callback')->assertRedirect(route('inscriptions.identity'));

        $this->get('/inscription/identite')->assertOk();

        $this->post('/inscription/identite', [
            'nom' => 'Jean Google',
            'telephone' => '+229 96 11 11 11',
        ])->assertRedirect(route('dashboard'));

        $utilisateur = User::where('email', 'google@example.com')->first();

        $this->assertNotNull($utilisateur);
        $this->assertAuthenticatedAs($utilisateur);
        // Mot de passe aleatoire : le compte n'est pas accessible en direct
        $this->assertNotEmpty($utilisateur->mot_de_passe);
    }

    public function test_google_reconnecte_un_compte_deja_actif(): void
    {
        $existant = User::create(['nom' => 'Ancien', 'email' => 'ancien@example.com', 'role' => 'admin', 'statut' => 'actif']);
        $this->simulerGoogle('ancien@example.com');

        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($existant);
    }

    public function test_google_refuse_un_compte_rejete(): void
    {
        User::create(['nom' => 'Refuse', 'email' => 'rejete@example.com', 'role' => 'admin', 'statut' => 'rejete']);
        $this->simulerGoogle('rejete@example.com');

        $this->get('/auth/google/callback')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_google_echoue_proprement_si_le_callback_renvoie_une_erreur(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andThrow(new \Exception('state invalide'));

        $factory = Mockery::mock(SocialiteFactory::class);
        $factory->shouldReceive('driver')->with('google')->andReturn($provider);
        $this->app->instance(SocialiteFactory::class, $factory);

        config(['app.debug' => false]);

        $this->get('/auth/google/callback')->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // --------------------------------------------------- securite du parcours

    public function test_impossible_de_contourner_le_mot_de_passe_avec_from_google(): void
    {
        Mail::fake();

        $this->envoyerCode('pirate@example.com');

        // Le client tente de s'auto-attribuer le mode Google
        $this->post('/inscription/verifier', ['code' => $this->codeEnvoye, 'from_google' => '1'])
            ->assertRedirect(route('inscriptions.identity'));

        // ... puis de creer un compte sans mot de passe
        $this->post('/inscription/identite', [
            'nom' => 'Pirate',
            'telephone' => '+229 90 00 00 00',
        ])->assertSessionHasErrors('mot_de_passe');

        $this->assertNull(User::where('email', 'pirate@example.com')->first());
        $this->assertGuest();
    }

    public function test_session_regenereree_apres_creation_du_compte(): void
    {
        Mail::fake();

        $this->allerJusquALIdentite('fixation@example.com');

        // Une vraie requete doit passer pour que l'ID lu ensuite soit celui
        // de la session du navigateur, sinon on compare a une session jetable.
        $this->get('/inscription/identite')->assertOk();
        $avant = session()->getId();

        $this->post('/inscription/identite', [
            'nom' => 'Test Session',
            'telephone' => '+229 90 00 00 00',
            'mot_de_passe' => 'Motdepasse1!',
            'mot_de_passe_confirmation' => 'Motdepasse1!',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertNotSame($avant, session()->getId(), 'L ID de session doit changer apres connexion');
    }

    public function test_email_prise_entre_temps_ne_provoque_pas_une_500(): void
    {
        Mail::fake();

        $this->allerJusquALIdentite('concurrent@example.com');

        // Un autre passage (Google) cree le compte entre-temps
        User::create(['nom' => 'Concurrent', 'email' => 'concurrent@example.com', 'role' => 'admin', 'statut' => 'incomplet']);

        $this->post('/inscription/identite', [
            'nom' => 'Perdant',
            'telephone' => '+229 90 00 00 00',
            'mot_de_passe' => 'Motdepasse1!',
            'mot_de_passe_confirmation' => 'Motdepasse1!',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertSame(1, User::where('email', 'concurrent@example.com')->count());
        $this->assertGuest();
    }
}
