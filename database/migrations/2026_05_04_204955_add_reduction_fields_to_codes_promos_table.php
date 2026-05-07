<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('codes_promos', function (Blueprint $table) {
            $table->enum('type_reduction', ['pourcentage', 'fixe'])->default('pourcentage')->after('code');
            $table->decimal('valeur_reduction', 10, 2)->default(0)->after('type_reduction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('codes_promos', function (Blueprint $table) {
            $table->dropColumn(['type_reduction', 'valeur_reduction']);
        });
    }
};
