@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('page-title', 'Tableau de Bord')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Tableau de bord</li>
@endsection

@section('topbar-actions')
<span class="badge-scans" style="background:rgba(18,151,110,0.1);color:var(--vert);padding:0.35rem 0.75rem;border-radius:20px;font-size:0.75rem;font-weight:600;white-space:nowrap;">
    <i class="bi bi-credit-card me-1"></i> KKiaPay
</span>
<a href="{{ route('tickets.index') }}" class="btn btn-secondary-custom btn-sm" style="border-radius:8px;">
    <i class="bi bi-ticket-perforated me-1"></i> Tickets
</a>

@endsection

@section('content')
<div class="page-content">

    {{-- Actions rapides --}}
    <div class="row g-2 mb-3 align-items-center">
        <div class="col-md-6">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span style="font-size:0.82rem;color:var(--gris);font-weight:500;">
                    <i class="bi bi-calendar3 me-1"></i> Période :
                </span>
                <select class="form-select form-select-sm" style="width:auto;border-radius:6px;font-size:0.78rem;border-color:#e0dde3;">
                    <option>30 derniers jours</option>
                    <option>7 derniers jours</option>
                    <option>Ce mois</option>
                </select>
            </div>
        </div>
        <div class="col-md-6 d-flex justify-content-md-end gap-2 flex-wrap">
            <a href="{{ route('admin.evenements.create') }}" class="btn btn-sm" style="background:var(--vert);color:#fff;border-radius:8px;font-weight:600;font-size:0.78rem;">
                <i class="bi bi-plus-lg me-1"></i> Nouvel événement
            </a>
            <a href="{{ route('admin.evenements.index') }}" class="btn btn-sm" style="border:1px solid #e0dde3;border-radius:8px;font-weight:600;font-size:0.78rem;">
                <i class="bi bi-calendar-week me-1"></i> Mes événements
            </a>
            <a href="{{ route('scan.index') }}" class="btn btn-sm" style="border:1px solid #e0dde3;border-radius:8px;font-weight:600;font-size:0.78rem;">
                <i class="bi bi-qr-code-scan me-1"></i> Scanner
            </a>
            <a href="{{ route('tickets.index') }}" class="btn btn-sm" style="border:1px solid #e0dde3;border-radius:8px;font-weight:600;font-size:0.78rem;">
                <i class="bi bi-ticket-perforated me-1"></i> Tickets
            </a>
        </div>
    </div>

    {{-- Metrics Row --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.1);">⛺</div>
                <div class="metric-label">Événements</div>
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
        <div class="col-6 col-lg">
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
                        <span style="color:var(--vert);">+{{ $pct }}%</span> ce mois
                    @elseif($pct < 0)
                        <span style="color:var(--danger);">{{ $pct }}%</span> ce mois
                    @else
                        — ce mois
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="metric-card" style="border-top-color: var(--teal);">
                <div class="metric-icon" style="background: rgba(66,140,121,0.1);"><i class="bi bi-cash-coin" style="color:var(--teal);"></i></div>
                <div class="metric-label">Revenu brut</div>
                <div class="metric-value" style="font-size:1.3rem;">{{ number_format($recettesTotales, 0, ',', ' ') }}</div>
                <div class="metric-subtitle">
                    FCFA · Commission {{ $commissionPct }}%:
                    <span style="color:var(--gris);">-{{ number_format($commission, 0, ',', ' ') }}</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);"><i class="bi bi-wallet2" style="color:var(--vert);"></i></div>
                <div class="metric-label">Net encaissé</div>
                <div class="metric-value" style="font-size:1.3rem;color:var(--vert);">{{ number_format($recettesNettes, 0, ',', ' ') }}</div>
                <div class="metric-subtitle">FCFA après commission</div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="metric-card" style="border-top-color: var(--aubergine);">
                <div class="metric-icon" style="background: rgba(109,78,114,0.1);"><i class="bi bi-qr-code-scan" style="color:var(--aubergine);"></i></div>
                <div class="metric-label">Scan d'entrée</div>
                <div class="metric-value">{{ $tauxScan }}%</div>
                <div class="metric-subtitle">{{ number_format($ticketsScannes, 0, ',', ' ') }} validées</div>
            </div>
        </div>
    </div>

    {{-- Ligne principale : Graphique + Stats secondaires --}}
    <div class="row g-3 mb-4">
        {{-- Graphique ventes --}}
        <div class="col-lg-7">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-graph-up me-1" style="color:var(--vert);"></i> Évolution des ventes (7 derniers jours)</h5>
                    <a href="{{ route('tickets.index') }}">Voir tout</a>
                </div>
                <div class="panel-card-body">
                    <canvas id="ventesChart" height="140"></canvas>
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <div class="d-flex gap-3">
                            <div class="d-flex align-items-center gap-1">
                                <div style="width:10px;height:10px;border-radius:2px;background:#12976e;"></div>
                                <small style="color:#98919b;font-size:0.7rem;">Aujourd'hui</small>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <div style="width:10px;height:10px;border-radius:2px;background:#b2e0d6;"></div>
                                <small style="color:#98919b;font-size:0.7rem;">Jours précédents</small>
                            </div>
                        </div>
                        <span class="badge-scans" style="font-size:0.68rem;">
                            <i class="bi bi-credit-card me-1"></i> Paiements KKiaPay
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cartes stats secondaires --}}
        <div class="col-lg-5">
            <div class="row g-3">
                <div class="col-6">
                    <div class="panel-card" style="height:100%;">
                        <div class="panel-card-body text-center py-3">
                            <div style="font-size:1.6rem;font-weight:800;color:var(--violet);">{{ $codesGeneres }}</div>
                            <div style="font-size:0.72rem;color:var(--gris);font-weight:500;">Codes promo générés</div>
                            <div style="font-size:0.65rem;color:#bbb;margin-top:0.2rem;">{{ $codesUtilises }} utilisés</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="panel-card" style="height:100%;">
                        <div class="panel-card-body text-center py-3">
                            <div style="font-size:1.6rem;font-weight:800;color:var(--teal);">{{ number_format($ticketsScannes, 0, ',', ' ') }}</div>
                            <div style="font-size:0.72rem;color:var(--gris);font-weight:500;">Entrées scannées</div>
                            <div style="font-size:0.65rem;color:#bbb;margin-top:0.2rem;">{{ $tauxScan }}% de taux de scan</div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="panel-card">
                        <div class="panel-card-header">
                            <h5 style="font-size:0.82rem;"><i class="bi bi-activity me-1" style="color:var(--vert);"></i> Activité récente</h5>
                        </div>
                        <div class="panel-card-body" style="padding:0.75rem 1rem;">
                            @foreach($activiteRecents as $act)
                                <div class="activity-item" style="padding:0.35rem 0;font-size:0.75rem;">
                                    <div class="activity-dot" style="width:5px;height:5px;margin-top:0.3rem;" style="background:{{ $act['color'] }};"></div>
                                    <div class="activity-text">{!! $act['text'] !!}</div>
                                    <span class="activity-time">{{ $act['time'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ligne basse : Événements récents + Derniers tickets vendus --}}
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="panel-card" style="height:100%;">
                <div class="panel-card-header">
                    <h5><i class="bi bi-calendar-event me-1" style="color:var(--violet);"></i> Événements récents</h5>
                    <a href="{{ route('admin.evenements.index') }}">Voir tout</a>
                </div>
                <div class="panel-card-body" style="padding:0;">
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
                        <div class="event-row" style="padding:0.65rem 1.25rem;">
                            <div class="event-dot" style="background:{{ $dotColor }};"></div>
                            <div class="event-info">
                                <div class="event-name">{{ $evenement->titre }}</div>
                                <div class="event-meta">
                                    {{ $evenement->date_event->format('d M Y') }} — {{ $evenement->quota_vendu }} tickets vendus
                                </div>
                            </div>
                            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4" style="color:var(--gris);">
                            <i class="bi bi-calendar-x d-block mb-2" style="font-size:2rem;"></i>
                            <small>Aucun événement créé</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel-card" style="height:100%;">
                <div class="panel-card-header">
                    <h5><i class="bi bi-ticket-perforated me-1" style="color:var(--teal);"></i> Derniers tickets vendus</h5>
                    <a href="{{ route('tickets.index') }}">Voir tout</a>
                </div>
                <div class="panel-card-body" style="padding:0;">
                    @php
                        $recentTickets = App\Models\Ticket::whereHas('evenement', fn($q) => $q->where('user_id', auth()->id()))
                            ->where('statut_paiement', 'payé')
                            ->latest('date_achat')
                            ->take(5)
                            ->get();
                    @endphp

                    @if($recentTickets->isNotEmpty())
                        @foreach($recentTickets as $ticket)
                            <div class="d-flex align-items-center px-3 py-2" style="border-bottom:1px solid #f5f5f5;">
                                <div style="width:32px;height:32px;background:rgba(18,151,110,0.1);border-radius:6px;display:flex;align-items:center;justify-content:center;margin-right:0.75rem;flex-shrink:0;">
                                    <i class="bi bi-ticket" style="color:var(--vert);font-size:0.8rem;"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:0.82rem;font-weight:600;color:var(--sombre);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $ticket->nom_acheteur ?? 'Anonyme' }}
                                    </div>
                                    <div style="font-size:0.68rem;color:var(--gris);">
                                        {{ $ticket->evenement->titre ?? '—' }} · {{ $ticket->date_achat?->format('d/m H:i') }}
                                    </div>
                                </div>
                                <div style="font-size:0.85rem;font-weight:700;color:var(--vert);white-space:nowrap;">
                                    {{ number_format($ticket->montant, 0, ',', ' ') }} F
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4" style="color:var(--gris);">
                            <i class="bi bi-cart-x d-block mb-2" style="font-size:1.5rem;"></i>
                            <small>Aucune vente récente</small>
                        </div>
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
