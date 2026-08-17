<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\FedapayService;
use App\Services\PaiementMapper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CorrigerOperateursTickets extends Command
{
    protected $signature = 'tickets:corriger-operateurs';

    protected $description = 'Réattribue l\'opérateur mobile (mtn/moov/celtiis) aux tickets payés en interrogeant le mode FedaPay';

    public function __construct(
        protected FedapayService $fedapay,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $candidats = Ticket::where('statut_paiement', 'payé')
            ->whereNotNull('fedapay_transaction_id')
            ->where('type_paiement', 'mobile_money')
            ->where(function ($q) {
                $q->whereNull('methode_paiement')
                    ->orWhere('methode_paiement', '')
                    ->orWhere('methode_paiement', 'mobile_money');
            })
            ->orderBy('id')
            ->get();

        if ($candidats->isEmpty()) {
            $this->info('Aucun ticket à corriger.');

            return 0;
        }

        // Regroupe par transaction FedaPay : un seul appel API par paiement
        $groupes = $candidats->groupBy('fedapay_transaction_id');

        $corriges = 0;
        $sansInfo = 0;
        $echecs = 0;

        foreach ($groupes as $txId => $groupe) {
            try {
                $txData = $this->fedapay->getTransaction((string) $txId);

                $mode = $txData['mode'] ?? $txData['payment_method'] ?? null;
                if (is_array($mode)) {
                    $mode = $mode['provider'] ?? $mode['name'] ?? null;
                }
                $operateur = PaiementMapper::operateur($mode);

                if (! $operateur) {
                    $sansInfo += $groupe->count();
                    continue;
                }

                foreach ($groupe as $ticket) {
                    if ($ticket->update(['methode_paiement' => $operateur])) {
                        $corriges++;
                    }
                }
            } catch (\Exception $e) {
                Log::error('tickets:corriger-operateurs - erreur transaction '.$txId.' : '.$e->getMessage());
                $echecs += $groupe->count();
            }
        }

        $this->info("{$corriges} ticket(s) corrigé(s), {$sansInfo} sans info opérateur, {$echecs} en échec API.");

        return 0;
    }
}
