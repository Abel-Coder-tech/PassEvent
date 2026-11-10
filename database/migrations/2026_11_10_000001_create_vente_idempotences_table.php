<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vente_idempotences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_vente_id');
            $table->string('cle', 100);
            // Empreinte de la demande : empêche de réutiliser une clé pour une autre vente.
            $table->char('empreinte', 64);
            $table->string('transaction_id', 100);
            $table->timestamps();

            // Garde-fou atomique : deux requêtes concurrentes avec la même clé
            // ne peuvent pas créer deux ventes. La seconde échoue sur cet index
            // puis relit la vente déjà enregistrée.
            $table->unique(['agent_vente_id', 'cle'], 'vente_idempotence_agent_cle_unique');
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vente_idempotences');
    }
};
