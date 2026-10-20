<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Ouvre un evenement au co-parrainage (mecanisme B) et fixe la commission offerte.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->boolean('co_parrainage_actif')->default(false)->after('commission_pourcentage');
            $table->decimal('co_parrainage_pourcentage', 5, 2)->nullable()->after('co_parrainage_actif');
        });
    }

    public function down(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->dropColumn(['co_parrainage_actif', 'co_parrainage_pourcentage']);
        });
    }
};
