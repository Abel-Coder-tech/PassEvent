<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // « Clôturer les ventes » (ventes_fermees) disparaît : fermer la billetterie fait
    // la même chose. Les événements déjà clôturés restent fermés. La colonne
    // ventes_fermees est conservée en base mais n'est plus utilisée.
    public function up(): void
    {
        DB::table('evenement')
            ->where('ventes_fermees', true)
            ->update(['billetterie_ouverte' => false, 'billetterie_ouvre_le' => null]);
    }

    public function down(): void
    {
        // Irréversible sans perte : on ne sait plus quels événements étaient clôturés.
    }
};
