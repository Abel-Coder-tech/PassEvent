<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->boolean('ventes_fermees')->default(false)->after('statut');
        });
    }

    public function down(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->dropColumn('ventes_fermees');
        });
    }
};
