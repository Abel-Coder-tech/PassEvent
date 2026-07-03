@extends('layouts.app')

@section('title', 'Gestion des tickets')

@section('page-title', 'Gestion des tickets')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item active" aria-current="page">Tickets</li>
@endsection

@section('content')
<div class="page-content">
    <!-- Header -->
    <p class="text-muted mb-4" style="font-size: 0.9rem; margin-top: -0.5rem;">
        {{ $totalTickets }} ticket{{ $totalTickets > 1 ? 's' : '' }} genere{{ $totalTickets > 1 ? 's' : '' }} &middot; Tous evenements confondus &middot; Prestataire <strong>KKiaPay</strong>
    </p>

    <!-- Stat Cards -->
    <div class="row g-2 mt-3 mb-4">
        <div class="col-6 col-md">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.08); color: var(--violet);">
                    <i class="bi bi-ticket-perforated"></i>
                </div>
                <div class="metric-label">Total tickets</div>
                <div class="metric-value">{{ $totalTickets }}</div>
                <div class="metric-subtitle">tous evenements</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.08); color: var(--vert);">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="metric-label">Valides</div>
                <div class="metric-value" style="color: var(--vert);">{{ $valides }}</div>
                <div class="metric-subtitle">non encore scannes</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="metric-card" style="border-top-color: var(--teal);">
                <div class="metric-icon" style="background: rgba(66,140,121,0.08); color: var(--teal);">
                    <i class="bi bi-qr-code-scan"></i>
                </div>
                <div class="metric-label">Scannes</div>
                <div class="metric-value" style="color: var(--teal);">{{ $scannes }}</div>
                <div class="metric-subtitle">entrees validees</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="metric-card" style="border-top-color: var(--aubergine);">
                <div class="metric-icon" style="background: rgba(109,78,114,0.08); color: var(--aubergine);">
                    <i class="bi bi-mortarboard"></i>
                </div>
                <div class="metric-label">Etudiants</div>
                <div class="metric-value" style="color: var(--aubergine);">{{ $etudiants }}</div>
                <div class="metric-subtitle">avec code promo</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="metric-card" style="border-top-color: var(--danger);">
                <div class="metric-icon" style="background: rgba(231,76,60,0.08); color: var(--danger);">
                    <i class="bi bi-x-octagon"></i>
                </div>
                <div class="metric-label">Annules</div>
                <div class="metric-value" style="color: var(--danger);">{{ $annules }}</div>
                <div class="metric-subtitle">rembourses</div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="panel-card mb-4">
        <div class="panel-card-body py-3">
            <form action="{{ route('tickets.index') }}" method="GET">
                <div class="row g-2 align-items-center">
                    <div class="col-md-8">
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--gris); font-size: 0.95rem;"></i>
                            <input type="text" name="q" class="form-control ps-5 py-2" placeholder="Rechercher par nom, telephone, QR code..." value="{{ $search ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-vert px-3" style="border-radius: 8px;">
                            <i class="bi bi-funnel me-1"></i> Filtrer
                        </button>
                        @if($search)
                            <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary px-3" style="border-radius: 8px;">
                                <i class="bi bi-x-lg me-1"></i> Effacer
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h5><i class="bi bi-list-ul me-2"></i>Liste des tickets</h5>
            @if($search)
                <span class="badge" style="background: rgba(135,66,139,0.1); color: var(--violet);">
                    <i class="bi bi-search me-1"></i> "{{ $search }}"
                </span>
            @endif
        </div>
        <div class="panel-card-body p-0">
            @if($tickets->isNotEmpty())
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Participant</th>
                                <th>Evenement</th>
                                <th>QR code</th>
                                <th>Type</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th class="pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                                @php
                                    $now = now();
                                    $isPaid = $ticket->statut_paiement === 'payé';
                                    $isScanned = $ticket->utilise;
                                    $isCancelled = in_array($ticket->statut_paiement, ['annulé', 'remboursé']);
                                    $isPending = $ticket->statut_paiement === 'en_attente';

                                    if ($isCancelled) {
                                        $badgeClass = 'bg-danger';
                                        $statusLabel = ucfirst($ticket->statut_paiement);
                                    } elseif ($isScanned) {
                                        $badgeClass = 'status-badge status-termine';
                                        $statusLabel = 'Scanne';
                                    } elseif ($isPaid) {
                                        $badgeClass = 'status-badge status-en-cours';
                                        $statusLabel = 'Valide';
                                    } elseif ($isPending) {
                                        $badgeClass = 'badge bg-warning text-dark';
                                        $statusLabel = 'En attente';
                                    } else {
                                        $badgeClass = 'badge bg-secondary';
                                        $statusLabel = ucfirst($ticket->statut_paiement);
                                    }
                                @endphp
                                <tr class="border-bottom">
                                    <td class="ps-3">
                                        <div class="fw-bold" style="font-size: 0.85rem;">{{ $ticket->nom_acheteur }}</div>
                                        <small class="text-muted">{{ $ticket->telephone_acheteur }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold" style="font-size: 0.85rem;">{{ $ticket->evenement?->titre ?? '—' }}</div>
                                        <small class="text-muted">{{ $ticket->evenement?->date_event?->isoFormat('D MMM YYYY') ?? '—' }}</small>
                                    </td>
                                    <td>
                                        <code class="fw-bold" style="font-size: 0.82rem; color: var(--violet);">{{ $ticket->code_unique }}</code>
                                    </td>
                                    @if($ticket->montant > 0)
                                    <td>
                                        <span class="badge me-1" style="background: {{ $ticket->categorie === 'etudiant' ? 'rgba(135,66,139,0.1)' : 'rgba(66,140,121,0.1)' }}; color: {{ $ticket->categorie === 'etudiant' ? 'var(--violet)' : 'var(--teal)' }};">
                                            {{ ucfirst($ticket->categorie) }}
                                        </span>
                                        <small class="text-muted d-block">{{ ucfirst($ticket->type) }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-bold" style="color: var(--vert);">{{ number_format($ticket->montant, 0, ',', ' ') }} F</span>
                                    </td>
                                    @else
                                    <td colspan="2" class="text-muted small">Entrée gratuite</td>
                                    @endif
                                    <td>
                                        <span class="{{ $badgeClass }}" style="{{ is_string($badgeClass) && str_contains($badgeClass, 'status-badge') ? '' : '' }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="pe-3">
                                        <div class="d-flex gap-1" style="white-space: nowrap;">
                                            <a href="{{ route('tickets.pdf', $ticket->id) }}" class="btn-icon" title="Telecharger PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                            <a href="{{ route('tickets.show', $ticket->id) }}" class="btn-icon" title="Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if(!$isCancelled)
                                                <form action="{{ route('tickets.renvoyer', $ticket->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn-icon btn-icon-vert" title="Renvoyer par email">
                                                        <i class="bi bi-envelope"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if($isPaid || $isPending)
                                                <form action="{{ route('tickets.annuler', $ticket->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Annuler ce ticket ? Cette action est irreversible.');">
                                                    @csrf
                                                    <button type="submit" class="btn-icon btn-icon-danger" title="Annuler">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if($isCancelled)
                                                <a href="{{ route('tickets.show', $ticket->id) }}" class="btn-icon btn-icon-gris" title="Voir les logs">
                                                    <i class="bi bi-clock-history"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($tickets->hasPages())
                    <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                        <span class="text-muted" style="font-size: 0.82rem;">
                            Affichage {{ $tickets->firstItem() }} a {{ $tickets->lastItem() }} sur {{ $tickets->total() }} tickets
                        </span>
                        <nav>
                            {{ $tickets->links() }}
                        </nav>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="bi bi-ticket-perforated d-block mb-3" style="font-size: 3rem; color: var(--gris);"></i>
                    <h5 class="text-muted">Aucun ticket trouve</h5>
                    <p class="text-muted">
                        @if($search)
                            Aucun resultat pour "{{ $search }}". Essayez un autre terme.
                        @else
                            Les tickets generes apparaîtront ici.
                        @endif
                    </p>
                    @if($search)
                        <a href="{{ route('tickets.index') }}" class="btn btn-violet btn-sm" style="border-radius: 8px;">
                            <i class="bi bi-arrow-left me-1"></i> Voir tous les tickets
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
