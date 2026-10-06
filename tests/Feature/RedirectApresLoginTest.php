<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Regression : apres une expiration de session (CheckInactivite), l URL de
 * departure doit etre conservee puis rendue a l utilisateur apres le login,
 * au lieu de le jeter sur le dashboard.
 */
class RedirectApresLoginTest extends TestCase
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

        // Page protegee minimaliste : isole le flux login/redirection de toute
        // dependance a la base (tables de lots, evenements, ...).
        Route::middleware(['web', 'auth'])
            ->get('/__page-protegee', fn () => response('page protegee'))
            ->name('page.protegee.test');
    }

    private function userOrganisateur(): User
    {
        return User::create([
            'nom' => 'Test',
            'email' => 'redirect@test.test',
            'mot_de_passe' => Hash::make('secret-123'),
            'role' => 'organisateur',
            'statut' => 'valide',
        ]);
    }

    // Session simple (pas de timeout) : l URL doit etre memorisee.
    public function test_acces_non_authentifie_memorise_l_url_de_depart(): void
    {
        $url = $this->app['url']->to('/parametres');

        $this->get('/parametres')->assertRedirect('/login');

        $this->assertSame($url, $this->app['session.store']->get('url.intended'));
    }

    // Le login rend l URL memorisee, pas le dashboard.
    public function test_login_redirige_vers_la_page_precedente(): void
    {
        $this->userOrganisateur();
        $url = $this->app['url']->to('/admin/lots-physiques/5/template');

        $this->withSession(['url.intended' => $url])
            ->post('/login', ['email' => 'redirect@test.test', 'mot_de_passe' => 'secret-123'])
            ->assertRedirect($url);
    }

    // Sans URL memorisee : retour au dashboard (comportement attendu).
    public function test_login_sans_url_memorisee_va_au_dashboard(): void
    {
        $this->userOrganisateur();

        $this->post('/login', ['email' => 'redirect@test.test', 'mot_de_passe' => 'secret-123'])
            ->assertRedirect(route('dashboard'));
    }

    // Flux complet : expiration sur la page, puis reconnexion -> retour.
    public function test_flux_complet_expiration_puis_reconnexion(): void
    {
        $user = $this->userOrganisateur();
        $url = $this->app['url']->to('/__page-protegee');

        // 1. l utilisateur consulte la page, puis reste 61 min sans rien faire
        $this->actingAs($user)
            ->withSession(['derniere_activite_web' => now()->subMinutes(61)])
            ->get('/__page-protegee')
            ->assertRedirect('/login')
            ->assertSessionHas('error');

        $intended = $this->app['session.store']->get('url.intended');
        $this->assertSame($url, $intended, 'CheckInactivite doit memoriser l URL de depart.');

        // 2. reconnexion : la session renouvelee doit toujours porter l URL
        $this->post('/login', ['email' => 'redirect@test.test', 'mot_de_passe' => 'secret-123'])
            ->assertRedirect($url);
    }

    // La session expiree ne doit pas envoyer l utilisateur sur une page qu il
    // n a pas le droit de voir (le dashboard reste le repli sans intended).
    public function test_le_flux_ne_doit_pas_aller_au_dashboard(): void
    {
        $user = $this->userOrganisateur();
        $url = $this->app['url']->to('/__page-protegee');

        $this->actingAs($user)
            ->withSession(['derniere_activite_web' => now()->subMinutes(61)])
            ->get('/__page-protegee')
            ->assertRedirect('/login');

        $this->post('/login', ['email' => 'redirect@test.test', 'mot_de_passe' => 'secret-123'])
            ->assertRedirect($url, 'La reconnexion doit rendre la page de depart, pas /dashboard.');
    }

    // Une soumission avec token perime (session expiree) doit rediriger au lieu
    // d'afficher la page 419, et memoriser la page pour la reconnexion.
    public function test_419_redirige_et_memorise_l_url_de_depart(): void
    {
        $url = $this->app['url']->to('/admin/lots-physiques/5/template');

        $request = Request::create('/admin/lots-physiques/5/template', 'POST', [], [], [], [
            'HTTP_REFERER' => $url,
            'HTTP_HOST' => 'localhost',
        ]);
        $request->setLaravelSession($this->app['session.store']);

        $response = $this->app[ExceptionHandler::class]->render($request, new TokenMismatchException());

        $this->assertEquals(302, $response->getStatusCode(), 'Le 419 doit renvoyer une redirection.');
        $this->assertSame($url, $this->app['session.store']->get('url.intended'));
    }

    // En AJAX/JSON, le 419 reste du JSON (aucune redirection parasite).
    public function test_419_json_reste_json(): void
    {
        $request = Request::create('/admin/lots-physiques/5/template', 'POST', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_HOST' => 'localhost',
        ]);
        $request->setLaravelSession($this->app['session.store']);

        $response = $this->app[ExceptionHandler::class]->render($request, new TokenMismatchException());

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals(419, $response->getStatusCode());
    }

    // Le 419 ne redirige pas vers le login si l'utilisateur arrivait d'une page
    // de connexion elle-meme : on ne memorise pas de boucle.
    public function test_419_depuis_le_login_ne_memorise_pas_de_boucle(): void
    {
        $request = Request::create('/login', 'POST', [], [], [], [
            'HTTP_REFERER' => $this->app['url']->to('/login'),
            'HTTP_HOST' => 'localhost',
        ]);
        $request->setLaravelSession($this->app['session.store']);

        $this->app[ExceptionHandler::class]->render($request, new TokenMismatchException());

        $this->assertNull($this->app['session.store']->get('url.intended'));
    }
}
