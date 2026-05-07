<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evenement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->dateTime('date_event');
            $table->string('lieu');
            $table->unsignedInteger('capacite');
            $table->unsignedInteger('quota_vendu')->default(0);
            $table->dateTime('date_fin_vente')->nullable();
            $table->string('image')->nullable();
            $table->enum('statut', ['brouillon', 'publié', 'terminé', 'annulé'])->default('brouillon');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evenement');
    }
};