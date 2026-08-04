<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('telephone')->nullable()->after('email');
            $table->string('email_achat')->nullable()->after('telephone');
            $table->string('transaction_id')->nullable()->after('objet');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['telephone', 'email_achat', 'transaction_id']);
        });
    }
};
