<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('evenement', function (Blueprint $table) {
            $table->boolean('offline_mode_enabled')->default(false)->after('ventes_fermees');
            $table->enum('offline_unknown_policy', ['refuse', 'provisional_allowed'])->default('refuse')->after('offline_mode_enabled');
            $table->unsignedInteger('offline_grace_minutes')->default(0)->after('offline_unknown_policy');
        });
    }
    public function down(): void {
        Schema::table('evenement', function (Blueprint $table) {
            $table->dropColumn(['offline_mode_enabled', 'offline_unknown_policy', 'offline_grace_minutes']);
        });
    }
};
