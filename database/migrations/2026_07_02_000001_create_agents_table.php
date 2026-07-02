<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('evenement_id')->constrained('evenement')->onDelete('cascade');
            $table->string('code_acces', 6);
            $table->boolean('actif')->default(true);
            $table->unsignedTinyInteger('tentatives_code')->default(0);
            $table->timestamp('bloque_jusqua')->nullable();
            $table->timestamp('dernier_acces')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
