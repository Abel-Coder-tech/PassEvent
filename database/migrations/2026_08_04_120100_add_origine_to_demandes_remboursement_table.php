<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes_remboursement', function (Blueprint $table) {
            $table->string('origine')->default('organisateur')->after('evenement_id');
            $table->foreignId('organisateur_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('demandes_remboursement', function (Blueprint $table) {
            $table->dropColumn('origine');
        });
    }
};
