<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('evenements:terminer')->hourly();
Schedule::command('tickets:purger-en-attente')->everyFifteenMinutes();
// Filet de sécurité paiements : retrouve les transactions FedaPay approuvées
// qu'un webhook/callback manquant n'a jamais rattachées à un billet.
Schedule::command('paiements:rapprocher-fedapay')->everyFiveMinutes()->withoutOverlapping(10);
Schedule::command('tickets:envoyer-emails')->everyFiveMinutes()->withoutOverlapping(10);
Schedule::command('tickets:reconcilier')->hourly();
Schedule::command('billetterie:notifier-ouverture')->everyFiveMinutes()->withoutOverlapping(10);
Schedule::command('tickets:corriger-operateurs')->hourly();
Schedule::command('affiliations:liberer-commissions')->hourly();
Schedule::command('sanctum:prune-expired --hours=24')->daily();
Schedule::command('ventes:purger-idempotences')->daily();
// Sauvegarde de la base chaque nuit à 3 h (14 jours conservés), en plus de celles de l'hébergeur
Schedule::command('paxevent:sauvegarder-base')->dailyAt('03:00')->withoutOverlapping(60);
// Journal de sécurité : entrées de plus de 90 jours et blocages d'IP expirés
Schedule::command('securite:purger')->dailyAt('03:30');
