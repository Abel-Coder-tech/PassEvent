<?php

namespace Tests\Feature;

use App\Models\OrganizerActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminOrganizerActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_includes_organizer_activities(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'super_admin',
            'statut' => 'actif',
        ]);

        $organizer = User::factory()->create([
            'role' => 'admin',
            'statut' => 'actif',
        ]);

        OrganizerActivityLog::create([
            'user_id' => $organizer->id,
            'action' => 'evenement.create',
            'status' => 'success',
            'description' => 'Événement créé : Demo',
            'details' => ['evenement_id' => 1],
            'ip' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($superadmin, 'superadmin')
            ->get(route('superadmin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('activitesOrganisateurs');
        $activites = $response->viewData('activitesOrganisateurs');
        $this->assertTrue($activites->contains(fn ($log) => $log->action === 'evenement.create' && $log->status === 'success'));
    }
}
