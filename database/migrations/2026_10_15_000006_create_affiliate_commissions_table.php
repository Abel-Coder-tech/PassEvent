<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Commissions d'affiliation rattachees a un billet vendu (mecanismes A et B).
// La maturation a lieu a la fin de l'evenement concerne.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->foreignId('evenement_id')->constrained('evenement')->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained('ticket')->cascadeOnDelete();
            $table->foreignId('affiliate_event_link_id')->nullable()->constrained('affiliate_event_links')->nullOnDelete();
            $table->enum('type', ['parrainage_direct', 'co_parrainage']);
            $table->decimal('base_montant', 12, 2)->default(0);
            $table->decimal('pourcentage', 5, 2)->default(0);
            $table->decimal('montant', 12, 2)->default(0);
            $table->enum('statut', ['en_attente', 'disponible', 'paye', 'annule'])->default('en_attente');
            $table->timestamp('disponible_le')->nullable();
            $table->timestamp('paye_le')->nullable();
            $table->foreignId('affiliate_payout_id')->nullable()->constrained('affiliate_payouts')->nullOnDelete();
            $table->timestamps();

            // Un billet genere au plus une commission de chaque type
            $table->unique(['ticket_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
    }
};
