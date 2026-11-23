<?php

namespace Tests\Feature;

use App\Models\IpBloquee;
use App\Models\JournalSecurite;
use App\Services\SecuriteService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class JournalSecuriteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('journal_securite');
        Schema::dropIfExists('ips_bloquees');
        Schema::create('journal_securite', function (Blueprint $t) {
            $t->id();
            $t->string('type', 30);
            $t->string('regle', 60)->nullable();
            $t->string('ip', 45);
            $t->string('methode', 10);
            $t->string('url', 2000);
            $t->string('espace', 30)->nullable();
            $t->string('identifiant')->nullable();
            $t->text('donnees')->nullable();
            $t->string('user_agent', 500)->nullable();
            $t->timestamp('created_at')->useCurrent();
        });
        Schema::create('ips_bloquees', function (Blueprint $t) {
            $t->id();
            $t->string('ip', 45)->unique();
            $t->string('motif')->nullable();
            $t->string('bloque_par')->nullable();
            $t->timestamp('expire_le')->nullable();
            $t->timestamps();
        });
        Cache::flush();
        \Illuminate\Support\Facades\Mail::fake();

        Route::match(['get', 'post'], '/__test-securite', fn () => 'ok');
    }

    private function analyser(string $uri, array $donnees = [], string $ua = 'Mozilla/5.0'): ?array
    {
        $request = Request::create($uri, 'POST', $donnees, [], [], ['HTTP_USER_AGENT' => $ua]);

        return app(SecuriteService::class)->analyser($request);
    }

    public function test_detecte_les_attaques_classiques(): void
    {
        $this->assertSame(['attaque', 'injection_sql'], $this->analyser("/evenements?q=1' OR '1'='1"));
        $this->assertSame(['attaque', 'injection_sql'], $this->analyser('/evenements?q=1 UNION SELECT password FROM users'));
        $this->assertSame(['attaque', 'script_xss'], $this->analyser('/evenements', ['q' => '<script>alert(1)</script>']));
        $this->assertSame(['attaque', 'lecture_fichier'], $this->analyser('/evenements?q=../../../etc/passwd'));
        $this->assertSame(['attaque', 'injection_commande'], $this->analyser('/evenements?q='.urlencode('; wget http://x.y/a.sh')));
        $this->assertSame(['lien_suspect', 'wordpress'], $this->analyser('/wp-admin/install.php?step=1'));
        $this->assertSame(['lien_suspect', 'fichier_env'], $this->analyser('/.env'));
        $this->assertSame(['robot', 'sqlmap'], $this->analyser('/evenements', [], 'sqlmap/1.7'));
    }

    public function test_les_recherches_normales_ne_sont_pas_des_attaques(): void
    {
        foreach (["l'atelier", "soirée d'or", 'Concert à Cotonou', 'Select Music Festival', 'Jazz & Blues', 'Union des étudiants'] as $q) {
            $this->assertNull($this->analyser('/evenements?q='.urlencode($q)), $q);
        }
        $this->assertNull($this->analyser('/evenements/mon-evenement'));
    }

    public function test_un_mot_de_passe_n_est_jamais_enregistre_ni_analyse(): void
    {
        // Un mot de passe qui ressemble à une attaque n'est pas une attaque
        $this->assertNull($this->analyser('/login', ['email' => 'a@b.c', 'mot_de_passe' => "' OR '1'='1"]));

        $request = Request::create('/login', 'POST', ['email' => 'a@b.c', 'mot_de_passe' => 'MonSecret123', 'password' => 'x', '_token' => 't']);
        $donnees = app(SecuriteService::class)->donneesMasquees($request);

        $this->assertSame('a@b.c', $donnees['email']);
        $this->assertSame('••••••', $donnees['mot_de_passe']);
        $this->assertSame('••••••', $donnees['password']);
        $this->assertArrayNotHasKey('_token', $donnees);
    }

    public function test_un_robot_d_attaque_est_refuse_et_enregistre(): void
    {
        $this->withHeader('User-Agent', 'sqlmap/1.7')->get('/__test-securite')->assertForbidden();

        $this->assertDatabaseHas('journal_securite', ['type' => 'robot', 'regle' => 'sqlmap']);
    }

    public function test_une_attaque_est_enregistree_sans_bloquer_la_requete(): void
    {
        $this->get('/__test-securite?q='.urlencode("1' OR '1'='1"))->assertOk();

        $this->assertDatabaseHas('journal_securite', ['type' => 'attaque', 'regle' => 'injection_sql']);
    }

    public function test_une_ip_bloquee_est_refusee(): void
    {
        IpBloquee::create(['ip' => '203.0.113.9', 'expire_le' => now()->addHour()]);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])->get('/__test-securite')->assertForbidden();
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])->get('/__test-securite')->assertOk();
    }

    public function test_un_blocage_expire_ne_bloque_plus(): void
    {
        IpBloquee::create(['ip' => '203.0.113.11', 'expire_le' => now()->subMinute()]);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.11'])->get('/__test-securite')->assertOk();
    }

    public function test_un_robot_ne_remplit_pas_la_base(): void
    {
        for ($i = 0; $i < SecuriteService::MAX_LIGNES_PAR_IP + 15; $i++) {
            $this->withHeader('User-Agent', 'nikto')->get('/__test-securite');
        }

        $this->assertSame(SecuriteService::MAX_LIGNES_PAR_IP, JournalSecurite::count());
    }

    public function test_une_alerte_est_declenchee_puis_verrouillee_une_heure(): void
    {
        $this->assertFalse(Cache::has('securite:alerte:127.0.0.1'));

        for ($i = 0; $i < SecuriteService::SEUIL_ALERTE; $i++) {
            $this->withHeader('User-Agent', 'nikto')->get('/__test-securite');
        }

        // Le verrou d'une heure est posé : les événements suivants n'enverront pas d'autre email
        $this->assertTrue(Cache::has('securite:alerte:127.0.0.1'));
    }
}
