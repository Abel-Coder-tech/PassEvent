<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('titre');
        });

        // Backfill : génère un slug unique pour chaque événement existant.
        $rows = DB::table('evenement')->orderBy('id')->get(['id', 'titre']);
        $utilises = [];

        foreach ($rows as $row) {
            $base = Str::slug($row->titre) ?: 'evenement-' . $row->id;
            $slug = $base;
            $i = 2;
            while (in_array($slug, $utilises, true)) {
                $slug = $base . '-' . $i;
                $i++;
            }
            $utilises[] = $slug;

            DB::table('evenement')->where('id', $row->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('evenement', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};