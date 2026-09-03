<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('demandes_modification_tarif', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('evenement')->cascadeOnDelete();
            $table->foreignId('tarif_id')->constrained('tarifs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('ancien_prix', 12, 2);
            $table->decimal('nouveau_prix', 12, 2);
            $table->enum('statut', ['en_attente', 'approuve', 'refuse'])->default('en_attente');
            $table->text('notes')->nullable();
            $table->timestamp('traitee_le')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('demandes_modification_tarif');
    }
};
