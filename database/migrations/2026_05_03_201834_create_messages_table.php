<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('nom_complet');
            $table->string('email');
            $table->string('objet');
            $table->text('message');
            $table->boolean('lu')->default(false);
            $table->text('reponse_admin')->nullable();
            $table->timestamp('date_reponse')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
