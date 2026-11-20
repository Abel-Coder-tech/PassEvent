<?php

use App\Support\ProfileTypeNormalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Les anciens profils « professionnel » (entreprise, association, club)
     * reprennent le type réel choisi dans le parcours organisateur.
     * Aucune donnée n'est supprimée et la migration est idempotente.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'admin')
            ->whereNotNull('type')
            ->orderBy('id')
            ->chunkById(200, function ($utilisateurs) {
                foreach ($utilisateurs as $utilisateur) {
                    [$type, $detail] = ProfileTypeNormalizer::normaliser(
                        $utilisateur->type,
                        $utilisateur->type_detail
                    );

                    if ($type === $utilisateur->type && $detail === $utilisateur->type_detail) {
                        continue;
                    }

                    DB::table('users')->where('id', $utilisateur->id)->update([
                        'type' => $type,
                        'type_detail' => $detail,
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    /**
     * Réparation de données non réversible : revenir en arrière écraserait
     * les profils créés depuis avec les nouveaux types.
     */
    public function down(): void
    {
        // Intentionnellement vide.
    }
};
