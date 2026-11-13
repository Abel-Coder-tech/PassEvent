<?php

namespace App\Console\Commands;

use App\Models\Evenement;
use App\Models\Tarif;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculerCompteursEvenements extends Command
{
    protected $signature = 'evenements:recalculer-compteurs
        {--evenement= : Limiter à un événement (id)}
        {--dry-run : Afficher les écarts sans rien modifier}';

    protected $description = 'Recalcule quota_vendu (événements) et quantite_vendue (tarifs) à partir des billets réellement actifs';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $corriges = 0;

        $evenements = Evenement::query()
            ->when($this->option('evenement'), fn ($q, $id) => $q->whereKey($id))
            ->orderBy('id')
            ->get();

        foreach ($evenements as $evenement) {
            DB::transaction(function () use ($evenement, $dryRun, &$corriges) {
                $verrou = Evenement::whereKey($evenement->id)->lockForUpdate()->first();
                if (! $verrou) {
                    return;
                }

                $reel = (int) Ticket::query()
                    ->where('evenement_id', $verrou->id)
                    ->actifsPourCapacite()
                    ->sum('quantite');

                if ((int) $verrou->quota_vendu !== $reel) {
                    $this->line("Événement #{$verrou->id} « {$verrou->titre} » : quota_vendu {$verrou->quota_vendu} → {$reel}");
                    if (! $dryRun) {
                        $verrou->forceFill(['quota_vendu' => $reel])->saveQuietly();
                    }
                    $corriges++;
                }

                // Le tarif ne compte que les billets payés (la réservation en
                // attente n'incrémente quantite_vendue qu'à la confirmation).
                foreach (Tarif::where('evenement_id', $verrou->id)->lockForUpdate()->get() as $tarif) {
                    $vendus = (int) Ticket::query()
                        ->where('tarif_id', $tarif->id)
                        ->actifsPourCapacite()
                        ->where('statut_paiement', 'payé')
                        ->sum('quantite');

                    if ((int) $tarif->quantite_vendue !== $vendus) {
                        $this->line("  Tarif #{$tarif->id} « {$tarif->nom} » : quantite_vendue {$tarif->quantite_vendue} → {$vendus}");
                        if (! $dryRun) {
                            $tarif->forceFill(['quantite_vendue' => $vendus])->saveQuietly();
                        }
                        $corriges++;
                    }
                }
            });
        }

        $this->info(($dryRun ? '[dry-run] ' : '')."{$corriges} compteur(s) ".($dryRun ? 'à corriger' : 'corrigé(s)').' sur '.$evenements->count().' événement(s).');

        return self::SUCCESS;
    }
}
