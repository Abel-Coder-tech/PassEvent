<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_releases', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 16)->default('android');
            $table->string('package_name', 128);
            $table->unsignedInteger('version_code');
            $table->string('version_name', 32);
            $table->string('sha256', 64);
            $table->unsignedBigInteger('size_bytes');
            // Nom du fichier uniquement : le binaire vit hors du webroot.
            $table->string('path');
            $table->text('notes')->nullable();
            $table->boolean('mandatory')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Un versionCode ne peut être publié qu'une fois par plateforme.
            $table->unique(['platform', 'version_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_releases');
    }
};
