<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Index pour le scan : « ce billet a-t-il déjà été scanné ? » et les compteurs
// d'un agent restent rapides quand la table des journaux grossit.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->index(['ticket_id', 'type_operation', 'created_at'], 'logs_ticket_type_date_index');
            $table->index(['agent_id', 'type_operation', 'created_at'], 'logs_agent_type_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropIndex('logs_ticket_type_date_index');
            $table->dropIndex('logs_agent_type_date_index');
        });
    }
};
