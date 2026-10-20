<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Rattache un billet vendu au lien de co-parrainage utilise (mecanisme B).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->foreignId('affiliate_event_link_id')->nullable()->after('agent_vente_id')
                ->constrained('affiliate_event_links')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->dropForeign(['affiliate_event_link_id']);
            $table->dropColumn('affiliate_event_link_id');
        });
    }
};
