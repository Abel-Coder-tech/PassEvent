<?php

namespace App\Console\Commands;

use App\Models\IpBloquee;
use App\Models\JournalSecurite;
use Illuminate\Console\Command;

class PurgerJournalSecurite extends Command
{
    protected $signature = 'securite:purger {--jours=90 : Âge maximal des entrées conservées}';

    protected $description = 'Supprime les entrées anciennes du journal de sécurité et les blocages d\'IP expirés';

    public function handle(): int
    {
        $jours = max(7, (int) $this->option('jours'));

        $journal = JournalSecurite::where('created_at', '<', now()->subDays($jours))->delete();
        $blocages = IpBloquee::whereNotNull('expire_le')->where('expire_le', '<', now())->get()->each->delete()->count();

        $this->info("{$journal} entrée(s) du journal et {$blocages} blocage(s) expiré(s) supprimés.");

        return self::SUCCESS;
    }
}
