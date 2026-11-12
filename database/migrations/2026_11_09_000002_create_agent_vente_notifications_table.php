<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cette migration s'appelait 2026_11_10_000002 avant d'être renommée
        // (commit fe3fb71). Les bases qui l'ont exécutée sous l'ancien nom ont
        // déjà la table : on ne la recrée pas.
        if (Schema::hasTable('agent_vente_notifications')) {
            return;
        }

        Schema::create('agent_vente_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_vente_id')->constrained('agents_vente')->cascadeOnDelete();
            $table->foreignId('evenement_id')->nullable()->constrained('evenement')->nullOnDelete();
            $table->string('titre');
            $table->text('message');
            $table->timestamp('lu_at')->nullable();
            $table->timestamps();

            $table->index(['agent_vente_id', 'lu_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_vente_notifications');
    }
};
