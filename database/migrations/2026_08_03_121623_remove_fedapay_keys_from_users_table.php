<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['fedapay_public_key', 'fedapay_secret_key', 'fedapay_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('fedapay_public_key')->nullable()->after('fedapay_active');
            $table->string('fedapay_secret_key')->nullable()->after('fedapay_public_key');
            $table->boolean('fedapay_active')->default(false)->after('fedapay_secret_key');
        });
    }
};
