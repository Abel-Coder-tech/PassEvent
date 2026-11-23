<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrustProxiesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Route::get('/__test-ip', fn () => response()->json([
            'ip' => request()->ip(),
            'https' => request()->isSecure(),
        ]));
    }

    public function test_un_visiteur_direct_ne_peut_pas_inventer_son_ip(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
            ->withHeader('X-Forwarded-For', '1.2.3.4')
            ->get('/__test-ip')
            ->assertJson(['ip' => '203.0.113.7']);
    }

    public function test_le_proxy_local_de_l_hebergeur_transmet_la_vraie_ip_et_le_https(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders(['X-Forwarded-For' => '198.51.100.20', 'X-Forwarded-Proto' => 'https'])
            ->get('/__test-ip')
            ->assertJson(['ip' => '198.51.100.20', 'https' => true]);
    }

    public function test_un_proxy_sur_reseau_prive_est_aussi_reconnu(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '10.2.3.4'])
            ->withHeader('X-Forwarded-For', '198.51.100.21')
            ->get('/__test-ip')
            ->assertJson(['ip' => '198.51.100.21']);
    }
}
