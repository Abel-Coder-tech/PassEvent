<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->string('categorie')->default('General')->after('lieu');
        });
    }

    public function down(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->dropColumn('categorie');
        });
    }
};
