<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Mecanisme A : parrainage direct d'un organisateur par un affilie.
// Un organisateur ne peut avoir qu'un seul parrain direct.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->foreignId('organisateur_id')->constrained('users')->cascadeOnDelete();
            $table->string('code_parrainage', 20)->nullable();
            // Premier evenement de l'organisateur, seul couvert par le mecanisme A
            $table->foreignId('premier_evenement_id')->nullable()->constrained('evenement')->nullOnDelete();
            $table->enum('statut', ['en_attente', 'valide', 'annule'])->default('en_attente');
            $table->timestamp('valide_le')->nullable();
            $table->timestamps();

            $table->unique('organisateur_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_referrals');
    }
};
