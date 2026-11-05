@extends('layouts.affiliate')

@section('title', 'Tableau de bord — Espace affilié PaxEvent')

@section('content')
    <div class="mb-4">
        <h1 class="h4 fw-bold mb-1">Tableau de bord</h1>
        <p class="text-muted small mb-0">Vue d'ensemble de vos performances d'affiliation.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card aff-card h-100 border-0" style="border-top:4px solid #542680 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Liens</span>
                        <i class="bi bi-link-45deg" style="color:#542680;"></i>
                    </div>
                    <div class="h4 fw-bold mb-0">{{ $stats['liens'] }}</div>
                    <div class="small text-muted">{{ $stats['liens_actifs'] }} actif(s)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card aff-card h-100 border-0" style="border-top:4px solid #0dcaf0 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Clics</span>
                        <i class="bi bi-cursor text-info"></i>
                    </div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['clics'], 0, ',', ' ') }}</div>
                    <div class="small text-muted">Visites sur vos liens</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card aff-card h-100 border-0" style="border-top:4px solid #198754 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Ventes</span>
                        <i class="bi bi-ticket-perforated text-success"></i>
                    </div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['ventes'], 0, ',', ' ') }}</div>
                    <div class="small text-muted">Billets vendus via vos liens</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card aff-card h-100 border-0" style="border-top:4px solid #FED514 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Commissions</span>
                        <i class="bi bi-cash-coin" style="color:#b8860b;"></i>
                    </div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['total_gagne'], 0, ',', ' ') }}</div>
                    <div class="small text-muted">FCFA générés · {{ $stats['evenements_promus'] }} événement(s) promu(s)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card aff-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h6 fw-bold mb-0">Performance (30 derniers jours)</h2>
                        <span class="text-muted small">Ventes & commissions</span>
                    </div>
                    <div style="height:260px;">
                        <canvas id="affStatsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card aff-card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-3">Résumé</h2>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Liens actifs</span>
                        <span class="fw-semibold">{{ $stats['liens_actifs'] }} / {{ $stats['liens'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Taux de conversion</span>
                        <span class="fw-semibold">
                            @php $taux = $stats['clics'] > 0 ? round(($stats['ventes'] / $stats['clics']) * 100, 1) : 0; @endphp
                            {{ $taux }} %
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Événements promus</span>
                        <span class="fw-semibold">{{ $stats['evenements_promus'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted small">Commissions / vente</span>
                        <span class="fw-semibold">
                            @php $moy = $stats['ventes'] > 0 ? round($stats['total_gagne'] / $stats['ventes'], 0) : 0; @endphp
                            {{ number_format($moy, 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-7">
            <div class="card aff-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h6 fw-bold mb-0">Liens récents</h2>
                        <a href="{{ route('affiliate.liens') }}" class="small text-decoration-none">Voir tout</a>
                    </div>
                    @if($liens->isEmpty())
                        <div class="text-muted small">Aucun lien pour le moment. <a href="{{ route('affiliate.liens') }}">Générez votre premier lien</a>.</div>
                    @else
                        <div class="d-flex flex-column gap-2">
                            @foreach($liens as $lien)
                                @php
                                    $detailUrl = $lien->evenement ? route('affiliate.evenements.show', $lien->evenement) : null;
                                    $url = $lien->evenement ? route('evenements.affilie.redirect', ['code' => $lien->code]) : null;
                                    $ventes = (int) ($lien->ventes_count ?? 0);
                                    $comm = (float) ($lien->commissions_sum_montant ?? 0);
                                @endphp
                                <div class="d-flex align-items-center justify-content-between gap-2 p-2 rounded-3" style="background:#faf9fc;">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        @if($detailUrl)
                                            <a href="{{ $detailUrl }}" class="aff-ev-thumb" style="width:44px;height:44px;border-radius:10px;">
                                                @if($lien->evenement->image)
                                                    <img src="{{ asset('storage/' . $lien->evenement->image) }}" alt="" loading="lazy">
                                                @else
                                                    <i class="bi bi-calendar-event"></i>
                                                @endif
                                            </a>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="small fw-semibold text-truncate">{{ $lien->evenement->titre ?? '—' }}</div>
                                            <div class="text-muted" style="font-size:0.75rem;">
                                                {{ $lien->clics }} clics · {{ $ventes }} ventes · {{ number_format($comm, 0, ',', ' ') }} FCFA
                                                @unless($lien->actif) · <span class="text-danger">Inactif</span>@endunless
                                            </div>
                                        </div>
                                    </div>
                                    @if($url)
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copierLien(this, '{{ $url }}')" title="Copier"><i class="bi bi-clipboard"></i></button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <div class="card aff-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h6 fw-bold mb-0">Dernières commissions</h2>
                        <a href="{{ route('affiliate.commissions') }}" class="small text-decoration-none">Voir tout</a>
                    </div>
                    @if($commissions->isEmpty())
                        <div class="text-muted small">Aucune commission pour le moment.</div>
                    @else
                        <div class="d-flex flex-column gap-2">
                            @foreach($commissions as $commission)
                                @php
                                    $badge = ['en_attente'=>'bg-warning-subtle text-warning-emphasis','disponible'=>'bg-info-subtle text-info-emphasis','paye'=>'bg-success-subtle text-success-emphasis','annule'=>'bg-secondary-subtle text-secondary-emphasis'][$commission->statut] ?? 'bg-secondary-subtle';
                                    $labels = ['en_attente'=>'En attente','disponible'=>'Disponible','paye'=>'Payé','annule'=>'Annulé'];
                                @endphp
                                <div class="d-flex align-items-center justify-content-between gap-2 py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="min-w-0">
                                        <div class="small fw-semibold text-truncate">{{ $commission->evenement?->titre ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:0.75rem;">{{ $commission->created_at?->format('d/m/Y') }}</div>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <div class="small fw-bold">{{ number_format((float) $commission->montant, 0, ',', ' ') }} F</div>
                                        <span class="badge {{ $badge }}" style="font-size:0.65rem;">{{ $labels[$commission->statut] ?? $commission->statut }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card aff-card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h2 class="h6 fw-bold mb-1">Événements ouverts au co-parrainage</h2>
                    <p class="text-muted small mb-0">Générez votre lien et touchez une commission sur chaque billet vendu.</p>
                </div>
                <a href="{{ route('affiliate.liens') }}" class="small text-decoration-none">Voir tous les événements <i class="bi bi-arrow-right"></i></a>
            </div>
            @if($evenementsOuverts->isNotEmpty())
                <div class="row g-3">
                    @foreach($evenementsOuverts as $ev)
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="border rounded-3 h-100 d-flex flex-column overflow-hidden bg-white">
                                <a href="{{ route('affiliate.evenements.show', $ev) }}" class="aff-offer-cover" style="height:120px;">
                                    @if($ev->image)
                                        <img src="{{ asset('storage/' . $ev->image) }}" alt="{{ $ev->titre }}" loading="lazy">
                                    @else
                                        <i class="bi bi-calendar-event"></i>
                                    @endif
                                </a>
                                <div class="p-3 d-flex flex-column flex-grow-1">
                                    <a href="{{ route('affiliate.evenements.show', $ev) }}" class="fw-semibold text-decoration-none small text-truncate d-block" style="color:inherit;">{{ $ev->titre }}</a>
                                    <div class="text-muted mb-2" style="font-size:0.75rem;">
                                        {{ $ev->user?->nom ?? 'Organisateur' }}@if($ev->date_event) · {{ $ev->date_event->format('d/m/Y') }}@endif
                                    </div>
                                    <div class="mb-2">
                                        <span class="badge bg-success-subtle text-success-emphasis" style="font-size:0.7rem;">{{ rtrim(rtrim(number_format((float) $ev->co_parrainage_pourcentage, 2, ',', ' '), '0'), ',') }} % / billet</span>
                                    </div>
                                    <form action="{{ route('affiliate.liens.generer', $ev) }}" method="POST" class="mt-auto">
                                        @csrf
                                        <button type="submit" class="btn btn-violet btn-sm w-100"><i class="bi bi-link-45deg me-1"></i> Générer mon lien</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-muted small">Aucun événement à promouvoir pour le moment. <a href="{{ route('affiliate.liens') }}">Voir tous les événements</a>.</div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function copierLien(btn, val) {
    function done() { var html = btn.innerHTML; btn.innerHTML = '<i class="bi bi-check2"></i>'; setTimeout(function(){ btn.innerHTML = html; }, 1500); }
    function fallback() { var i = document.createElement('input'); i.value = val; document.body.appendChild(i); i.select(); try{document.execCommand('copy');}catch(e){} document.body.removeChild(i); done(); }
    if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(val).then(done).catch(fallback); else fallback();
}
(function() {
    var el = document.getElementById('affStatsChart');
    if (!el || typeof Chart === 'undefined') return;
    var labels = @json($chart['labels'] ?? []);
    var ventes = @json($chart['ventes'] ?? []);
    var montants = @json($chart['montants'] ?? []);
    new Chart(el, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Ventes',
                    data: ventes,
                    backgroundColor: 'rgba(25, 135, 84, 0.75)',
                    borderColor: '#198754',
                    borderWidth: 1,
                    borderRadius: 4,
                    yAxisID: 'y',
                    maxBarThickness: 18
                },
                {
                    label: 'Commissions (FCFA)',
                    data: montants,
                    backgroundColor: 'rgba(84, 38, 128, 0.55)',
                    borderColor: '#542680',
                    borderWidth: 1,
                    borderRadius: 4,
                    yAxisID: 'y1',
                    maxBarThickness: 18
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: true, position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            var v = ctx.parsed.y;
                            if (ctx.dataset.yAxisID === 'y1') return ctx.dataset.label + ': ' + new Intl.NumberFormat('fr-FR').format(v) + ' FCFA';
                            return ctx.dataset.label + ': ' + v;
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 10, maxRotation: 0 } },
                y: { type: 'linear', display: true, position: 'left', beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'Ventes' } },
                y1: { type: 'linear', display: true, position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, ticks: { callback: function(v){ return new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v); } }, title: { display: true, text: 'FCFA' } }
            }
        }
    });
})();
</script>
@endpush
