@extends('layouts.app')

@section('title', 'Paiement QR codes - Billetterie')
@section('page-title', 'Paiement de votre commande')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.lots-physiques.index') }}">Vente physique</a></li>
    <li class="breadcrumb-item active" aria-current="page">Paiement</li>
@endsection

@section('content')
<div class="page-content">
    <style>
    .steps-bar { display: flex; align-items: center; margin-bottom: 1.5rem; overflow-x: auto; padding-bottom: .25rem; }
    .step-item { display: flex; align-items: center; flex: 1; min-width: max-content; }
    .step-dot { width: 30px; height: 30px; border-radius: 50%; background: #e9ecef; color: #6c757d; display: flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 700; flex-shrink: 0; transition: all .3s; }
    .step-label { font-size: .72rem; color: #6c757d; margin-left: .4rem; white-space: nowrap; }
    .step-line { flex: 1; height: 2px; background: #e9ecef; margin: 0 .5rem; min-width: 16px; transition: background .3s; }
    .step-item.done .step-dot { background: #198754; color: #fff; }
    .step-item.done .step-label { color: #198754; }
    .step-item.done .step-line { background: #198754; }
    .step-item.active .step-dot { background: #542680; color: #fff; box-shadow: 0 0 0 4px rgba(84,38,128,.15); }
    .step-item.active .step-label { color: #542680; font-weight: 700; }
    </style>

    <!-- Barre de progression : étapes 1-3 validées -->
    <div class="steps-bar">
        <div class="step-item done"><span class="step-dot"><i class="bi bi-check"></i></span><span class="step-label">Événement</span><span class="step-line"></span></div>
        <div class="step-item done"><span class="step-dot"><i class="bi bi-check"></i></span><span class="step-label">Quantités</span><span class="step-line"></span></div>
        <div class="step-item done"><span class="step-dot"><i class="bi bi-check"></i></span><span class="step-label">Récapitulatif</span><span class="step-line"></span></div>
        <div class="step-item active"><span class="step-dot">4</span><span class="step-label">Paiement</span><span class="step-line"></span></div>
        <div class="step-item"><span class="step-dot">5</span><span class="step-label">Téléchargement</span></div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
    @endif

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <strong><i class="bi bi-qr-code me-1" style="color:#542680;"></i> Commande {{ $reference }}</strong>
                </div>
                <div class="card-body p-3 p-md-4">
                    @foreach($lots as $lot)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom small">
                        <span>
                            <span class="badge me-1" style="background:#542680;color:#fff;">{{ $lot->tarif?->nom ?? 'Pass' }}</span>
                            {{ $lot->quantite }} ticket(s)
                        </span>
                        <span class="fw-semibold">{{ number_format($lot->montant_commission, 0, ',', ' ') }} F</span>
                    </div>
                    @endforeach

                    <div class="d-flex justify-content-between align-items-center pt-3 pb-2">
                        <span class="fw-semibold">Total à payer</span>
                        <strong style="font-size:1.4rem;color:#542680;">{{ number_format($total, 0, ',', ' ') }} F</strong>
                    </div>

                    <div class="alert alert-light border py-2 mb-3" style="font-size:.78rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Commission de génération (5 % du prix des billets), payée d'avance et non remboursable.
                        Vos planches PDF seront disponibles immédiatement après confirmation du paiement.
                        Réception envoyée à <strong>{{ $lots->first()->email_reception }}</strong>.
                    </div>

                    <button type="button" id="btnFedaPay" class="btn w-100 text-white" style="background:#542680;border-radius:10px;font-weight:700;padding:.7rem;">
                        <i class="bi bi-shield-lock me-1"></i> Payer {{ number_format($total, 0, ',', ' ') }} F avec FedaPay
                    </button>
                    <div id="paymentError" class="mt-3" style="display:none;color:#dc3545;font-size:.85rem;"></div>

                    <div class="text-center mt-3">
                        <a href="{{ route('admin.lots-physiques.index') }}" class="text-decoration-none" style="font-size:.82rem;">
                            <i class="bi bi-arrow-left me-1"></i>Modifier ma commande
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.fedapay.com/checkout.js?v=1.1.3"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnFedaPay');
    const errorDiv = document.getElementById('paymentError');
    const callbackUrl = '{{ route('paiement.callback') }}?source=lot_physique&reference={{ $reference }}';

    if (!btn) return;

    if (typeof FedaPay === 'undefined') {
        errorDiv.textContent = 'Erreur de chargement du module de paiement. Actualisez la page.';
        errorDiv.style.display = 'block';
        return;
    }

    FedaPay.init(btn, {
        public_key: '{{ $publicKey }}',
        environment: '{{ $sandbox ? 'sandbox' : 'live' }}',
        transaction: {
            amount: {{ (int) ceil($total) }},
            description: 'QR codes physiques - {{ $lots->first()->evenement?->titre ?? 'PaxEvent' }}',
            external_id: '{{ $reference }}',
            custom_metadata: {
                type: 'lot_physique',
                reference: '{{ $reference }}'
            }
        },
        customer: {
            email: '{{ $lots->first()->email_reception }}',
            firstname: '{{ addslashes(Auth::user()->nom) }}'
        },
        currency: {
            iso: 'XOF'
        },
        onComplete: function(data) {
            if (data.transaction && data.transaction.id) {
                window.location.href = callbackUrl + '&id=' + data.transaction.id + '&status=' + (data.transaction.status || 'approved');
            } else {
                errorDiv.textContent = 'Paiement annulé ou fermé. Vous pouvez réessayer.';
                errorDiv.style.display = 'block';
            }
        }
    });
});
</script>
@endsection
