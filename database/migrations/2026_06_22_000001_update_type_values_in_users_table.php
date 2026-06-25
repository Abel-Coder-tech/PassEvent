<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereIn('type', ['entreprise', 'association'])
            ->update(['type' => 'professionnel']);

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->string('type', 30)->nullable()->comment('universitaire, professionnel')->change();
            });
        }
    }

    public function down(): void
    {
        DB::table('users')
            ->where('type', 'professionnel')
            ->update(['type' => 'entreprise']);
    }
};
