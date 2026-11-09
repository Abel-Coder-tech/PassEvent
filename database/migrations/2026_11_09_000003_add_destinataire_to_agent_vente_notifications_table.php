<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_vente_notifications', function (Blueprint $table) {
            $table->string('type', 50)->default('info')->after('message');
            $table->string('destinataire_type', 30)->default('agent')->after('type');
            $table->json('data')->nullable()->after('destinataire_type');
        });
    }

    public function down(): void
    {
        Schema::table('agent_vente_notifications', function (Blueprint $table) {
            $table->dropColumn(['type', 'destinataire_type', 'data']);
        });
    }
};
