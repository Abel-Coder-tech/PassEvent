@extends('layouts.affiliate')

@section('title', 'Mes liens — Espace affilié PaxEvent')

@section('content')
    <div class="mb-4">
        <h1 class="h4 fw-bold mb-1">Mes liens</h1>
        <p class="text-muted small mb-0">Générez et suivez vos liens de co-parrainage.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card aff-card h-100 border-0" style="border-top:4px solid #542680 !important;"><div class="card-body">
                <div class="text-muted small fw-semibold">Liens</div>
                <div class="h5 fw-bold mb-0">{{ $stats['liens'] }}</div>
                <div class="small text-muted">{{ $stats['liens_actifs'] }} actif(s)</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card aff-card h-100 border-0" style="border-top:4px solid #0dcaf0 !important;"><div class="card-body">
                <div class="text-muted small fw-semibold">Clics</div>
                <div class="h5 fw-bold mb-0">{{ number_format($stats['clics'], 0, ',', ' ') }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card aff-card h-100 border-0" style="border-top:4px solid #198754 !important;"><div class="card-body">
                <div class="text-muted small fw-semibold">Ventes</div>
                <div class="h5 fw-bold mb-0">{{ number_format($stats['ventes'] ?? 0, 0, ',', ' ') }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card aff-card h-100 border-0" style="border-top:4px solid #FED514 !important;"><div class="card-body">
                <div class="text-muted small fw-semibold">Commissions</div>
                <div class="h5 fw-bold mb-0">{{ number_format($stats['commissions_total'] ?? 0, 0, ',', ' ') }} <small class="fs-6 fw-normal">F</small></div>
            </div></div>
        </div>
    </div>

    <div class="card aff-card mb-4">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3">Événements ouverts au co-parrainage</h2>
            <form method="GET" action="{{ route('affiliate.liens') }}" class="row g-2 align-items-end mb-3">
                <div class="col-12 col-md-5">
                    <label class="form-label small fw-semibold text-muted mb-1">Rechercher</label>
                    <input type="text" name="q" value="{{ $recherche }}" class="form-control form-control-sm" placeholder="Nom de l'événement…">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Mes liens</label>
                    <select name="statut" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="actif" @selected($statut === 'actif')>Actifs</option>
                        <option value="inactif" @selected($statut === 'inactif')>Inactifs</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-violet btn-sm flex-grow-1">Filtrer</button>
                    @if($recherche || $statut)
                        <a href="{{ route('affiliate.liens') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>

            @if($evenementsOuverts->isEmpty())
                <div class="text-muted small">
                    {{ $recherche ? 'Aucun événement ne correspond à votre recherche.' : 'Aucun nouvel événement à promouvoir.' }}
                </div>
            @else
                <div class="d-flex flex-column gap-2">
                    @foreach($evenementsOuverts as $ev)
                        <div class="d-flex align-items-center gap-3 p-2 border rounded-3 bg-white">
                            <a href="{{ route('affiliate.evenements.show', $ev) }}" class="aff-ev-thumb" style="width:56px;height:56px;">
                                @if($ev->image)
                                    <img src="{{ asset('storage/' . $ev->image) }}" alt="" loading="lazy">
                                @else
                                    <i class="bi bi-calendar-event"></i>
                                @endif
                            </a>
                            <div class="flex-grow-1 min-w-0">
                                <a href="{{ route('affiliate.evenements.show', $ev) }}" class="fw-semibold text-decoration-none small text-truncate d-block" style="color:inherit;">{{ $ev->titre }}</a>
                                <div class="text-muted" style="font-size:0.75rem;">
                                    {{ $ev->user?->nom ?? 'Organisateur' }}@if($ev->date_event) · {{ $ev->date_event->format('d/m/Y') }}@endif
                                    · <span class="text-success">{{ rtrim(rtrim(number_format((float) $ev->co_parrainage_pourcentage, 2, ',', ' '), '0'), ',') }} %</span>
                                </div>
                            </div>
                            <form action="{{ route('affiliate.liens.generer', $ev) }}" method="POST" class="flex-shrink-0">
                                @csrf
                                <button type="submit" class="btn btn-violet btn-sm"><i class="bi bi-link-45deg me-1"></i> Générer</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card aff-card">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3">Mes liens générés</h2>
            @if($liens->isEmpty())
                <div class="text-muted small">Aucun lien généré pour le moment.</div>
            @else
                <div class="d-flex flex-column gap-2">
                    @foreach($liens as $lien)
                        @php
                            $detailUrl = $lien->evenement ? route('affiliate.evenements.show', $lien->evenement) : null;
                            $url = $lien->evenement ? route('evenements.affilie.redirect', ['code' => $lien->code]) : null;
                            $ventes = (int) ($lien->ventes_count ?? 0);
                            $comm = (float) ($lien->commissions_sum_montant ?? 0);
                        @endphp
                        <div class="d-flex align-items-center gap-3 p-2 border rounded-3 {{ !$lien->actif ? 'bg-light' : 'bg-white' }}">
                            @if($detailUrl)
                                <a href="{{ $detailUrl }}" class="aff-ev-thumb" style="width:56px;height:56px;">
                                    @if($lien->evenement?->image)
                                        <img src="{{ asset('storage/' . $lien->evenement->image) }}" alt="" loading="lazy">
                                    @else
                                        <i class="bi bi-calendar-event"></i>
                                    @endif
                                </a>
                            @endif
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @if($detailUrl)
                                        <a href="{{ $detailUrl }}" class="fw-semibold text-decoration-none small text-truncate" style="color:inherit; max-width:260px;">{{ $lien->evenement->titre }}</a>
                                    @else
                                        <span class="fw-semibold small">—</span>
                                    @endif
                                    @if(!$lien->actif)<span class="badge bg-secondary-subtle text-secondary-emphasis">Inactif</span>@endif
                                </div>
                                <div class="text-muted" style="font-size:0.75rem;">
                                    {{ $lien->clics }} clics · {{ $ventes }} ventes · {{ number_format($comm, 0, ',', ' ') }} FCFA
                                </div>
                                @if($url)
                                    <div class="small mt-1">
                                        <code class="small text-break" style="font-size:0.72rem; background:#f5f3ff; padding:2px 6px; border-radius:6px;">{{ $url }}</code>
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0 align-items-center">
                                @if($url)
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copierLien(this, '{{ $url }}')" title="Copier"><i class="bi bi-clipboard"></i></button>
                                @endif
                                <form action="{{ route('affiliate.liens.toggle', $lien) }}" method="POST" class="m-0">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $lien->actif ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $lien->actif ? 'Désactiver' : 'Activer' }}">
                                        <i class="bi {{ $lien->actif ? 'bi-pause' : 'bi-play' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($liens->hasPages())
                    <div class="mt-3">{{ $liens->links() }}</div>
                @endif
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
function copierLien(btn, val) {
    function done() { var html = btn.innerHTML; btn.innerHTML = '<i class="bi bi-check2"></i>'; setTimeout(function(){ btn.innerHTML = html; }, 1500); }
    function fallback() { var i = document.createElement('input'); i.value = val; document.body.appendChild(i); i.select(); try{document.execCommand('copy');}catch(e){} document.body.removeChild(i); done(); }
    if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(val).then(done).catch(fallback); else fallback();
}
</script>
@endpush
