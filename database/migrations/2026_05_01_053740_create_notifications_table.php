<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('ticket')->cascadeOnDelete();
            $table->enum('canal', ['whatsapp', 'sms', 'email']);
            $table->enum('statut', ['envoyé', 'échoué', 'en_attente'])->default('en_attente');
            $table->text('message')->nullable();
            $table->text('erreur')->nullable();
            $table->unsignedInteger('tentative')->default(1);
            $table->dateTime('date_envoi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};