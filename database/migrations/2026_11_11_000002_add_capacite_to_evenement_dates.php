<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jauge propre à chaque jour d'un événement multijours.
     * `null` = repli sur la capacité globale de l'événement.
     * L'occupation n'est pas stockée : elle est calculée à partir des billets
     * actifs, ce qui évite tout désynchronisation des compteurs.
     */
    public function up(): void
    {
        Schema::table('evenement_dates', function (Blueprint $table) {
            $table->unsignedInteger('capacite')->nullable()->after('ordre');
        });
    }

    public function down(): void
    {
        Schema::table('evenement_dates', function (Blueprint $table) {
            $table->dropColumn('capacite');
        });
    }
};
