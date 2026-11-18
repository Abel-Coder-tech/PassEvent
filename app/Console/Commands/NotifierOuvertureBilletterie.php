<?php

namespace App\Console\Commands;

use App\Mail\BilletterieOuverte;
use App\Models\BilletterieAlerte;
use App\Models\Evenement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

// Prévient les visiteurs inscrits dès que la billetterie d'un événement ouvre,
// que l'ouverture soit programmée (date atteinte) ou faite à la main.
class NotifierOuvertureBilletterie extends Command
{
    protected $signature = 'billetterie:notifier-ouverture';

    protected $description = 'Envoie l\'email « billetterie ouverte » aux visiteurs qui ont demandé à être prévenus';

    public function handle(): int
    {
        $envoyes = 0;

        $evenementIds = BilletterieAlerte::whereNull('notifie_le')->distinct()->pluck('evenement_id');

        foreach (Evenement::with('dates')->whereIn('id', $evenementIds)->get() as $evenement) {
            if ($evenement->statut !== 'publié'
                || $evenement->billetteriePasOuverte()
                || $evenement->evenementPasse()) {
                continue;
            }

            BilletterieAlerte::where('evenement_id', $evenement->id)
                ->whereNull('notifie_le')
                ->orderBy('id')
                ->each(function (BilletterieAlerte $alerte) use ($evenement, &$envoyes) {
                    try {
                        Mail::to($alerte->email)->send(new BilletterieOuverte($evenement));
                        $alerte->update(['notifie_le' => now()]);
                        $envoyes++;
                    } catch (Throwable $e) {
                        // Pas marquée : nouvel essai au prochain passage.
                        Log::warning('Alerte ouverture billetterie non envoyée', [
                            'alerte' => $alerte->id,
                            'erreur' => $e->getMessage(),
                        ]);
                    }
                });
        }

        $this->info("{$envoyes} alerte(s) envoyée(s).");

        return self::SUCCESS;
    }
}
