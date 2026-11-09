<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AgentDeletionLogsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('agents', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->foreign('agent_id')->references('id')->on('agents')->nullOnDelete();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('logs');
        Schema::dropIfExists('agents');

        parent::tearDown();
    }

    public function test_deleting_an_agent_preserves_logs_without_the_agent_reference(): void
    {
        $agentId = DB::table('agents')->insertGetId([]);
        DB::table('logs')->insert(['agent_id' => $agentId]);

        $migration = require dirname(__DIR__, 2) . '/database/migrations/2026_11_09_000004_make_logs_agent_id_nullable.php';
        $migration->up();

        DB::table('agents')->where('id', $agentId)->delete();

        $this->assertDatabaseHas('logs', [
            'agent_id' => null,
        ]);
    }
}
