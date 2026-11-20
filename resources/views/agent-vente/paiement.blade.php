@extends('layouts.agent-vente')

@section('title', 'Paiement mobile — ' . $agent->evenement->titre)

@section('content')
<div class="av-app">
    @include('agent-vente.partials.header', ['section' => 'Paiement'])

    <main class="av-main">
        <section class="av-card" aria-labelledby="avPaiementTitle">
            <div class="av-card-head">
                <div>
                    <span class="av-kicker">Paiement mobile</span>
                    <h1 id="avPaiementTitle">Finaliser la vente</h1>
                </div>
                <span class="av-live"><span aria-hidden="true"></span> Sécurisé</span>
            </div>

            <div style="padding: 0 16px 16px;">
                <p class="av-refresh" style="padding: 0 0 12px;">
                    <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                    Le client doit payer pour finaliser la vente.
                </p>

                <div class="av-recap" aria-label="Détails de la vente">
                    <div class="av-recap-ligne">
                        <span>Client</span>
                        <strong>{{ $ticket->nom_acheteur }}</strong>
                    </div>
                    <div class="av-recap-ligne av-recap-ligne--montant">
                        <span>Montant</span>
                        <strong>{{ number_format($ticket->montant, 0, ',', ' ') }} FCFA</strong>
                    </div>
                    <div class="av-recap-ligne">
                        <span>Email</span>
                        <strong>{{ $ticket->email_acheteur }}</strong>
                    </div>
                    <div class="av-recap-ligne">
                        <span>{{ $ticket->aUnCodeDefinitif() ? 'Ticket' : 'Référence' }}</span>
                        <code>{{ $ticket->aUnCodeDefinitif() ? $ticket->code_unique : $ticket->transaction_id }}</code>
                    </div>
                </div>

                @if ($publicKey)
                    <button type="button" id="btnFedaPay" class="av-action">
                        <i class="bi bi-shield-lock" aria-hidden="true"></i>
                        Payer {{ number_format($ticket->montant, 0, ',', ' ') }} FCFA
                    </button>
                    <p class="av-secure">Paiement sécurisé par <strong>FedaPay</strong></p>
                @else
                    <div class="av-alert av-alert--error" role="alert">
                        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                        <div class="av-alert-body">
                            <strong>Paiement indisponible</strong>
                            <span>Le paiement mobile est temporairement indisponible. Utilisez les espèces.</span>
                        </div>
                    </div>
                @endif

                <div id="paymentError" class="av-error-box" style="display: none;" role="alert"></div>

                <a href="{{ route('agent-vente.dashboard') }}" class="av-footer-link av-back">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i> Annuler et revenir au point de vente
                </a>
            </div>
        </section>
    </main>
</div>
@endsection

@push('styles')
@include('agent-vente.partials.styles')
@endpush

@push('scripts')
<script src="https://cdn.fedapay.com/checkout.js?v=1.1.3"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnFedaPay');
    if (!btn) return;

    const errorDiv = document.getElementById('paymentError');
    const callbackUrl = @json(route('paiement.callback').'?ticket='.$ticket->id.'&source=agent_vente');

    function afficherErreur(message) {
        if (!errorDiv) return;
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }

    if (typeof FedaPay === 'undefined') {
        afficherErreur('Erreur de chargement du module de paiement. Actualisez la page.');
        return;
    }

    const nom = @json($ticket->nom_acheteur).trim().split(' ');
    const firstname = nom.slice(0, -1).join(' ') || nom[0] || 'Client';
    const lastname = nom.length > 1 ? nom[nom.length - 1] : '';

    FedaPay.init(btn, {
        public_key: @json($publicKey),
        environment: @json($sandbox ? 'sandbox' : 'live'),
        transaction: {
            amount: {{ (int) $ticket->montant }},
            description: @json('Ticket - '.$ticket->evenement->titre),
            external_id: @json((string) $ticket->id),
            custom_metadata: {
                ticket_id: @json((string) $ticket->id),
                group_transaction_id: @json($ticket->transaction_id)
            }
        },
        customer: {
            email: @json($ticket->email_acheteur),
            firstname: firstname,
            lastname: lastname
        },
        currency: {
            iso: 'XOF'
        },
        onComplete: function (data) {
            if (data.transaction && data.transaction.id) {
                const pm = data.transaction.payment_method || 'mobile_money';
                const ph = data.transaction.phone || '';
                window.location.href = callbackUrl + '&id=' + data.transaction.id + '&status=' + (data.transaction.status || 'approved') + '&payment_method=' + pm + '&phone=' + ph;
            } else {
                afficherErreur('Paiement annulé ou fermé. Vous pouvez réessayer.');
            }
        }
    });
});
</script>
@endpush
