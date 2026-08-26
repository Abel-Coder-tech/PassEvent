<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lots_physiques', function (Blueprint $table) {
            $table->string('template_path')->nullable()->after('transmis_at');
            $table->integer('qr_x')->nullable()->after('template_path');
            $table->integer('qr_y')->nullable()->after('qr_x');
            $table->integer('qr_size')->default(40)->after('qr_y');
            $table->integer('pdf_par_page')->default(4)->after('qr_size');
        });
    }

    public function down(): void
    {
        Schema::table('lots_physiques', function (Blueprint $table) {
            $table->dropColumn(['template_path', 'qr_x', 'qr_y', 'qr_size', 'pdf_par_page']);
        });
    }
};
