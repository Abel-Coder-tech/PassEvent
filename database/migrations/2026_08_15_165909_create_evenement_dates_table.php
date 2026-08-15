<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evenement_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('evenement')->cascadeOnDelete();
            $table->dateTime('date_debut');
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });

        // Backfill : chaque événement existant reçoit sa date unique comme première session
        $evenements = DB::table('evenement')->whereNotNull('date_event')->get(['id', 'date_event']);
        foreach ($evenements as $ev) {
            DB::table('evenement_dates')->insert([
                'evenement_id' => $ev->id,
                'date_debut' => $ev->date_event,
                'ordre' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evenement_dates');
    }
};