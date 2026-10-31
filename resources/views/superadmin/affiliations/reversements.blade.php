@extends('superadmin.layouts.master')

@section('title', 'Reversements affiliation - Super Admin PaxEvent')
@section('page-title', 'Reversements d\'affiliation')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(243,156,18,0.1); color: var(--sa-warning);"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ $stats['demande'] }}</div>
                <div class="kpi-label">Demandes</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(52,152,219,0.1); color: #3498db;"><i class="bi bi-arrow-repeat"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ $stats['en_cours'] }}</div>
                <div class="kpi-label">En cours</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-check-circle-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($stats['paye'], 0, ',', ' ') }} F</div>
                <div class="kpi-label">Payé</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(84,38,128,0.1); color: var(--sa-primary);"><i class="bi bi-receipt"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ $stats['total'] }}</div>
                <div class="kpi-label">Reversements payés</div>
            </div>
        </div>
    </div>
</div>

<div class="sa-card mb-4">
    <div class="sa-card-body">
        <div class="d-flex flex-nowrap gap-2 align-items-center overflow-auto justify-content-between">
            <div class="d-flex flex-nowrap gap-2 align-items-center">
                <form method="GET" action="{{ route('superadmin.affiliations.reversements') }}" id="reversementsFilterForm" class="d-flex flex-nowrap gap-2 align-items-center">
                    <input type="text" name="q" id="reversementsSearch" value="{{ $recherche }}" class="sa-form-control" placeholder="Affilié ou référence..." style="min-width:220px;" autocomplete="off">
                    <select name="statut" id="reversementsStatut" class="sa-form-control" style="min-width:150px;">
                        <option value="">Tous les statuts</option>
                        <option value="demande" @selected($statut === 'demande')>Demandé</option>
                        <option value="en_cours" @selected($statut === 'en_cours')>En cours</option>
                        <option value="paye" @selected($statut === 'paye')>Payé</option>
                        <option value="refuse" @selected($statut === 'refuse')>Refusé</option>
                    </select>
                    @if($recherche !== '' || $statut)
                        <a href="{{ route('superadmin.affiliations.reversements') }}" class="sa-btn sa-btn-outline">Réinitialiser</a>
                    @endif
                </form>
            </div>
            <div class="d-flex flex-nowrap gap-2 align-items-center ms-auto">
                <a href="{{ route('superadmin.affiliations.index') }}" class="sa-btn sa-btn-outline"><i class="bi bi-people"></i> Affiliés</a>
                <a href="{{ route('superadmin.affiliations.dashboard') }}" class="sa-btn sa-btn-outline"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
                <a href="{{ route('superadmin.affiliations.commissions') }}" class="sa-btn sa-btn-outline"><i class="bi bi-list-columns"></i> Commissions</a>
            </div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-cash-coin me-2" style="color: var(--sa-primary);"></i>Demandes de reversement</span>
        <span class="sa-topbar-badge">{{ $reversements->total() }} demande(s)</span>
    </div>
    <div class="sa-card-body p-0">
        @if($reversements->count() > 0)
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Affilié</th>
                            <th class="text-end">Montant</th>
                            <th>Opérateur / numéro</th>
                            <th class="text-center">Commissions</th>
                            <th>Statut</th>
                            <th>Note</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reversements as $reversement)
                            <tr>
                                <td style="white-space:nowrap;">{{ $reversement->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($reversement->affiliate)
                                        <a href="{{ route('superadmin.affiliations.show', $reversement->affiliate) }}" style="color:var(--sa-primary);text-decoration:none;">
                                            <strong>{{ $reversement->affiliate->nom_complet }}</strong>
                                        </a>
                                        <br><small style="color:var(--sa-text-muted);">{{ $reversement->affiliate->email }}</small>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end"><strong>{{ number_format((float) $reversement->montant, 0, ',', ' ') }} F</strong></td>
                                <td>
                                    {{ $reversement->operateur ?: '—' }}
                                    <br><small style="color:var(--sa-text-muted);">{{ $reversement->numero_paiement ?: '—' }}</small>
                                    @if($reversement->titulaire)
                                        <br><small style="color:var(--sa-text-muted);">{{ $reversement->titulaire }}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ $reversement->commissions_count }}</td>
                                <td>
                                    @if($reversement->statut === 'demande')
                                        <span class="sa-badge sa-badge-warning">Demandé</span>
                                    @elseif($reversement->statut === 'en_cours')
                                        <span class="sa-badge sa-badge-info">En cours</span>
                                    @elseif($reversement->statut === 'paye')
                                        <span class="sa-badge sa-badge-success">Payé</span>
                                    @else
                                        <span class="sa-badge sa-badge-danger">Refusé</span>
                                    @endif
                                </td>
                                <td style="max-width:180px;font-size:0.78rem;">{{ $reversement->note ?: '—' }}</td>
                                <td class="text-end" style="white-space:nowrap;">
                                    @if($reversement->statut === 'demande')
                                        <form action="{{ route('superadmin.affiliations.reversements.approuver', $reversement) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="sa-btn sa-btn-sm sa-btn-primary" title="Approuver">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if(in_array($reversement->statut, ['demande', 'en_cours'], true))
                                        @if($reversement->statut === 'en_cours')
                                            <form action="{{ route('superadmin.affiliations.reversements.confirmer', $reversement) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-primary" title="Confirmer le paiement"
                                                    onclick="return confirm('Confirmer que le transfert a bien été effectué ?')">
                                                    <i class="bi bi-check-all"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('superadmin.affiliations.reversements.rejeter', $reversement) }}" method="POST" style="display:inline;"
                                            onsubmit="this.note.value = prompt('Motif du rejet (facultatif)') || '';">
                                            @csrf
                                            <input type="hidden" name="note" value="">
                                            <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Rejeter">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $reversements->links() }}</div>
        @else
            <div class="p-4 text-center" style="color:var(--sa-text-muted);">
                <i class="bi bi-cash-coin" style="font-size:2rem;"></i>
                <p class="mt-2 mb-0">Aucune demande de reversement.</p>
            </div>
        @endif
    </div>
</div>
@endsection
