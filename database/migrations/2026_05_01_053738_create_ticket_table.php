<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('evenement')->cascadeOnDelete();
            $table->foreignId('tarif_id')->nullable()->constrained('tarifs')->nullOnDelete();
            $table->string('code_unique')->unique();
            $table->string('qr_signature');
            $table->string('email_acheteur');
            $table->string('telephone_acheteur');
            $table->string('nom_acheteur')->nullable();
            $table->decimal('montant', 10, 2);
            $table->enum('categorie', ['etudiant', 'externe']);
            $table->enum('type', ['normal', 'vip'])->default('normal');
            $table->enum('statut_paiement', ['en_attente', 'payé', 'échoué', 'remboursé'])->default('en_attente');
            $table->string('transaction_id')->nullable();
            $table->enum('methode_paiement', ['mtn', 'moov', 'celtiis'])->nullable();
            $table->boolean('utilise')->default(false);
            $table->dateTime('date_achat');
            $table->string('code_promo_utilise')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket');
    }
};