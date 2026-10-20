<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Comptes affilies : entite distincte des utilisateurs (organisateurs exclus).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->string('email')->unique();
            $table->string('telephone')->nullable();
            $table->string('pays', 2)->default('BJ');
            $table->string('password');
            $table->string('code_parrainage', 20)->unique();
            // Coordonnees de reversement (Mobile Money / banque)
            $table->string('operateur_paiement')->nullable();
            $table->string('numero_paiement')->nullable();
            $table->string('titulaire_paiement')->nullable();
            // Soldes (mis a jour par le service de commission)
            $table->decimal('solde_en_attente', 12, 2)->default(0);
            $table->decimal('solde_disponible', 12, 2)->default(0);
            $table->decimal('total_gagne', 12, 2)->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('dernier_acces')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }
};
