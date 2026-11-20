@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de Bord')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Tableau de bord</li>
@endsection

@section('styles')
<style>
    .dash-hello h1 { font-size: 1.45rem; font-weight: 800; letter-spacing: -.02em; margin: 0; }
    .dash-hello p { font-size: .82rem; color: var(--gris-fonce); margin: .25rem 0 0; }
    .dash-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
    .dash-actions .app-btn { flex: 1 1 auto; }

    .flux { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .flux-item { padding: 1rem 1.1rem; border-right: 1px solid var(--ligne); min-width: 0; }
    .flux-item:last-child { border-right: 0; }
    .flux-label { display: flex; align-items: center; gap: .4rem; font-size: .7rem; font-weight: 700; color: var(--gris-fonce); }
    .flux-label i { color: var(--c); }
    .flux-value { font-size: 1.05rem; font-weight: 800; margin-top: .4rem; color: var(--c); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .flux-bar { height: 5px; border-radius: 99px; background: var(--gris-clair); margin-top: .6rem; overflow: hidden; }
    .flux-bar > i { display: block; height: 100%; border-radius: 99px; background: var(--c); }
    .flux-note { font-size: .68rem; color: var(--gris); margin-top: .35rem; }

    .dash-event { display: flex; align-items: center; gap: .7rem; padding: .7rem 1.1rem; border-bottom: 1px solid var(--ligne); }
    .dash-event:last-child { border-bottom: 0; }
    .dash-event .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--c); flex-shrink: 0; }
    .dash-event .txt { flex: 1; min-width: 0; }
    .dash-event .txt strong { display: block; font-size: .82rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dash-event .txt small { font-size: .7rem; color: var(--gris); }
    .dash-pill { font-size: .64rem; font-weight: 700; padding: .2rem .55rem; border-radius: 99px; white-space: nowrap; }

    @media (max-width: 991.98px) {
        .flux { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .flux-item:nth-child(2) { border-right: 0; }
        .flux-item:nth-child(-n+2) { border-bottom: 1px solid var(--ligne); }
    }
    @media (max-width: 575.98px) {
        .dash-hello h1 { font-size: 1.25rem; }
        .dash-actions .app-btn { flex: 1 1 45%; justify-content: center; }
        .flux-item { padding: .8rem; }
        .flux-value { font-size: .9rem; }
        .dash-tickets-table { display: none; }
        .dash-ticket-card { display: block; }
    }
    @media (min-width: 576px) { .dash-ticket-card { display: none; } }
</style>
@endsection

@section('content')
<div class="page-content">

    {{-- État du compte : un seul bandeau, une seule action --}}
    @if(Auth::user()->statut === 'incomplet')
        <div class="app-alert alert-warn">
            <div>
                <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Profil incomplet</strong>
                <span class="d-block mt-1">Finalisez la création de votre compte pour créer des événements.</span>
            </div>
            <a href="{{ route('profil.step2') }}" class="app-btn primary"><i class="bi bi-arrow-right-circle me-1"></i> Compléter mon profil</a>
        </div>
    @elseif(Auth::user()->statut === 'corrections_demandees')
        <div class="app-alert alert-warn">
            <div>
                <strong><i class="bi bi-pencil-square me-1"></i> Corrections demandées</strong>
                <span class="d-block mt-1">Des modifications sont requises sur votre profil. Corrigez puis soumettez à nouveau.</span>
            </div>
            <a href="{{ route('profil.step2') }}" class="app-btn primary"><i class="bi bi-arrow-right-circle me-1"></i> Modifier mon profil</a>
        </div>
    @elseif(Auth::user()->statut === 'actif' && empty(Auth::user()->contrat_telecharge_le))
        <div class="app-alert alert-ok">
            <div><strong><i class="bi bi-check-circle-fill me-1"></i> Compte valide</strong></div>
            <a href="{{ route('contrat-prestation') }}" class="app-btn vert"><i class="bi bi-file-pdf me-1"></i> Télécharger mon contrat</a>
        </div>
    @endif

    @if(Auth::user()->profilEnCoursDeValidation())
        <div class="app-alert alert-info">
            <div>
                <i class="bi bi-info-circle me-1"></i> Vous pouvez créer et publier vos événements dès maintenant. La vente s'ouvrira automatiquement dès validation de votre profil par PaxEvent.
            </div>
        </div>
    @endif

    {{-- Accueil --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div class="dash-hello">
            <div class="section-title mb-1"><i class="bi bi-grid-1x2"></i> Vue d’ensemble</div>
            <h1>Bonjour {{ Auth::user()->nom }} 👋</h1>
            <p><i class="bi bi-calendar3 me-1"></i> {{ now()->isoFormat('dddd D MMMM YYYY') }}</p>
        </div>
        <div class="dash-actions">
            <a href="{{ route('admin.evenements.create') }}" class="app-btn vert"><i class="bi bi-plus-lg"></i> Nouvel événement</a>
            <a href="{{ route('admin.evenements.index') }}" class="app-btn"><i class="bi bi-calendar-week"></i> Mes événements</a>
            <a href="{{ route('scan.index') }}" class="app-btn"><i class="bi bi-qr-code-scan"></i> Scanner</a>
            @if($appRelease)
            <a href="{{ URL::temporarySignedRoute('app.download', now()->addMinutes(15), ['release' => $appRelease->id, 'uid' => Auth::id()]) }}" class="app-btn primary">
                <i class="bi bi-phone"></i> PaxAgent v{{ $appRelease->version_code }}
            </a>
            @endif
        </div>
    </div>

    @if($appRelease)
    <div class="mb-3" style="font-size:0.72rem;color:var(--gris);">
        PaxAgent {{ $appRelease->version_name }} (build {{ $appRelease->version_code }}) · SHA-256 :
        <code style="font-size:0.7rem;">{{ $appRelease->sha256 }}</code>
    </div>
    @endif

    {{-- Indicateurs clés --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color:var(--violet);">
                <div class="metric-icon" style="background:var(--violet-pale);color:var(--violet);"><i class="bi bi-calendar-event"></i></div>
                <div class="metric-label">Événements</div>
                <div class="metric-value">{{ $totalEvenements }}</div>
                <div class="metric-subtitle">{{ $evenementsActifs }} actifs</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            @php
                $thisMonth = App\Models\Ticket::whereHas('evenement', fn($q) => $q->where('user_id', auth()->id()))
                    ->where('statut_paiement', 'payé')->whereNull('lot_physique_id')
                    ->where('date_achat', '>=', now()->startOfMonth())->count();
                $lastMonth = App\Models\Ticket::whereHas('evenement', fn($q) => $q->where('user_id', auth()->id()))
                    ->where('statut_paiement', 'payé')->whereNull('lot_physique_id')
                    ->whereBetween('date_achat', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->count();
                $pct = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100) : 0;
            @endphp
            <div class="metric-card" style="border-top-color:var(--vert);">
                <div class="metric-icon" style="background:var(--vert-pale);color:var(--vert);"><i class="bi bi-ticket-perforated"></i></div>
                <div class="metric-label">Tickets vendus</div>
                <div class="metric-value">{{ number_format($ticketsVendus, 0, ',', ' ') }}</div>
                <div class="metric-subtitle">
                    @if($pct > 0)<span style="color:var(--vert);font-weight:700;">+{{ $pct }}%</span> vs mois dernier
                    @elseif($pct < 0)<span style="color:var(--danger);font-weight:700;">{{ $pct }}%</span> vs mois dernier
                    @else vs mois dernier @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color:var(--info);">
                <div class="metric-icon" style="background:var(--info-pale);color:var(--info);"><i class="bi bi-cash-coin"></i></div>
                <div class="metric-label">Revenu brut</div>
                <div class="metric-value" style="font-size:1.4rem;">{{ number_format($recettesTotales, 0, ',', ' ') }}</div>
                <div class="metric-subtitle">FCFA · Commission {{ $commissionPct }}%</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color:var(--vert);">
                <div class="metric-icon" style="background:var(--vert-pale);color:var(--vert);"><i class="bi bi-wallet2"></i></div>
                <div class="metric-label">Net retirable</div>
                <div class="metric-value" style="font-size:1.4rem;color:var(--vert);">{{ number_format($retirable, 0, ',', ' ') }}</div>
                <div class="metric-subtitle">FCFA · après commission</div>
            </div>
        </div>
    </div>

    {{-- Flux d'argent : une seule bande --}}
    @php
        $fluxBase = max($mobileRecettes + $cashRecettes, 1);
        $brutBase = max($recettesTotales, 1);
        $mobilePct = min(100, round($mobileRecettes / $fluxBase * 100));
        $cashPct = min(100, round($cashRecettes / $fluxBase * 100));
        $commissionFluxPct = min(100, round($commission / $brutBase * 100));
        $netFluxPct = min(100, round($recettesNettes / $brutBase * 100));
    @endphp
    <div class="panel-card mb-4">
        <div class="panel-card-header">
            <h5><i class="bi bi-bar-chart-steps" style="color:var(--violet);"></i> Flux d’argent</h5>
            <a href="{{ route('admin.retraits.index') }}">Voir les retraits</a>
        </div>
        <div class="flux">
            <div class="flux-item" style="--c:var(--info);">
                <div class="flux-label"><i class="bi bi-phone"></i> Mobile money</div>
                <div class="flux-value">{{ number_format($mobileRecettes, 0, ',', ' ') }} F</div>
                <div class="flux-bar"><i style="width:{{ $mobilePct }}%"></i></div>
                <div class="flux-note">Retirable via FedaPay</div>
            </div>
            <div class="flux-item" style="--c:var(--warning);">
                <div class="flux-label"><i class="bi bi-cash"></i> Espèces</div>
                <div class="flux-value">{{ number_format($cashRecettes, 0, ',', ' ') }} F</div>
                <div class="flux-bar"><i style="width:{{ $cashPct }}%"></i></div>
                <div class="flux-note">Déjà en votre possession</div>
            </div>
            <div class="flux-item" style="--c:var(--violet-clair);">
                <div class="flux-label"><i class="bi bi-percent"></i> Commission</div>
                <div class="flux-value">{{ number_format($commission, 0, ',', ' ') }} F</div>
                <div class="flux-bar"><i style="width:{{ $commissionFluxPct }}%"></i></div>
                <div class="flux-note">{{ $commissionPct }} % du brut</div>
            </div>
            <div class="flux-item" style="--c:var(--vert);">
                <div class="flux-label"><i class="bi bi-check2-circle"></i> Net total</div>
                <div class="flux-value">{{ number_format($recettesNettes, 0, ',', ' ') }} F</div>
                <div class="flux-bar"><i style="width:{{ $netFluxPct }}%"></i></div>
                <div class="flux-note">{{ $formuleNet }}</div>
            </div>
        </div>
    </div>

    {{-- Activité : graphique + événements récents --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-graph-up" style="color:var(--vert);"></i> Ventes — 7 derniers jours</h5>
                    <span class="status-badge status-en-cours">{{ array_sum($ventes7Jours ?? []) }} tickets</span>
                </div>
                <div class="panel-card-body">
                    <div style="position:relative;height:200px;"><canvas id="ventesChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-calendar-event" style="color:var(--violet);"></i> Événements récents</h5>
                    <a href="{{ route('admin.evenements.index') }}">Voir tout</a>
                </div>
                <div style="padding:.25rem 0;">
                    @forelse($evenementsRecents as $evenement)
                        @php
                            $now = now();
                            $isPast = $evenement->date_event < $now;
                            $isToday = $evenement->date_event->isToday();
                            if ($evenement->statut === 'terminé' || $evenement->statut === 'annulé' || ($isPast && $evenement->statut === 'publié')) {
                                $statusLabel = 'Terminé'; $dotColor = 'var(--gris)'; $pill = 'status-brouillon';
                            } elseif ($isToday) {
                                $statusLabel = 'En cours'; $dotColor = 'var(--vert)'; $pill = 'status-en-cours';
                            } elseif ($evenement->statut === 'publié') {
                                $statusLabel = 'À venir'; $dotColor = 'var(--violet)'; $pill = 'status-a-venir';
                            } else {
                                $statusLabel = ucfirst($evenement->statut); $dotColor = 'var(--gris)'; $pill = 'status-brouillon';
                            }
                        @endphp
                        <div class="dash-event" style="--c:{{ $dotColor }};">
                            <span class="dot"></span>
                            <div class="txt">
                                <strong>{{ $evenement->titre }}</strong>
                                <small>{{ $evenement->date_event->isoFormat('D MMM YYYY') }} — {{ $evenement->quota_vendu }} tickets</small>
                            </div>
                            <span class="dash-pill status-badge {{ $pill }}">{{ $statusLabel }}</span>
                        </div>
                    @empty
                        <div class="empty-state"><i class="bi bi-calendar-x"></i><small>Aucun événement</small></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Derniers tickets vendus --}}
    @php
        $recentTickets = App\Models\Ticket::whereHas('evenement', fn($q) => $q->where('user_id', auth()->id()))
            ->where('statut_paiement', 'payé')->whereNull('lot_physique_id')
            ->latest('date_achat')->take(8)->get();
    @endphp
    <div class="panel-card">
        <div class="panel-card-header">
            <h5><i class="bi bi-ticket-perforated" style="color:var(--teal);"></i> Derniers tickets vendus</h5>
            <a href="{{ route('tickets.index') }}">Voir tout</a>
        </div>
        @if($recentTickets->isNotEmpty())
            <div class="table-responsive dash-tickets-table">
                <table class="table align-middle mb-0 custom-table" style="font-size:.82rem;">
                    <thead>
                        <tr>
                            <th class="ps-3">Acheteur</th>
                            <th>Événement</th>
                            <th>Date</th>
                            <th class="text-end pe-3">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTickets as $ticket)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold">{{ $ticket->nom_acheteur ?? 'Anonyme' }}</div>
                                    @if($ticket->whatsapp_acheteur || $ticket->telephone_paiement || $ticket->telephone_acheteur)
                                        <div class="text-muted" style="font-size:.72rem;">
                                            <i class="bi bi-whatsapp me-1"></i>{{ $ticket->whatsapp_acheteur ?? $ticket->telephone_paiement ?? $ticket->telephone_acheteur }}
                                        </div>
                                    @endif
                                </td>
                                <td style="color:var(--gris-fonce);">{{ $ticket->evenement->titre ?? '—' }}</td>
                                <td style="color:var(--gris-fonce);white-space:nowrap;">{{ $ticket->date_achat?->format('d/m/Y H:i') }}</td>
                                <td class="text-end pe-3 fw-bold" style="color:var(--vert);white-space:nowrap;">
                                    @if($ticket->montant > 0){{ number_format($ticket->montant, 0, ',', ' ') }} F @else <span style="color:var(--gris);">Gratuit</span> @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:.8rem;">
                @foreach($recentTickets as $ticket)
                    <div class="dash-ticket-card" style="border:1px solid var(--ligne);border-radius:11px;padding:.7rem;margin-bottom:.5rem;">
                        <div class="d-flex justify-content-between" style="font-size:.72rem;color:var(--gris);">
                            <span>{{ $ticket->date_achat?->format('d/m · H:i') }}</span>
                            <span class="status-badge status-en-cours">Payé</span>
                        </div>
                        <strong style="display:block;margin-top:.3rem;font-size:.85rem;">{{ $ticket->nom_acheteur ?? 'Anonyme' }}</strong>
                        <div class="d-flex justify-content-between align-items-center" style="font-size:.75rem;margin-top:.35rem;">
                            <span style="color:var(--gris-fonce);">{{ $ticket->evenement->titre ?? '—' }}</span>
                            <b style="color:var(--vert);">@if($ticket->montant > 0){{ number_format($ticket->montant, 0, ',', ' ') }} F @else Gratuit @endif</b>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state"><i class="bi bi-cart-x"></i><small>Aucune vente récente</small></div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script>
    const canvas = document.getElementById('ventesChart');
    if (canvas) {
        const data = @json($ventes7Jours);
        const labels = @json($joursLabels);
        new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: labels.map((_, i) => i === labels.length - 1 ? '#12976e' : '#b2e0d6'),
                    borderRadius: 6,
                    borderSkipped: false,
                    barPercentage: 0.6,
                    categoryPercentage: 0.72,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#211c31',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: { label: c => c.parsed.y + ' tickets' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(33,28,49,0.05)', drawBorder: false },
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
    }
</script>
@endsection
