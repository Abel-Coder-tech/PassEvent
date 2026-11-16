<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MODIFY COLUMN n'existe qu'en MySQL/MariaDB ; la colonne passe en texte plus tard (2026_05_31).
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE logs MODIFY COLUMN type_operation ENUM('achat', 'scan', 'remboursement', 'envoi_notification', 'envoi', 'erreur_paiement', 'echec_paiement', 'vente_manuelle') NOT NULL");
        }
    }

    public function down(): void
    {
        // MODIFY COLUMN n'existe qu'en MySQL/MariaDB ; la colonne passe en texte plus tard (2026_05_31).
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE logs MODIFY COLUMN type_operation ENUM('achat', 'scan', 'remboursement', 'envoi_notification', 'erreur_paiement', 'vente_manuelle') NOT NULL");
        }
    }
};
