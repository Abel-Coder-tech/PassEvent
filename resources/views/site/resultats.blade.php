@extends('layouts.public')

@section('title', 'Resultats - PassEvent')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tickets.recuperer') }}">Mon ticket</a></li>
    <li class="breadcrumb-item active" aria-current="page">Resultats</li>
@endsection

@section('content')
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h4 class="fw-bold mb-0">
                <i class="bi bi-ticket-perforated me-2" style="color: var(--violet);"></i>
                {{ count($tickets) }} billet(s) trouve(s)
            </h4>
            <a href="{{ route('tickets.recuperer') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px;">
                <i class="bi bi-arrow-left me-1"></i> Nouvelle recherche
            </a>
        </div>

        <div class="row g-3">
            @foreach($tickets as $ticket)
                @php
                    $isPaid = $ticket->statut_paiement === 'payé';
                @endphp
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid {{ $isPaid ? 'var(--vert)' : 'var(--gris)' }};">
                        <div class="card-body p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                                <div>
                                    <h6 class="fw-bold mb-1">{{ $ticket->evenement->titre }}</h6>
                                    <small class="text-muted">{{ $ticket->evenement->date_event->format('d M Y') }} - {{ $ticket->evenement->lieu }}</small>
                                </div>
                                @php
                                    $statusBadge = match($ticket->statut_paiement) {
                                        'payé' => 'badge-publie',
                                        'en_attente' => 'badge bg-warning text-dark',
                                        'annulé', 'remboursé' => 'badge bg-danger',
                                        default => 'badge bg-secondary',
                                    };
                                @endphp
                                <span class="{{ $statusBadge }}">{{ ucfirst(str_replace('_', ' ', $ticket->statut_paiement)) }}</span>
                            </div>

                            <hr>

                            <div class="row g-2 mb-3" style="font-size: 0.88rem;">
                                <div class="col-12">
                                    <span class="text-muted">Acheteur :</span><br>
                                    <strong>{{ $ticket->nom_acheteur }}</strong>
                                    <span class="text-muted ms-3">Email :</span>
                                    <strong>{{ $ticket->email_acheteur }}</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Type :</span><br>
                                    <strong>{{ ucfirst($ticket->categorie) }} / {{ ucfirst($ticket->type) }}</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="text-muted">Montant :</span><br>
                                    <strong style="color: var(--vert);">{{ number_format($ticket->montant, 0, ',', ' ') }} F</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Code :</span><br>
                                    <code class="fw-bold">{{ $ticket->code_unique }}</code>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="text-muted">Scan :</span><br>
                                    <strong>{{ $ticket->utilise ? 'Scanne' : 'Non utilise' }}</strong>
                                </div>
                            </div>

                            @if($isPaid)
                                <div class="d-flex gap-2">
                                    <a href="{{ route('tickets.telecharger', $ticket->id) }}" class="btn btn-violet btn-sm" style="border-radius: 6px;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Telecharger PDF
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
