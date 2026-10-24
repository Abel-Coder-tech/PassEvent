<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Compteur de clics sur le lien d'inscription organisateur d'un affilie
// (mecanisme A). Un clic est compte une seule fois par session.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->unsignedInteger('clics_parrainage')->default(0)->after('code_parrainage');
        });
    }

    public function down(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->dropColumn('clics_parrainage');
        });
    }
};
