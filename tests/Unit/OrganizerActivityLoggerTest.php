<?php

namespace Tests\Unit;

use App\Models\OrganizerActivityLog;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizerActivityLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_creates_organizer_activity_log(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        ActivityLogger::success('evenement.create', 'Événement créé : Test', null, ['evenement_id' => 123]);

        $this->assertDatabaseHas('organizer_activity_logs', [
            'user_id' => $user->id,
            'action' => 'evenement.create',
            'status' => 'success',
            'description' => 'Événement créé : Test',
        ]);

        $log = OrganizerActivityLog::first();
        $this->assertNotNull($log);
        $this->assertSame('success', $log->status);
        $this->assertSame(['evenement_id' => 123], $log->details);
        $this->assertSame('Création d\'événement', $log->labelAction());
    }

    public function test_failure_creates_organizer_activity_log(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        ActivityLogger::failure('agent_scan.create', 'Validation échouée', null, ['errors' => ['email']]);

        $this->assertDatabaseHas('organizer_activity_logs', [
            'user_id' => $user->id,
            'action' => 'agent_scan.create',
            'status' => 'failure',
        ]);

        $log = OrganizerActivityLog::first();
        $this->assertSame('failure', $log->status);
        $this->assertSame(['errors' => ['email']], $log->details);
    }
}
