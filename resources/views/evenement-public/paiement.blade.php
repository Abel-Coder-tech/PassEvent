@extends('layouts.public')

@section('title', 'Paiement — ' . $ticket->evenement->titre . ' — PaxEvent')
@section('description', 'Finalisez votre achat de billets pour ' . $ticket->evenement->titre . ' sur PaxEvent. Paiement sécurisé.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('evenements.public') }}">Evenements</a></li>
    <li class="breadcrumb-item"><a href="{{ route('evenements.public.show', $ticket->evenement) }}">{{ Str::limit($ticket->evenement->titre, 30) }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Paiement</li>
@endsection

@section('content')
@php
    $evenement = $ticket->evenement;
    $textes = $evenement->getTextes();
    $premiereDate = $evenement->dates->first()?->date_debut;
    $infos = collect([
        $premiereDate ? Str::ucfirst($premiereDate->isoFormat('dddd D MMMM')) : null,
        $premiereDate?->format('H:i'),
        $evenement->lieu,
    ])->filter()->implode(' · ');
    if ($evenement->dates->count() > 1) {
        $infos = 'Du ' . $evenement->dates->first()->date_debut->isoFormat('D MMM') . ' au ' . $evenement->dates->last()->date_debut->isoFormat('D MMM') . ($evenement->lieu ? ' · ' . $evenement->lieu : '');
    }
    $fcfa = fn ($n) => $n <= 0 ? 'Gratuit' : number_format($n, 0, ',', ' ') . ' FCFA';
@endphp
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                @if(session('error'))
                    <div class="alert alert-danger py-2" style="font-size: 0.85rem; border-radius: 10px;">
                        <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
                    </div>
                @endif

                <div class="cmd-carte">
                    <div class="cmd-entete" @if($evenement->image) style="background-image:linear-gradient(180deg,rgba(33,28,49,.15),rgba(33,28,49,.85)),url('{{ asset('storage/' . $evenement->image) }}');" @endif>
                        <div class="cmd-kicker">Récapitulatif</div>
                        <h1 class="cmd-titre">{{ $evenement->titre }}</h1>
                    </div>
                    <div class="cmd-corps">
                        @if($infos)
                            <p class="cmd-infos">{{ $infos }}</p>
                        @endif

                        @foreach($lignesCommande as $ligne)
                            <div class="cmd-ligne">
                                <span>{{ $ligne['quantite'] }} × {{ $ligne['nom'] }}</span>
                                <strong>{{ $fcfa($ligne['montant']) }}</strong>
                            </div>
                        @endforeach

                        @if($ticket->code_promo_utilise)
                            <div class="cmd-ligne">
                                <span class="badge" style="background: rgba(84,38,128,0.1); color: #542680; font-size: 0.75rem;">
                                    <i class="bi bi-tag me-1"></i>Code promo : {{ $ticket->code_promo_utilise }}
                                </span>
                            </div>
                        @endif

                        <div class="cmd-total">
                            <span>Total</span>
                            <strong>{{ $fcfa($montantTotal) }}</strong>
                        </div>

                        <p class="cmd-envoi">
                            <i class="bi bi-envelope me-2"></i>
                            @if($nombreBillets > 1)
                                Les {{ $nombreBillets }} {{ mb_strtolower($textes['billet_pluriel']) }} seront envoyés à <strong>{{ $ticket->email_acheteur }}</strong>
                            @else
                                Votre {{ mb_strtolower($textes['billet']) }} PDF sera envoyé à <strong>{{ $ticket->email_acheteur }}</strong>
                            @endif
                        </p>

                        @if($publicKey)
                            <button type="button" id="btnFedaPay" class="show-btn-payer">
                                <i class="bi bi-shield-lock me-2"></i> Payer {{ number_format($montantTotal, 0, ',', ' ') }} FCFA
                            </button>
                        @else
                            <div class="alert alert-warning py-2 mb-0" style="border-radius: 10px; font-size: 0.85rem;">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Le paiement est temporairement indisponible. Réessayez dans quelques minutes.
                            </div>
                        @endif

                        <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                            <small class="text-muted" style="font-size: 0.75rem;">Paiement sécurisé par</small>
                            <strong style="color: #542680; font-size: 0.9rem;">FedaPay</strong>
                        </div>
                        <div class="text-center mt-2" style="font-size: 0.8rem; color: #999;">
                            <i class="bi bi-clock me-1"></i> Votre place est réservée pendant 15 minutes
                        </div>

                        <div id="paymentError" class="mt-3 text-center" style="display: none; color: var(--danger); font-size: 0.85rem;"></div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('evenements.public.show', $evenement) }}" style="color:#542680; font-size:0.85rem; font-weight:600; text-decoration:none;">
                        <i class="bi bi-arrow-left me-1"></i> Retour à l'événement
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.cmd-carte{background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 6px 24px rgba(33,28,49,.08)}
.cmd-entete{min-height:200px;display:flex;flex-direction:column;justify-content:flex-end;padding:1.25rem 1.5rem;background:linear-gradient(135deg,#542680,#9972B0);background-size:cover;background-position:center;color:#fff}
.cmd-kicker{font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;opacity:.85}
.cmd-titre{font-size:1.6rem;font-weight:800;margin:.2rem 0 0;color:#fff}
.cmd-corps{padding:1.25rem 1.5rem 1.5rem}
.cmd-infos{color:#6b6474;font-size:.92rem;margin-bottom:.9rem}
.cmd-ligne{display:flex;justify-content:space-between;gap:1rem;padding:.35rem 0;font-size:.95rem;color:#211C31}
.cmd-total{display:flex;justify-content:space-between;align-items:baseline;border-top:2px dashed #ddd6e3;margin-top:.8rem;padding-top:1rem;font-weight:700;color:#211C31}
.cmd-total strong{font-size:1.9rem;font-weight:800}
.cmd-envoi{font-size:.88rem;color:#4a4453;margin:.6rem 0 1.2rem}
.show-btn-payer{width:100%;border:0;border-radius:12px;padding:.95rem;background:#542680;color:#fff;font-weight:700;font-size:1rem}
.show-btn-payer:hover{background:#45206a}
</style>
@endsection

@section('scripts')
<script src="https://cdn.fedapay.com/checkout.js?v=1.1.3"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnFedaPay');
    const errorDiv = document.getElementById('paymentError');
    const callbackUrl = '{{ route('paiement.callback') }}?ticket={{ $ticket->id }}';

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
            amount: {{ (int) $montantTotal }},
            description: 'Ticket - {{ $ticket->evenement->titre }}',
            external_id: '{{ $ticket->id }}',
            custom_metadata: {
                ticket_id: '{{ $ticket->id }}',
                group_transaction_id: '{{ $ticket->transaction_id }}'
            }
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
            if (data.transaction && data.transaction.id) {
                var pm = data.transaction.payment_method || 'mobile_money';
                var ph = data.transaction.phone || '';
                window.location.href = callbackUrl + '&id=' + data.transaction.id + '&status=' + (data.transaction.status || 'approved') + '&payment_method=' + pm + '&phone=' + ph;
            } else {
                errorDiv.textContent = 'Paiement annulé ou fermé. Vous pouvez réessayer.';
                errorDiv.style.display = 'block';
            }
        }
    });
});
</script>
@endsection
