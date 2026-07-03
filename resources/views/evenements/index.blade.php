@extends('layouts.app')

@section('title', 'Événements')

@section('page-title', 'Mes événements')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item active" aria-current="page">Événements</li>
@endsection

@section('content')
<div class="page-content">
    <!-- Subtitle -->
    <p class="text-muted mb-4" style="font-size: 0.9rem; margin-top: -0.5rem;">
        {{ $totalEvenements }} événement{{ $totalEvenements > 1 ? 's' : '' }} · {{ number_format($totalBilletsVendus, 0, ',', ' ') }} billet{{ $totalBilletsVendus > 1 ? 's' : '' }} vendu{{ $totalBilletsVendus > 1 ? 's' : '' }}
    </p>

    <!-- Stat Cards -->
    <div class="row g-2 mt-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-label">Total événements</div>
                <div class="metric-value">{{ $totalEvenements }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-label">En cours</div>
                <div class="metric-value" style="color: var(--vert);">{{ $enCours }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--teal);">
                <div class="metric-label">À venir</div>
                <div class="metric-value" style="color: var(--teal);">{{ $aVenir }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--gris);">
                <div class="metric-label">Terminés</div>
                <div class="metric-value" style="color: var(--gris);">{{ $termines }}</div>
            </div>
        </div>
    </div>

    <!-- Toolbar: Search + Filters + Create -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2 w-100">
            <div class="position-relative w-100" style="max-width: 320px;">
                <i class="bi bi-search position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: var(--gris); font-size: 0.9rem;"></i>
                <input type="text" class="form-control ps-5 py-2 border rounded-3" id="searchInput" placeholder="Rechercher un événement…" style="border-color: #e2e0e4; font-size: 0.88rem;">
            </div>
            <div class="d-flex gap-2 flex-wrap" id="filterBtns">
                <button class="btn btn-sm filter-btn active" data-filter="all" style="border-radius: 20px; font-size: 0.82rem; padding: 0.35rem 1rem; border: 1px solid var(--vert); background: var(--vert); color: #fff;">Tous</button>
                <button class="btn btn-sm filter-btn" data-filter="en-cours" style="border-radius: 20px; font-size: 0.82rem; padding: 0.35rem 1rem; border: 1px solid #e2e0e4; color: var(--sombre); background: var(--blanc);">En cours</button>
                <button class="btn btn-sm filter-btn" data-filter="a-venir" style="border-radius: 20px; font-size: 0.82rem; padding: 0.35rem 1rem; border: 1px solid #e2e0e4; color: var(--sombre); background: var(--blanc);">À venir</button>
                <button class="btn btn-sm filter-btn" data-filter="termines" style="border-radius: 20px; font-size: 0.82rem; padding: 0.35rem 1rem; border: 1px solid #e2e0e4; color: var(--sombre); background: var(--blanc);">Terminés</button>
            </div>
        </div>
    </div>

    <!-- Event Cards List -->
    <div class="d-flex flex-column gap-3" id="eventsList">
        @forelse($evenements as $evenement)
            @php
                $now = now();
                $isPast = $evenement->date_event < $now;
                $isToday = $evenement->date_event->isToday();

                $ventes = $evenement->tickets_count ?? $evenement->quota_vendu;
                $capacite = $evenement->capacite;
                $remplissage = $capacite > 0 ? round(($ventes / $capacite) * 100) : 0;

                $revenus = $evenement->tickets()->where('statut_paiement', 'payé')->sum('montant');

                if ($evenement->statut === 'terminé') {
                    $statusLabel = 'Terminé';
                    $statusClass = 'status-termine';
                    $filterKey = 'termines';
                } elseif ($evenement->statut === 'annulé') {
                    $statusLabel = 'Annulé';
                    $statusClass = 'badge bg-danger';
                    $filterKey = 'termines';
                } elseif ($evenement->statut === 'brouillon') {
                    $statusLabel = 'À venir';
                    $statusClass = 'status-a-venir';
                    $filterKey = 'a-venir';
                } elseif ($evenement->statut === 'publié') {
                    if ($isToday) {
                        $statusLabel = 'En cours';
                        $statusClass = 'status-en-cours';
                        $filterKey = 'en-cours';
                    } elseif ($isPast) {
                        $statusLabel = 'Terminé';
                        $statusClass = 'status-termine';
                        $filterKey = 'termines';
                    } else {
                        $statusLabel = 'À venir';
                        $statusClass = 'status-a-venir';
                        $filterKey = 'a-venir';
                    }
                } else {
                    $statusLabel = ucfirst($evenement->statut);
                    $statusClass = 'status-brouillon';
                    $filterKey = 'a-venir';
                }

                $progressColor = $remplissage >= 80 ? 'var(--vert)' : ($remplissage >= 50 ? 'var(--teal)' : 'var(--menthe)');
            @endphp
            <div class="panel-card event-card" data-filter="{{ $filterKey }}" data-title="{{ strtolower($evenement->titre . ' ' . ($evenement->categorie ?? '')) }}">
                <div class="panel-card-body py-3">
                    <!-- Top row: Title + Badge -->
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="fw-bold mb-0" style="font-size: 1.05rem;">{{ $evenement->titre }}</h5>
                        <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>

                    <!-- Date + Location -->
                    <div class="text-muted mb-3" style="font-size: 0.88rem;">
                        <i class="bi bi-calendar3 me-1"></i>{{ $evenement->date_event->isoFormat('D MMM YYYY') }}
                        <span class="mx-2">·</span>
                        <i class="bi bi-geo-alt me-1"></i>{{ $evenement->lieu }}
                        @if($evenement->categorie)
                            <span class="mx-2">·</span>
                            <span class="badge" style="background: rgba(135,66,139,0.1); color: var(--violet); font-size: 0.75rem; border-radius: 12px;">
                                {{ ucfirst($evenement->categorie) }}
                            </span>
                        @endif
                    </div>

                    <!-- Sales progress + Revenues -->
                    <div class="row align-items-center g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span style="font-size: 0.85rem; font-weight: 600;">{{ $ventes }} / {{ $capacite }} billets</span>
                                <span style="font-size: 0.85rem; font-weight: 700; color: {{ $progressColor }};">{{ $remplissage }}%</span>
                            </div>
                            <div class="progress-bar-custom">
                                <div class="progress-bar-fill" style="width: {{ $remplissage }}%; background: {{ $progressColor }};"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 text-md-end">
                            <span style="font-size: 0.8rem; color: var(--gris);">Revenus encaissés</span>
                            <div class="fw-bold" style="font-size: 1rem; color: var(--vert);">
                                {{ number_format($revenus, 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="d-flex flex-wrap justify-content-end gap-2">
                        <a href="{{ route('admin.evenements.show', $evenement->id) }}" class="btn btn-sm btn-secondary-custom" style="border-radius: 6px;">
                            <i class="bi bi-eye"></i>
                        </a>
                        <form action="{{ route('admin.evenements.destroy', $evenement->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet événement ? Cette action est irréversible.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm" style="border-radius: 6px; border: 1px solid #e74c3c; color: #e74c3c; background: transparent;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        <a href="{{ route('admin.evenements.edit', $evenement->id) }}" class="btn btn-sm btn-secondary-custom" style="border-radius: 6px;">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if($evenement->statut === 'publié')
                            <a href="{{ route('scan.index') }}" class="btn btn-sm" style="border-radius: 6px; background: var(--vert); color: #fff; border: none;">
                                <i class="bi bi-qr-code-scan me-1"></i>Scan
                            </a>
                        @elseif($evenement->statut === 'brouillon' || $evenement->statut === 'publié' && !$isPast)
                            <form action="{{ route('admin.evenements.destroy', $evenement->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Annuler cet événement ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm" style="border-radius: 6px; border: 1px solid #e74c3c; color: #e74c3c; background: transparent;">
                                    <i class="bi bi-x-circle me-1"></i>Annuler
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="panel-card text-center py-5">
                <i class="bi bi-calendar-x d-block mb-3" style="font-size: 3rem; color: var(--gris);"></i>
                <h5 class="text-muted">Aucun événement</h5>
                <p class="text-muted">Commencez par créer votre premier événement.</p>
                <a href="{{ route('admin.evenements.create') }}" class="btn btn-primary-custom" style="border-radius: 8px;">
                    <i class="bi bi-plus-lg me-1"></i> Créer un événement
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($evenements->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $evenements->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const cards = document.querySelectorAll('.event-card');

    function filterEvents() {
        const query = searchInput.value.toLowerCase().trim();
        const activeFilter = document.querySelector('.filter-btn.active').dataset.filter;

        cards.forEach(card => {
            const title = card.dataset.title;
            const filter = card.dataset.filter;

            const matchesSearch = title.includes(query);
            const matchesFilter = activeFilter === 'all' || filter === activeFilter;

            card.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterEvents);

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => {
                b.classList.remove('active');
                b.style.background = 'var(--blanc)';
                b.style.color = 'var(--sombre)';
                b.style.borderColor = '#e2e0e4';
            });
            this.classList.add('active');
            this.style.background = 'var(--vert)';
            this.style.color = '#fff';
            this.style.borderColor = 'var(--vert)';
            filterEvents();
        });
    });
});
</script>
@endsection
