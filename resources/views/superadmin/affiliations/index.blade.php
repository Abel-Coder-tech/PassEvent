@extends('superadmin.layouts.master')

@section('title', 'Affiliations - Super Admin PaxEvent')
@section('page-title', 'Programme d\'affiliation')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(84,38,128,0.1); color: var(--sa-primary);"><i class="bi bi-people-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ $stats['total'] }}</div>
                <div class="kpi-label">Affiliés ({{ $stats['actifs'] }} actifs)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(243,156,18,0.1); color: var(--sa-warning);"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($stats['en_attente'], 0, ',', ' ') }} F</div>
                <div class="kpi-label">Commissions en attente</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(52,152,219,0.1); color: #3498db;"><i class="bi bi-wallet2"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($stats['disponible'], 0, ',', ' ') }} F</div>
                <div class="kpi-label">Disponible (à reverser)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-check-circle-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($stats['paye'], 0, ',', ' ') }} F</div>
                <div class="kpi-label">Commissions payées</div>
            </div>
        </div>
    </div>
</div>

<div class="sa-card mb-4">
    <div class="sa-card-body">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <form method="GET" action="{{ route('superadmin.affiliations.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
                <input type="text" name="q" value="{{ $recherche }}" class="sa-form-control" placeholder="Nom, email, code..." style="min-width:220px;">
                <select name="statut" class="sa-form-control" style="min-width:150px;">
                    <option value="">Tous les statuts</option>
                    <option value="actif" @selected($statut === 'actif')>Actifs</option>
                    <option value="inactif" @selected($statut === 'inactif')>Inactifs</option>
                </select>
                <button type="submit" class="sa-btn sa-btn-primary"><i class="bi bi-search"></i> Filtrer</button>
                @if($recherche !== '' || $statut)
                    <a href="{{ route('superadmin.affiliations.index') }}" class="sa-btn sa-btn-outline">Réinitialiser</a>
                @endif
            </form>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('superadmin.affiliations.dashboard') }}" class="sa-btn sa-btn-outline"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
                <a href="{{ route('superadmin.affiliations.commissions') }}" class="sa-btn sa-btn-outline"><i class="bi bi-list-columns"></i> Commissions</a>
                <a href="{{ route('superadmin.affiliations.reversements') }}" class="sa-btn sa-btn-outline">
                    <i class="bi bi-cash-coin"></i> Reversements
                    @if($stats['reversements_demandes'] > 0)
                        <span class="sa-badge sa-badge-warning ms-1">{{ $stats['reversements_demandes'] }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-people-fill me-2" style="color: var(--sa-primary);"></i>Affiliés</span>
        <span class="sa-topbar-badge">{{ $affilies->total() }} compte(s)</span>
    </div>
    <div class="sa-card-body p-0">
        @if($affilies->count() > 0)
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead>
                        <tr>
                            <th>Affilié</th>
                            <th>Code</th>
                            <th>Événements co-parrainés</th>
                            <th class="text-center">Filleuls</th>
                            <th class="text-center">Liens</th>
                            <th class="text-end">En attente</th>
                            <th class="text-end">Disponible</th>
                            <th class="text-end">Total gagné</th>
                            <th class="text-center">Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($affilies as $affilie)
                            <tr>
                                <td>
                                    <strong>{{ $affilie->nom_complet }}</strong>
                                    <br><small style="color:var(--sa-text-muted);">{{ $affilie->email }}</small>
                                </td>
                                <td><code style="font-size:0.72rem;">{{ $affilie->code_parrainage }}</code></td>
                                <td style="min-width:220px;">
                                    @php($evenementsAffilie = $affilie->eventLinks->pluck('evenement')->filter()->unique('id'))
                                    @if($evenementsAffilie->count() > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($evenementsAffilie->take(2) as $evenementAffilie)
                                                <a href="{{ route('superadmin.evenements.voir', $evenementAffilie) }}"
                                                   class="sa-badge sa-badge-info text-decoration-none"
                                                   style="font-size:0.65rem;"
                                                   title="{{ $evenementAffilie->titre }}">
                                                    {{ \Illuminate\Support\Str::limit($evenementAffilie->titre, 24) }}
                                                </a>
                                            @endforeach
                                            @if($evenementsAffilie->count() > 2)
                                                <a href="{{ route('superadmin.affiliations.show', $affilie) }}"
                                                   class="sa-badge sa-badge-secondary text-decoration-none"
                                                   style="font-size:0.65rem;"
                                                   title="Voir tous les événements co-parrainés">
                                                    +{{ $evenementsAffilie->count() - 2 }}
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <span style="color:var(--sa-text-muted);">—</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $affilie->referrals_count }}</td>
                                <td class="text-center">{{ $affilie->event_links_count }}</td>
                                <td class="text-end">{{ number_format((float) $affilie->solde_en_attente, 0, ',', ' ') }} F</td>
                                <td class="text-end"><strong>{{ number_format((float) $affilie->solde_disponible, 0, ',', ' ') }} F</strong></td>
                                <td class="text-end">
                                    {{ number_format((float) $affilie->total_gagne, 0, ',', ' ') }} F
                                    <br><small style="color:var(--sa-text-muted);">{{ $affilie->commissions_count }} commission(s)</small>
                                </td>
                                <td class="text-center">
                                    @if($affilie->actif)
                                        <span class="sa-badge sa-badge-success">Actif</span>
                                    @else
                                        <span class="sa-badge sa-badge-danger">Inactif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('superadmin.affiliations.show', $affilie) }}" class="sa-btn sa-btn-sm sa-btn-primary" title="Voir la fiche">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form action="{{ route('superadmin.affiliations.statut', $affilie) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="sa-btn sa-btn-sm {{ $affilie->actif ? 'sa-btn-danger' : 'sa-btn-primary' }}"
                                            title="{{ $affilie->actif ? 'Désactiver' : 'Réactiver' }}"
                                            onclick="return confirm('{{ $affilie->actif ? 'Désactiver' : 'Réactiver' }} ce compte affilié ?')">
                                            <i class="bi {{ $affilie->actif ? 'bi-pause-fill' : 'bi-play-fill' }}"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $affilies->links() }}</div>
        @else
            <div class="p-4 text-center" style="color:var(--sa-text-muted);">
                <i class="bi bi-person-plus" style="font-size:2rem;"></i>
                <p class="mt-2 mb-0">Aucun affilié trouvé.</p>
            </div>
        @endif
    </div>
</div>
@endsection
