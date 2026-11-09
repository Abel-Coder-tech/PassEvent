<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->string('idempotency_key', 100)->nullable()->after('transaction_id');
            $table->timestamp('annule_at')->nullable()->after('annule');
            $table->timestamp('expire_at')->nullable()->after('annule_at');
            $table->timestamp('dernier_renvoi_at')->nullable()->after('expire_at');
            $table->unsignedInteger('renvoi_count')->default(0)->after('dernier_renvoi_at');

            $table->index(['agent_vente_id', 'idempotency_key'], 'ticket_agent_idempotency_index');
        });
    }

    public function down(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->dropIndex('ticket_agent_idempotency_index');
            $table->dropColumn([
                'idempotency_key',
                'annule_at',
                'expire_at',
                'dernier_renvoi_at',
                'renvoi_count',
            ]);
        });
    }
};
