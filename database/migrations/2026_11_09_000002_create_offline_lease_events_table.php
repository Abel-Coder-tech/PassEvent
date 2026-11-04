<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('offline_lease_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offline_lease_id')->constrained('offline_leases')->cascadeOnDelete();
            $table->string('action');
            $table->string('actor_type')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['offline_lease_id', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('offline_lease_events'); }
};
