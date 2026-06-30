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
                            <h4 class="fw-bold mb-2">{{ $ticket->montant <= 0 ? 'Inscription confirmee !' : 'Paiement confirme !' }}</h4>
                            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                {{ $ticket->montant <= 0 ? 'Votre participation a ete enregistree avec succes.' : 'Votre billet a ete genere avec succes.' }}<br>
                                Il a ete envoye par email a <strong>{{ $ticket->email_acheteur }}</strong>
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
                                <div class="col-6">
                                    <span class="text-muted">Type :</span><br>
                                    <strong>{{ ucfirst($ticket->categorie) }} / {{ ucfirst($ticket->type) }}</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Code :</span><br>
                                    <code class="fw-bold">{{ $ticket->code_unique }}</code>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="text-muted">Montant :</span><br>
                                    <strong style="color: var(--violet);">{{ number_format($ticket->montant, 0, ',', ' ') }} F</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2">
                            <a href="{{ route('tickets.telecharger', $ticket->id) }}" class="btn btn-violet py-3" style="border-radius: 8px;">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Telecharger mon billet PDF
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
