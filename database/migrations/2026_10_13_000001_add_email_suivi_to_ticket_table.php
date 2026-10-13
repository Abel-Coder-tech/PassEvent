<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->timestamp('email_envoye_at')->nullable()->after('date_achat');
            $table->unsignedTinyInteger('email_tentatives')->default(0)->after('email_envoye_at');
            $table->text('email_derniere_erreur')->nullable()->after('email_tentatives');
        });

        // Backfill : les billets déjà existants sont considérés comme déjà notifiés,
        // sinon le rattrapage tickets:envoyer-emails renverrait tout l'historique.
        DB::table('ticket')->whereNull('email_envoye_at')->update([
            'email_envoye_at' => DB::raw('COALESCE(date_achat, NOW())'),
        ]);
    }

    public function down(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->dropColumn(['email_envoye_at', 'email_tentatives', 'email_derniere_erreur']);
        });
    }
};
