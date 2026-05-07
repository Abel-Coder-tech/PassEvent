<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->string('telephone_paiement')->nullable()->after('telephone_acheteur');
        });

        DB::statement("ALTER TABLE ticket MODIFY COLUMN methode_paiement ENUM('mtn', 'moov', 'celtiis', 'movimoney', 'especes') NULL");
    }

    public function down(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->dropColumn('telephone_paiement');
        });

        DB::statement("ALTER TABLE ticket MODIFY COLUMN methode_paiement ENUM('mtn', 'moov', 'celtiis') NULL");
    }
};
