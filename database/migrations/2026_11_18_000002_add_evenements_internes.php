<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Événements créés par le super admin hors billetterie en ligne : masqués du public,
    // rattachés à un compte organisateur interne, avec l'auteur saisi librement.
    public function up(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->boolean('interne')->default(false)->index()->after('statut');
            $table->string('auteur_nom', 100)->nullable()->after('interne');
            $table->string('auteur_prenom', 100)->nullable()->after('auteur_nom');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('interne')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->dropColumn(['interne', 'auteur_nom', 'auteur_prenom']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('interne');
        });
    }
};
