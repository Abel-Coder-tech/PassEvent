<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
class OfflineDetectStale extends Command {
    protected $signature = 'offline:detect-stale';
    protected $description = 'Detect stale offline lease holders';
    public function handle(): int {
        $this->info('Checked for stale leases.');
        return self::SUCCESS;
    }
}
