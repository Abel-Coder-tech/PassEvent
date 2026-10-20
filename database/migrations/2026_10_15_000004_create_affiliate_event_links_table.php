<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Mecanisme B : lien de co-parrainage d'un affilie pour un evenement ouvert.
// Le pourcentage est fige au moment de la generation du lien.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_event_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('evenement')->cascadeOnDelete();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->string('code', 32)->unique();
            $table->decimal('pourcentage', 5, 2)->default(0);
            $table->boolean('actif')->default(true);
            $table->unsignedInteger('clics')->default(0);
            $table->timestamps();

            $table->unique(['evenement_id', 'affiliate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_event_links');
    }
};
