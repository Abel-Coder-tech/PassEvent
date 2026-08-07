<?php

use App\Models\Ticket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Réécrit les codes existants au nouveau format PAX-XXXXX (5 caractères aléatoires)
    public function up(): void
    {
        DB::table('ticket')
            ->where('code_unique', '!=', 'TMP')
            ->orderBy('id')
            ->chunkById(200, function ($tickets) {
                foreach ($tickets as $ticket) {
                    DB::table('ticket')
                        ->where('id', $ticket->id)
                        ->update(['code_unique' => Ticket::genererCodeSecurise()]);
                }
            });
    }

    // Irréversible : les anciens codes ne sont pas conservés.
    public function down(): void
    {
        //
    }
};
