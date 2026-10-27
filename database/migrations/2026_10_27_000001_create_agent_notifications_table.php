<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->foreignId('evenement_id')->nullable()->constrained('evenement')->nullOnDelete();
            $table->string('titre');
            $table->text('message');
            $table->timestamp('lu_at')->nullable();
            $table->timestamps();
            $table->index(['agent_id', 'lu_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_notifications');
    }
};
