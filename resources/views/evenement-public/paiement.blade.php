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
                <!-- Recapitulatif -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-receipt me-2" style="color: var(--violet);"></i>
                            Recapitulatif
                        </h5>

                        <div class="p-3 rounded mb-3" style="background: var(--blanc-casse);">
                            <div class="mb-2">
                                <strong>{{ $ticket->evenement->titre }}</strong>
                            </div>
                            <div class="row g-2" style="font-size: 0.9rem;">
                                <div class="col-6">
                                    <span class="text-muted">Tarif :</span><br>
                                    <strong>{{ $ticket->categorie }} / {{ $ticket->type === 'normal' ? 'Standard' : 'VIP' }}</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="text-muted">Montant :</span><br>
                                    <strong style="color: var(--vert);">{{ number_format($ticket->montant, 0, ',', ' ') }} FCFA</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Acheteur :</span><br>
                                    <strong>{{ $ticket->nom_acheteur }}</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="text-muted">Code :</span><br>
                                    <code class="fw-bold">{{ $ticket->code_unique }}</code>
                                </div>
                                @if($ticket->code_promo_utilise)
                                    <div class="col-12">
                                        <span class="badge" style="background: rgba(135,66,139,0.12); color: var(--violet); font-size: 0.75rem;">
                                            <i class="bi bi-tag me-1"></i>Code promo: {{ $ticket->code_promo_utilise }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($ticket->montant_reduction > 0)
                            <div class="alert" style="background: rgba(18,151,110,0.08); border: 1px solid rgba(18,151,110,0.2); border-radius: 8px; padding: 0.5rem 0.75rem; font-size: 0.82rem;">
                                <i class="bi bi-check-circle me-1" style="color: var(--vert);"></i>
                                Reduction appliquee: <strong>-{{ number_format($ticket->montant_reduction, 0, ',', ' ') }} F</strong>
                            </div>
                        @endif

                        <div class="text-center" style="font-size: 0.82rem; color: var(--gris);">
                            <i class="bi bi-clock me-1"></i>
                            Votre place est reservee pendant 15 minutes
                        </div>
                    </div>
                </div>

                <!-- KKiaPay Button -->
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-wallet2 me-2" style="color: var(--vert);"></i>
                            Paiement securise via KKiaPay
                        </h5>

                        @if(config('services.kkiapay.sandbox'))
                            <div class="alert alert-info py-2 mb-3" style="font-size: 0.8rem; border-radius: 8px;">
                                <i class="bi bi-info-circle me-1"></i>
                                Mode <strong>TEST (sandbox)</strong> — Aucun argent ne sera debite.
                            </div>
                        @endif

                        <button type="button" class="btn btn-vert w-100 py-3 kkiapay-button" style="border-radius: 8px; font-size: 1rem; font-weight: 600;"
                            data-amount="{{ (int) $ticket->montant }}"
                            data-key="{{ config('services.kkiapay.app_id') }}"
                            data-email="{{ $ticket->email_acheteur }}"
                            data-name="{{ $ticket->nom_acheteur }}"
                            data-phone="{{ $ticket->telephone_acheteur }}"
                            data-sandbox="true"
                            data-position="center"
                            data-callback="{{ route('paiement.callback') }}?ticket={{ $ticket->id }}"
                        >
                            <i class="bi bi-shield-lock me-2"></i> Payer {{ number_format($ticket->montant, 0, ',', ' ') }} FCFA
                        </button>

                        <div class="text-center mt-3">
                            <img src="https://kkiapay.me/assets/images/kkiapay.png" alt="KKiaPay" style="height: 24px; opacity: 0.6;">
                        </div>

                        <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.78rem;">
                            <i class="bi bi-envelope me-1" style="color: var(--vert);"></i>
                            Votre billet PDF sera envoye a <strong>{{ $ticket->email_acheteur }}</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script src="https://cdn.kkiapay.me/k.js"></script>
<script>
addSuccessListener(function(response) {
    console.log('Payment success:', response);
    window.location.href = "{{ route('paiement.callback') }}?ticket={{ $ticket->id }}&transaction_id=" + (response.transactionId || response.transaction_id || '');
});

addPaymentAbortedListener(function() {
    console.log('Payment aborted');
});

addFailedListener(function() {
    console.log('Payment failed');
    alert('Le paiement a echoue. Veuillez reessayer.');
});
</script>
@endsection
