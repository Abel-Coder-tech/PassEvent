@extends('layouts.public')

@section('title', 'Paiement confirmé — PaxEvent')
@section('description', 'Votre paiement a été confirmé. Votre billet vous sera envoyé par email.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('evenements.public') }}">Evenements</a></li>
    <li class="breadcrumb-item active" aria-current="page">Confirmation</li>
@endsection

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4 text-center">
                        <div class="mb-4">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; background: rgba(18,151,110,0.1);">
                                <i class="bi bi-check-circle-fill" style="font-size: 2.5rem; color: var(--violet);"></i>
                            </div>
                            @php $quantite = $groupTickets->count(); @endphp
                            <h4 class="fw-bold mb-2">{{ $ticket->montant <= 0 ? 'Inscription confirmee !' : 'Paiement confirme !' }}</h4>
                            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                {{ $quantite > 1 ? "Vos {$quantite} billets ont ete generes avec succes." : 'Votre billet a ete genere avec succes.' }}<br>
                                Il{{ $quantite > 1 ? 's' : '' }} ont ete envoye{{ $quantite > 1 ? 's' : '' }} par email a <strong>{{ $ticket->email_acheteur }}</strong>
                            </p>
                        </div>

                        <!-- Ticket details -->
                        <div class="p-3 rounded mb-4 text-start" style="background: var(--blanc-casse);">
                            <div class="row g-2" style="font-size: 0.88rem;">
                                <div class="col-12">
                                    <span class="text-muted">Evenement :</span><br>
                                    <strong>{{ $ticket->evenement->titre }}</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Date :</span><br>
                                    <strong>{{ $ticket->evenement->date_event->format('d/m/Y') }}</strong>
                                </div>
                                @if($ticket->montant > 0)
                                <div class="col-6">
                                    <span class="text-muted">Type :</span><br>
                                    <strong>{{ ucfirst($ticket->categorie) }} / {{ ucfirst($ticket->type) }}</strong>
                                </div>
                                @endif
                            </div>

                            @if($quantite > 1)
                            <div class="mt-3 pt-2" style="border-top: 1px solid #eee;">
                                <span class="text-muted" style="font-size: 0.82rem;">Codes des billets ({{ $quantite }}) :</span><br>
                                @foreach($groupTickets as $gt)
                                    <code class="fw-bold" style="font-size: 0.78rem; display: block; margin-top: 2px;">{{ $gt->code_unique }}</code>
                                @endforeach
                            </div>
                            @else
                            <div class="mt-2">
                                <span class="text-muted">Code :</span><br>
                                <code class="fw-bold">{{ $ticket->code_unique }}</code>
                            </div>
                            @endif

                            @if($ticket->montant > 0)
                            <div class="mt-2 text-end">
                                <span class="text-muted">Total paye :</span><br>
                                @if($quantite > 1)
                                    <strong style="color: var(--violet);">{{ number_format($groupTickets->sum('montant'), 0, ',', ' ') }} F</strong>
                                    <small class="d-block text-muted" style="font-size: 0.72rem;">{{ number_format($ticket->montant, 0, ',', ' ') }} F × {{ $quantite }} billet(s)</small>
                                @else
                                    <strong style="color: var(--violet);">{{ number_format($ticket->montant, 0, ',', ' ') }} F</strong>
                                @endif
                            </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2">
                            <a href="{{ route('tickets.telecharger', $ticket->id) }}" class="btn btn-violet py-3" style="border-radius: 8px;">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Telecharger le billet PDF
                            </a>
                            <a href="{{ route('accueil') }}" class="btn btn-accent py-2" style="border-radius: 8px;">
                                <i class="bi bi-house me-1"></i> Retour a l'accueil
                            </a>
                            <a href="{{ route('evenements.public') }}" class="btn btn-outline-secondary py-2" style="border-radius: 8px;">
                                <i class="bi bi-calendar-event me-1"></i> Voir d'autres evenements
                            </a>
                        </div>

                        <p class="text-muted mt-3 mb-0" style="font-size: 0.78rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Presentez votre billet (impression ou ecran) a l'entree de l'evenement
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
