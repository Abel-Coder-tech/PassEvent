@extends('superadmin.layouts.master')

@section('title', 'Commissions - Super Admin PaxEvent')
@section('page-title', 'Commissions d\'affiliation')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(84,38,128,0.1); color: var(--sa-primary);"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($stats['total'], 0, ',', ' ') }} F</div>
                <div class="kpi-label">Total généré</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(243,156,18,0.1); color: var(--sa-warning);"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($stats['en_attente'], 0, ',', ' ') }} F</div>
                <div class="kpi-label">En attente</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(52,152,219,0.1); color: #3498db;"><i class="bi bi-wallet2"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($stats['disponible'], 0, ',', ' ') }} F</div>
                <div class="kpi-label">Disponible</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-check-circle-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($stats['paye'], 0, ',', ' ') }} F</div>
                <div class="kpi-label">Payées</div>
            </div>
        </div>
    </div>
</div>

<div class="sa-card mb-4">
    <div class="sa-card-body">
        <div class="d-flex flex-nowrap gap-2 align-items-center overflow-auto justify-content-between">
            <div class="d-flex flex-nowrap gap-2 align-items-center">
                <form method="GET" action="{{ route('superadmin.affiliations.commissions') }}" id="commissionsFilterForm" class="d-flex flex-nowrap gap-2 align-items-center">
                    <input type="text" name="q" id="commissionsSearch" value="{{ $recherche }}" class="sa-form-control" placeholder="Affilié ou événement..." style="min-width:220px;" autocomplete="off">
                    <select name="statut" id="commissionsStatut" class="sa-form-control" style="min-width:150px;">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente" @selected($statut === 'en_attente')>En attente</option>
                        <option value="disponible" @selected($statut === 'disponible')>Disponible</option>
                        <option value="paye" @selected($statut === 'paye')>Payée</option>
                        <option value="annule" @selected($statut === 'annule')>Annulée</option>
                    </select>
                    @if($recherche !== '' || $statut)
                        <a href="{{ route('superadmin.affiliations.commissions') }}" class="sa-btn sa-btn-outline">Réinitialiser</a>
                    @endif
                </form>
            </div>
            <div class="d-flex flex-nowrap gap-2 align-items-center ms-auto">
                <a href="{{ route('superadmin.affiliations.index') }}" class="sa-btn sa-btn-outline"><i class="bi bi-people"></i> Affiliés</a>
                <a href="{{ route('superadmin.affiliations.dashboard') }}" class="sa-btn sa-btn-outline"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
                <a href="{{ route('superadmin.affiliations.reversements') }}" class="sa-btn sa-btn-outline"><i class="bi bi-cash-coin"></i> Reversements</a>
            </div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-cash-stack me-2" style="color: var(--sa-primary);"></i>Commissions</span>
        <span class="sa-topbar-badge">{{ $commissions->total() }} ligne(s)</span>
    </div>
    <div class="sa-card-body p-0">
        @if($commissions->count() > 0)
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Affilié</th>
                            <th>Type</th>
                            <th>Événement</th>
                            <th class="text-end">Base</th>
                            <th>%</th>
                            <th class="text-end">Montant</th>
                            <th>Statut</th>
                            <th>Disponible le</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commissions as $commission)
                            <tr>
                                <td style="white-space:nowrap;">{{ $commission->created_at->format('d/m/Y') }}</td>
                                <td>
                                    @if($commission->affiliate)
                                        <a href="{{ route('superadmin.affiliations.show', $commission->affiliate) }}" style="color:var(--sa-primary);text-decoration:none;">
                                            <strong>{{ $commission->affiliate->nom_complet }}</strong>
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($commission->type === \App\Models\AffiliateCommission::TYPE_PARRAINAGE)
                                        <span class="sa-badge" style="background:rgba(84,38,128,0.1);color:var(--sa-primary);">Parrainage direct</span>
                                    @else
                                        <span class="sa-badge" style="background:rgba(52,152,219,0.1);color:#3498db;">Co-parrainage</span>
                                    @endif
                                </td>
                                <td>{{ $commission->evenement?->titre ?? '—' }}</td>
                                <td class="text-end">{{ number_format((float) $commission->base_montant, 0, ',', ' ') }} F</td>
                                <td>{{ rtrim(rtrim(number_format((float) $commission->pourcentage, 2, ',', ' '), '0'), ',') }} %</td>
                                <td class="text-end"><strong>{{ number_format((float) $commission->montant, 0, ',', ' ') }} F</strong></td>
                                <td>@include('superadmin.affiliations.partials.statut-commission', ['commission' => $commission])</td>
                                <td style="white-space:nowrap;">{{ $commission->disponible_le?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="text-end">
                                    @if(! in_array($commission->statut, ['paye', 'annule'], true) && ! $commission->affiliate_payout_id)
                                        <form action="{{ route('superadmin.affiliations.commissions.annuler', $commission) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Annuler la commission"
                                                onclick="return confirm('Annuler cette commission ? Le solde de l\'affilié sera recalculé.')">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span style="color:var(--sa-text-muted);">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $commissions->links() }}</div>
        @else
            <div class="p-4 text-center" style="color:var(--sa-text-muted);">
                <i class="bi bi-cash-stack" style="font-size:2rem;"></i>
                <p class="mt-2 mb-0">Aucune commission trouvée.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    (function() {
        const form = document.getElementById('commissionsFilterForm');
        const search = document.getElementById('commissionsSearch');
        const statut = document.getElementById('commissionsStatut');
        if (!form) return;

        let timeout;
        if (search) {
            search.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    form.submit();
                }, 400);
            });
        }
        if (statut) {
            statut.addEventListener('change', function() {
                form.submit();
            });
        }
    })();
</script>
@endpush
@endsection
