@extends('layouts.affiliate')

@section('title', 'Tableau de bord — Espace affilié PaxEvent')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-bold mb-1">Bonjour {{ auth('affiliate')->user()->prenom ?: auth('affiliate')->user()->nom }}</h1>
            <p class="text-muted mb-0 small">Voici l'activité de votre programme d'affiliation.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Solde disponible</div>
                <div class="value">{{ number_format((float) $stats['solde_disponible'], 0, ',', ' ') }} <small class="fs-6">FCFA</small></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">En attente</div>
                <div class="value">{{ number_format((float) $stats['solde_en_attente'], 0, ',', ' ') }} <small class="fs-6">FCFA</small></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Total gagné</div>
                <div class="value">{{ number_format((float) $stats['total_gagne'], 0, ',', ' ') }} <small class="fs-6">FCFA</small></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Parrainages historiques</div>
                <div class="value">{{ $stats['filleuls'] }}</div>
            </div>
        </div>
    </div>

    @if($evenementsOuverts->isNotEmpty())
        <div class="mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h2 class="h6 fw-bold mb-1"><i class="bi bi-share me-1"></i> Événements ouverts au co-parrainage</h2>
                    <p class="text-muted small mb-0">Générez votre lien personnel et touchez un pourcentage sur chaque billet vendu via celui-ci.</p>
                </div>
                <a href="{{ route('affiliate.liens') }}" class="small text-decoration-none">
                    Voir tous les événements <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="row g-3">
                @foreach($evenementsOuverts as $ev)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="border rounded-3 h-100 d-flex flex-column overflow-hidden bg-white">
                            <a href="{{ route('affiliate.evenements.show', $ev) }}" class="aff-offer-cover" title="Voir le détail de l'événement">
                                @if($ev->image)
                                    <img src="{{ asset('storage/' . $ev->image) }}" alt="{{ $ev->titre }}" loading="lazy">
                                @else
                                    <i class="bi bi-calendar-event"></i>
                                @endif
                            </a>
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <a href="{{ route('affiliate.evenements.show', $ev) }}" class="fw-semibold text-decoration-none" style="color:inherit;">
                                    {{ $ev->titre }}
                                </a>
                                <div class="text-muted small mb-2">
                                    <i class="bi bi-person me-1"></i>{{ $ev->user?->nom ?? 'Organisateur' }}
                                    @if($ev->date_event)
                                        · <i class="bi bi-calendar-event me-1"></i>{{ $ev->date_event->format('d/m/Y') }}
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
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card aff-card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-link-45deg me-1"></i> Vos liens de co-parrainage</h2>
                    @if($liens->isEmpty())
                        @include('affiliate.partials.empty-state', [
                            'icon' => 'bi-link-45deg',
                            'title' => 'Aucun lien de co-parrainage',
                            'text' => "Rendez-vous sur un événement ouvert au co-parrainage pour générer votre lien dédié.",
                            'action' => route('affiliate.liens'),
                            'actionLabel' => 'Générer un lien',
                        ])
                    @else
                        <ul class="list-unstyled mb-3">
                            @foreach($liens as $lien)
                                @php
                                    $detailUrl = $lien->evenement ? route('affiliate.evenements.show', $lien->evenement) : null;
                                    $url = $lien->evenement ? route('evenements.public.show', $lien->evenement).'?ref='.$lien->code : null;
                                @endphp
                                <li class="d-flex justify-content-between align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        @if($detailUrl)
                                            <a href="{{ $detailUrl }}" class="aff-ev-thumb" style="width:48px;height:48px;border-radius:10px;" title="Voir le détail de l'événement">
                                                @if($lien->evenement->image)
                                                    <img src="{{ asset('storage/' . $lien->evenement->image) }}" alt="{{ $lien->evenement->titre }}" loading="lazy">
                                                @else
                                                    <i class="bi bi-calendar-event"></i>
                                                @endif
                                            </a>
                                        @endif
                                        <div class="min-w-0">
                                            @if($detailUrl)
                                                <a href="{{ $detailUrl }}" class="small fw-semibold text-truncate d-block text-decoration-none" style="color:inherit;">{{ $lien->evenement->titre }}</a>
                                            @else
                                                <div class="small fw-semibold text-truncate">—</div>
                                            @endif
                                            <div class="text-muted" style="font-size:0.75rem;">
                                                {{ rtrim(rtrim(number_format((float) $lien->pourcentage, 2), '0'), '.') }} % · {{ $lien->clics }} clic(s)
                                                @unless($lien->actif) · <span class="text-danger">Inactif</span>@endunless
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1 flex-shrink-0">
                                        @if($detailUrl)
                                            <a href="{{ $detailUrl }}" class="btn btn-sm btn-outline-secondary" title="Détails de l'événement"><i class="bi bi-eye"></i></a>
                                        @endif
                                        @if($url)
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copier(this)" title="Copier le lien"><i class="bi bi-clipboard"></i></button>
                                            <input type="text" class="d-none" value="{{ $url }}" readonly>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('affiliate.liens') }}" class="small text-decoration-none">
                            Voir tous mes liens <i class="bi bi-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card aff-card">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3">Dernières commissions</h2>
            @if($commissions->isEmpty())
                @include('affiliate.partials.empty-state', [
                    'icon' => 'bi-cash-coin',
                    'title' => 'Aucune commission',
                    'text' => 'Vos gains apparaîtront ici dès vos premières ventes tracées.',
                ])
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="small text-muted">
                                <th>Date</th>
                                <th>Type</th>
                                <th>Événement</th>
                                <th class="text-end">Montant</th>
                                <th class="text-end">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($commissions as $commission)
                                <tr>
                                    <td class="small">{{ $commission->created_at?->format('d/m/Y') }}</td>
                                    <td class="small">
                                        Co-parrainage
                                    </td>
                                    <td class="small">{{ $commission->evenement?->titre ?? '—' }}</td>
                                    <td class="text-end fw-semibold">{{ number_format((float) $commission->montant, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-end">
                                        @php
                                            $badge = [
                                                'en_attente' => 'bg-warning-subtle text-warning-emphasis',
                                                'disponible' => 'bg-info-subtle text-info-emphasis',
                                                'paye' => 'bg-success-subtle text-success-emphasis',
                                                'annule' => 'bg-secondary-subtle text-secondary-emphasis',
                                            ][$commission->statut] ?? 'bg-secondary-subtle text-secondary-emphasis';
                                            $labels = [
                                                'en_attente' => 'En attente',
                                                'disponible' => 'Disponible',
                                                'paye' => 'Payé',
                                                'annule' => 'Annulé',
                                            ];
                                        @endphp
                                        <span class="badge {{ $badge }}">{{ $labels[$commission->statut] ?? $commission->statut }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
