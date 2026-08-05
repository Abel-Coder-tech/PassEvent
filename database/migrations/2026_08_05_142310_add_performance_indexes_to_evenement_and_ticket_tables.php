<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->index(['statut', 'date_event'], 'evenement_statut_date_event_index');
        });

        Schema::table('ticket', function (Blueprint $table) {
            $table->index('statut_paiement', 'ticket_statut_paiement_index');
            $table->index(['evenement_id', 'statut_paiement'], 'ticket_evenement_statut_index');
        });
    }

    public function down(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->dropIndex('evenement_statut_date_event_index');
        });

        Schema::table('ticket', function (Blueprint $table) {
            $table->dropIndex('ticket_statut_paiement_index');
            $table->dropIndex('ticket_evenement_statut_index');
        });
    }
};
