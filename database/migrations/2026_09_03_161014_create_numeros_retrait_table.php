<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('numeros_retrait', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('operateur'); // mtn, moov, celtiis
            $table->string('nom'); // nom complet du beneficiare
            $table->string('mobile');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numeros_retrait');
    }
};
