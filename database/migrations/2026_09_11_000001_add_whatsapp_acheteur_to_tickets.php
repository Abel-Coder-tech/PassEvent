<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->string('whatsapp_acheteur')->nullable()->after('telephone_paiement');
        });

        Schema::table('event_waitlist', function (Blueprint $table) {
            $table->string('whatsapp_acheteur')->nullable()->after('telephone_acheteur');
        });
    }

    public function down(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->dropColumn('whatsapp_acheteur');
        });

        Schema::table('event_waitlist', function (Blueprint $table) {
            $table->dropColumn('whatsapp_acheteur');
        });
    }
};