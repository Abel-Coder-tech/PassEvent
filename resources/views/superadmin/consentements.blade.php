@extends('superadmin.layouts.master')

@section('title', 'Consentements cookies - Super Admin')
@section('page-title', 'Consentements cookies')

@section('content')
<style>
    .sa-label { display: block; font-size: 0.72rem; font-weight: 600; color: #6b7280; margin-bottom: 0.25rem; }
    .center-chart { display: flex; align-items: center; justify-content: center; }
    .center-chart canvas { max-width: 230px; max-height: 200px; margin: 0 auto; }
</style>
@if (session('success'))
<div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="alert alert-danger py-2 small">{{ session('error') }}</div>
@endif

{{-- Filtres : recherche, statut, période + export --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="sa-card">
            <div class="sa-card-body py-3">
                <form method="GET" action="{{ route('superadmin.consentements') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="sa-label">Recherche</label>
                        <input type="text" name="q" class="sa-form-control sa-form-control-sm" placeholder="Email, nom, session ou IP" value="{{ request('q') }}" autocomplete="off">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="sa-label">Statut</label>
                        <select name="statut" class="sa-form-control sa-form-control-sm">
                            <option value="">Tous</option>
                            @foreach(['accepte' => 'Accepté', 'refuse' => 'Refusé', 'personnalise' => 'Personnalisé'] as $valeur => $libelle)
                            <option value="{{ $valeur }}" {{ request('statut') === $valeur ? 'selected' : '' }}>{{ $libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="sa-label">Du</label>
                        <input type="date" name="debut" class="sa-form-control sa-form-control-sm" value="{{ request('debut') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="sa-label">Au</label>
                        <input type="date" name="fin" class="sa-form-control sa-form-control-sm" value="{{ request('fin') }}">
                    </div>
                    <div class="col-6 col-md-2 d-flex gap-2">
                        <button type="submit" class="sa-btn sa-btn-primary sa-btn-sm flex-fill"><i class="bi bi-funnel me-1"></i>Filtrer</button>
                        <a href="{{ route('superadmin.consentements') }}" class="sa-btn sa-btn-sm" style="background:#f1f2f6;border:1px solid #e3e6ed;color:#555;" title="Réinitialiser">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </form>
                <div class="row g-2 align-items-center mt-1">
                    <div class="col text-muted" style="font-size:0.75rem;">
                        <i class="bi bi-info-circle me-1"></i>Chaque clic sur le bandeau est consigné (version politique, services, session, IP).
                        Purge &amp; rotation du cookie : <code style="font-size:0.7rem;">php artisan consentements:purger [--statut=accepte] [--sans-rotation]</code>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('superadmin.consentements.export', request()->query()) }}" class="sa-btn sa-btn-sm" style="background:#542680;border:none;color:#fff;" {{ $stats['total'] === 0 ? 'disabled' : '' }}>
                            <i class="bi bi-download me-1"></i>Export CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- KPI --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(107,63,160,0.1); color: var(--sa-primary);"><i class="bi bi-shield-check"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($stats['total'], 0, ',', ' ') }}</div>
                <div class="kpi-label">Décisions enregistrées</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-check2-circle"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ $stats['taux_acceptation'] }}%</div>
                <div class="kpi-label">Taux d'acceptation</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-person-check"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($stats['acceptes'], 0, ',', ' ') }}</div>
                <div class="kpi-label">
                    Acceptées
                    <span class="text-muted" style="font-size:0.68rem;">/ {{ number_format($stats['connectes'] + $stats['anonymes'], 0, ',', ' ') }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(231,76,60,0.1); color: #e74c3c;"><i class="bi bi-x-circle"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($stats['refuses'], 0, ',', ' ') }}</div>
                <div class="kpi-label">Refusées</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(243,156,18,0.1); color: var(--sa-warning);"><i class="bi bi-sliders"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($stats['personnalises'], 0, ',', ' ') }}</div>
                <div class="kpi-label">Personnalisées</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(52,152,219,0.1); color: var(--sa-info, #3498db);"><i class="bi bi-person-lines-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($stats['connectes'], 0, ',', ' ') }} <span class="text-muted" style="font-size:1rem;">/ {{ number_format($stats['anonymes'], 0, ',', ' ') }}</span></div>
                <div class="kpi-label">Comptes connectés / anonymes</div>
            </div>
        </div>
    </div>
</div>

{{-- Graphes --}}
<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="sa-card h-100">
            <div class="sa-card-header"><span><i class="bi bi-pie-chart-fill me-2" style="color: var(--sa-primary);"></i>Répartition par statut</span></div>
            <div class="sa-card-body">
                <div class="chart-container center-chart"><canvas id="statutChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="sa-card h-100">
            <div class="sa-card-header"><span><i class="bi bi-person-fill me-2" style="color: var(--sa-primary);"></i>Connectés vs anonymes</span></div>
            <div class="sa-card-body">
                <div class="chart-container center-chart"><canvas id="compteChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="sa-card h-100">
            <div class="sa-card-header"><span><i class="bi bi-shield-plus me-2" style="color: var(--sa-primary);"></i>Services acceptés</span></div>
            <div class="sa-card-body">
                <div class="chart-container center-chart"><canvas id="serviceChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="sa-card h-100">
            <div class="sa-card-header"><span><i class="bi bi-graph-up me-2" style="color: var(--sa-primary);"></i>Évolution quotidienne des décisions</span></div>
            <div class="sa-card-body">
                <div class="chart-container"><canvas id="evolutionChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="sa-card h-100">
            <div class="sa-card-header"><span><i class="bi bi-file-earmark-text me-2" style="color: var(--sa-primary);"></i>Par version de politique</span></div>
            <div class="sa-card-body">
                <div class="chart-container center-chart"><canvas id="versionChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

{{-- Journal des décisions --}}
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-list-check me-2" style="color: var(--sa-primary);"></i>Journal des décisions cookies</span>
        <span class="text-muted ms-auto" style="font-size:0.8rem;">{{ $consentements->total() }} décision(s) affichée(s)</span>
    </div>
    <div class="sa-card-body p-0">
        @if($consentements->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-shield-check" style="font-size:2.5rem;"></i>
            <p class="mt-2 mb-0">Aucune décision de consentement pour le moment.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Personne</th>
                        <th>Statut</th>
                        <th>Services acceptés</th>
                        <th>Version politique</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($consentements as $c)
                    <tr>
                        <td class="text-nowrap" style="font-size:0.8rem;">{{ $c->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($c->user)
                            <div>{{ $c->user->nom }} <span class="text-muted" style="font-size:0.75rem;">({{ $c->user->email }})</span></div>
                            @else
                            <span class="text-muted">Visiteur {{ $c->user_id ? '' : '(anonyme)' }}</span>
                            @endif
                            <div class="text-muted" style="font-size:0.7rem;">Session : {{ $c->session_id ? substr($c->session_id, 0, 12).'…' : '—' }}</div>
                        </td>
                        <td>
                            <span class="sa-badge sa-badge-{{ $c->badgeStatut() }}">{{ $c->libelleStatut() }}</span>
                        </td>
                        <td>
                            @if(empty($c->listeServices()))
                            <span class="text-muted">Aucun</span>
                            @else
                            <div style="font-size:0.75rem;">
                                @foreach($c->listeServices() as $service)
                                <span class="sa-badge sa-badge-secondary me-1">{{ $service }}</span>
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td style="font-size:0.8rem;">{{ $c->version_politique ?? '—' }}</td>
                        <td style="font-size:0.8rem;">{{ $c->ip_visiteur ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">{{ $consentements->links() }}</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var stats = @json($stats);

    function viderSiVide(canvasId, videId, datasets) {
        var aValeur = datasets.some(function (ds) {
            return ds.data.reduce(function (a, b) { return a + b; }, 0) > 0;
        });
        if (!aValeur) {
            var c = document.getElementById(canvasId);
            if (c) { c.style.display = 'none'; }
            var v = document.getElementById(videId);
            if (v) { v.style.display = 'block'; }
            return true;
        }
        var vide = document.getElementById(videId);
        if (vide) { vide.style.display = 'none'; }
        return false;
    }

    var defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, font: { size: 10 } } } }
    };

    // Répartition par statut (donut)
    var statutVide = document.createElement('div');
    statutVide.className = 'text-center text-muted py-4';
    statutVide.style.display = 'none';
    statutVide.innerHTML = 'Pas de données';
    document.querySelector('#statutChart').parentNode.appendChild(statutVide);
    if (!viderSiVide('statutChart', null, [{ data: [stats.acceptes, stats.refuses, stats.personnalises] }]) && typeof Chart !== 'undefined') {
        new Chart(document.getElementById('statutChart'), {
            type: 'doughnut',
            data: {
                labels: ['Acceptés', 'Refusés', 'Personnalisés'],
                datasets: [{
                    data: [stats.acceptes, stats.refuses, stats.personnalises],
                    backgroundColor: ['#27ae60', '#e74c3c', '#f39c12'],
                    borderWidth: 0
                }]
            },
            options: Object.assign({}, defaultOptions, { cutout: '62%' })
        });
    }

    // Connectés vs anonymes (donut)
    new Chart(document.getElementById('compteChart'), {
        type: 'doughnut',
        data: {
            labels: ['Connectés', 'Anonymes'],
            datasets: [{
                data: [stats.connectes, stats.anonymes],
                backgroundColor: ['#6B3FA0', '#95a5a6'],
                borderWidth: 0
            }]
        },
        options: Object.assign({}, defaultOptions, { cutout: '62%' })
    });

    // Services acceptés (barres)
    var servLabels = stats.services.map(function (s) { return s.service; });
    var servData = stats.services.map(function (s) { return s.n; });
    var servVide = document.createElement('div');
    servVide.className = 'text-center text-muted py-4';
    servVide.style.display = 'none';
    servVide.innerHTML = 'Pas de données';
    document.querySelector('#serviceChart').parentNode.appendChild(servVide);
    if (!viderSiVide('serviceChart', null, [{ data: servData }]) && typeof Chart !== 'undefined') {
        new Chart(document.getElementById('serviceChart'), {
            type: 'bar',
            data: {
                labels: servLabels,
                datasets: [{
                    label: 'Acceptations',
                    data: servData,
                    backgroundColor: 'rgba(107,63,160,0.75)',
                    borderRadius: 6
                }]
            },
            options: Object.assign({}, defaultOptions, {
                indexAxis: 'y',
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            })
        });
    }

    // Évolution quotidienne (lignes)
    var evoLabels = stats.evolution.map(function (j) { return j.jour; });
    var evoTot = stats.evolution.map(function (j) { return j.total; });
    var evoAcc = stats.evolution.map(function (j) { return j.accepte; });
    if (typeof Chart !== 'undefined' && evoLabels.length > 0) {
        new Chart(document.getElementById('evolutionChart'), {
            type: 'line',
            data: {
                labels: evoLabels,
                datasets: [
                    {
                        label: 'Nombre de décisions',
                        data: evoTot,
                        borderColor: '#6B3FA0',
                        backgroundColor: 'rgba(107,63,160,0.08)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 2,
                        pointBackgroundColor: '#6B3FA0'
                    },
                    {
                        label: 'Dont acceptées',
                        data: evoAcc,
                        borderColor: '#27ae60',
                        backgroundColor: 'rgba(39,174,96,0.08)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 2,
                        pointBackgroundColor: '#27ae60'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } } },
                scales: {
                    y: { beginAtZero: true, ticks: { font: { size: 10 }, maxTicksLimit: 6, precision: 0 }, grid: { color: 'rgba(0,0,0,0.04)' } },
                    x: { ticks: { font: { size: 9 }, maxRotation: 60, minRotation: 30, autoSkip: true, maxTicksLimit: 15 } }
                }
            }
        });
    } else {
        document.getElementById('evolutionChart').parentNode.innerHTML = '<div class="text-center text-muted py-4">Pas de données</div>';
    }

    // Par version de politique (barres)
    var verLabels = stats.par_version.map(function (v) { return v.version; });
    var verData = stats.par_version.map(function (v) { return v.n; });
    var verVide = document.createElement('div');
    verVide.className = 'text-center text-muted py-4';
    verVide.style.display = 'none';
    verVide.innerHTML = 'Pas de données';
    document.querySelector('#versionChart').parentNode.appendChild(verVide);
    if (!viderSiVide('versionChart', null, [{ data: verData }]) && typeof Chart !== 'undefined') {
        new Chart(document.getElementById('versionChart'), {
            type: 'bar',
            data: {
                labels: verLabels,
                datasets: [{
                    label: 'Décisions',
                    data: verData,
                    backgroundColor: 'rgba(52,152,219,0.75)',
                    borderRadius: 6
                }]
            },
            options: Object.assign({}, defaultOptions, {
                indexAxis: 'y',
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            })
        });
    }
});
</script>
@endpush