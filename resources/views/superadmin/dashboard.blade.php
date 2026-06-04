@extends('superadmin.layouts.master')

@section('title', 'Dashboard - Super Admin PassEvent')
@section('page-title', 'Tableau de bord')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-3 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(107,63,160,0.1); color: var(--sa-primary);"><i class="bi bi-people-fill"></i></div>
            <div class="kpi-info"><div class="kpi-value">{{ $totalUsers }}</div><div class="kpi-label">Utilisateurs</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-3 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-calendar-event-fill"></i></div>
            <div class="kpi-info"><div class="kpi-value">{{ $totalEvenements }}</div><div class="kpi-label">Evenements</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-3 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(243,156,18,0.1); color: var(--sa-warning);"><i class="bi bi-ticket-perforated-fill"></i></div>
            <div class="kpi-info"><div class="kpi-value">{{ number_format($ticketsVendus, 0, ',', ' ') }}</div><div class="kpi-label">Tickets vendus</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-3 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-info"><div class="kpi-value">{{ number_format($recettesGlobales, 0, ',', ' ') }}</div><div class="kpi-label">Revenus (FCFA)</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-3 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(107,63,160,0.1); color: var(--sa-primary);"><i class="bi bi-activity"></i></div>
            <div class="kpi-info"><div class="kpi-value">{{ $evenementsActifs }}</div><div class="kpi-label">Actifs</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-3 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(52,152,219,0.1); color: #3498db;"><i class="bi bi-qr-code"></i></div>
            <div class="kpi-info"><div class="kpi-value">{{ $scansAujourdhui }}</div><div class="kpi-label">Scans aujourdhui</div></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-graph-up me-2" style="color: var(--sa-primary);"></i>Evolution des ventes (7 jours)</span>
            </div>
            <div class="sa-card-body">
                <div class="chart-container"><canvas id="ventesChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-pie-chart-fill me-2" style="color: var(--sa-primary);"></i>Repartition utilisateurs</span>
            </div>
            <div class="sa-card-body">
                <div class="chart-container"><canvas id="usersChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-trophy-fill me-2" style="color: var(--sa-warning);"></i>Top evenements</span>
            </div>
            <div class="sa-card-body">
                <div class="chart-container"><canvas id="topEvenementsChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-activity me-2" style="color: var(--sa-success);"></i>Activité en direct</span>
                <span class="activity-pulse"></span>
            </div>
            <div class="sa-card-body" style="max-height: 300px; overflow-y: auto;">
                @forelse($activiteEnDirect as $act)
                    <div class="sa-activity-item">
                        <div class="sa-activity-dot" style="background: {{ $act['action'] === 'achat' ? 'var(--sa-success)' : ($act['action'] === 'scan' ? '#3498db' : 'var(--sa-warning)') }};"></div>
                        <div class="sa-activity-content">
                            <div class="sa-activity-text">
                                <strong>{{ ucfirst($act['action']) }}</strong>
                                @if($act['evenement'] !== 'N/A')
                                    &mdash; {{ $act['evenement'] }}
                                @endif
                            </div>
                            <div class="sa-activity-time"><i class="bi bi-clock me-1"></i>{{ $act['date'] }} <span class="ms-2 text-muted">IP: {{ $act['ip'] }}</span></div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Aucune activité récente</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-shield-fill me-2" style="color: var(--sa-danger);"></i>Securite</span>
            </div>
            <div class="sa-card-body">
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Scans invalides (aujourdhui)</span><strong>{{ $scanInvalides }}</strong></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Paiements echoues</span><strong style="color: var(--sa-danger);">{{ $paiementsEchoues }}</strong></div>
                <div class="d-flex justify-content-between py-2"><span>Tickets dupliques suspects</span><strong style="color: var(--sa-warning);">{{ $ticketsDupliques }}</strong></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-cash-coin me-2" style="color: var(--sa-success);"></i>Finances</span>
            </div>
            <div class="sa-card-body">
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Transactions reussies</span><strong>{{ $transactionsReussies }}</strong></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Transactions echouees</span><strong style="color: var(--sa-danger);">{{ $transactionsEchouees }}</strong></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Montant journalier</span><strong>{{ number_format($montantsJournaliers, 0, ',', ' ') }} F</strong></div>
                <div class="d-flex justify-content-between py-2"><span>Commission plateforme (5%)</span><strong>{{ number_format($commissionPlateforme, 0, ',', ' ') }} F</strong></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-bell-fill me-2" style="color: var(--sa-warning);"></i>Notifications</span>
            </div>
            <div class="sa-card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>Messages non lus</span>
                    <span class="sa-badge sa-badge-danger">{{ $messagesNonLus }}</span>
                </div>
                <div class="d-flex justify-content-between py-2"><span>Abonnes newsletter</span><strong>{{ $newsletterCount }}</strong></div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .col-xl { flex: 1 0 0%; min-width: 160px; }
    @media (max-width: 575.98px) {
        .col-xl { min-width: 140px; }
        .kpi-card { padding: 0.75rem; gap: 0.6rem; }
        .kpi-icon { width: 36px; height: 36px; font-size: 1rem; }
        .kpi-value { font-size: 1.1rem; }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    new Chart(document.getElementById('ventesChart'), {
        type: 'line',
        data: {
            labels: @json($ventes7Jours->pluck('date')),
            datasets: [
                {
                    label: 'Tickets vendus',
                    data: @json($ventes7Jours->pluck('tickets')),
                    borderColor: '#6B3FA0',
                    backgroundColor: 'rgba(107,63,160,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#6B3FA0',
                    yAxisID: 'y'
                },
                {
                    label: 'Revenus (FCFA)',
                    data: @json($ventes7Jours->pluck('revenus')),
                    borderColor: '#27ae60',
                    backgroundColor: 'rgba(39,174,96,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#27ae60',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } } },
            scales: {
                y: { beginAtZero: true, ticks: { font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });

    new Chart(document.getElementById('usersChart'), {
        type: 'doughnut',
        data: {
            labels: ['Etudiants', 'Externes', 'Admins'],
            datasets: [{
                data: [{{ $usersParRole['etudiants'] }}, {{ $usersParRole['externes'] }}, {{ $usersParRole['admins'] }}],
                backgroundColor: ['#6B3FA0', '#27ae60', '#3498db'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, font: { size: 10 } } }
            },
            cutout: '65%'
        }
    });

    new Chart(document.getElementById('topEvenementsChart'), {
        type: 'bar',
        data: {
            labels: @json($topEvenements->pluck('titre')->map(fn($t) => mb_strlen($t) > 18 ? mb_substr($t, 0, 18).'...' : $t)),
            datasets: [
                {
                    label: 'Tickets vendus',
                    data: @json($topEvenements->pluck('tickets')),
                    backgroundColor: 'rgba(107,63,160,0.7)',
                    borderColor: '#6B3FA0',
                    borderWidth: 1,
                    borderRadius: 4,
                    yAxisID: 'y'
                },
                {
                    label: 'Remplissage %',
                    data: @json($topEvenements->pluck('remplissage')),
                    backgroundColor: 'rgba(39,174,96,0.6)',
                    borderColor: '#27ae60',
                    borderWidth: 1,
                    borderRadius: 4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { font: { size: 9 } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { font: { size: 9 }, format: { suffix: '%' } } }
            }
        }
    });
});
</script>
@endpush
