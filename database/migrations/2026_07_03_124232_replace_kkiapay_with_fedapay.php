<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['kkiapay_public_key', 'kkiapay_secret_key', 'kkiapay_api_key', 'kkiapay_active']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('fedapay_public_key')->nullable()->after('notif_scan');
            $table->string('fedapay_secret_key')->nullable()->after('fedapay_public_key');
            $table->boolean('fedapay_active')->default(false)->after('fedapay_secret_key');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['fedapay_public_key', 'fedapay_secret_key', 'fedapay_active']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('kkiapay_public_key')->nullable()->after('notif_scan');
            $table->string('kkiapay_secret_key')->nullable()->after('kkiapay_public_key');
            $table->string('kkiapay_api_key')->nullable()->after('kkiapay_secret_key');
            $table->boolean('kkiapay_active')->default(false)->after('kkiapay_api_key');
        });
    }
};
