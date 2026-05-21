@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('page-title', 'Tableau de bord')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Tableau de bord</li>
@endsection

@section('topbar-actions')
<span class="badge-scans">{{ $ticketsScannes }} scans récents</span>
<a href="{{ route('tickets.index') }}" class="btn btn-secondary-custom btn-sm">Voir les tickets</a>
@endsection

@section('content')
<div class="page-content">
    <!-- Metrics Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.1);">⛺</div>
                <div class="metric-label">Événements créés</div>
                <div class="metric-value">{{ $totalEvenements }}</div>
                <div class="metric-subtitle">
                    @php
                        $thisWeek = App\Models\Evenement::where('user_id', auth()->id())
                            ->where('statut', 'publié')
                            ->where('created_at', '>=', now()->startOfWeek())
                            ->count();
                    @endphp
                    {{ $evenementsActifs }} actifs · {{ $thisWeek }} cette semaine
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);">🎫</div>
                <div class="metric-label">Tickets vendus</div>
                <div class="metric-value">{{ number_format($ticketsVendus, 0, ',', ' ') }}</div>
                <div class="metric-subtitle">
                    @php
                        $thisMonth = App\Models\Ticket::whereHas('evenement', fn($q) => $q->where('user_id', auth()->id()))
                            ->where('statut_paiement', 'payé')
                            ->where('date_achat', '>=', now()->startOfMonth())
                            ->count();
                        $lastMonth = App\Models\Ticket::whereHas('evenement', fn($q) => $q->where('user_id', auth()->id()))
                            ->where('statut_paiement', 'payé')
                            ->whereBetween('date_achat', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
                            ->count();
                        $pct = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100) : 0;
                    @endphp
                    @if($pct > 0)
                        +{{ $pct }}% ce mois
                    @elseif($pct < 0)
                        {{ $pct }}% ce mois
                    @else
                        — ce mois
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--teal);">
                <div class="metric-icon" style="background: rgba(66,140,121,0.1);">💰</div>
                <div class="metric-label">Revenus encaissés</div>
                <div class="metric-value" style="font-size: 1.5rem;">{{ number_format($recettesTotales, 0, ',', ' ') }}</div>
                <div class="metric-subtitle">FCFA via <span style="color: var(--vert); font-weight: 700;">KKiaPay</span></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--aubergine);">
                <div class="metric-icon" style="background: rgba(109,78,114,0.1);"><span style="color: var(--vert);">✓</span></div>
                <div class="metric-label">Taux de scan</div>
                <div class="metric-value">{{ $tauxScan }}%</div>
                <div class="metric-subtitle">{{ number_format($ticketsScannes, 0, ',', ' ') }} entrées validées</div>
            </div>
        </div>
    </div>

    <!-- Ligne 2 : Graphique + Actualités -->
    <div class="row g-3 mb-4">
        <!-- Chart -->
        <div class="col-lg-7">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5>Ventes de tickets — 7 derniers jours</h5>
                    <a href="{{ route('tickets.index') }}">Voir tout</a>
                </div>
                <div class="panel-card-body">
                    <canvas id="ventesChart" height="120"></canvas>
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <div class="d-flex gap-3">
                            <div class="d-flex align-items-center gap-1">
                                <div style="width: 10px; height: 10px; border-radius: 2px; background: #12976e;"></div>
                                <small style="color: #98919b; font-size: 0.7rem;">Aujourd'hui</small>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <div style="width: 10px; height: 10px; border-radius: 2px; background: #b2e0d6;"></div>
                                <small style="color: #98919b; font-size: 0.7rem;">Jours précédents</small>
                            </div>
                        </div>
                        <span class="badge-scans" style="font-size: 0.68rem;">
                            <i class="bi bi-credit-card me-1"></i>Paiements KKiaPay
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Événements récents -->
        <div class="col-lg-5">
            <div class="panel-card" style="height: 100%;">
                <div class="panel-card-header">
                    <h5>Événements</h5>
                    <a href="{{ route('admin.evenements.index') }}">Voir tout</a>
                </div>
                <div class="panel-card-body" style="padding: 0;">
                    @forelse($evenementsRecents as $evenement)
                        @php
                            $now = now();
                            $isPast = $evenement->date_event < $now;
                            $isToday = $evenement->date_event->isToday();

                            if ($evenement->statut === 'terminé' || $evenement->statut === 'annulé') {
                                $statusLabel = 'Terminé';
                                $statusClass = 'status-termine';
                                $dotColor = '#98919b';
                            } elseif ($isPast && $evenement->statut === 'publié') {
                                $statusLabel = 'Terminé';
                                $statusClass = 'status-termine';
                                $dotColor = '#98919b';
                            } elseif ($isToday || $evenement->statut === 'publié') {
                                if ($isToday) {
                                    $statusLabel = 'En cours';
                                    $statusClass = 'status-en-cours';
                                    $dotColor = '#12976e';
                                } else {
                                    $statusLabel = 'À venir';
                                    $statusClass = 'status-a-venir';
                                    $dotColor = '#87428b';
                                }
                            } else {
                                $statusLabel = ucfirst($evenement->statut);
                                $statusClass = 'status-brouillon';
                                $dotColor = '#98919b';
                            }
                        @endphp
                        <div class="event-row" style="padding: 0.75rem 1.25rem;">
                            <div class="event-dot" style="background: {{ $dotColor }};"></div>
                            <div class="event-info">
                                <div class="event-name">{{ $evenement->titre }}</div>
                                <div class="event-meta">
                                    {{ $evenement->date_event->format('d M Y') }} — {{ $evenement->quota_vendu }} tickets
                                </div>
                            </div>
                            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4" style="color: #98919b;">
                            <i class="bi bi-calendar-x d-block mb-2" style="font-size: 2rem;"></i>
                            <small>Aucun événement</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Ligne 3 : Scan + Promotions + Activité -->
    <div class="row g-3">
        <!-- Scan en temps réel -->
        <div class="col-lg-4">
            <div class="panel-card" style="height: 100%;">
                <div class="panel-card-header">
                    <h5>Scan en temps réel</h5>
                </div>
                <div class="panel-card-body">
                    @if($eventEnCours)
                        <div style="background: #f5f5f5; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                            <div style="font-size: 0.75rem; color: #98919b; margin-bottom: 0.25rem;">{{ $eventEnCours->titre }}</div>
                            <div style="font-size: 1.5rem; font-weight: 800; color: #3d4345;">{{ $scanValides }} / {{ $scanTotal }}</div>
                            <div class="progress-bar-custom mt-2">
                                <div class="progress-bar-fill" style="width: {{ $scanPct }}%;"></div>
                            </div>
                        </div>
                    @else
                        <div style="background: #f5f5f5; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; text-align: center;">
                            <div style="font-size: 0.82rem; color: #98919b;">Aucun événement actif</div>
                        </div>
                    @endif

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ number_format($ticketsScannes, 0, ',', ' ') }}</div>
                                <div class="mini-stat-label">Entrées</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mini-stat">
                                <div class="mini-stat-value text-rouge">{{ number_format($ticketsAbsents, 0, ',', ' ') }}</div>
                                <div class="mini-stat-label">Absents</div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('scan.index') }}" class="btn btn-secondary-custom w-100" style="font-size: 0.82rem;">
                        <i class="bi bi-camera me-1"></i> Ouvrir le scanner
                    </a>
                    <div class="text-center mt-2" style="font-size: 0.65rem; color: #98919b;">
                        Partagez l'accès à un agent sans compte
                    </div>
                </div>
            </div>
        </div>

        <!-- Codes promos -->
        <div class="col-lg-4">
            <div class="panel-card" style="height: 100%;">
                <div class="panel-card-header">
                    <h5>Codes promos</h5>
                    <a href="#">+ Générer</a>
                </div>
                <div class="panel-card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="mini-stat">
                                <div class="mini-stat-value">{{ $codesGeneres }}</div>
                                <div class="mini-stat-label">Générés</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mini-stat">
                                <div class="mini-stat-value text-vert">{{ $codesUtilises }}</div>
                                <div class="mini-stat-label">Utilisés</div>
                            </div>
                        </div>
                    </div>

                    @if($codesPromos->isNotEmpty())
                        <div style="max-height: 150px; overflow-y: auto;">
                            @foreach($codesPromos as $code)
                                <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid #f5f5f5;">
                                    <span class="promo-code">{{ $code->code }}</span>
                                    @if($code->nb_utilisations > 0)
                                        <span class="promo-status promo-utilise">Utilisé</span>
                                    @else
                                        <span class="promo-status promo-disponible">Disponible</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3" style="color: #98919b; font-size: 0.82rem;">
                            Aucun code promo généré
                        </div>
                    @endif

                    <button class="btn btn-secondary-custom w-100 mt-3" style="font-size: 0.82rem;">
                        <i class="bi bi-download me-1"></i> Exporter CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Activité & Logs -->
        <div class="col-lg-4">
            <div class="panel-card" style="height: 100%;">
                <div class="panel-card-header">
                    <h5>Activité & Logs</h5>
                    <a href="#">Logs</a>
                </div>
                <div class="panel-card-body">
                    @foreach($activiteRecents as $act)
                        <div class="activity-item">
                            <div class="activity-dot" style="background: {{ $act['color'] }};"></div>
                            <div class="activity-text">{!! $act['text'] !!}</div>
                            <span class="activity-time">{{ $act['time'] }}</span>
                        </div>
                    @endforeach

                    @php
                        $refundsPending = App\Models\Ticket::whereHas('evenement', fn($q) => $q->where('user_id', auth()->id()))
                            ->where('statut_paiement', 'en_attente')
                            ->count();
                    @endphp
                    @if($refundsPending > 0)
                        <button class="btn btn-outline-rouge w-100 mt-3" style="font-size: 0.82rem;">
                            <i class="bi bi-exclamation-triangle me-1"></i> {{ $refundsPending }} remboursement{{ $refundsPending > 1 ? 's' : '' }} en attente
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const ctx = document.getElementById('ventesChart').getContext('2d');

    const data = @json($ventes7Jours);
    const labels = @json($joursLabels);
    const colors = labels.map((_, i) => i === 6 ? '#12976e' : '#b2e0d6');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderRadius: 4,
                borderSkipped: false,
                barPercentage: 0.6,
                categoryPercentage: 0.7,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#3d4345',
                    padding: 10,
                    cornerRadius: 6,
                    titleFont: { size: 11 },
                    bodyFont: { size: 12, weight: 'bold' },
                    callbacks: {
                        label: function(ctx) {
                            return ctx.parsed.y + ' tickets';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                    ticks: {
                        color: '#98919b',
                        font: { size: 10 },
                        stepSize: 1,
                    },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#98919b',
                        font: { size: 10, weight: '500' }
                    },
                    border: { display: false }
                }
            }
        }
    });
</script>
@endsection
