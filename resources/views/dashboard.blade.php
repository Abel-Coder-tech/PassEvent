@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de Bord')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Tableau de bord</li>
@endsection

@section('content')
<div class="page-content">

    @if(Auth::user()->statut === 'incomplet')
    <div class="alert alert-warning d-flex align-items-center gap-3 flex-wrap" style="border:none;background:#fff3e0;border-radius:12px;padding:0.75rem 1rem;">
        <div style="flex:1;">
            <strong style="color:#8b6914;"><i class="bi bi-exclamation-triangle-fill me-1"></i> Profil incomplet</strong>
            <span style="color:#6c5b3a;font-size:0.85rem;"> — Finalisez la création de votre compte pour créer des événements</span>
        </div>
        <a href="{{ route('profil.step2') }}" class="btn btn-sm" style="background:#542680;color:#fff;border-radius:8px;font-weight:600;text-decoration:none;white-space:nowrap;">
            <i class="bi bi-arrow-right-circle me-1"></i> Compléter mon profil
        </a>
    </div>
    @endif

    {{-- Actions rapides --}}
    <div class="row g-2 mb-4 align-items-center">
        <div class="col-md-6">
            <span style="font-size:0.82rem;color:var(--gris);font-weight:500;">
                <i class="bi bi-calendar3 me-1"></i> {{ now()->isoFormat('D MMM YYYY') }}
            </span>
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
        </div>
    </div>

    {{-- Metrics --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.1);">⛺</div>
                <div class="metric-label">Événements</div>
                <div class="metric-value">{{ $totalEvenements }}</div>
                <div class="metric-subtitle">{{ $evenementsActifs }} actifs</div>
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
                        <span style="color:var(--vert);">+{{ $pct }}%</span> vs mois dernier
                    @elseif($pct < 0)
                        <span style="color:var(--danger);">{{ $pct }}%</span> vs mois dernier
                    @else
                        vs mois dernier
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--teal);">
                <div class="metric-icon" style="background: rgba(66,140,121,0.1);"><i class="bi bi-cash-coin" style="color:var(--teal);"></i></div>
                <div class="metric-label">Revenu brut</div>
                <div class="metric-value" style="font-size:1.3rem;">{{ number_format($recettesTotales, 0, ',', ' ') }}</div>
                <div class="metric-subtitle">FCFA · Commission {{ $commissionPct }}%</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);"><i class="bi bi-wallet2" style="color:var(--vert);"></i></div>
                <div class="metric-label">Net retirable (Mobile)</div>
                <div class="metric-value" style="font-size:1.3rem;color:var(--vert);">{{ number_format($retirable, 0, ',', ' ') }}</div>
                <div class="metric-subtitle">FCFA après commission</div>
            </div>
        </div>
    </div>

    {{-- Répartition Mobile / Espèces --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: #3498db;">
                <div class="metric-icon" style="background: rgba(52,152,219,0.1);"><i class="bi bi-phone" style="color:#3498db;"></i></div>
                <div class="metric-label">Paiements Mobile</div>
                <div class="metric-value" style="font-size:1.2rem;color:#3498db;">{{ number_format($mobileRecettes, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Retirable via FedaPay</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: #f39c12;">
                <div class="metric-icon" style="background: rgba(243,156,18,0.1);"><i class="bi bi-cash" style="color:#f39c12;"></i></div>
                <div class="metric-label">Paiements Espèces</div>
                <div class="metric-value" style="font-size:1.2rem;color:#f39c12;">{{ number_format($cashRecettes, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Déjà en votre possession</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.1);"><i class="bi bi-percent" style="color:var(--violet);"></i></div>
                <div class="metric-label">Commission ({{ $commissionPct }}%)</div>
                <div class="metric-value" style="font-size:1.2rem;color:var(--danger);">{{ number_format($commission, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Sur tous les tickets</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);"><i class="bi bi-cash-stack" style="color:var(--vert);"></i></div>
                <div class="metric-label">Net total</div>
                <div class="metric-value" style="font-size:1.2rem;color:var(--vert);">{{ number_format($recettesNettes, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Brut − Commission</div>
            </div>
        </div>
    </div>

    {{-- Graphique + Événements récents --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-graph-up me-1" style="color:var(--vert);"></i> Ventes (7 derniers jours)</h5>
                </div>
                <div class="panel-card-body">
                    <canvas id="ventesChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
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
                                $dotColor = '#98919b';
                            } elseif ($isPast && $evenement->statut === 'publié') {
                                $statusLabel = 'Terminé';
                                $dotColor = '#98919b';
                            } elseif ($isToday || $evenement->statut === 'publié') {
                                $statusLabel = $isToday ? 'En cours' : 'À venir';
                                $dotColor = $isToday ? '#12976e' : '#87428b';
                            } else {
                                $statusLabel = ucfirst($evenement->statut);
                                $dotColor = '#98919b';
                            }
                        @endphp
                        <div class="d-flex align-items-center px-3 py-2" style="border-bottom:1px solid #f5f5f5;">
                            <div style="width:8px;height:8px;border-radius:50%;background:{{ $dotColor }};margin-right:0.75rem;flex-shrink:0;"></div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:0.82rem;font-weight:600;color:var(--sombre);">{{ $evenement->titre }}</div>
                                <div style="font-size:0.7rem;color:var(--gris);">
                                    {{ $evenement->date_event->isoFormat('D MMM YYYY') }} — {{ $evenement->quota_vendu }} tickets
                                </div>
                            </div>
                            <span style="font-size:0.65rem;font-weight:600;color:{{ $dotColor }};white-space:nowrap;">{{ $statusLabel }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4" style="color:var(--gris);">
                            <i class="bi bi-calendar-x d-block mb-2" style="font-size:1.5rem;"></i>
                            <small>Aucun événement</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Derniers tickets vendus --}}
    <div class="row g-3">
        <div class="col-12">
            <div class="panel-card">
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
                                <div style="width:28px;height:28px;background:rgba(18,151,110,0.1);border-radius:6px;display:flex;align-items:center;justify-content:center;margin-right:0.75rem;flex-shrink:0;">
                                    <i class="bi bi-ticket" style="color:var(--vert);font-size:0.75rem;"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:0.82rem;font-weight:600;color:var(--sombre);">
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

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: labels.map((_, i) => i === 6 ? '#12976e' : '#b2e0d6'),
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
                    ticks: { color: '#98919b', font: { size: 10 }, stepSize: 1 },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#98919b', font: { size: 10 } },
                    border: { display: false }
                }
            }
        }
    });
</script>
@endsection
