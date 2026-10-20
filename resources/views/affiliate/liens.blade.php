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
                            <div class="border rounded-3 p-3 h-100 d-flex flex-column">
                                <div class="fw-semibold">{{ $ev->titre }}</div>
                                <div class="text-muted small mb-2">
                                    <i class="bi bi-person me-1"></i>{{ $ev->user?->nom ?? 'Organisateur' }}
                                    @if($ev->date_event) · <i class="bi bi-calendar-event me-1"></i>{{ $ev->date_event->format('d/m/Y') }}@endif
                                </div>
                                <div class="mb-3">
                                    <span class="badge bg-success-subtle text-success-emphasis">
                                        Commission : {{ rtrim(rtrim(number_format((float) $ev->co_parrainage_pourcentage, 2, ',', ' '), '0'), ',') }} % du billet
                                    </span>
                                </div>
                                <form action="{{ route('affiliate.liens.generer', $ev) }}" method="POST" class="mt-auto">
                                    @csrf
                                    <button type="submit" class="btn btn-violet btn-sm w-100">
                                        <i class="bi bi-link-45deg"></i> Générer mon lien
                                    </button>
                                </form>
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
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="small text-muted">
                                <th>Événement</th>
                                <th>Commission</th>
                                <th>Lien</th>
                                <th class="text-center">Clics</th>
                                <th class="text-center">Statut</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($liens as $lien)
                                @php $url = route('evenements.public.show', $lien->evenement).'?ref='.$lien->code; @endphp
                                <tr>
                                    <td class="small">{{ $lien->evenement?->titre ?? '—' }}</td>
                                    <td class="small">{{ rtrim(rtrim(number_format((float) $lien->pourcentage, 2), '0'), '.') }} %</td>
                                    <td>
                                        <div class="input-group input-group-sm" style="min-width: 220px;">
                                            <input type="text" class="form-control" value="{{ $url }}" readonly onclick="this.select()">
                                            <button class="btn btn-outline-secondary" type="button" onclick="copier(this)"><i class="bi bi-clipboard"></i></button>
                                        </div>
                                    </td>
                                    <td class="text-center small">{{ $lien->clics }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $lien->actif ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                            {{ $lien->actif ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('affiliate.liens.toggle', $lien) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $lien->actif ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                {{ $lien->actif ? 'Désactiver' : 'Réactiver' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
