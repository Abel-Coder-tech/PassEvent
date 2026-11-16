<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Autorisation donnée par l'organisateur à PaxEvent d'agir à sa place (mandat).
    // mandat_mode : organisateur | creation_paxevent | paxevent_recueilli.
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('mandat_actif')->default(false);
            $table->timestamp('mandat_accorde_le')->nullable();
            $table->string('mandat_mode', 30)->nullable();
            $table->string('mandat_pdf_chemin')->nullable();
            $table->string('mandat_justificatif_chemin')->nullable();
            $table->string('mandat_enregistre_par')->nullable();
            $table->timestamp('mandat_retire_le')->nullable();
            $table->string('mandat_retire_par')->nullable();
        });

        // Comptes déjà créés par PaxEvent : l'autorisation est réputée accordée dès la création.
        DB::table('users')->where('cree_par_paxevent', true)->update([
            'mandat_actif' => true,
            'mandat_accorde_le' => now(),
            'mandat_mode' => 'creation_paxevent',
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mandat_actif', 'mandat_accorde_le', 'mandat_mode', 'mandat_pdf_chemin',
                'mandat_justificatif_chemin', 'mandat_enregistre_par', 'mandat_retire_le', 'mandat_retire_par',
            ]);
        });
    }
};
