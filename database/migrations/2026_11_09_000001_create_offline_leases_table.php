<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('offline_leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('evenement')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->enum('statut', ['autorise', 'arme', 'activation_en_attente', 'actif', 'revocation_en_attente', 'revoque', 'expire'])->default('autorise');
            $table->unsignedBigInteger('active_slot')->nullable();
            $table->unsignedBigInteger('snapshot_version')->nullable();
            $table->string('snapshot_checksum')->nullable();
            $table->timestamp('authorized_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revocation_requested_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('revocation_acked_at')->nullable();
            $table->timestamp('force_revoked_at')->nullable();
            $table->foreignId('authorized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            $table->unique('active_slot');
            $table->index(['evenement_id', 'statut']);
            $table->index('agent_id');
        });
    }
    public function down(): void { Schema::dropIfExists('offline_leases'); }
};
