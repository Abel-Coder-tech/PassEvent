<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Commission % du lot physique définie par le super admin lors de la génération
    public function up(): void
    {
        Schema::table('lots_physiques', function (Blueprint $table) {
            $table->decimal('commission_pourcentage', 5, 2)->nullable()->after('tarif_id');
        });
    }

    public function down(): void
    {
        Schema::table('lots_physiques', function (Blueprint $table) {
            $table->dropColumn('commission_pourcentage');
        });
    }
};