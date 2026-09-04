<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Date limite de réservation : si le ticket reste en_attente au-delà,
        // la place réservée est libérée et remise en vente / proposée à la file d'attente.
        Schema::table('ticket', function (Blueprint $table) {
            $table->dateTime('reservation_expire_le')->nullable()->after('transaction_id');
            $table->index('reservation_expire_le', 'ticket_reservation_expire_le_index');
        });

        // File d'attente : utilisateurs refusés (capacité pleine) en attente d'une place.
        Schema::create('event_waitlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('evenement')->cascadeOnDelete();
            $table->foreignId('tarif_id')->nullable()->constrained('tarifs')->nullOnDelete();
            $table->string('nom_acheteur')->nullable();
            $table->string('email_acheteur');
            $table->string('telephone_acheteur')->nullable();
            $table->integer('quantite')->default(1);
            $table->string('code_promo_utilise')->nullable();
            $table->unsignedInteger('montant_unitaire')->default(0);
            $table->unsignedInteger('montant_reduction')->default(0);
            $table->enum('statut', ['en_attente', 'place_offerte', 'expire', 'annule'])->default('en_attente');
            $table->dateTime('place_offerte_le')->nullable();
            $table->dateTime('expire_le')->nullable();
            $table->timestamps();

            $table->index(['evenement_id', 'statut'], 'waitlist_evenement_statut_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_waitlist');
        Schema::table('ticket', function (Blueprint $table) {
            $table->dropIndex('ticket_reservation_expire_le_index');
            $table->dropColumn('reservation_expire_le');
        });
    }
};
