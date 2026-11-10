<?php

namespace Tests\Unit;

use App\Mail\PaymentMonitoringAlert;
use App\Models\Log as LogModel;
use App\Services\PaymentMonitoringService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentMonitoringServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('logs')) {
            Schema::create('logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id')->nullable();
                $table->unsignedBigInteger('agent_id')->nullable();
                $table->string('type_operation', 50);
                $table->json('details')->nullable();
                $table->string('ip')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamp('created_at');
            });
        } elseif (! Schema::hasColumn('logs', 'agent_id')) {
            Schema::table('logs', fn (Blueprint $table) => $table->unsignedBigInteger('agent_id')->nullable());
        }

        DB::table('logs')->delete();

        config([
            'services.payment_monitoring.alert_email' => 'paxevent09@gmail.com',
            'services.payment_monitoring.alert_cooldown_minutes' => 15,
            'services.payment_monitoring.failure_threshold' => 2,
            'services.payment_monitoring.failure_window_minutes' => 10,
        ]);

        Mail::fake();
    }

    public function test_records_payment_actions_without_alerting_on_normal_events(): void
    {
        $request = Request::create('/callback', 'POST', [], [], [], [
            'REMOTE_ADDR' => '192.0.2.10',
            'HTTP_USER_AGENT' => str_repeat('a', 300),
        ]);

        app(PaymentMonitoringService::class)->record(
            'paiement_initie',
            [
                'transaction_id' => 'GRP-123',
                'amount' => 2500,
                'currency' => 'XOF',
            ],
            $request,
        );

        $log = LogModel::firstOrFail();
        $this->assertNull($log->ticket_id);
        $this->assertSame('paiement', $log->type_operation);
        $this->assertSame('192.0.2.10', $log->ip);
        $this->assertSame(255, mb_strlen($log->user_agent));
        $this->assertSame('paiement_initie', $log->details['action']);
        $this->assertSame('info', $log->details['severity']);
        Mail::assertNothingSent();
    }

    public function test_sends_a_deduplicated_alert_for_an_anomaly(): void
    {
        $monitoring = app(PaymentMonitoringService::class);
        $details = ['transaction_id' => 'FED-123', 'received_amount' => 10];

        $monitoring->record('montant_incoherent', $details, null, null, 'erreur_paiement', 'critical', true);
        $monitoring->record('montant_incoherent', $details, null, null, 'erreur_paiement', 'critical', true);

        $this->assertSame(3, LogModel::count());
        Mail::assertQueued(PaymentMonitoringAlert::class, 1);
        Mail::assertQueued(PaymentMonitoringAlert::class, fn (PaymentMonitoringAlert $mail) => $mail->hasTo('paxevent09@gmail.com'));
    }

    public function test_alerts_when_payment_failures_reach_the_configured_threshold(): void
    {
        $monitoring = app(PaymentMonitoringService::class);

        $monitoring->recordFailure('paiement_refuse', ['transaction_id' => 'FED-1']);
        Mail::assertNothingSent();

        $monitoring->recordFailure('paiement_refuse', ['transaction_id' => 'FED-2']);

        $this->assertSame(3, LogModel::count());
        Mail::assertQueued(PaymentMonitoringAlert::class, 1);
        Mail::assertQueued(
            PaymentMonitoringAlert::class,
            fn (PaymentMonitoringAlert $mail) => $mail->action === 'paiement_echecs_repetes',
        );
    }

    public function test_alerts_are_queued_and_never_sent_synchronously(): void
    {
        app(PaymentMonitoringService::class)->record(
            'montant_incoherent',
            ['transaction_id' => 'FED-QUEUE'],
            null,
            null,
            'erreur_paiement',
            'critical',
            true,
        );

        Mail::assertNothingSent();
        Mail::assertQueued(PaymentMonitoringAlert::class, 1);
    }

    public function test_a_broken_monitoring_journal_never_breaks_the_payment(): void
    {
        // Plus aucune écriture possible : ni le journal, ni le résultat d'alerte.
        Schema::drop('logs');

        app(PaymentMonitoringService::class)->record(
            'paiement_confirme',
            ['transaction_id' => 'GRP-RESILIENT'],
            null,
            null,
            'paiement',
            'info',
            true,
        );

        $this->assertTrue(true, 'Le monitoring ne doit jamais lever d\'exception dans un paiement.');
        Mail::assertQueued(PaymentMonitoringAlert::class, 1);
    }
}
