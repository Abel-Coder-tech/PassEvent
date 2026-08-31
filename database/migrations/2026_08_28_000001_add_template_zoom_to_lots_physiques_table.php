<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lots_physiques', function (Blueprint $table) {
            $table->unsignedTinyInteger('template_zoom')->default(100)->after('template_path');
        });
    }

    public function down(): void
    {
        Schema::table('lots_physiques', function (Blueprint $table) {
            $table->dropColumn('template_zoom');
        });
    }
};