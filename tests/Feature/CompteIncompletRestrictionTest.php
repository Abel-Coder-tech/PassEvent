<?php

namespace Tests\Feature;

use App\Http\Middleware\RestreindreCompteIncomplet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Un compte incomplet ne peut atteindre que la création d'événement et la
 * finalisation du profil, y compris en tapant une URL directement.
 */
class CompteIncompletRestrictionTest extends TestCase
{
    private function requete(string $nomRoute, bool $json = false): Request
    {
        $route = new Route('GET', '/__test-restriction', fn () => null);
        $route->name($nomRoute);

        $request = Request::create('/__test-restriction', 'GET', [], [], [], [
            'HTTP_ACCEPT' => $json ? 'application/json' : 'text/html',
        ]);
        $request->setRouteResolver(fn () => $route);

        return $request;
    }

    private function middlewarePour(?User $user): RestreindreCompteIncomplet
    {
        Auth::shouldReceive('user')->andReturn($user);

        return new RestreindreCompteIncomplet();
    }

    private function utilisateur(string $statut, string $role = 'admin'): User
    {
        return new User([
            'nom' => 'Test',
            'email' => 'restriction@test.test',
            'role' => $role,
            'statut' => $statut,
        ]);
    }

    public function test_un_compte_incomplet_peut_creer_un_evenement(): void
    {
        $appele = false;
        $reponse = $this->middlewarePour($this->utilisateur('incomplet'))
            ->handle($this->requete('admin.evenements.create'), function () use (&$appele) {
                $appele = true;

                return 'ok';
            });

        $this->assertTrue($appele);
        $this->assertSame('ok', $reponse);
    }

    public function test_un_compte_incomplet_peut_finaliser_son_profil(): void
    {
        foreach (['profil.incomplet', 'profil.step2', 'profil.post-step2', 'profil.recap', 'profil.submit', 'dashboard', 'logout'] as $route) {
            $this->middlewarePour($this->utilisateur('incomplet'))
                ->handle($this->requete($route), fn () => 'ok');

            $this->assertTrue(true);
        }
    }

    public function test_un_compte_incomplet_est_redirige_depuis_une_autre_rubrique(): void
    {
        $appele = false;
        $reponse = $this->middlewarePour($this->utilisateur('incomplet'))
            ->handle($this->requete('statistiques.index'), function () use (&$appele) {
                $appele = true;

                return 'ok';
            });

        $this->assertFalse($appele);
        $this->assertInstanceOf(RedirectResponse::class, $reponse);
        $this->assertSame(route('profil.incomplet'), $reponse->getTargetUrl());
    }

    public function test_une_requete_json_est_refusee_avec_403(): void
    {
        $reponse = $this->middlewarePour($this->utilisateur('incomplet'))
            ->handle($this->requete('admin.evenements.index', true), fn () => 'ok');

        $this->assertSame(403, $reponse->getStatusCode());
        $this->assertSame(
            'Finalisez votre profil avant d\'accéder à cette rubrique.',
            $reponse->getData(true)['message']
        );
    }

    public function test_un_compte_actif_conserve_l_acces_aux_autres_rubriques(): void
    {
        $appele = false;
        $reponse = $this->middlewarePour($this->utilisateur('actif'))
            ->handle($this->requete('statistiques.index'), function () use (&$appele) {
                $appele = true;

                return 'ok';
            });

        $this->assertTrue($appele);
        $this->assertSame('ok', $reponse);
    }

    public function test_un_membre_equipe_n_est_pas_restreint(): void
    {
        $appele = false;
        $this->middlewarePour($this->utilisateur('incomplet', 'equipe'))
            ->handle($this->requete('statistiques.index'), function () use (&$appele) {
                $appele = true;

                return 'ok';
            });

        $this->assertTrue($appele);
    }
}
