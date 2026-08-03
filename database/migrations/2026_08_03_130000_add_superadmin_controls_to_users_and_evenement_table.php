<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('ventes_especes', ['toujours', 'jamais'])->nullable()->after('statut');
            $table->decimal('commission_pourcentage', 5, 2)->nullable()->after('ventes_especes');
        });

        Schema::table('evenement', function (Blueprint $table) {
            $table->enum('ventes_especes', ['toujours', 'jamais'])->nullable()->after('gratuit');
            $table->decimal('commission_pourcentage', 5, 2)->nullable()->after('ventes_especes');
        });
    }

    public function down(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->dropColumn(['ventes_especes', 'commission_pourcentage']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ventes_especes', 'commission_pourcentage']);
        });
    }
};
