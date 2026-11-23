<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Suppression réversible des comptes : le compte part dans une corbeille
    // (deleted_at) avec toutes ses données, et peut être restauré tel quel.
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('statut_avant_suppression', 30)->nullable()->after('statut');
            $table->string('supprime_par')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['statut_avant_suppression', 'supprime_par']);
        });
    }
};
