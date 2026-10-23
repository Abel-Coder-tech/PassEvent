@extends('layouts.affiliate')

@section('title', 'Tableau de bord — Espace affilié PaxEvent')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-bold mb-1">Bonjour {{ auth('affiliate')->user()->prenom ?: auth('affiliate')->user()->nom }}</h1>
            <p class="text-muted mb-0 small">Voici l'activité de votre programme d'affiliation.</p>
        </div>
        <div class="text-end">
            <div class="small text-muted mb-1">Votre code de parrainage</div>
            <span class="code-chip">{{ $affiliate->code_parrainage }}</span>
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
                <div class="label">Organisateurs parrainés</div>
                <div class="value">{{ $stats['filleuls'] }}</div>
            </div>
        </div>
    </div>

    @if($evenementsOuverts->isNotEmpty())
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card aff-card">
                    <div class="card-body">
                        <h2 class="h6 fw-bold mb-3"><i class="bi bi-share me-1"></i> Événements ouverts au co-parrainage</h2>
                        <p class="text-muted small mb-3">Générez votre lien personnel et touchez un pourcentage sur chaque billet vendu via celui-ci.</p>
                        <div class="d-flex flex-column gap-2">
                            @foreach($evenementsOuverts as $ev)
                                <div class="d-flex flex-wrap align-items-center gap-3 p-2 rounded" style="border:1px solid #e2e0e4;">
                                    <a href="{{ route('evenements.public.show', $ev) }}" target="_blank" rel="noopener" class="aff-ev-thumb" title="Voir l'événement">
                                        @if($ev->image)
                                            <img src="{{ asset('storage/' . $ev->image) }}" alt="{{ $ev->titre }}" loading="lazy">
                                        @else
                                            <i class="bi bi-calendar-event"></i>
                                        @endif
                                    </a>
                                    <div class="min-w-0 flex-grow-1">
                                        <a href="{{ route('evenements.public.show', $ev) }}" target="_blank" rel="noopener" class="small fw-semibold text-truncate d-block text-decoration-none" style="color:inherit;">{{ $ev->titre }}</a>
                                        <div class="text-muted" style="font-size:0.75rem;">
                                            {{ $ev->date_event?->format('d/m/Y') }}@if($ev->lieu) · {{ $ev->lieu }}@endif ·
                                            <strong style="color:var(--violet);">{{ rtrim(rtrim(number_format((float) $ev->co_parrainage_pourcentage, 2), '0'), '.') }} %</strong>/billet
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-auto">
                                        <a href="{{ route('evenements.public.show', $ev) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;" title="Détails de l'événement">
                                            <i class="bi bi-eye"></i><span class="d-none d-sm-inline ms-1">Détails</span>
                                        </a>
                                        <form action="{{ route('affiliate.liens.generer', $ev) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm text-white fw-semibold" style="border-radius:8px; background: var(--violet);">
                                                <i class="bi bi-link me-1"></i> Générer mon lien
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
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
                                @php $url = route('evenements.public.show', $lien->evenement).'?ref='.$lien->code; @endphp
                                <li class="d-flex justify-content-between align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="min-w-0">
                                        <div class="small fw-semibold text-truncate">{{ $lien->evenement?->titre ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:0.75rem;">
                                            {{ rtrim(rtrim(number_format((float) $lien->pourcentage, 2), '0'), '.') }} % · {{ $lien->clics }} clic(s)
                                            @unless($lien->actif) · <span class="text-danger">Inactif</span>@endunless
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1 flex-shrink-0">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copier(this)" title="Copier le lien"><i class="bi bi-clipboard"></i></button>
                                        <input type="text" class="d-none" value="{{ $url }}" readonly>
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
        <div class="col-12 col-lg-6">
            <div class="card aff-card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-person-plus me-1"></i> Parrainage direct</h2>
                    <p class="text-muted small mb-2">Partagez votre code de parrainage avec des organisateurs :</p>
                    <span class="code-chip">{{ $affiliate->code_parrainage }}</span>
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
                                        {{ $commission->type === 'parrainage_direct' ? 'Parrainage direct' : 'Co-parrainage' }}
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
