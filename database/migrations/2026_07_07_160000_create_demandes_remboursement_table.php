<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_remboursement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisateur_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('evenement_id')->nullable()->constrained('evenement')->onDelete('cascade');
            $table->string('type'); // individuel / groupe
            $table->decimal('montant_total', 10, 2)->default(0);
            $table->text('motif');
            $table->text('notes_admin')->nullable();
            $table->string('statut')->default('en_attente'); // en_attente / en_cours / rembourse / refuse
            $table->foreignId('traitee_par')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('traitee_le')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_remboursement');
    }
};
