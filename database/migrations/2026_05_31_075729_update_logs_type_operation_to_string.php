<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE logs MODIFY COLUMN type_operation VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE logs MODIFY COLUMN type_operation ENUM('achat','scan','remboursement','envoi_notification','envoi','erreur_paiement','echec_paiement','vente_manuelle') NOT NULL");
    }
};
