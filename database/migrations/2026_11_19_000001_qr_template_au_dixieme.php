<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Dimensions du ticket et position/taille du QR au dixième de millimètre
    // (l'éditeur travaille au 0,1 mm).
    public function up(): void
    {
        Schema::table('lots_physiques', function (Blueprint $table) {
            $table->decimal('largeur_personnalisee', 6, 1)->nullable()->change();
            $table->decimal('hauteur_personnalisee', 6, 1)->nullable()->change();
            $table->decimal('qr_x', 6, 1)->nullable()->change();
            $table->decimal('qr_y', 6, 1)->nullable()->change();
            $table->decimal('qr_size', 6, 1)->default(40)->change();
        });
    }

    public function down(): void
    {
        Schema::table('lots_physiques', function (Blueprint $table) {
            $table->unsignedSmallInteger('largeur_personnalisee')->nullable()->change();
            $table->unsignedSmallInteger('hauteur_personnalisee')->nullable()->change();
            $table->integer('qr_x')->nullable()->change();
            $table->integer('qr_y')->nullable()->change();
            $table->integer('qr_size')->default(40)->change();
        });
    }
};
