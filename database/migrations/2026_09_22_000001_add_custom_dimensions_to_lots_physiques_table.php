<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lots_physiques', function (Blueprint $table) {
            $table->unsignedSmallInteger('largeur_personnalisee')->nullable()->after('format');
            $table->unsignedSmallInteger('hauteur_personnalisee')->nullable()->after('largeur_personnalisee');
        });
    }

    public function down(): void
    {
        Schema::table('lots_physiques', function (Blueprint $table) {
            $table->dropColumn(['largeur_personnalisee', 'hauteur_personnalisee']);
        });
    }
};