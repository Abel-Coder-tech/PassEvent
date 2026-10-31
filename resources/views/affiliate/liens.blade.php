@extends('layouts.affiliate')

@section('title', 'Mes liens de co-parrainage — Espace affilié PaxEvent')

@section('content')
    <h1 class="h4 fw-bold mb-1">Mes liens de co-parrainage</h1>
    <p class="text-muted small mb-4">Générez un lien dédié pour les événements ouverts au co-parrainage et touchez la commission sur chaque billet vendu via ce lien.</p>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Mes liens</div>
                <div class="value">{{ $stats['liens'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Liens actifs</div>
                <div class="value">{{ $stats['liens_actifs'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Clics cumulés</div>
                <div class="value">{{ number_format($stats['clics'], 0, ',', ' ') }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Événements ouverts</div>
                <div class="value">{{ $stats['evenements_ouverts'] }}</div>
            </div>
        </div>
    </div>

    <div class="card aff-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-muted mb-1">Rechercher un événement</label>
                    <input type="text" name="q" class="form-control form-control-sm" value="{{ $recherche }}" placeholder="Nom de l'événement…">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Statut de mes liens</label>
                    <select name="statut" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="actif" @selected($statut === 'actif')>Actif</option>
                        <option value="inactif" @selected($statut === 'inactif')>Inactif</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-violet btn-sm flex-grow-1"><i class="bi bi-search me-1"></i> Rechercher</button>
                    @if($recherche !== '' || $statut !== null)
                        <a href="{{ route('affiliate.liens') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card aff-card mb-4">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3"><i class="bi bi-broadcast me-1"></i> Événements ouverts au co-parrainage</h2>
            @if($evenementsOuverts->isEmpty())
                @include('affiliate.partials.empty-state', [
                    'icon' => 'bi-broadcast',
                    'title' => 'Aucun événement disponible',
                    'text' => $recherche !== '' ? 'Aucun événement ouvert ne correspond à votre recherche.' : 'Aucun nouvel événement ouvert au co-parrainage pour le moment.',
                ])
            @else
                <div class="row g-3">
                    @foreach($evenementsOuverts as $ev)
                        <div class="col-12 col-md-6">
                            <div class="border rounded-3 h-100 d-flex flex-column overflow-hidden">
                                <a href="{{ route('affiliate.evenements.show', $ev) }}" class="aff-offer-cover" title="Voir le détail de l'événement">
                                    @if($ev->image)
                                        <img src="{{ asset('storage/' . $ev->image) }}" alt="{{ $ev->titre }}" loading="lazy">
                                    @else
                                        <i class="bi bi-calendar-event"></i>
                                    @endif
                                </a>
                                <div class="p-3 d-flex flex-column flex-grow-1">
                                    <a href="{{ route('affiliate.evenements.show', $ev) }}" class="fw-semibold text-decoration-none" style="color:inherit;">{{ $ev->titre }}</a>
                                    <div class="text-muted small mb-2">
                                        <i class="bi bi-person me-1"></i>{{ $ev->user?->nom ?? 'Organisateur' }}
                                        @if($ev->date_event) · <i class="bi bi-calendar-event me-1"></i>{{ $ev->date_event->format('d/m/Y') }}@endif
                                        @if($ev->tarifs->isNotEmpty())
                                            · À partir de {{ number_format((float) $ev->tarifs->min('prix'), 0, ',', ' ') }} F
                                        @endif
                                    </div>
                                    <div class="mb-3">
                                        <span class="badge bg-success-subtle text-success-emphasis">
                                            Commission : {{ rtrim(rtrim(number_format((float) $ev->co_parrainage_pourcentage, 2, ',', ' '), '0'), ',') }} % du billet
                                        </span>
                                    </div>
                                    <div class="mt-auto d-flex flex-column gap-2">
                                        <a href="{{ route('affiliate.evenements.show', $ev) }}" class="btn btn-outline-secondary btn-sm w-100">
                                            <i class="bi bi-eye me-1"></i> Voir le détail
                                        </a>
                                        <form action="{{ route('affiliate.liens.generer', $ev) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-violet btn-sm w-100">
                                                <i class="bi bi-link-45deg"></i> Générer mon lien
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
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
                @include('affiliate.partials.empty-state', [
                    'icon' => 'bi-link-45deg',
                    'title' => 'Aucun lien',
                    'text' => ($recherche !== '' || $statut !== null)
                        ? 'Aucun de vos liens ne correspond à ces filtres.'
                        : 'Vous n\'avez encore généré aucun lien.',
                ])
            @else
                <div class="row g-3">
                    @foreach($liens as $lien)
                        @php
                            $detailUrl = $lien->evenement ? route('affiliate.evenements.show', $lien->evenement) : null;
                            $url = $lien->evenement ? route('evenements.public.show', $lien->evenement).'?ref='.$lien->code : null;
                        @endphp
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="border rounded-3 h-100 d-flex flex-column overflow-hidden bg-white">
                                @if($detailUrl)
                                    <a href="{{ $detailUrl }}" class="aff-offer-cover" style="height:130px;" title="Voir le détail de l'événement">
                                        @if($lien->evenement->image)
                                            <img src="{{ asset('storage/' . $lien->evenement->image) }}" alt="{{ $lien->evenement->titre }}" loading="lazy">
                                        @else
                                            <i class="bi bi-calendar-event"></i>
                                        @endif
                                    </a>
                                @endif
                                <div class="p-3 d-flex flex-column flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                        @if($detailUrl)
                                            <a href="{{ $detailUrl }}" class="fw-semibold text-decoration-none" style="color:inherit;">{{ $lien->evenement->titre }}</a>
                                        @else
                                            <span class="fw-semibold">—</span>
                                        @endif
                                        <span class="badge flex-shrink-0 {{ $lien->actif ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                            {{ $lien->actif ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </div>
                                    <div class="text-muted small mb-2">
                                        @if($lien->evenement?->user)
                                            <i class="bi bi-person me-1"></i>{{ $lien->evenement->user->nom }}
                                        @endif
                                        @if($lien->evenement?->date_event)
                                            · <i class="bi bi-calendar-event me-1"></i>{{ $lien->evenement->date_event->format('d/m/Y') }}
                                        @endif
                                    </div>
                                    <div class="d-flex flex-wrap gap-3 small text-muted mb-2">
                                        <span>
                                            <i class="bi bi-percent me-1"></i>{{ rtrim(rtrim(number_format((float) $lien->pourcentage, 2, ',', ' '), '0'), ',') }} %
                                        </span>
                                        <span><i class="bi bi-cursor me-1"></i>{{ $lien->clics }} clic(s)</span>
                                    </div>
                                    @if($url)
                                        <div class="input-group input-group-sm mb-2">
                                            <input type="text" class="form-control" value="{{ $url }}" readonly onclick="this.select()">
                                            <button class="btn btn-outline-secondary" type="button" onclick="copier(this)" title="Copier le lien">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                            <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-outline-secondary" title="Tester le lien tracé">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        </div>
                                    @endif
                                    <div class="mt-auto d-flex gap-2">
                                        @if($detailUrl)
                                            <a href="{{ $detailUrl }}" class="btn btn-outline-secondary btn-sm flex-grow-1">
                                                <i class="bi bi-eye me-1"></i> Détails
                                            </a>
                                        @endif
                                        <form method="POST" action="{{ route('affiliate.liens.toggle', $lien) }}" class="flex-shrink-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $lien->actif ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                {{ $lien->actif ? 'Désactiver' : 'Réactiver' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('affiliate.liens.destroy', $lien) }}" class="flex-shrink-0" onsubmit="return confirm('Retirer cet événement de vos liens ? Vos commissions déjà générées seront conservées.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" aria-label="Retirer cet événement de mes liens" title="Retirer de mes liens">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 d-flex justify-content-center">{{ $liens->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
function copier(btn) {
    var input = btn.parentElement.querySelector('input');
    var val = input.value;
    function done() {
        var html = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i>';
        setTimeout(function () { btn.innerHTML = html; }, 1500);
    }
    function fallback() {
        input.select();
        input.setSelectionRange(0, 99999);
        try { document.execCommand('copy'); } catch (e) {}
        done();
    }
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(val).then(done).catch(fallback);
    } else {
        fallback();
    }
}
</script>
@endpush
