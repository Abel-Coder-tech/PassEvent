@extends('layouts.public')

@section('title', 'Paiement - ' . $ticket->evenement->titre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('evenements.public') }}">Evenements</a></li>
    <li class="breadcrumb-item"><a href="{{ route('evenements.public.show', $ticket->evenement->id) }}">{{ Str::limit($ticket->evenement->titre, 30) }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Paiement</li>
@endsection

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-7 col-lg-6">
                {{-- Alerts --}}
                @if(session('error'))
                    <div class="alert alert-danger py-2" style="font-size: 0.85rem; border-radius: 10px;">
                        <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
                    </div>
                @endif

                {{-- Recapitulatif --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3" style="color: #333;">
                            <i class="bi bi-receipt me-2" style="color: #7B3FA0;"></i>
                            Recapitulatif
                        </h5>

                        <div class="p-3 rounded-3 mb-3" style="background: #f8f6f9;">
                            <div class="mb-2">
                                <strong style="font-size: 1.05rem;">{{ $ticket->evenement->titre }}</strong>
                            </div>
                            <div class="row g-2" style="font-size: 0.9rem;">
                                <div class="col-6">
                                    <span class="text-muted">Tarif :</span><br>
                                    <strong>{{ ucfirst($ticket->categorie) }} / {{ $ticket->type === 'normal' ? 'Standard' : 'VIP' }}</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="text-muted">Montant :</span><br>
                                    <strong style="color: #7B3FA0; font-size: 1.1rem;">{{ number_format($ticket->montant, 0, ',', ' ') }} FCFA</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Acheteur :</span><br>
                                    <strong>{{ $ticket->nom_acheteur }}</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="text-muted">Code :</span><br>
                                    <code class="fw-bold" style="background: #fff; padding: 0.15rem 0.4rem; border-radius: 4px; border: 1px solid #eee;">{{ $ticket->code_unique }}</code>
                                </div>
                                @if($ticket->code_promo_utilise)
                                    <div class="col-12 mt-2">
                                        <span class="badge" style="background: rgba(123,63,160,0.1); color: #7B3FA0; font-size: 0.75rem;">
                                            <i class="bi bi-tag me-1"></i>Code promo: {{ $ticket->code_promo_utilise }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($ticket->montant_reduction > 0)
                            <div class="alert py-2 mb-3" style="background: rgba(46,125,79,0.06); border: 1px solid rgba(46,125,79,0.15); border-radius: 8px; font-size: 0.82rem;">
                                <i class="bi bi-check-circle me-1" style="color: #2E7D4F;"></i>
                                Reduction appliquee: <strong>-{{ number_format($ticket->montant_reduction, 0, ',', ' ') }} F</strong>
                            </div>
                        @endif

                        <div class="text-center" style="font-size: 0.82rem; color: #999;">
                            <i class="bi bi-clock me-1"></i>
                            Votre place est reservee pendant 15 minutes
                        </div>
                    </div>
                </div>

                {{-- Paiement KKiaPay --}}
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold mb-3" style="color: #333;">
                            <i class="bi bi-wallet2 me-2" style="color: #2E7D4F;"></i>
                            Paiement securise via KKiaPay
                        </h5>

                        @if(config('services.kkiapay.sandbox'))
                            <div class="alert alert-info py-2 mb-3" style="font-size: 0.8rem; border-radius: 8px; border: none;">
                                <i class="bi bi-info-circle me-1"></i>
                                Mode <strong>TEST (sandbox)</strong> — Aucun argent ne sera debite.
                            </div>
                        @endif

                        {{-- Bouton paiement --}}
                        <button type="button" id="btnKkiaPay" class="btn w-100 py-3" style="background: #7B3FA0; color: #fff; border-radius: 10px; font-size: 1rem; font-weight: 700; border: none;">
                            <i class="bi bi-shield-lock me-2"></i> Payer {{ number_format($ticket->montant, 0, ',', ' ') }} FCFA
                        </button>

                        <div class="text-center mt-3">
                            <img src="https://kkiapay.me/assets/images/kkiapay.png" alt="KKiaPay" style="height: 22px; opacity: 0.5;">
                        </div>

                        <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.78rem;">
                            <i class="bi bi-envelope me-1" style="color: #2E7D4F;"></i>
                            Votre billet PDF sera envoye a <strong>{{ $ticket->email_acheteur }}</strong>
                        </p>

                        {{-- Message d'erreur --}}
                        <div id="paymentError" class="mt-3" style="display: none; color: var(--danger); font-size: 0.85rem;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
document.getElementById('btnKkiaPay').addEventListener('click', function() {
    const btn = this;
    const errorDiv = document.getElementById('paymentError');
    errorDiv.style.display = 'none';

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Initialisation...';

    const callbackUrl = '{{ route('paiement.callback') }}?ticket={{ $ticket->id }}';

    if (typeof KKiaPay !== 'undefined') {
        KKiaPay.popup({
            key: '{{ config('services.kkiapay.api_key') }}',
            amount: {{ (int) $ticket->montant }},
            email: '{{ $ticket->email_acheteur }}',
            name: '{{ $ticket->nom_acheteur }}',
            phone: '{{ $ticket->telephone_acheteur }}',
            sandbox: {{ config('services.kkiapay.sandbox') ? 'true' : 'false' }},
            position: 'center',
            onSuccess: function(response) {
                var transactionId = response.transactionId || response.transaction_id || '';
                window.location.href = callbackUrl + '&transaction_id=' + transactionId;
            },
            onError: function(error) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-shield-lock me-2"></i> Payer {{ number_format($ticket->montant, 0, ',', ' ') }} FCFA';
                errorDiv.textContent = 'Le paiement a echoue. Veuillez reessayer.';
                errorDiv.style.display = 'block';
            },
            onClose: function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-shield-lock me-2"></i> Payer {{ number_format($ticket->montant, 0, ',', ' ') }} FCFA';
            }
        });
    } else {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-shield-lock me-2"></i> Payer {{ number_format($ticket->montant, 0, ',', ' ') }} FCFA';
        errorDiv.textContent = 'Erreur de chargement du module de paiement. Actualisez la page.';
        errorDiv.style.display = 'block';
    }
});
</script>

{{-- Fallback: charger le SDK KKiaPay si pas deja charge --}}
<script src="https://cdn.kkiapay.me/k.js"></script>
@endsection
