<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('organisation')->nullable()->after('nom');
            $table->text('description')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('description');
            $table->boolean('notif_email_evenement')->default(true)->after('avatar');
            $table->boolean('notif_email_ticket')->default(true)->after('notif_email_evenement');
            $table->boolean('notif_email_paiement')->default(true)->after('notif_email_ticket');
            $table->boolean('notif_scan')->default(true)->after('notif_email_paiement');
            $table->string('kkiapay_public_key')->nullable()->after('notif_scan');
            $table->string('kkiapay_secret_key')->nullable()->after('kkiapay_public_key');
            $table->string('kkiapay_api_key')->nullable()->after('kkiapay_secret_key');
            $table->boolean('kkiapay_active')->default(false)->after('kkiapay_api_key');
            $table->string('code_acces_scan')->nullable()->after('kkiapay_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'organisation',
                'description',
                'avatar',
                'notif_email_evenement',
                'notif_email_ticket',
                'notif_email_paiement',
                'notif_scan',
                'kkiapay_public_key',
                'kkiapay_secret_key',
                'kkiapay_api_key',
                'kkiapay_active',
                'code_acces_scan',
            ]);
        });
    }
};
