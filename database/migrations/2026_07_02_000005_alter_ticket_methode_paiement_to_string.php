<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MODIFY COLUMN n'existe qu'en MySQL/MariaDB (SQLite des tests : rien à faire).
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE ticket MODIFY COLUMN methode_paiement VARCHAR(20) NULL");
        } else {
            // SQLite (tests) : reconstruit la colonne pour retirer la contrainte de l'ancien enum.
            Schema::table('ticket', fn (Blueprint $table) => $table->string('methode_paiement', 20)->nullable()->change());
        }
    }

    public function down(): void
    {
        // MODIFY COLUMN n'existe qu'en MySQL/MariaDB (SQLite des tests : rien à faire).
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE ticket MODIFY COLUMN methode_paiement ENUM('mtn','moov','celtiis') NULL");
        }
    }
};
