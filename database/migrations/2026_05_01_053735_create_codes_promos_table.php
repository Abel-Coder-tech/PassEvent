<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('codes_promos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('evenement')->cascadeOnDelete();
            $table->foreignId('tarif_id')->constrained('tarifs')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->unsignedInteger('max_utilisations')->nullable();
            $table->unsignedInteger('nb_utilisations')->default(0);
            $table->dateTime('date_expiration')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codes_promos');
    }
};