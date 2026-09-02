<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'numero_piece')) {
                $table->dropColumn('numero_piece');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('numero_rc')->nullable()->after('signature');
            $table->string('numero_cip')->nullable()->after('numero_rc');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['numero_rc', 'numero_cip']);
            $table->string('numero_piece')->nullable()->after('signature');
        });
    }
};