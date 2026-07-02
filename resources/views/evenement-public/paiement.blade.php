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
            <div class="col-12 col-md-8 col-lg-6">
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

                {{-- Sélecteur de méthode de paiement --}}
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 text-center" style="color: #333;">
                            <i class="bi bi-wallet2 me-2" style="color: #542680;"></i>
                            Choisissez votre mode de paiement
                        </h5>

                        @if(empty($methodes))
                            <div class="alert alert-warning py-2 mb-0" style="border-radius: 10px; font-size: 0.85rem; text-align: center;">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Aucune methode de paiement disponible pour le moment.
                            </div>
                        @else
                            {{-- Cartes des méthodes --}}
                            <div class="row g-3 mb-4">
                                @foreach($methodes as $cle => $methode)
                                    <div class="col-{{ count($methodes) >= 3 ? '4' : (count($methodes) === 2 ? '6' : '12') }}">
                                        <div class="methode-card {{ $loop->first ? 'selected' : '' }}"
                                             data-methode="{{ $cle }}"
                                             onclick="selectionnerMethode(this, '{{ $cle }}')">
                                            <div class="methode-icon">
                                                <i class="bi {{ $methode['icone'] ?? 'bi-credit-card' }}"></i>
                                            </div>
                                            <div class="methode-nom">{{ $methode['nom'] }}</div>
                                            <div class="methode-desc">{{ $methode['description'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Paiement KKiaPay --}}
                            @if(isset($methodes['kkiapay']))
                            <div id="section-kkiapay" class="methode-section">
                                @if($kkiapayKey)
                                    <button type="button" id="btnKkiaPay" class="btn w-100 py-3 btn-methode" style="background: {{ $methodes['kkiapay']['couleur'] ?? '#542680' }};">
                                        <i class="bi bi-shield-lock me-2"></i> Payer {{ number_format($ticket->montant, 0, ',', ' ') }} FCFA
                                    </button>
                                    <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                                        <small class="text-muted" style="font-size: 0.75rem;">Paiement securisé par</small>
                                        <img src="https://kkiapay.me/wp-content/uploads/2024/04/footer-logo.svg" alt="KKiaPay" style="height: 22px;">
                                    </div>
                                @else
                                    <div class="alert alert-warning py-2 mb-0" style="border-radius: 10px; font-size: 0.85rem; text-align: center;">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Ce mode de paiement n'est pas configuré.
                                    </div>
                                @endif
                            </div>
                            @endif

                            {{-- Paiement Sebpay --}}
                            @if(isset($methodes['sebpay']))
                            <div id="section-sebpay" class="methode-section" style="display:none;">
                                @if($sebpayConfigured)
                                    <button type="button" id="btnSebpay" class="btn w-100 py-3 btn-methode" style="background: {{ $methodes['sebpay']['couleur'] ?? '#198754' }};">
                                        <i class="bi bi-shield-lock me-2"></i> Payer {{ number_format($ticket->montant, 0, ',', ' ') }} FCFA
                                    </button>
                                    <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                                        <small class="text-muted" style="font-size: 0.75rem;">Paiement securisé par</small>
                                        <strong style="font-size: 0.82rem; color: {{ $methodes['sebpay']['couleur'] ?? '#198754' }};">Sebpay</strong>
                                    </div>
                                @else
                                    <div class="alert alert-warning py-2 mb-0" style="border-radius: 10px; font-size: 0.85rem; text-align: center;">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Ce mode de paiement n'est pas configuré.
                                    </div>
                                @endif
                            </div>
                            @endif

                            <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.78rem;">
                                <i class="bi bi-envelope me-1" style="color: #542680;"></i>
                                Votre billet PDF sera envoye a <strong>{{ $ticket->email_acheteur }}</strong>
                            </p>

                            <div id="paymentError" class="mt-3" style="display: none; color: var(--danger); font-size: 0.85rem; text-align: center;"></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .methode-card {
        border: 2px solid #e0dde3;
        border-radius: 14px;
        padding: 1.25rem 0.75rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        height: 100%;
        background: #fff;
    }
    .methode-card:hover {
        border-color: #9972B0;
        background: #faf8fb;
        transform: translateY(-2px);
    }
    .methode-card.selected {
        border-color: #542680;
        background: #f5f0f9;
        box-shadow: 0 0 0 3px rgba(84,38,128,0.1);
    }
    .methode-icon {
        font-size: 1.8rem;
        color: #542680;
        margin-bottom: 0.4rem;
    }
    .methode-nom {
        font-weight: 700;
        font-size: 0.95rem;
        color: #1d1d1f;
        margin-bottom: 0.2rem;
    }
    .methode-desc {
        font-size: 0.72rem;
        color: #6c757d;
        line-height: 1.3;
    }
    .methode-section {
        animation: fadeIn 0.3s ease;
    }
    .btn-methode {
        color: #fff;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 700;
        border: none;
        transition: all 0.2s;
    }
    .btn-methode:hover {
        transform: translateY(-1px);
        filter: brightness(1.1);
        color: #fff;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('scripts')
{{-- SDK KKiaPay --}}
<script src="https://cdn.kkiapay.me/k.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const errorDiv = document.getElementById('paymentError');

    window.selectionnerMethode = function(el, methode) {
        document.querySelectorAll('.methode-card').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        document.querySelectorAll('.methode-section').forEach(s => s.style.display = 'none');
        const section = document.getElementById('section-' + methode);
        if (section) section.style.display = 'block';
    };

    @if(isset($methodes['kkiapay']) && $kkiapayKey)
    const btnKkia = document.getElementById('btnKkiaPay');
    if (btnKkia) {
        const callbackUrl = '{{ route('paiement.callback') }}?ticket={{ $ticket->id }}';
        btnKkia.addEventListener('click', function() {
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
    }
    @endif

    @if(isset($methodes['sebpay']) && $sebpayConfigured)
    const btnSebpay = document.getElementById('btnSebpay');
    if (btnSebpay) {
        btnSebpay.addEventListener('click', function() {
            errorDiv.style.display = 'none';
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('paiement.sebpay.init') }}';
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            const ticket = document.createElement('input');
            ticket.type = 'hidden';
            ticket.name = 'ticket_id';
            ticket.value = '{{ $ticket->id }}';
            form.appendChild(ticket);
            document.body.appendChild(form);
            form.submit();
        });
    }
    @endif
});
</script>
@endsection
