@extends('layouts.app')

@section('title', 'Statistiques - PaxEvent')

@section('page-title', 'Statistiques')

@section('styles')
<style>
    @media (max-width: 575.98px) {
        .top-bar-right { flex-wrap: nowrap; gap: .35rem; }
        .top-bar-right .btn-reduire-commission .btn-text,
        .top-bar-right .btn-vert .btn-text { display: inline; }
        .top-bar-right .btn-reduire-commission { white-space: nowrap; }
    }
</style>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item active" aria-current="page">Statistiques</li>
@endsection

@section('topbar-actions')
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-reduire-commission" style="background:var(--violet-clair);color:#fff;border-radius:8px;font-weight:600;font-size:0.78rem;" onclick="openDemande('reduction_commission')">
            <i class="bi bi-percent me-1"></i> <span class="btn-text">Réduire<span class="d-none d-sm-inline"> ma commission</span></span>
        </button>
    </div>
@endsection

@section('content')
<div class="page-content">
    <p class="text-muted mb-4" style="font-size: 0.9rem;">
        <i class="bi bi-calendar3 me-1"></i>
        Période sélectionnée : <strong>{{ $stats['periode_label'] }}</strong>
        <span class="mx-1">·</span> Profil : <strong>{{ Auth::user()->type_libelle }}</strong>
    </p>

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="metric-icon" style="background: rgba(18,151,110,0.1); color: var(--vert);">
                        <i class="bi bi-ticket-perforated"></i>
                    </div>
                    <span class="badge {{ $stats['tickets_vendus_evolution'] >= 0 ? 'bg-success' : 'bg-danger' }}" style="font-size: 0.7rem; white-space: nowrap;">
                        {{ $stats['tickets_vendus_evolution'] >= 0 ? '+' : '-' }}{{ abs($stats['tickets_vendus_evolution']) }}%
                    </span>
                </div>
                <div class="metric-label">Tickets vendus</div>
                <div class="metric-value" style="font-size: 1.5rem;">{{ number_format($stats['tickets_vendus']) }}</div>
                <div class="metric-subtitle">vs {{ $stats['periode_label'] }} precedent</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="metric-icon" style="background: rgba(123,63,160,0.1); color: var(--violet);">
                        <i class="bi bi-currency-exchange"></i>
                    </div>
                    <span class="badge {{ $stats['revenus_evolution'] >= 0 ? 'bg-success' : 'bg-danger' }}" style="font-size: 0.7rem; white-space: nowrap;">
                        {{ $stats['revenus_evolution'] >= 0 ? '+' : '-' }}{{ abs($stats['revenus_evolution']) }}%
                    </span>
                </div>
                <div class="metric-label">Revenus FedaPay</div>
                <div class="metric-value" style="font-size: 1.5rem;">{{ number_format($stats['revenus'], 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">vs {{ $stats['periode_label'] }} precedent</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--teal);">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="metric-icon" style="background: rgba(66,140,121,0.1); color: var(--teal);">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                </div>
                <div class="metric-label">Taux de scan</div>
                <div class="metric-value" style="font-size: 1.5rem;">{{ $stats['taux_scan'] }}%</div>
                <div class="metric-subtitle">Tickets utilisés / vendus</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--danger);">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="metric-icon" style="background: rgba(231,76,60,0.1); color: var(--danger);">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
                <div class="metric-label">Taux echec paiement</div>
                <div class="metric-value" style="font-size: 1.5rem;">{{ $stats['taux_echec'] }}%</div>
                <div class="metric-subtitle">Paiements non confirmés</div>
            </div>
        </div>
    </div>

    {{-- Charts Row 1 --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="panel-card">
                <div class="panel-card-header">
                    <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap w-100">
                        <h5 class="mb-0 text-truncate" style="min-width: 0;"><i class="bi bi-graph-up me-2" style="color: var(--vert);"></i>Ventes de tickets — {{ $stats['periode_graphique'] }}</h5>
                        <select class="form-select form-select-sm w-auto flex-shrink-0" aria-label="Choisir une période" onchange="if(this.value){window.location.href=this.value;}">
                            <option value="{{ route('statistiques.index', ['periode' => '7']) }}" @selected($periode === '7')>7 jours</option>
                            <option value="{{ route('statistiques.index', ['periode' => '30']) }}" @selected($periode === '30')>30 jours</option>
                            <option value="{{ route('statistiques.index', ['periode' => '90']) }}" @selected($periode === '90')>3 mois</option>
                            <option value="{{ route('statistiques.index', ['periode' => 'annee']) }}" @selected($periode === 'annee')>Cette année</option>
                            <option value="{{ route('statistiques.index', ['periode' => 'tout']) }}" @selected($periode === 'tout')>Tout</option>
                        </select>
                    </div>
                </div>
                <div class="panel-card-body">
                    <div style="height: 300px;">
                        <canvas id="ventesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card h-100">
                <div class="panel-card-header">
                    <h5><i class="bi bi-trophy me-2" style="color: var(--violet);"></i>Top evenements</h5>
                </div>
                <div class="panel-card-body p-0">
                    @forelse($topEvenements as $index => $event)
                        <div class="d-flex align-items-center p-3" style="border-bottom: 1px solid var(--gris-clair);">
                            <div class="d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 50%; background: {{ $index === 0 ? 'rgba(123,63,160,0.15)' : ($index === 1 ? 'rgba(18,151,110,0.12)' : 'rgba(152,145,155,0.15)') }}; color: {{ $index === 0 ? 'var(--violet)' : ($index === 1 ? 'var(--vert)' : 'var(--gris)') }}; font-weight: 800; font-size: 0.75rem; flex-shrink: 0;">
                                {{ $index + 1 }}
                            </div>
                            <div class="ms-3 flex-1 min-w-0">
                                <div class="fw-bold" style="font-size: 0.82rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $event->titre }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $event->nb_tickets }} tickets {{ isset($event->date_event) ? ' - ' . \Carbon\Carbon::parse($event->date_event)->format('d/m/Y') : '' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <p class="text-muted mb-0" style="font-size: 0.85rem;">Aucun evenement pour le moment.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 2 --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="panel-card h-100">
                <div class="panel-card-header">
                    <h5><i class="bi bi-send me-2" style="color: var(--teal);"></i>Canaux d'envoi tickets</h5>
                </div>
                <div class="panel-card-body">
                    <div style="height: 200px;">
                        <canvas id="canauxChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid var(--gris-clair);">
                            <span class="text-muted" style="font-size: 0.82rem;"><i class="bi bi-envelope me-1" style="color: var(--vert);"></i> Email</span>
                            <span class="fw-bold" style="font-size: 0.82rem;">--%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid var(--gris-clair);">
                            <span class="text-muted" style="font-size: 0.82rem;"><i class="bi bi-chat-dots me-1" style="color: #25D366;"></i> WhatsApp</span>
                            <span class="fw-bold" style="font-size: 0.82rem;">--%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span class="text-muted" style="font-size: 0.82rem;"><i class="bi bi-phone me-1" style="color: var(--violet);"></i> SMS</span>
                            <span class="fw-bold" style="font-size: 0.82rem;">--%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card h-100">
                <div class="panel-card-header">
                    <h5><i class="bi bi-wallet2 me-2" style="color: var(--violet);"></i>Paiements par reseau</h5>
                </div>
                <div class="panel-card-body">
                    @foreach($paiementsParReseau as $key => $reseau)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold" style="font-size: 0.82rem;">{{ $reseau['label'] }}</span>
                                <span class="text-muted" style="font-size: 0.78rem;">
                                    {{ $reseau['count'] }} ticket{{ $reseau['count'] > 1 ? 's' : '' }} &middot; {{ number_format($reseau['montant'], 0, ',', ' ') }} F
                                </span>
                            </div>
                            <div class="progress-bar-custom">
                                <div class="progress-bar-fill" style="width: {{ $reseau['percentage'] }}%; background: {{ $key === 'mtn' ? '#ffcc00' : ($key === 'moov' ? '#0066cc' : ($key === 'celtiis' ? '#cc0000' : 'var(--gris)')) }};"></div>
                            </div>
                            <span class="text-muted" style="font-size: 0.72rem;">{{ $reseau['percentage'] }}% des paiements</span>
                        </div>
                    @endforeach
                    <div class="mt-3 pt-3" style="border-top: 1px solid var(--gris-clair);">
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="mini-stat">
                                    <div class="mini-stat-value text-vert">{{ $stats['tickets_vendus'] }}</div>
                                    <div class="mini-stat-label">Reussis</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mini-stat">
                                    <div class="mini-stat-value text-rouge">{{ $paiementsEchoues }}</div>
                                    <div class="mini-stat-label">Echoues</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card h-100">
                <div class="panel-card-header">
                    <h5><i class="bi bi-calendar-week me-2" style="color: var(--vert);"></i>Activité par jour</h5>
                </div>
                <div class="panel-card-body">
                    <div style="height: 250px;">
                        <canvas id="activiteChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Financial Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-calculator me-2" style="color: var(--violet);"></i>Resume financier</h5>
                </div>
                <div class="panel-card-body">
                    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2 py-2" style="border-bottom: 1px solid var(--gris-clair);">
                        <span class="text-muted">Revenus bruts</span>
                        <span class="fw-bold">{{ number_format($resumeFinancier['revenus_bruts'], 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2 py-2" style="border-bottom: 1px solid var(--gris-clair);">
                        <span class="text-muted">Tickets gratuits (seuil &lt; 100)</span>
                        <span class="fw-bold">{{ $resumeFinancier['gratuits'] }}</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2 py-2" style="border-bottom: 1px solid var(--gris-clair);">
                        <span class="text-muted">Commission PaxEvent : {{ $commissionPct }}%</span>
                        <span class="fw-bold" style="color: var(--vert);">{{ number_format($resumeFinancier['commission'], 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2 py-2" style="border-bottom: 1px solid var(--gris-clair);">
                        <span class="text-muted">Remboursements</span>
                        <span class="fw-bold" style="color: var(--danger);">-{{ number_format($resumeFinancier['remboursements'], 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3 px-3 rounded" style="background: rgba(18,151,110,0.08);">
                        <span class="fw-bold" style="font-size: 1rem; color: var(--vert);">Net a reverser</span>
                        <span class="fw-bold" style="font-size: 1.25rem; color: var(--vert);">{{ number_format($resumeFinancier['net_reverser'], 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-info-circle me-2" style="color: var(--teal);"></i>Informations</h5>
                </div>
                <div class="panel-card-body">
                    <div class="p-3 rounded" style="background: var(--blanc-casse);">
                        <p class="mb-2 fw-semibold" style="font-size: 0.9rem;">Retrait normal</p>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;">
                            J+5 après cloture : via FedaPay. Les fonds sont transferés automatiquement après verification des transactions.
                        </p>
                    </div>

                    <div class="mt-3 p-3 rounded" style="background: rgba(123,63,160,0.05);">
                        <p class="mb-2 fw-semibold" style="font-size: 0.9rem; color: var(--violet);">
                            <i class="bi bi-lightbulb me-1"></i> Conseil
                        </p>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;">
                            Surveillez regulièrement le taux de scan pour identifier les évènements avec des billets non utilisés et optimiser vos prochaines ventes.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const ventesData = @json($ventesParJour);
const activiteData = @json($activiteParJourSemaine);

const ventesLabels = ventesData.map(d => {
    const date = new Date(d.date);
    return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
});

const ventesChart = new Chart(document.getElementById('ventesChart'), {
    type: 'line',
    data: {
        labels: ventesLabels,
        datasets: [
            @foreach($ventesCategories as $categorie)
            {
                label: @json($categorie['label']),
                data: ventesData.map(d => d[@json($categorie['key'])] ?? 0),
                borderColor: @json($categorie['couleur']),
                backgroundColor: @json($categorie['fond']),
                tension: 0.3,
                fill: true,
                pointRadius: 3,
            }{{ $loop->last ? '' : ',' }}
            @endforeach
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: { size: 11 }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: { font: { size: 11 } }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 10 }, maxRotation: 45 }
            }
        }
    }
});

const activiteChart = new Chart(document.getElementById('activiteChart'), {
    type: 'bar',
    data: {
        labels: activiteData.map(d => d.label),
        datasets: [{
            label: 'Achats',
            data: activiteData.map(d => d.total),
            backgroundColor: [
                'rgba(18,151,110,0.7)',
                'rgba(18,151,110,0.6)',
                'rgba(18,151,110,0.5)',
                'rgba(18,151,110,0.6)',
                'rgba(18,151,110,0.7)',
                'rgba(135,66,139,0.8)',
                'rgba(135,66,139,0.9)',
            ],
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: { font: { size: 11 } }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 11 } }
            }
        }
    }
});

const canauxChart = new Chart(document.getElementById('canauxChart'), {
    type: 'doughnut',
    data: {
        labels: ['Email', 'WhatsApp', 'SMS'],
        datasets: [{
            data: [100, 0, 0],
            backgroundColor: ['#12976e', '#25D366', '#87428b'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: { size: 11 }
                }
            }
        },
        cutout: '60%',
    }
});
</script>
@endsection
