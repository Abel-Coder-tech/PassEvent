<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ticket MODIFY COLUMN methode_paiement VARCHAR(20) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE ticket MODIFY COLUMN methode_paiement ENUM('mtn','moov','celtiis') NULL");
    }
};
