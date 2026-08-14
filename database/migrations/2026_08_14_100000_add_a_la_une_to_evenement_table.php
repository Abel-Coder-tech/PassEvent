<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->boolean('a_la_une')->default(false)->after('max_agents_vente');
            $table->unsignedInteger('a_la_une_ordre')->default(0)->after('a_la_une');
        });
    }

    public function down(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->dropColumn(['a_la_une', 'a_la_une_ordre']);
        });
    }
};
