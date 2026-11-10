<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Un billet ne doit jamais porter de code scannable avant confirmation du
 * paiement. Cette migration neutralise les codes déjà émis à tort pour des
 * réservations en_attente / échoué et attribue un code définitif aux billets
 * payés qui n'en auraient pas (données historiques).
 *
 * Les billets remboursés ou annulés après paiement conservent leur code : il
 * permet au scan de répondre « Ticket remboursé » / « Ticket annulé ».
 */
return new class extends Migration
{
    public function up(): void
    {
        $aNeutraliser = DB::table('ticket')
            ->whereIn('statut_paiement', ['en_attente', 'échoué'])
            ->where(function ($query) {
                $query->whereNull('code_unique')
                    ->orWhere(function ($q) {
                        $q->where('code_unique', '!=', 'TMP')
                            ->where('code_unique', 'not like', 'TMP-%');
                    });
            })
            ->orderBy('id')
            ->pluck('id');

        foreach ($aNeutraliser as $id) {
            DB::table('ticket')->where('id', $id)->update([
                'code_unique' => $this->codeReservation(),
            ]);
        }

        $sansCode = DB::table('ticket')
            ->where('statut_paiement', 'payé')
            ->where(function ($query) {
                $query->whereNull('code_unique')
                    ->orWhere('code_unique', '')
                    ->orWhere('code_unique', 'TMP')
                    ->orWhere('code_unique', 'like', 'TMP-%');
            })
            ->orderBy('id')
            ->pluck('id');

        foreach ($sansCode as $id) {
            DB::table('ticket')->where('id', $id)->update([
                'code_unique' => $this->codeDefinitif(),
            ]);
        }
    }

    public function down(): void
    {
        // Irréversible : les anciens codes non payés ne doivent jamais revenir.
    }

    private function codeReservation(): string
    {
        do {
            $code = 'TMP-'.strtoupper(Str::random(10));
        } while (DB::table('ticket')->where('code_unique', $code)->exists());

        return $code;
    }

    private function codeDefinitif(): string
    {
        do {
            $code = 'PAX-'.strtoupper(Str::random(5));
        } while (DB::table('ticket')->where('code_unique', $code)->exists());

        return $code;
    }
};
