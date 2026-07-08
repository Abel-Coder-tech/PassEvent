<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_remboursement_ticket', function (Blueprint $table) {
            $table->foreignId('demande_remboursement_id')->constrained('demandes_remboursement')->onDelete('cascade');
            $table->foreignId('ticket_id')->constrained('ticket')->onDelete('cascade');
            $table->primary(['demande_remboursement_id', 'ticket_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_remboursement_ticket');
    }
};
