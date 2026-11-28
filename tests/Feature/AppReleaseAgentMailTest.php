<?php

namespace Tests\Feature;

use App\Http\Controllers\AppReleaseController;
use App\Mail\AgentAssigned;
use App\Mail\AgentVenteAssigned;
use App\Models\Agent;
use App\Models\AgentVente;
use App\Models\AppRelease;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * L'APK PaxAgent est proposé dans le mail de création d'un agent, et la
 * commande paxagent:publier alimente le dashboard et ces mails.
 */
class AppReleaseAgentMailTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Storage::disk('local')->deleteDirectory('apps');
        parent::tearDown();
    }

    private function evenement(): Evenement
    {
        $organisateur = User::factory()->create(['role' => 'admin', 'statut' => 'actif']);

        return Evenement::create([
            'user_id' => $organisateur->id,
            'titre' => 'Concert APK',
            'slug' => 'concert-apk',
            'lieu' => 'Cotonou',
            'statut' => 'publié',
            'date_event' => now()->addDays(10),
            'capacite' => 100,
            'quota_vendu' => 0,
            'gratuit' => false,
            'ventes_fermees' => false,
            'billetterie_ouverte' => true,
            'ventes_especes' => 'toujours',
        ]);
    }

    private function agentScan(bool $actif = true): Agent
    {
        return Agent::create([
            'nom' => 'Agent scan',
            'email' => 'scan-apk@example.test',
            'password' => bcrypt('secret'),
            'code_acces' => bcrypt('1234'),
            'evenement_id' => $this->evenement()->id,
            'actif' => $actif,
        ]);
    }

    private function release(): AppRelease
    {
        Storage::disk('local')->put('apps/PaxAgent-1.0.0-7.apk', 'apk-de-test');

        return AppRelease::create([
            'platform' => 'android',
            'package_name' => 'com.paxevent.agent',
            'version_code' => 7,
            'version_name' => '1.0.0',
            'sha256' => str_repeat('a', 64),
            'size_bytes' => 11,
            'path' => 'PaxAgent-1.0.0-7.apk',
            'published_at' => now()->subMinute(),
        ]);
    }

    public function test_le_mail_agent_scan_contient_le_lien_de_telechargement(): void
    {
        $this->release();
        $agent = $this->agentScan();

        $mail = new AgentAssigned($agent, 'secret', '1234');

        $this->assertNotNull($mail->lienApp);
        $mail->assertSeeInHtml('Télécharger PaxAgent');
    }

    public function test_le_mail_agent_vente_contient_le_lien_de_telechargement(): void
    {
        $this->release();
        $agent = AgentVente::create([
            'nom' => 'Agent vente',
            'email' => 'vente-apk@example.test',
            'password' => bcrypt('secret'),
            'evenement_id' => $this->evenement()->id,
            'actif' => true,
        ]);

        $mail = new AgentVenteAssigned($agent, 'secret');

        $this->assertNotNull($mail->lienApp);
        $mail->assertSeeInHtml('Télécharger PaxAgent');
    }

    public function test_sans_version_publiee_le_mail_n_affiche_pas_de_lien(): void
    {
        $mail = new AgentAssigned($this->agentScan(), 'secret', '1234');

        $this->assertNull($mail->lienApp);
        $mail->assertDontSeeInHtml('Télécharger PaxAgent');
    }

    public function test_le_lien_du_mail_sert_l_apk_sans_connexion(): void
    {
        $this->release();
        $agent = $this->agentScan();

        $this->get(AppReleaseController::lienPourAgent('scan', $agent->id))
            ->assertOk()
            ->assertDownload('PaxAgent-1.0.0-v7.apk')
            ->assertHeader('Content-Type', 'application/vnd.android.package-archive');
    }

    public function test_un_lien_modifie_ou_expire_est_refuse(): void
    {
        $this->release();
        $agent = $this->agentScan();
        $lien = AppReleaseController::lienPourAgent('scan', $agent->id);

        $this->get(str_replace("/scan/{$agent->id}", '/scan/'.($agent->id + 1), $lien))
            ->assertForbidden();

        $this->travel(8)->days();
        $this->get($lien)->assertForbidden();
    }

    public function test_un_agent_desactive_ne_peut_plus_telecharger(): void
    {
        $this->release();
        $agent = $this->agentScan(actif: false);

        $this->get(URL::temporarySignedRoute(
            'app.download.agent',
            now()->addDays(7),
            ['type' => 'scan', 'agent' => $agent->id],
        ))->assertForbidden();
    }

    // APK de test : une archive ZIP avec le vrai manifeste d'une build 8 (v1.0.7)
    private function apkBuild8(): string
    {
        $chemin = tempnam(sys_get_temp_dir(), 'apk').'.apk';
        $zip = new \ZipArchive;
        $zip->open($chemin, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFile(base_path('tests/fixtures/AndroidManifest-paxagent-8.xml'), 'AndroidManifest.xml');
        $zip->close();

        return $chemin;
    }

    public function test_la_commande_lit_la_version_dans_l_apk(): void
    {
        $source = $this->apkBuild8();

        // Sans --build ni --nom-version : tout est lu dans le fichier
        $this->artisan('paxagent:publier', ['apk' => $source, '--notes' => 'Première version'])->assertSuccessful();

        $release = AppRelease::latest();
        $this->assertSame(8, $release->version_code);
        $this->assertSame('1.0.7', $release->version_name);
        $this->assertSame(hash_file('sha256', $source), $release->sha256);
        Storage::disk('local')->assertExists('apps/PaxAgent-1.0.7-8.apk');

        // Republier le même fichier (ou une build plus ancienne) est refusé
        $this->artisan('paxagent:publier', ['apk' => $source])->assertFailed();
        $this->assertSame(1, AppRelease::count());

        @unlink($source);
    }

    public function test_la_commande_refuse_un_numero_de_build_different_de_l_apk(): void
    {
        $source = $this->apkBuild8();

        // C'est l'erreur qui faisait proposer la mise à jour en boucle
        $this->artisan('paxagent:publier', ['apk' => $source, '--nom-version' => '1.0.7', '--build' => '9'])->assertFailed();
        $this->artisan('paxagent:publier', ['apk' => $source, '--package' => 'com.autre.appli'])->assertFailed();
        $this->assertNull(AppRelease::latest());

        // Les bonnes valeurs sont acceptées
        $this->artisan('paxagent:publier', ['apk' => $source, '--nom-version' => '1.0.7', '--build' => '8'])->assertSuccessful();

        @unlink($source);
    }

    public function test_la_commande_refuse_un_fichier_qui_n_est_pas_un_apk(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'apk').'.apk';
        file_put_contents($source, 'pas un zip');

        $this->artisan('paxagent:publier', [
            'apk' => $source,
            '--nom-version' => '1.0.0',
            '--build' => '7',
        ])->assertFailed();

        $this->assertNull(AppRelease::latest());
        @unlink($source);
    }

    public function test_l_appli_d_un_agent_recoit_un_lien_de_mise_a_jour_utilisable_sans_session(): void
    {
        $this->release();
        $agent = $this->agentScan();
        $token = $agent->createToken('agent-auth', ['agent:read'])->plainTextToken;

        $reponse = $this->withToken($token)->getJson('/api/app/paxagent/latest')->assertOk();
        $this->assertSame(7, $reponse->json('version_code'));
        $url = $reponse->json('url');
        $this->assertStringContainsString('/app/paxagent/agent/scan/'.$agent->id, $url);

        // Ouvert dans le navigateur du téléphone : aucune session, aucun jeton
        $this->app['auth']->forgetGuards();
        $this->get($url)->assertOk();

        // Agent désactivé : le lien ne marche plus
        $agent->update(['actif' => false]);
        $this->get($url)->assertForbidden();
    }
}
