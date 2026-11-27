<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Une seule session active par compte d'agent : la clé est écrite à la
// connexion (web ou API) et effacée à la déconnexion. Elle permet de refuser
// une seconde connexion avec les mêmes identifiants.
return new class extends Migration
{
    public function up(): void
    {
        foreach (['agents', 'agents_vente'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('session_cle', 64)->nullable()->after('dernier_acces');
                $table->timestamp('session_expire_le')->nullable()->after('session_cle');
            });
        }
    }

    public function down(): void
    {
        foreach (['agents', 'agents_vente'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['session_cle', 'session_expire_le']);
            });
        }
    }
};
