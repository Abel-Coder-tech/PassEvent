@extends('layouts.agent-vente')

@section('title', 'Paiement Mobile - ' . $agent->evenement->titre)

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" height="60">
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small"><i class="bi bi-person-circle me-1"></i>{{ $agent->nom }}</span>
            <form method="POST" action="{{ route('agent-vente.logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Déconnecter
                </button>
            </form>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-6">
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 16px;">
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 64px; height: 64px; background: #f5f3ff;">
                            <i class="bi bi-wallet2" style="font-size: 1.75rem; color: #7c3aed;"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Paiement Mobile</h5>
                        <p class="text-muted small mb-0">Le client doit payer pour finaliser la vente</p>
                    </div>

                    <div class="p-3 rounded-3 mb-3 text-start" style="background: #f8f6f9;">
                        <div class="row g-2" style="font-size: 0.88rem;">
                            <div class="col-6">
                                <span class="text-muted">Client :</span><br>
                                <strong>{{ $ticket->nom_acheteur }}</strong>
                            </div>
                            <div class="col-6 text-end">
                                <span class="text-muted">Montant :</span><br>
                                <strong style="color: #7c3aed;">{{ number_format($ticket->montant, 0, ',', ' ') }} FCFA</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted">Email :</span><br>
                                <strong>{{ $ticket->email_acheteur }}</strong>
                            </div>
                            <div class="col-6 text-end">
                                <span class="text-muted">Ticket :</span><br>
                                <code class="fw-bold" style="background: #fff; padding: 0.15rem 0.4rem; border-radius: 4px; border: 1px solid #eee;">{{ $ticket->code_unique }}</code>
                            </div>
                        </div>
                    </div>

                    @if($publicKey)
                        <button type="button" id="btnFedaPay" class="btn w-100 py-3" style="background: #7c3aed; color: #fff; border-radius: 10px; font-size: 1rem; font-weight: 700; border: none;">
                            <i class="bi bi-shield-lock me-2"></i> Payer {{ number_format($ticket->montant, 0, ',', ' ') }} FCFA
                        </button>
                    @else
                        <div class="alert alert-warning py-2 mb-0" style="border-radius: 10px; font-size: 0.85rem;">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Aucune méthode de paiement configurée.
                        </div>
                    @endif

                    <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                        <small class="text-muted" style="font-size: 0.75rem;">Paiement sécurisé par</small>
                        <strong style="color: #7c3aed; font-size: 0.9rem;">FedaPay</strong>
                    </div>

                    <div id="paymentError" class="mt-3" style="display: none; color: #dc3545; font-size: 0.85rem;"></div>

                    <a href="{{ route('agent-vente.dashboard') }}" class="btn btn-sm btn-outline-secondary mt-3">
                        <i class="bi bi-arrow-left"></i> Annuler et revenir
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.fedapay.com/checkout.js?v=1.1.3"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnFedaPay');
    const errorDiv = document.getElementById('paymentError');
    const callbackUrl = '{{ route('paiement.callback') }}?ticket={{ $ticket->id }}&source=agent_vente';

    if (!btn) return;

    if (typeof FedaPay === 'undefined') {
        errorDiv.textContent = 'Erreur de chargement du module de paiement. Actualisez la page.';
        errorDiv.style.display = 'block';
        return;
    }

    const nameParts = '{{ $ticket->nom_acheteur }}'.trim().split(' ');
    const firstname = nameParts.slice(0, -1).join(' ') || nameParts[0] || 'Client';
    const lastname = nameParts.length > 1 ? nameParts[nameParts.length - 1] : '';

    FedaPay.init(btn, {
        public_key: '{{ $publicKey }}',
        environment: '{{ $sandbox ? 'sandbox' : 'live' }}',
        transaction: {
            amount: {{ (int) $ticket->montant }},
            description: 'Ticket - {{ $ticket->evenement->titre }}'
        },
        customer: {
            email: '{{ $ticket->email_acheteur }}',
            firstname: firstname,
            lastname: lastname
        },
        currency: {
            iso: 'XOF'
        },
        onComplete: function(data) {
            if (data.reason === 'CHECKOUT COMPLETE' && data.transaction && data.transaction.id) {
                window.location.href = callbackUrl + '&id=' + data.transaction.id + '&status=' + (data.transaction.status || 'approved');
            } else {
                errorDiv.textContent = 'Paiement annulé ou fermé. Vous pouvez réessayer.';
                errorDiv.style.display = 'block';
            }
        }
    });
});
</script>
@endpush