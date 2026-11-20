<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les codes PIN étaient stockés en clair. On élargit la colonne puis on
     * hache les valeurs existantes une seule fois (un hash bcrypt commence
     * toujours par $2y$).
     */
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->string('code_acces', 255)->change();
        });

        DB::table('agents')
            ->select('id', 'code_acces')
            ->whereNotNull('code_acces')
            ->orderBy('id')
            ->chunkById(100, function ($agents) {
                foreach ($agents as $agent) {
                    $code = (string) $agent->code_acces;

                    if ($code === ''
                        || str_starts_with($code, '$2y$')
                        || str_starts_with($code, '$2a$')
                        || str_starts_with($code, '$argon')) {
                        continue;
                    }

                    DB::table('agents')->where('id', $agent->id)->update([
                        'code_acces' => Hash::make($code),
                    ]);
                }
            });
    }

    /**
     * Non réversible : un code haché ne peut pas être retrouvé.
     */
    public function down(): void
    {
        //
    }
};
