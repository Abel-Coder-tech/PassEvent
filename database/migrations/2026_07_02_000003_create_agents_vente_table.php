<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents_vente', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('evenement_id')->constrained('evenement')->cascadeOnDelete();
            $table->string('code_vente', 6);
            $table->boolean('actif')->default(true);
            $table->unsignedInteger('tickets_count')->default(0);
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->timestamp('dernier_acces')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents_vente');
    }
};
