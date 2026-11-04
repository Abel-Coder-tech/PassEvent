<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ticket_scan_days')) {
            // Ensure columns exist
            return;
        }

        Schema::create('ticket_scan_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->date('scan_date');
            $table->unsignedBigInteger('log_id');
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->timestamps();

            $table->unique(['ticket_id', 'scan_date']);
            $table->index('scan_date');
        });

        Schema::table('ticket_scan_days', function (Blueprint $table) {
            if (Schema::hasTable('tickets')) {
                $table->foreign('ticket_id')->references('id')->on('tickets')->cascadeOnDelete();
            }
            if (Schema::hasTable('logs')) {
                $table->foreign('log_id')->references('id')->on('logs')->cascadeOnDelete();
            }
            if (Schema::hasTable('agents')) {
                $table->foreign('agent_id')->references('id')->on('agents')->nullOnDelete();
            }
        });

        try {
            $logs = DB::table('logs')
                ->where('type_operation', 'scan')
                ->where('resultat', 'valide')
                ->whereNotNull('ticket_id')
                ->orderBy('created_at')
                ->get();

            $seen = [];
            foreach ($logs as $log) {
                $date = $log->scan_date ?: ($log->created_at ? date('Y-m-d', strtotime($log->created_at)) : null);
                if (! $date) {
                    continue;
                }
                $key = $log->ticket_id.'|'.$date;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;

                if (! $log->scanned_at) {
                    try {
                        DB::table('logs')->where('id', $log->id)->update([
                            'scanned_at' => $log->created_at,
                            'scan_date' => $date,
                            'source' => $log->source ?: 'online',
                        ]);
                    } catch (\Throwable $e) {
                    }
                } elseif (! $log->scan_date) {
                    try {
                        DB::table('logs')->where('id', $log->id)->update(['scan_date' => $date]);
                    } catch (\Throwable $e) {
                    }
                }

                try {
                    DB::table('ticket_scan_days')->insert([
                        'ticket_id' => $log->ticket_id,
                        'scan_date' => $date,
                        'log_id' => $log->id,
                        'agent_id' => $log->agent_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                }
            }
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_scan_days');
    }
};
