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
        Schema::table('agents_vente', function (Blueprint $table) {
            $table->dropColumn('code_vente');
        });
    }

    public function down(): void
    {
        Schema::table('agents_vente', function (Blueprint $table) {
            $table->string('code_vente', 6)->after('evenement_id');
        });
    }
};
