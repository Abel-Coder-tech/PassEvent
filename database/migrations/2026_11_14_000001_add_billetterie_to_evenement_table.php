<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Billetterie pas encore ouverte : l'événement est publié à l'avance mais
    // les ventes n'ont pas démarré. Distinct de ventes_fermees (clôture).
    // billetterie_ouvre_le : ouverture automatique à cette date (facultative).
    public function up(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->boolean('billetterie_ouverte')->default(true)->after('ventes_fermees');
            $table->dateTime('billetterie_ouvre_le')->nullable()->after('billetterie_ouverte');
        });
    }

    public function down(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->dropColumn(['billetterie_ouverte', 'billetterie_ouvre_le']);
        });
    }
};
