<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->unsignedBigInteger('agent_id')->nullable()->change();
            $table->foreign('agent_id')
                ->references('id')
                ->on('agents')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Keep agent_id nullable so deleting an agent never blocks on retained logs.
    }
};
