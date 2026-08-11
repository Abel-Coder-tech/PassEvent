<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Lots de tickets physiques générés par le super admin et transmis aux organisateurs
    public function up(): void
    {
        Schema::create('lots_physiques', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('evenement_id');
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->string('nom', 100);
            $table->unsignedInteger('quantite')->default(0);
            $table->string('statut', 20)->default('genere');
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamp('transmis_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('evenement_id')->references('id')->on('evenement')->cascadeOnDelete();
            $table->foreign('tarif_id')->references('id')->on('tarifs')->nullOnDelete();
        });

        Schema::table('ticket', function (Blueprint $table) {
            $table->unsignedBigInteger('lot_physique_id')->nullable()->after('agent_vente_id');
            $table->boolean('annule')->default(false)->after('lot_physique_id');

            $table->foreign('lot_physique_id')->references('id')->on('lots_physiques')->nullOnDelete();
            $table->index('lot_physique_id');
        });
    }

    public function down(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->dropForeign(['lot_physique_id']);
            $table->dropIndex(['lot_physique_id']);
            $table->dropColumn(['lot_physique_id', 'annule']);
        });

        Schema::dropIfExists('lots_physiques');
    }
};
