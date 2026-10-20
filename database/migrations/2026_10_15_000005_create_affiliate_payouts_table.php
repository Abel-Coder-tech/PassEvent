<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Demandes de reversement des commissions affilie (PAYOUT FedaPay).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->decimal('montant', 12, 2);
            $table->string('operateur')->nullable();
            $table->string('numero_paiement')->nullable();
            $table->string('titulaire')->nullable();
            $table->enum('statut', ['demande', 'en_cours', 'paye', 'refuse'])->default('demande');
            $table->text('note')->nullable();
            $table->timestamp('traite_le')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_payouts');
    }
};
