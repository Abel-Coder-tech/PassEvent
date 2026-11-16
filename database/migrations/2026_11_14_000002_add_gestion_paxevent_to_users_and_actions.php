<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Comptes organisateurs créés ou finalisés par PaxEvent : contrat figé
    // (copie du PDF au moment de la finalisation), suivi de sa transmission,
    // et journal des actions réalisées par PaxEvent au nom d'un organisateur.
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('cree_par_paxevent')->default(false);
            $table->timestamp('contrat_genere_le')->nullable();
            $table->string('contrat_chemin')->nullable();
            $table->timestamp('contrat_transmis_le')->nullable();
            $table->string('contrat_transmis_canal', 30)->nullable();
            $table->string('contrat_transmis_par')->nullable();
            $table->string('contrat_signe_chemin')->nullable();
        });

        Schema::create('actions_paxevent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('admin_email');
            $table->string('methode', 10);
            $table->string('route')->nullable();
            $table->string('resume', 255);
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actions_paxevent');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'cree_par_paxevent', 'contrat_genere_le', 'contrat_chemin', 'contrat_transmis_le',
                'contrat_transmis_canal', 'contrat_transmis_par', 'contrat_signe_chemin',
            ]);
        });
    }
};
