<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Le co-parrainage est désormais interdit sur les événements entièrement gratuits.
     * On désactive l'option pour les événements gratuits existants.
     */
    public function up(): void
    {
        DB::table('evenement')
            ->where('gratuit', true)
            ->where('co_parrainage_actif', true)
            ->update([
                'co_parrainage_actif' => false,
                'co_parrainage_pourcentage' => null,
            ]);
    }

    public function down(): void
    {
        // Aucune restauration : la règle interdit le co-parrainage sur les événements gratuits.
    }
};
