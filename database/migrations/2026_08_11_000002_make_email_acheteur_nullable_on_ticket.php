<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Les tickets physiques n'ont pas d'email acheteur : on rend la colonne nullable
    public function up(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->string('email_acheteur')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->string('email_acheteur')->nullable(false)->change();
        });
    }
};
