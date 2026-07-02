<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->foreignId('agent_vente_id')->nullable()->after('tarif_id')->constrained('agents_vente')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->dropForeign(['agent_vente_id']);
            $table->dropColumn('agent_vente_id');
        });
    }
};
