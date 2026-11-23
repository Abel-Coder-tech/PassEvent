<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Journal de sécurité (connexions échouées, liens suspects, attaques, robots)
    // et liste des IP bloquées par le superadmin.
    public function up(): void
    {
        Schema::create('journal_securite', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);           // connexion_echouee, connexion_bloquee, lien_suspect, attaque, robot, ip_bloquee
            $table->string('regle', 60)->nullable(); // motif détecté (ex. injection_sql, wp-admin)
            $table->string('ip', 45);
            $table->string('methode', 10);
            $table->string('url', 2000);
            $table->string('espace', 30)->nullable();      // organisateur, superadmin, agent…
            $table->string('identifiant')->nullable();     // email / pseudo tenté
            $table->text('donnees')->nullable();           // champs envoyés, sensibles masqués (JSON)
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['created_at']);
            $table->index(['ip', 'created_at']);
            $table->index(['type', 'created_at']);
        });

        Schema::create('ips_bloquees', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->unique();
            $table->string('motif')->nullable();
            $table->string('bloque_par')->nullable();
            $table->timestamp('expire_le')->nullable(); // null = permanent
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ips_bloquees');
        Schema::dropIfExists('journal_securite');
    }
};
