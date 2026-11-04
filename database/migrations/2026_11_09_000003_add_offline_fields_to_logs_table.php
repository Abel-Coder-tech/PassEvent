<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('logs', function (Blueprint $table) {
            $table->uuid('client_uuid')->nullable()->unique()->after('ticket_id');
            $table->timestamp('scanned_at')->nullable()->after('client_uuid');
            $table->date('scan_date')->nullable()->after('scanned_at');
            $table->foreignId('lease_id')->nullable()->after('scan_date')->constrained('offline_leases')->nullOnDelete();
            $table->enum('source', ['online', 'offline_sync', 'web', 'access_code'])->default('online')->after('lease_id');
            $table->string('server_resultat')->nullable()->after('source');
            $table->timestamp('synced_at')->nullable()->after('server_resultat');
            $table->index(['scan_date', 'ticket_id']);
            $table->index(['agent_id', 'scan_date']);
        });
    }
    public function down(): void {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropUnique(['client_uuid']);
            $table->dropForeign(['lease_id']);
            $table->dropColumn(['client_uuid', 'scanned_at', 'scan_date', 'lease_id', 'source', 'server_resultat', 'synced_at']);
        });
    }
};
