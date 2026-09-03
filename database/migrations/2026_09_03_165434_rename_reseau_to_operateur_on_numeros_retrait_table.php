<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('numeros_retrait', function (Blueprint $table) {
            $table->renameColumn('reseau', 'operateur');
        });
    }

    public function down(): void
    {
        Schema::table('numeros_retrait', function (Blueprint $table) {
            $table->renameColumn('operateur', 'reseau');
        });
    }
};
