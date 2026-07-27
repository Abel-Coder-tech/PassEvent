<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ajouter colonne nom sur tarifs
        Schema::table('tarifs', function (Blueprint $table) {
            $table->string('nom')->after('evenement_id');
        });

        // 2. Migrer les données : categorie+type → nom
        DB::statement("UPDATE tarifs SET nom = CASE
            WHEN categorie = 'etudiant' AND type = 'vip' THEN 'Étudiant VIP'
            WHEN categorie = 'etudiant' THEN 'Étudiant'
            WHEN type = 'vip' THEN 'VIP'
            ELSE 'Standard'
        END");

        // 3. Supprimer les anciennes colonnes
        Schema::table('tarifs', function (Blueprint $table) {
            $table->dropColumn(['categorie', 'type']);
        });

        // 4. Ajouter colonne nom_tarif sur ticket
        Schema::table('ticket', function (Blueprint $table) {
            $table->string('nom_tarif')->nullable()->after('tarif_id');
        });

        // 5. Migrer les données ticket
        DB::statement("UPDATE ticket SET nom_tarif = CASE
            WHEN categorie = 'etudiant' AND type = 'vip' THEN 'Étudiant VIP'
            WHEN categorie = 'etudiant' THEN 'Étudiant'
            WHEN type = 'vip' THEN 'VIP'
            ELSE 'Standard'
        END");

        // 6. Supprimer les anciennes colonnes ticket
        Schema::table('ticket', function (Blueprint $table) {
            $table->dropColumn(['categorie', 'type']);
        });
    }

    public function down(): void
    {
        // Rollback ticket
        Schema::table('ticket', function (Blueprint $table) {
            $table->enum('categorie', ['etudiant', 'externe'])->default('externe')->after('tarif_id');
            $table->enum('type', ['normal', 'vip'])->default('normal')->after('categorie');
        });

        DB::statement("UPDATE ticket SET
            categorie = CASE WHEN nom_tarif LIKE '%tudiant%' THEN 'etudiant' ELSE 'externe' END,
            type = CASE WHEN nom_tarif LIKE '%VIP%' THEN 'vip' ELSE 'normal' END
        ");

        Schema::table('ticket', function (Blueprint $table) {
            $table->dropColumn('nom_tarif');
        });

        // Rollback tarifs
        Schema::table('tarifs', function (Blueprint $table) {
            $table->enum('categorie', ['etudiant', 'externe'])->default('externe')->after('evenement_id');
            $table->enum('type', ['normal', 'vip'])->default('normal')->after('categorie');
        });

        DB::statement("UPDATE tarifs SET
            categorie = CASE WHEN nom LIKE '%tudiant%' THEN 'etudiant' ELSE 'externe' END,
            type = CASE WHEN nom LIKE '%VIP%' THEN 'vip' ELSE 'normal' END
        ");

        Schema::table('tarifs', function (Blueprint $table) {
            $table->dropColumn('nom');
        });
    }
};
