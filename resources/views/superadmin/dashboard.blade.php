@extends('superadmin.layouts.master')

@section('title', 'Tableau de bord - Super Admin PaxEvent')
@section('page-title', 'Tableau de bord')

@section('content')
{{-- Filtres de période, opérateur et type d'événement --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="sa-card">
            <div class="sa-card-body py-3">
                <form id="sa_filter_form" action="{{ route('superadmin.dashboard') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="sa-label">Période</label>
                        <select name="periode" class="sa-form-control sa-form-control-sm" id="sa_periode" onchange="togglePerso(this.value)">
                            <option value="7" {{ $periode === '7' ? 'selected' : '' }}>7 jours</option>
                            <option value="30" {{ $periode === '30' || $periode === '' ? 'selected' : '' }}>30 jours</option>
                            <option value="90" {{ $periode === '90' ? 'selected' : '' }}>90 jours</option>
                            <option value="mois" {{ $periode === 'mois' ? 'selected' : '' }}>Ce mois-ci</option>
                            <option value="perso" {{ $periode === 'perso' ? 'selected' : '' }}>Personnalisée</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2" id="sa_perso_group" style="{{ $periode === 'perso' ? '' : 'display:none;' }}">
                        <label class="sa-label">Du</label>
                        <input type="date" name="date_debut" class="sa-form-control sa-form-control-sm" value="{{ $dateDebut }}">
                    </div>
                    <div class="col-6 col-md-3 col-lg-2" id="sa_perso_group2" style="{{ $periode === 'perso' ? '' : 'display:none;' }}">
                        <label class="sa-label">Au</label>
                        <input type="date" name="date_fin" class="sa-form-control sa-form-control-sm" value="{{ $dateFin }}">
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="sa-label">Opérateur</label>
                        <select name="operateur" class="sa-form-control sa-form-control-sm">
                            <option value="">Tous</option>
                            @foreach($operateurs as $cle => $label)
                                <option value="{{ $cle }}" {{ $operateur === $cle ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                            <option value="especes" {{ $operateur === 'especes' ? 'selected' : '' }}>Espèces</option>
                            <option value="bancaire" {{ $operateur === 'bancaire' ? 'selected' : '' }}>Carte bancaire</option>
                            <option value="mobile_money" {{ $operateur === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="sa-label">Type d'événement</label>
                        <select name="type_evenement" class="sa-form-control sa-form-control-sm">
                            <option value="">Tous</option>
                            @foreach($typeEvenements as $type)
                                <option value="{{ $type }}" {{ $typeEvenement === $type ? 'selected' : '' }}>
                                    @php $typeLabel = match($type) { 'formation' => 'Formation', 'conference' => 'Conférence', default => 'Spectacle / Soirée' }; @endphp
                                    {{ $typeLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2 col-lg-2 d-flex gap-2">
                        <button type="submit" class="sa-btn sa-btn-primary sa-btn-sm flex-fill"><i class="bi bi-funnel me-1"></i>Filtrer</button>
                        <a href="{{ route('superadmin.dashboard') }}" class="sa-btn sa-btn-sm" style="background:#f1f2f6;border:1px solid #e3e6ed;color:#555;" title="Réinitialiser">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                    <div class="col-12 text-muted" style="font-size:0.75rem;">
                        <i class="bi bi-calendar3 me-1"></i>Période affichée : <strong>{{ $periodeLabel }}</strong>
                        @if($periode === 'perso')
                            du {{ \Illuminate\Support\Carbon::parse($dateDebut ?? now()->subDays(29))->isoFormat('D MMM YYYY') }} au {{ \Illuminate\Support\Carbon::parse($dateFin ?? now())->isoFormat('D MMM YYYY') }}
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- KPIs avec évolution (piste 8) --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(107,63,160,0.1); color: var(--sa-primary);"><i class="bi bi-people-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($totalUsers, 0, ',', ' ') }}</div>
                <div class="kpi-label">
                    Nouveaux utilisateurs
                    <span class="sa-evolution {{ $usersEvolution >= 0 ? 'sa-evolution-up' : 'sa-evolution-down' }}">
                        <i class="bi {{ $usersEvolution >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>{{ number_format(abs($usersEvolution), 0, ',', ' ') }}%
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-calendar-event-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($totalEvenements, 0, ',', ' ') }}</div>
                <div class="kpi-label">
                    Événements créés
                    <span class="sa-evolution {{ $evenementsEvolution >= 0 ? 'sa-evolution-up' : 'sa-evolution-down' }}">
                        <i class="bi {{ $evenementsEvolution >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>{{ number_format(abs($evenementsEvolution), 0, ',', ' ') }}%
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(243,156,18,0.1); color: var(--sa-warning);"><i class="bi bi-ticket-perforated-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($ticketsVendus, 0, ',', ' ') }}</div>
                <div class="kpi-label">
                    Tickets vendus
                    <span class="sa-evolution {{ $ticketsEvolution >= 0 ? 'sa-evolution-up' : 'sa-evolution-down' }}">
                        <i class="bi {{ $ticketsEvolution >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>{{ number_format(abs($ticketsEvolution), 0, ',', ' ') }}%
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($recettesGlobales, 0, ',', ' ') }}</div>
                <div class="kpi-label">
                    Revenus (FCFA)
                    <span class="sa-evolution {{ $recettesEvolution >= 0 ? 'sa-evolution-up' : 'sa-evolution-down' }}">
                        <i class="bi {{ $recettesEvolution >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>{{ number_format(abs($recettesEvolution), 0, ',', ' ') }}%
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="sa-card">
            <div class="sa-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <span><i class="bi bi-graph-up me-2" style="color: var(--sa-primary);"></i>Ventes et revenus</span>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted" style="font-size:0.72rem;">Période</span>
                    <select name="periode_ventes" form="sa_filter_form" class="sa-form-control sa-form-control-sm" style="width:auto;min-width:110px;" onchange="this.form.submit()">
                        <option value="7" {{ $periodeVentes === '7' ? 'selected' : '' }}>7 jours</option>
                        <option value="30" {{ $periodeVentes === '30' || $periodeVentes === '' ? 'selected' : '' }}>30 jours</option>
                        <option value="90" {{ $periodeVentes === '90' ? 'selected' : '' }}>90 jours</option>
                        <option value="mois" {{ $periodeVentes === 'mois' ? 'selected' : '' }}>Ce mois-ci</option>
                        <option value="perso" {{ $periodeVentes === 'perso' ? 'selected' : '' }}>Personnalisée</option>
                    </select>
                </div>
            </div>
            <div class="sa-card-body">
                <div class="chart-container"><canvas id="ventesChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="sa-card">
            <div class="sa-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <span><i class="bi bi-pie-chart-fill me-2" style="color: var(--sa-primary);"></i>Répartition par réseau</span>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted" style="font-size:0.72rem;">Période</span>
                    <select name="periode_reseaux" form="sa_filter_form" class="sa-form-control sa-form-control-sm" style="width:auto;min-width:110px;" onchange="this.form.submit()">
                        <option value="7" {{ $periodeReseaux === '7' ? 'selected' : '' }}>7 jours</option>
                        <option value="30" {{ $periodeReseaux === '30' || $periodeReseaux === '' ? 'selected' : '' }}>30 jours</option>
                        <option value="90" {{ $periodeReseaux === '90' ? 'selected' : '' }}>90 jours</option>
                        <option value="mois" {{ $periodeReseaux === 'mois' ? 'selected' : '' }}>Ce mois-ci</option>
                        <option value="perso" {{ $periodeReseaux === 'perso' ? 'selected' : '' }}>Personnalisée</option>
                    </select>
                </div>
            </div>
            <div class="sa-card-body">
                <div class="chart-container"><canvas id="reseauxChart"></canvas></div>
                <div class="mt-3">
                    @foreach($repartitionReseaux as $reseau)
                        <div class="d-flex justify-content-between py-1 border-bottom" style="font-size:0.78rem;">
                            <span><span class="sa-legend-dot" style="background: {{ $loop->index === 0 ? '#6B3FA0' : ($loop->index === 1 ? '#27ae60' : ($loop->index === 2 ? '#3498db' : '#95a5a6')) }};"></span>{{ $reseau['label'] }}</span>
                            <span><strong>{{ number_format($reseau['count'], 0, ',', ' ') }}</strong> <span class="text-muted">({{ $reseau['percentage'] }}%)</span></span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <span><i class="bi bi-cash-coin me-2" style="color: var(--sa-success);"></i>Revenus cumulés</span>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted" style="font-size:0.72rem;">Période</span>
                    <select name="periode_cumules" form="sa_filter_form" class="sa-form-control sa-form-control-sm" style="width:auto;min-width:110px;" onchange="this.form.submit()">
                        <option value="7" {{ $periodeCumules === '7' ? 'selected' : '' }}>7 jours</option>
                        <option value="30" {{ $periodeCumules === '30' || $periodeCumules === '' ? 'selected' : '' }}>30 jours</option>
                        <option value="90" {{ $periodeCumules === '90' ? 'selected' : '' }}>90 jours</option>
                        <option value="mois" {{ $periodeCumules === 'mois' ? 'selected' : '' }}>Ce mois-ci</option>
                        <option value="perso" {{ $periodeCumules === 'perso' ? 'selected' : '' }}>Personnalisée</option>
                    </select>
                </div>
            </div>
            <div class="sa-card-body">
                <div class="chart-container"><canvas id="revenusCumulesChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <span><i class="bi bi-check-circle me-2" style="color: #27ae60;"></i>Taux de réussite des paiements</span>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted" style="font-size:0.72rem;">Période</span>
                    <select name="periode_taux" form="sa_filter_form" class="sa-form-control sa-form-control-sm" style="width:auto;min-width:110px;" onchange="this.form.submit()">
                        <option value="7" {{ $periodeTaux === '7' ? 'selected' : '' }}>7 jours</option>
                        <option value="30" {{ $periodeTaux === '30' || $periodeTaux === '' ? 'selected' : '' }}>30 jours</option>
                        <option value="90" {{ $periodeTaux === '90' ? 'selected' : '' }}>90 jours</option>
                        <option value="mois" {{ $periodeTaux === 'mois' ? 'selected' : '' }}>Ce mois-ci</option>
                        <option value="perso" {{ $periodeTaux === 'perso' ? 'selected' : '' }}>Personnalisée</option>
                    </select>
                </div>
            </div>
            <div class="sa-card-body">
                <div class="chart-container"><canvas id="tauxReussiteChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <span><i class="bi bi-person-plus-fill me-2" style="color: var(--sa-primary);"></i>Nouveaux utilisateurs / scans</span>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted" style="font-size:0.72rem;">Période</span>
                    <select name="periode_activite" form="sa_filter_form" class="sa-form-control sa-form-control-sm" style="width:auto;min-width:110px;" onchange="this.form.submit()">
                        <option value="7" {{ $periodeActivite === '7' ? 'selected' : '' }}>7 jours</option>
                        <option value="30" {{ $periodeActivite === '30' || $periodeActivite === '' ? 'selected' : '' }}>30 jours</option>
                        <option value="90" {{ $periodeActivite === '90' ? 'selected' : '' }}>90 jours</option>
                        <option value="mois" {{ $periodeActivite === 'mois' ? 'selected' : '' }}>Ce mois-ci</option>
                        <option value="perso" {{ $periodeActivite === 'perso' ? 'selected' : '' }}>Personnalisée</option>
                    </select>
                </div>
            </div>
            <div class="sa-card-body">
                <div class="chart-container"><canvas id="activiteChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <span><i class="bi bi-trophy-fill me-2" style="color: var(--sa-warning);"></i>Top événements</span>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted" style="font-size:0.72rem;">Période</span>
                    <select name="periode_top" form="sa_filter_form" class="sa-form-control sa-form-control-sm" style="width:auto;min-width:110px;" onchange="this.form.submit()">
                        <option value="7" {{ $periodeTop === '7' ? 'selected' : '' }}>7 jours</option>
                        <option value="30" {{ $periodeTop === '30' || $periodeTop === '' ? 'selected' : '' }}>30 jours</option>
                        <option value="90" {{ $periodeTop === '90' ? 'selected' : '' }}>90 jours</option>
                        <option value="mois" {{ $periodeTop === 'mois' ? 'selected' : '' }}>Ce mois-ci</option>
                        <option value="perso" {{ $periodeTop === 'perso' ? 'selected' : '' }}>Personnalisée</option>
                    </select>
                </div>
            </div>
            <div class="sa-card-body">
                <div class="chart-container"><canvas id="topEvenementsChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
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
    <div class="col-lg-6">
        <div class="row g-3">
            <div class="col-12">
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
            <div class="col-12">
                <div class="sa-card">
                    <div class="sa-card-header">
                        <span><i class="bi bi-cash-coin me-2" style="color: var(--sa-success);"></i>Finances</span>
                    </div>
                    <div class="sa-card-body">
                        <div class="d-flex justify-content-between py-2 border-bottom"><span>Transactions reussies</span><strong>{{ $transactionsReussies }}</strong></div>
                        <div class="d-flex justify-content-between py-2 border-bottom"><span>Transactions echouees</span><strong style="color: var(--sa-danger);">{{ $transactionsEchouees }}</strong></div>
                        <div class="d-flex justify-content-between py-2 border-bottom"><span>Montant journalier</span><strong>{{ number_format($montantsJournaliers, 0, ',', ' ') }} F</strong></div>
                        <div class="d-flex justify-content-between py-2"><span>Commission plateforme ({{ $commissionPct }}%)</span><strong>{{ number_format($commissionPlateforme, 0, ',', ' ') }} F</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
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

@endsection

@push('styles')
<style>
    .sa-label { display:block; font-size:0.72rem; font-weight:600; color:#6b7280; margin-bottom:0.25rem; }
    .sa-form-control-sm { font-size:0.8rem; padding:0.3rem 0.55rem; }
    .sa-evolution { font-size:0.68rem; font-weight:600; margin-left:0.25rem; white-space:nowrap; }
    .sa-evolution-up { color: var(--sa-success); }
    .sa-evolution-down { color: var(--sa-danger); }
    .sa-legend-dot { display:inline-block; width:9px; height:9px; border-radius:50%; margin-right:0.35rem; }
    @media (max-width: 575.98px) {
        .kpi-card { padding: 0.75rem; gap: 0.6rem; }
        .kpi-icon { width: 36px; height: 36px; font-size: 1rem; }
        .kpi-value { font-size: 1.1rem; }
        .kpi-label { font-size: 0.68rem; }
    }
</style>
@endpush

@push('scripts')
<script>
function togglePerso(val) {
    document.getElementById('sa_perso_group').style.display = val === 'perso' ? '' : 'none';
    document.getElementById('sa_perso_group2').style.display = val === 'perso' ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    var ventesData = @json($ventesParJour);

    new Chart(document.getElementById('ventesChart'), {
        type: 'line',
        data: {
            labels: ventesData.map(d => d.date),
            datasets: [
                {
                    label: 'Tickets vendus',
                    data: ventesData.map(d => d.tickets),
                    borderColor: '#6B3FA0',
                    backgroundColor: 'rgba(107,63,160,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                    pointBackgroundColor: '#6B3FA0',
                    yAxisID: 'y'
                },
                {
                    label: 'Revenus (FCFA)',
                    data: ventesData.map(d => d.montant),
                    borderColor: '#27ae60',
                    backgroundColor: 'rgba(39,174,96,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                    pointBackgroundColor: '#27ae60',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            layout: { padding: { left: 10, right: 10 } },
            plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } } },
            scales: {
                y: { beginAtZero: true, ticks: { font: { size: 10 }, maxTicksLimit: 6 }, grid: { color: 'rgba(0,0,0,0.04)' } },
                y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 6 } }
            }
        }
    });

    new Chart(document.getElementById('reseauxChart'), {
        type: 'doughnut',
        data: {
            labels: @json(collect($repartitionReseaux)->pluck('label')),
            datasets: [{
                data: @json(collect($repartitionReseaux)->pluck('count')),
                backgroundColor: ['#6B3FA0', '#27ae60', '#3498db', '#95a5a6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, font: { size: 10 } } }
            },
            cutout: '60%'
        }
    });

    var revenusCum = @json($revenusCumules);
    new Chart(document.getElementById('revenusCumulesChart'), {
        type: 'line',
        data: {
            labels: revenusCum.map(d => d.date),
            datasets: [{
                label: 'Revenus cumulés (FCFA)',
                data: revenusCum.map(d => d.montant),
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39,174,96,0.10)',
                fill: true,
                tension: 0.4,
                pointRadius: 2,
                pointBackgroundColor: '#27ae60'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } },
                tooltip: { callbacks: { label: function(ctx) { return ctx.dataset.label + ': ' + Number(ctx.raw).toLocaleString('fr-FR') + ' F'; } } }
            },
            scales: { y: { beginAtZero: true, ticks: { font: { size: 10 }, maxTicksLimit: 6 }, grid: { color: 'rgba(0,0,0,0.04)' } } }
        }
    });

    var tauxData = @json($tauxReussiteParJour);
    new Chart(document.getElementById('tauxReussiteChart'), {
        type: 'line',
        data: {
            labels: tauxData.map(d => d.date),
            datasets: [{
                label: 'Taux de réussite (%)',
                data: tauxData.map(d => d.taux),
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39,174,96,0.08)',
                fill: true,
                tension: 0.4,
                pointRadius: 2,
                pointBackgroundColor: '#27ae60'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } }
            },
            scales: {
                y: { beginAtZero: true, min: 0, max: 100, ticks: { font: { size: 10 }, maxTicksLimit: 6, format: { suffix: '%' } }, grid: { color: 'rgba(0,0,0,0.04)' } }
            }
        }
    });

    var usersData = @json($nouveauxUtilisateursParJour);
    var scansData = @json($scansParJour);
    new Chart(document.getElementById('activiteChart'), {
        type: 'line',
        data: {
            labels: usersData.map(d => d.date),
            datasets: [
                {
                    label: 'Nouveaux utilisateurs',
                    data: usersData.map(d => d.nb),
                    borderColor: '#6B3FA0',
                    backgroundColor: 'rgba(107,63,160,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                    pointBackgroundColor: '#6B3FA0'
                },
                {
                    label: 'Scans',
                    data: scansData.map(d => d.nb),
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52,152,219,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                    pointBackgroundColor: '#3498db'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } } },
            scales: { y: { beginAtZero: true, ticks: { font: { size: 10 }, maxTicksLimit: 6 }, grid: { color: 'rgba(0,0,0,0.04)' } } }
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
