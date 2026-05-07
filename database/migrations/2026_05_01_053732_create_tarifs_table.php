<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('evenement')->cascadeOnDelete();
            $table->enum('categorie', ['etudiant', 'externe']);
            $table->enum('type', ['normal', 'vip'])->default('normal');
            $table->decimal('prix', 10, 2);
            $table->unsignedInteger('quantite_disponible')->nullable();
            $table->unsignedInteger('quantite_vendue')->default(0);
            $table->enum('statut', ['actif', 'épuisé', 'désactivé'])->default('actif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifs');
    }
};