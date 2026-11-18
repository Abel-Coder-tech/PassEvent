<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Visiteurs qui demandent à être prévenus de l'ouverture de la billetterie.
    public function up(): void
    {
        Schema::create('billetterie_alertes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('evenement')->cascadeOnDelete();
            $table->string('email');
            $table->timestamp('notifie_le')->nullable();
            $table->timestamps();

            $table->unique(['evenement_id', 'email']);
            $table->index(['evenement_id', 'notifie_le']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billetterie_alertes');
    }
};
