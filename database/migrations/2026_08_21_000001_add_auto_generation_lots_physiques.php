<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Auto-génération de QR codes par l'organisateur (paiement commission 5 % via FedaPay)
    public function up(): void
    {
        Schema::table('lots_physiques', function (Blueprint $table) {
            $table->boolean('auto_genere')->default(false)->after('statut');
            $table->decimal('montant_commission', 10, 2)->nullable()->after('auto_genere');
            $table->string('email_reception')->nullable()->after('montant_commission');
            $table->string('fedapay_transaction_id')->nullable()->after('email_reception');
            $table->string('reference_paiement', 40)->nullable()->after('fedapay_transaction_id');
            $table->index('reference_paiement');
        });
    }

    public function down(): void
    {
        Schema::table('lots_physiques', function (Blueprint $table) {
            $table->dropIndex(['reference_paiement']);
            $table->dropColumn([
                'auto_genere',
                'montant_commission',
                'email_reception',
                'fedapay_transaction_id',
                'reference_paiement',
            ]);
        });
    }
};
