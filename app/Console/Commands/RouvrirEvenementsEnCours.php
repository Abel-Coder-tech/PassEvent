<?php

namespace App\Console\Commands;

use App\Models\Evenement;
use Illuminate\Console\Command;

/**
 * Remet en « publié » les événements marqués « terminé » trop tôt (ancienne
 * règle : clôture dès l'heure de début / 6 h après), alors qu'ils sont encore
 * dans leur fenêtre de vente et de scan (12 h après le début de la dernière
 * session). Un événement réellement terminé n'est jamais rouvert.
 */
class RouvrirEvenementsEnCours extends Command
{
    protected $signature = 'evenements:rouvrir
        {--essai : Affiche les événements concernés sans rien modifier}';

    protected $description = 'Rouvre (publié) les événements marqués terminés encore dans leur fenêtre de 12 h';

    public function handle(): int
    {
        $essai = (bool) $this->option('essai');

        $candidats = Evenement::with('dates')
            ->where('statut', 'terminé')
            // Pré-filtre large : la dernière session ne peut pas être plus ancienne que date_event
            ->where('date_event', '>=', Evenement::seuilDebutEnVente()->subDays(60))
            ->get()
            ->reject(fn (Evenement $e) => $e->evenementPasse())
            ->values();

        if ($candidats->isEmpty()) {
            $this->info('Aucun événement à rouvrir.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Titre', 'Dernière session', 'Vente/scan jusqu\'au'],
            $candidats->map(fn (Evenement $e) => [
                $e->id,
                $e->titre,
                $e->derniereDateVente()?->format('d/m/Y H:i'),
                $e->finVente()?->format('d/m/Y H:i'),
            ])->all(),
        );

        if ($essai) {
            $this->warn($candidats->count().' événement(s) seraient rouverts (mode essai, rien n\'est modifié).');

            return self::SUCCESS;
        }

        $candidats->each(fn (Evenement $e) => $e->update(['statut' => 'publié']));
        $this->info($candidats->count().' événement(s) rouvert(s) : achetables et scannables jusqu\'à la date indiquée.');

        return self::SUCCESS;
    }
}
