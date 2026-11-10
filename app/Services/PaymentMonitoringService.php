<?php

namespace App\Services;

use App\Mail\PaymentMonitoringAlert;
use App\Models\Log as LogModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PaymentMonitoringService
{
    public function record(
        string $action,
        array $details = [],
        ?Request $request = null,
        ?int $ticketId = null,
        string $type = 'paiement',
        string $severity = 'info',
        bool $notify = false,
        ?string $ip = null,
        ?string $userAgent = null,
    ): void {
        $details = [
            ...$details,
            'action' => $action,
            'severity' => $severity,
        ];

        $userAgent = $request?->userAgent() ?? $userAgent;

        try {
            LogModel::create([
                'ticket_id' => $ticketId,
                'type_operation' => $type,
                'details' => $details,
                'ip' => $request?->ip() ?? $ip,
                'user_agent' => $userAgent
                    ? mb_substr($userAgent, 0, 255)
                    : null,
            ]);
        } catch (Throwable $exception) {
            Log::critical('Échec de persistance du journal de paiement', [
                'action' => $action,
                'ticket_id' => $ticketId,
                'exception_class' => $exception::class,
            ]);

            // Le journal est un outil d'observation : il ne doit jamais faire
            // échouer un paiement ni changer son statut.
        }

        if ($notify) {
            $this->safeAlert($action, $details);
        }
    }

    public function recordFailure(
        string $action,
        array $details = [],
        ?Request $request = null,
        ?int $ticketId = null,
        bool $notifyImmediately = false,
    ): void {
        $this->record(
            $action,
            $details,
            $request,
            $ticketId,
            'echec_paiement',
            $notifyImmediately ? 'critical' : 'warning',
            $notifyImmediately,
        );

        if ($notifyImmediately) {
            return;
        }

        $windowMinutes = max(1, (int) config('services.payment_monitoring.failure_window_minutes', 10));
        $threshold = max(1, (int) config('services.payment_monitoring.failure_threshold', 5));
        $now = now();
        $windowSeconds = $windowMinutes * 60;
        $windowId = intdiv($now->timestamp, $windowSeconds);
        $counterKey = "payment-monitoring:failures:{$windowId}";
        $windowEnd = $now->copy()->setTimestamp(($windowId + 1) * $windowSeconds);
        $transactionReference = $details['transaction_id'] ?? $details['reference'] ?? null;

        try {
            if ($transactionReference !== null) {
                $seenKey = 'payment-monitoring:failure-seen:'.hash('sha256', (string) $transactionReference);
                if (! Cache::add($seenKey, true, $windowEnd)) {
                    return;
                }
            }

            Cache::add($counterKey, 0, $windowEnd);
            $failures = Cache::increment($counterKey);
        } catch (Throwable $exception) {
            Log::error('Impossible de compter les échecs de paiement pour les alertes', [
                'action' => $action,
                'exception_class' => $exception::class,
            ]);

            return;
        }

        if ($failures < $threshold) {
            return;
        }

        $this->safeAlert('paiement_echecs_repetes', [
            'failure_count' => $failures,
            'window_minutes' => $windowMinutes,
            'threshold' => $threshold,
        ]);
    }

    // Envoi d'alerte « best effort » : une panne de mail, de cache ou de file
    // d'attente ne doit jamais interrompre le paiement en cours.
    private function safeAlert(string $action, array $details): void
    {
        try {
            $this->sendAlertOnce($action, $details);
        } catch (Throwable $exception) {
            Log::error('Alerte de paiement non envoyée', [
                'action' => $action,
                'exception_class' => $exception::class,
            ]);
        }
    }

    private function sendAlertOnce(string $action, array $details): void
    {
        $key = 'payment-monitoring:alert:'.hash('sha256', $action);
        $cooldown = max(1, (int) config('services.payment_monitoring.alert_cooldown_minutes', 15));

        try {
            if (! Cache::add($key, true, now()->addMinutes($cooldown))) {
                return;
            }
        } catch (Throwable $exception) {
            Log::error('Impossible de limiter les alertes de paiement', [
                'action' => $action,
                'exception_class' => $exception::class,
            ]);
        }

        $recipient = config('services.payment_monitoring.alert_email');
        if (! is_string($recipient) || trim($recipient) === '') {
            Log::error('Aucun destinataire configuré pour les alertes de paiement', [
                'action' => $action,
            ]);
            $this->recordAlertDelivery('alerte_email_non_configuree', $action, null, 'critical');

            return;
        }

        try {
            // Mise en file : l'envoi SMTP ne doit pas bloquer la réponse au
            // client (webhook FedaPay, confirmation de vente, ...).
            Mail::mailer('support')
                ->to($recipient)
                ->queue(new PaymentMonitoringAlert($action, $details));
            $this->recordAlertDelivery('alerte_email_mise_en_file', $action, $recipient, 'info');
        } catch (Throwable $exception) {
            Cache::forget($key);
            Log::error('Échec de l’envoi d’une alerte de paiement', [
                'action' => $action,
                'recipient' => $recipient,
                'exception_class' => $exception::class,
            ]);
            $this->recordAlertDelivery('alerte_email_echec', $action, $recipient, 'critical', $exception::class);
        }
    }

    private function recordAlertDelivery(
        string $deliveryAction,
        string $alertAction,
        ?string $recipient,
        string $severity,
        ?string $errorClass = null,
    ): void {
        try {
            LogModel::create([
                'type_operation' => $severity === 'critical' ? 'erreur_paiement' : 'paiement',
                'details' => array_filter([
                    'action' => $deliveryAction,
                    'alert_action' => $alertAction,
                    'recipient' => $recipient,
                    'severity' => $severity,
                    'error_class' => $errorClass,
                ], fn ($value) => $value !== null),
            ]);
        } catch (Throwable $exception) {
            Log::error('Impossible de persister le résultat de livraison d’une alerte de paiement', [
                'action' => $deliveryAction,
                'alert_action' => $alertAction,
                'exception_class' => $exception::class,
            ]);
        }
    }
}
