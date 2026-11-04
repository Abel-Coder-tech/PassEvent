<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('sync_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('evenement')->cascadeOnDelete();
            $table->enum('entity_type', ['ticket', 'session', 'event', 'lease']);
            $table->unsignedBigInteger('entity_id');
            $table->string('action');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['evenement_id', 'id']);
        });
    }
    public function down(): void { Schema::dropIfExists('sync_changes'); }
};
