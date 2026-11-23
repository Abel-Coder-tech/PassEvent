<?php

namespace Tests\Feature;

use App\Models\AppRelease;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AppReleaseDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Storage::disk('local')->deleteDirectory('apps');
        parent::tearDown();
    }

    private function release(): AppRelease
    {
        Storage::disk('local')->put('apps/PaxAgent-test.apk', 'apk-de-test');

        return AppRelease::create([
            'platform' => 'android',
            'package_name' => 'com.example.pax_scan',
            'version_code' => 4,
            'version_name' => '1.0.0',
            'sha256' => str_repeat('a', 64),
            'size_bytes' => 11,
            'path' => 'PaxAgent-test.apk',
            'published_at' => now()->subMinute(),
        ]);
    }

    private function signedUrl(AppRelease $release, int $uid): string
    {
        return URL::temporarySignedRoute(
            'app.download',
            now()->addMinutes(15),
            ['release' => $release->id, 'uid' => $uid],
        );
    }

    public function test_un_organisateur_connecte_peut_telecharger_via_un_lien_signe(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $release = $this->release();

        $this->actingAs($user)
            ->get($this->signedUrl($release, $user->id))
            ->assertOk()
            ->assertHeader('X-Accel-Redirect', '/protected/apps/PaxAgent-test.apk')
            ->assertHeader('Content-Type', 'application/vnd.android.package-archive');
    }

    public function test_un_lien_sans_signature_est_refuse(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $release = $this->release();

        $this->actingAs($user)
            ->get(route('app.download', $release))
            ->assertForbidden();
    }

    public function test_un_lien_ne_peut_pas_etre_utilise_par_un_autre_compte(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $other = User::factory()->create(['role' => 'admin']);
        $release = $this->release();

        $this->actingAs($user)
            ->get($this->signedUrl($release, $other->id))
            ->assertForbidden();
    }

    public function test_modifier_luid_dans_un_lien_signe_est_refuse(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $other = User::factory()->create(['role' => 'admin']);
        $release = $this->release();
        $url = $this->signedUrl($release, $user->id);

        // On ne change que la valeur de uid : la signature ne couvre plus le
        // lien, et le contrôleur recalcule l'identité depuis la session/JWT.
        $modifiee = preg_replace(
            '/([?&]uid=)\d+/',
            '${1}'.$other->id,
            $url
        );

        $this->actingAs($user)->get($modifiee)->assertForbidden();
    }

    public function test_une_version_non_publiee_n_est_pas_downloadable(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $release = $this->release();
        $release->update(['published_at' => null]);

        $this->actingAs($user)
            ->get($this->signedUrl($release, $user->id))
            ->assertNotFound();
    }
}
