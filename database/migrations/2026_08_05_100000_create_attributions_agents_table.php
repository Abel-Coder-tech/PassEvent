<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributions_agents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('evenement_id')->nullable();
            $table->unsignedInteger('nb_agents_scan')->default(0);
            $table->unsignedInteger('nb_agents_vente')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('evenement_id')->references('id')->on('evenement')->cascadeOnDelete();
            $table->unique(['user_id', 'evenement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributions_agents');
    }
};
