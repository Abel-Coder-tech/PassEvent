<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Un lot généré par PaxEvent (super admin) est considéré comme payé : les
// lots encore « généré — à préparer » passent en « payé ». Les lots QR hors
// plateforme (externe) et les commandes organisateur (auto_genere) ne sont pas concernés.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('lots_physiques')
            ->where('statut', 'genere')
            ->where('auto_genere', false)
            ->where('externe', false)
            ->update(['statut' => 'paye']);
    }

    public function down(): void
    {
        // Irréversible sans perte : impossible de distinguer après coup les lots convertis.
    }
};
