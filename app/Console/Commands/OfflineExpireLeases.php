<?php
namespace App\Console\Commands;
use App\Services\OfflineLeaseService;
use Illuminate\Console\Command;
class OfflineExpireLeases extends Command {
    protected $signature = 'offline:expire-leases';
    protected $description = 'Expire offline leases past their expires_at';
    public function handle(OfflineLeaseService $service): int {
        $count = $service->expireIfNeeded();
        $this->info("Expired {$count} leases.");
        return self::SUCCESS;
    }
}
