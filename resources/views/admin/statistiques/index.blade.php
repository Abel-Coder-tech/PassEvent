@extends('layouts.app')

@section('title', 'Statistiques - PassEvent')

@section('page-title', 'Statistiques')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Statistiques</li>
@endsection

@section('topbar-actions')
    <div class="btn-group" role="group">
        <a href="{{ route('statistiques.index', ['periode' => '7']) }}" class="btn btn-sm btn-secondary-custom {{ $periode === '7' ? 'active' : '' }}" style="border-radius: 6px 0 0 6px; padding: 0.3rem 0.75rem; font-size: 0.78rem;">7 jours</a>
        <a href="{{ route('statistiques.index', ['periode' => '30']) }}" class="btn btn-sm btn-secondary-custom {{ $periode === '30' ? 'active' : '' }}" style="border-radius: 0; padding: 0.3rem 0.75rem; font-size: 0.78rem; border-left: none;">30 jours</a>
        <a href="{{ route('statistiques.index', ['periode' => '90']) }}" class="btn btn-sm btn-secondary-custom {{ $periode === '90' ? 'active' : '' }}" style="border-radius: 0; padding: 0.3rem 0.75rem; font-size: 0.78rem; border-left: none;">3 mois</a>
        <a href="{{ route('statistiques.index', ['periode' => 'annee']) }}" class="btn btn-sm btn-secondary-custom {{ $periode === 'annee' ? 'active' : '' }}" style="border-radius: 0; padding: 0.3rem 0.75rem; font-size: 0.78rem; border-left: none;">Cette annee</a>
        <a href="{{ route('statistiques.index', ['periode' => 'tout']) }}" class="btn btn-sm btn-secondary-custom {{ $periode === 'tout' ? 'active' : '' }}" style="border-radius: 0 6px 6px 0; padding: 0.3rem 0.75rem; font-size: 0.78rem; border-left: none;">Tout</a>
    </div>
@endsection

@section('content')
<div class="page-content">
    <p class="text-muted mb-4" style="font-size: 0.9rem;">
        <i class="bi bi-calendar3 me-1"></i>
        Periode selectionnee : <strong>{{ $stats['periode_label'] }}</strong>
    </p>

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="metric-icon" style="background: rgba(18,151,110,0.1); color: var(--vert);">
                        <i class="bi bi-ticket-perforated"></i>
                    </div>
                    <span class="badge {{ $stats['tickets_vendus_evolution'] >= 0 ? 'bg-success' : 'bg-danger' }}" style="font-size: 0.7rem;">
                        {{ $stats['tickets_vendus_evolution'] >= 0 ? '+' : '' }}{{ $stats['tickets_vendus_evolution'] }}%
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
                    <div class="metric-icon" style="background: rgba(135,66,139,0.1); color: var(--violet);">
                        <i class="bi bi-currency-exchange"></i>
                    </div>
                    <span class="badge {{ $stats['revenus_evolution'] >= 0 ? 'bg-success' : 'bg-danger' }}" style="font-size: 0.7rem;">
                        {{ $stats['revenus_evolution'] >= 0 ? '+' : '' }}{{ $stats['revenus_evolution'] }}%
                    </span>
                </div>
                <div class="metric-label">Revenus KKiaPay</div>
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
                <div class="metric-subtitle">Tickets utilises / vendus</div>
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
                <div class="metric-subtitle">Paiements non confirmes</div>
            </div>
        </div>
    </div>

    {{-- Charts Row 1 --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-graph-up me-2" style="color: var(--vert);"></i>Ventes de tickets - 30 derniers jours</h5>
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
                        <div class="d-flex align-items-center p-3" style="border-bottom: 1px solid #f5f5f5;">
                            <div class="d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 50%; background: {{ $index === 0 ? 'rgba(135,66,139,0.15)' : ($index === 1 ? 'rgba(18,151,110,0.12)' : 'rgba(152,145,155,0.15)') }}; color: {{ $index === 0 ? 'var(--violet)' : ($index === 1 ? 'var(--vert)' : 'var(--gris)') }}; font-weight: 800; font-size: 0.75rem; flex-shrink: 0;">
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
                        <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid #f5f5f5;">
                            <span class="text-muted" style="font-size: 0.82rem;"><i class="bi bi-envelope me-1" style="color: var(--vert);"></i> Email</span>
                            <span class="fw-bold" style="font-size: 0.82rem;">--%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid #f5f5f5;">
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
                                <span class="text-muted" style="font-size: 0.78rem;">{{ $reseau['percentage'] }}% ({{ $reseau['count'] }})</span>
                            </div>
                            <div class="progress-bar-custom">
                                <div class="progress-bar-fill" style="width: {{ $reseau['percentage'] }}%; background: {{ $key === 'mtn' ? '#ffcc00' : ($key === 'moov' ? '#0066cc' : '#cc0000') }};"></div>
                            </div>
                        </div>
                    @endforeach
                    <div class="mt-3 pt-3" style="border-top: 1px solid #f0f0f0;">
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
                    <h5><i class="bi bi-calendar-week me-2" style="color: var(--vert);"></i>Activite par jour</h5>
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
                    <div class="mb-3 d-flex justify-content-between py-2" style="border-bottom: 1px solid #f0f0f0;">
                        <span class="text-muted">Revenus bruts</span>
                        <span class="fw-bold">{{ number_format($resumeFinancier['revenus_bruts'], 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between py-2" style="border-bottom: 1px solid #f0f0f0;">
                        <span class="text-muted">Tickets gratuits (seuil &lt; 100)</span>
                        <span class="fw-bold">{{ $resumeFinancier['gratuits'] }}</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between py-2" style="border-bottom: 1px solid #f0f0f0;">
                        <span class="text-muted">Commission PassEvent : 5%</span>
                        <span class="fw-bold" style="color: var(--vert);">{{ number_format($resumeFinancier['commission'], 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between py-2" style="border-bottom: 1px solid #f0f0f0;">
                        <span class="text-muted">Frais KKiaPay : 3.5%</span>
                        <span class="fw-bold">{{ number_format($resumeFinancier['frais_kkiapay'], 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between py-2" style="border-bottom: 1px solid #f0f0f0;">
                        <span class="text-muted">Remboursements</span>
                        <span class="fw-bold" style="color: var(--danger);">-{{ number_format($resumeFinancier['remboursements'], 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="d-flex justify-content-between py-3 px-3 rounded" style="background: rgba(18,151,110,0.08);">
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
                        <p class="mb-2 fw-semibold" style="font-size: 0.9rem;">Prochain recrutement</p>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;">
                            J+2 apres cloture : via KKiaPay. Les fonds sont transfers automatiquement apres verification des transactions.
                        </p>
                    </div>

                    <div class="mt-3 p-3 rounded" style="background: rgba(135,66,139,0.05);">
                        <p class="mb-2 fw-semibold" style="font-size: 0.9rem; color: var(--violet);">
                            <i class="bi bi-lightbulb me-1"></i> Conseil
                        </p>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;">
                            Surveillez regulierement le taux de scan pour identifier les evenements avec des billets non utilises et optimiser vos prochaines ventes.
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
            {
                label: 'Etudiants',
                data: ventesData.map(d => d.etudiants),
                borderColor: '#12976e',
                backgroundColor: 'rgba(18,151,110,0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 3,
            },
            {
                label: 'Externes',
                data: ventesData.map(d => d.externes),
                borderColor: '#87428b',
                backgroundColor: 'rgba(135,66,139,0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 3,
            },
            {
                label: 'Vente manuelle',
                data: ventesData.map(d => d.manuelles),
                borderColor: '#428c79',
                backgroundColor: 'rgba(66,140,121,0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 3,
            }
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
