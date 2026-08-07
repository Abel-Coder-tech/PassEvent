<?php

use App\Services\PaiementMapper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Sépare le moyen de paiement (mobile_money / bancaire / especes) de l'opérateur
    public function up(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->string('type_paiement', 20)->nullable()->after('methode_paiement');
        });

        // Recalcule type_paiement et l'opérateur à partir des valeurs déjà stockées
        DB::table('ticket')
            ->orderBy('id')
            ->chunkById(200, function ($tickets) {
                foreach ($tickets as $ticket) {
                    $old = strtolower(trim((string) ($ticket->methode_paiement ?? '')));

                    $moyen = PaiementMapper::moyenPaiement($old ?: null);
                    $operateur = PaiementMapper::operateur($old ?: null);

                    // Espèces : on conserve la valeur existante ; sinon on garde l'opérateur
                    // (ou la valeur brute si opérateur indéterminé, ex: mastercard).
                    $nouvelleMethode = $moyen === 'especes'
                        ? ($old === '' ? 'especes' : $old)
                        : ($operateur ?? ($old === '' ? null : $old));

                    DB::table('ticket')
                        ->where('id', $ticket->id)
                        ->update([
                            'type_paiement' => $moyen,
                            'methode_paiement' => $nouvelleMethode,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->dropColumn('type_paiement');
        });
    }
};
