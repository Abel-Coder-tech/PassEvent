<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->nullable()->constrained('ticket')->nullOnDelete();
            $table->foreignId('agent_vente_id')->nullable()->constrained('agents_vente')->nullOnDelete();
            $table->foreignId('evenement_id')->nullable()->constrained('evenement')->nullOnDelete();
            $table->string('action', 50);
            $table->string('statut_avant', 30)->nullable();
            $table->string('statut_apres', 30)->nullable();
            $table->string('reference', 255)->nullable();
            $table->json('details')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at');

            $table->index(['agent_vente_id', 'action']);
            $table->index(['ticket_id', 'created_at']);
            $table->index('reference');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_actions');
    }
};
