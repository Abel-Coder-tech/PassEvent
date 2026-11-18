<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // QR codes pour des événements non enregistrés sur la plateforme :
    // un lot « externe » n'a ni organisateur, ni événement, ni tarif.
    public function up(): void
    {
        Schema::table('lots_physiques', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('evenement_id')->nullable()->change();
            $table->boolean('externe')->default(false)->after('auto_genere');
            $table->string('nom_evenement', 150)->nullable()->after('externe');
        });

        Schema::create('codes_qr_externes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lot_physique_id');
            $table->string('code', 20)->unique();
            $table->timestamps();

            $table->foreign('lot_physique_id')->references('id')->on('lots_physiques')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codes_qr_externes');

        Schema::table('lots_physiques', function (Blueprint $table) {
            $table->dropColumn(['externe', 'nom_evenement']);
        });
    }
};
