<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('prenom')->nullable()->after('nom');
            $table->json('permissions')->nullable()->after('role');
            $table->boolean('must_change_password')->default(false)->after('mot_de_passe');
            $table->enum('role', ['admin', 'super_admin', 'equipe'])->default('admin')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'super_admin'])->default('admin')->change();
            $table->dropColumn(['prenom', 'permissions', 'must_change_password']);
        });
    }
};
