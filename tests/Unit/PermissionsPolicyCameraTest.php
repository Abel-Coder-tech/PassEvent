<?php

namespace Tests\Unit;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;

// Regression : camera=() bloquait getUserMedia sur /agent/scan (NotAllowedError),
// la camera n'etait donc jamais autorisee pour les agents.
class PermissionsPolicyCameraTest extends TestCase
{
    private function permissionsPolicy(string $uri): string
    {
        $request = Request::create('https://paxevent.com' . $uri, 'GET');

        $response = (new SecurityHeaders())->handle($request, function () {
            return new Response('ok');
        });

        return (string) $response->headers->get('Permissions-Policy');
    }

    public function test_camera_autorisee_sur_le_scan_agent(): void
    {
        $this->assertStringContainsString('camera=(self)', $this->permissionsPolicy('/agent/scan'));
    }

    public function test_camera_bloquee_hors_scan_agent(): void
    {
        $this->assertStringContainsString('camera=()', $this->permissionsPolicy('/agent/dashboard'));
        $this->assertStringContainsString('camera=()', $this->permissionsPolicy('/'));
    }

    public function test_geolocalisation_et_micro_toujours_bloques(): void
    {
        $policy = $this->permissionsPolicy('/agent/scan');

        $this->assertStringContainsString('geolocation=()', $policy);
        $this->assertStringContainsString('microphone=()', $policy);
    }
}