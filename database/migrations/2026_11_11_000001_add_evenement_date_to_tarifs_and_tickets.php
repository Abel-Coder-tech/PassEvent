<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rattache un tarif à un jour précis de l'événement.
     * `null` conserve le comportement historique : valable toutes les dates
     * (c'est aussi le cas des packs). Le billet reçoit une copie figée du jour
     * au moment de la vente pour que modifier un tarif ne change pas les
     * billets déjà émis.
     */
    public function up(): void
    {
        Schema::table('tarifs', function (Blueprint $table) {
            $table->foreignId('evenement_date_id')
                ->nullable()
                ->after('evenement_id')
                ->constrained('evenement_dates')
                ->nullOnDelete();
        });

        Schema::table('ticket', function (Blueprint $table) {
            $table->foreignId('evenement_date_id')
                ->nullable()
                ->after('evenement_id')
                ->constrained('evenement_dates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->dropConstrainedForeignId('evenement_date_id');
        });

        Schema::table('tarifs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('evenement_date_id');
        });
    }
};
