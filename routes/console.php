<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('queue:work --tries=3 --timeout=60')->everyMinute()->withoutOverlapping();
Schedule::command('evenements:terminer')->hourly();
Schedule::command('tickets:purger-en-attente')->everyFifteenMinutes();
Schedule::command('tickets:reconcilier')->hourly();
Schedule::command('tickets:corriger-operateurs')->hourly();
