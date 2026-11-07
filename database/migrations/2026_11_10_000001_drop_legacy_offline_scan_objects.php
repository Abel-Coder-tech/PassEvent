<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Colonnes ajoutées à `logs` par le mode de scan hors ligne. */
    private const LOG_COLUMNS = [
        'client_uuid',
        'scanned_at',
        'scan_date',
        'lease_id',
        'source',
        'server_resultat',
        'synced_at',
    ];

    /** Index ajoutés à `logs` par le mode de scan hors ligne. */
    private const LOG_INDEXES = [
        'logs_client_uuid_unique',
        'logs_scan_date_ticket_id_index',
        'logs_agent_id_scan_date_index',
    ];

    /** Colonnes ajoutées à `evenement` par le mode de scan hors ligne. */
    private const EVENEMENT_COLUMNS = [
        'offline_mode_enabled',
        'offline_unknown_policy',
        'offline_grace_minutes',
    ];

    /** Tables créées par le mode de scan hors ligne. */
    private const TABLES = [
        'offline_lease_events',
        'ticket_scan_days',
        'sync_changes',
        'offline_leases',
    ];

    /** Migrations du mode hors ligne supprimées du dépôt. */
    private const LEGACY_MIGRATIONS = [
        '2026_11_09_000001_create_offline_leases_table',
        '2026_11_09_000002_create_offline_lease_events_table',
        '2026_11_09_000003_add_offline_fields_to_logs_table',
        '2026_11_09_000004_create_ticket_scan_days_table',
        '2026_11_09_000005_create_sync_changes_table',
        '2026_11_09_000006_add_offline_fields_to_evenements_table',
    ];

    /**
     * Supprime les objets de base de données laissés par l'ancien mode de scan hors ligne.
     * Migration défensive : ne fait rien si ces objets n'existent pas.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            Schema::disableForeignKeyConstraints();
        }

        if (Schema::hasTable('logs')) {
            if ($driver === 'mysql' && Schema::hasColumn('logs', 'lease_id')) {
                try {
                    Schema::table('logs', fn (Blueprint $table) => $table->dropForeign(['lease_id']));
                } catch (\Throwable) {
                    // La contrainte a déjà été supprimée.
                }
            }

            foreach (self::LOG_INDEXES as $index) {
                try {
                    if (Schema::hasIndex('logs', $index)) {
                        Schema::table('logs', fn (Blueprint $table) => $table->dropIndex($index));
                    }
                } catch (\Throwable) {
                    // L'index a déjà été supprimé.
                }
            }

            $columns = array_values(array_filter(
                self::LOG_COLUMNS,
                fn (string $column) => Schema::hasColumn('logs', $column)
            ));

            if ($columns !== []) {
                Schema::table('logs', fn (Blueprint $table) => $table->dropColumn($columns));
            }
        }

        if (Schema::hasTable('evenement')) {
            $columns = array_values(array_filter(
                self::EVENEMENT_COLUMNS,
                fn (string $column) => Schema::hasColumn('evenement', $column)
            ));

            if ($columns !== []) {
                Schema::table('evenement', fn (Blueprint $table) => $table->dropColumn($columns));
            }
        }

        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }

        if ($driver === 'mysql') {
            Schema::enableForeignKeyConstraints();
        }

        DB::table('migrations')->whereIn('migration', self::LEGACY_MIGRATIONS)->delete();
    }

    /**
     * Le mode hors ligne a été définitivement retiré : aucune restauration automatique.
     */
    public function down(): void
    {
        // Irréversible.
    }
};
