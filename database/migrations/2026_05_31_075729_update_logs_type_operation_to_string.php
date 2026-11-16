<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE logs MODIFY COLUMN type_operation VARCHAR(50) NOT NULL");
        } else {
            // SQLite (tests) : reconstruit la colonne pour retirer la contrainte de l'ancien enum.
            Schema::table('logs', fn (Blueprint $table) => $table->string('type_operation', 50)->change());
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE logs MODIFY COLUMN type_operation ENUM('achat','scan','remboursement','envoi_notification','envoi','erreur_paiement','echec_paiement','vente_manuelle') NOT NULL");
        }
    }
};
