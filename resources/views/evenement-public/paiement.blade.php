@extends('layouts.public')

@section('title', 'Paiement — ' . $ticket->evenement->titre . ' — PaxEvent')
@section('description', 'Finalisez votre achat de billets pour ' . $ticket->evenement->titre . ' sur PaxEvent. Paiement sécurisé.')

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
                            <i class="bi bi-receipt me-2" style="color: #542680;"></i>
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
                                    <strong style="color: #542680; font-size: 1.1rem;">{{ number_format($ticket->montant, 0, ',', ' ') }} FCFA</strong>
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
                                        <span class="badge" style="background: rgba(84,38,128,0.1); color: #542680; font-size: 0.75rem;">
                                            <i class="bi bi-tag me-1"></i>Code promo: {{ $ticket->code_promo_utilise }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($ticket->montant_reduction > 0)
                            <div class="alert py-2 mb-3" style="background: rgba(46,125,79,0.06); border: 1px solid rgba(46,125,79,0.15); border-radius: 8px; font-size: 0.82rem;">
                                <i class="bi bi-check-circle me-1" style="color: #542680;"></i>
                                Reduction appliquee: <strong>-{{ number_format($ticket->montant_reduction, 0, ',', ' ') }} F</strong>
                            </div>
                        @endif

                        <div class="text-center" style="font-size: 0.82rem; color: #999;">
                            <i class="bi bi-clock me-1"></i>
                            Votre place est reservée pendant 15 minutes
                        </div>
                    </div>
                </div>

                {{-- Paiement KKiaPay --}}
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold mb-3" style="color: #333;">
                            <i class="bi bi-wallet2 me-2" style="color: #542680;"></i>
                            Paiement securisé via KKiaPay
                        </h5>

                        {{-- Bouton paiement --}}
                        @if($kkiapayKey)
                            <button type="button" id="btnKkiaPay" class="btn w-100 py-3" style="background: #542680; color: #fff; border-radius: 10px; font-size: 1rem; font-weight: 700; border: none;">
                                <i class="bi bi-shield-lock me-2"></i> Payer {{ number_format($ticket->montant, 0, ',', ' ') }} FCFA
                            </button>
                        @else
                            <div class="alert alert-warning py-2 mb-0" style="border-radius: 10px; font-size: 0.85rem;">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                L'organisateur n'a pas encore configuré sa méthode de paiement.
                            </div>
                        @endif

                        <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                            <small class="text-muted" style="font-size: 0.75rem;">Paiement securisé par</small>
                            <img src="https://kkiapay.me/wp-content/uploads/2024/04/footer-logo.svg" alt="KKiaPay" style="height: 22px;">
                        </div>

                        <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.78rem;">
                            <i class="bi bi-envelope me-1" style="color: #542680;"></i>
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
{{-- SDK KKiaPay --}}
<script src="https://cdn.kkiapay.me/k.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnKkiaPay');
    const errorDiv = document.getElementById('paymentError');
    const callbackUrl = '{{ route('paiement.callback') }}?ticket={{ $ticket->id }}';

    if (!btn) return;

    btn.addEventListener('click', function() {
        errorDiv.style.display = 'none';

        if (typeof openKkiapayWidget === 'undefined') {
            errorDiv.textContent = 'Erreur de chargement du module de paiement. Actualisez la page.';
            errorDiv.style.display = 'block';
            return;
        }

        openKkiapayWidget({
            key: '{{ $kkiapayKey }}',
            amount: {{ (int) $ticket->montant }},
            email: '{{ $ticket->email_acheteur }}',
            name: '{{ $ticket->nom_acheteur }}',
            phone: '{{ $ticket->telephone_acheteur }}',
            sandbox: {{ $kkiapaySandbox ? 'true' : 'false' }},
            position: 'center',
            callback: callbackUrl,
        });
    });
});
</script>
@endsection
