@extends('superadmin.layouts.master')

@section('title', 'Affilié - Super Admin PaxEvent')
@section('page-title', 'Fiche affilié')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h5 class="mb-1">{{ $affiliate->nom_complet }}</h5>
        <div style="color:var(--sa-text-muted); font-size:0.85rem;">
            {{ $affiliate->email }}
            @if($affiliate->telephone) · {{ $affiliate->telephone }} @endif
            · Code : <code>{{ $affiliate->code_parrainage }}</code>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('superadmin.affiliations.index') }}" class="sa-btn sa-btn-outline"><i class="bi bi-arrow-left"></i> Retour</a>
        <form action="{{ route('superadmin.affiliations.statut', $affiliate) }}" method="POST">
            @csrf
            <button type="submit" class="sa-btn {{ $affiliate->actif ? 'sa-btn-danger' : 'sa-btn-primary' }}"
                onclick="return confirm('{{ $affiliate->actif ? 'Désactiver' : 'Réactiver' }} ce compte ?')">
                <i class="bi {{ $affiliate->actif ? 'bi-pause-fill' : 'bi-play-fill' }}"></i>
                {{ $affiliate->actif ? 'Désactiver' : 'Réactiver' }}
            </button>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(243,156,18,0.1); color: var(--sa-warning);"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format((float) $affiliate->solde_en_attente, 0, ',', ' ') }} F</div>
                <div class="kpi-label">En attente</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(52,152,219,0.1); color: #3498db;"><i class="bi bi-wallet2"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format((float) $affiliate->solde_disponible, 0, ',', ' ') }} F</div>
                <div class="kpi-label">Disponible</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-graph-up"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format((float) $affiliate->total_gagne, 0, ',', ' ') }} F</div>
                <div class="kpi-label">Total gagné</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(84,38,128,0.1); color: var(--sa-primary);"><i class="bi bi-calendar-check"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ $evenementsCoparaines }}</div>
                <div class="kpi-label">Événements co-parrainés</div>
                <div class="small" style="color:var(--sa-text-muted);">{{ $affiliate->event_links_count }} lien(s) · {{ $affiliate->referrals_count }} filleul(s)</div>
            </div>
        </div>
    </div>
</div>

{{-- Coordonnées de reversement --}}
<div class="sa-card mb-4">
    <div class="sa-card-header"><span><i class="bi bi-bank me-2" style="color:var(--sa-primary);"></i>Coordonnées de reversement</span></div>
    <div class="sa-card-body">
        <div class="row g-3" style="font-size:0.85rem;">
            <div class="col-md-4"><strong>Opérateur :</strong> {{ $affiliate->operateur_paiement ?: '—' }}</div>
            <div class="col-md-4"><strong>Numéro :</strong> {{ $affiliate->numero_paiement ?: '—' }}</div>
            <div class="col-md-4"><strong>Titulaire :</strong> {{ $affiliate->titulaire_paiement ?: '—' }}</div>
            <div class="col-md-4"><strong>Pays :</strong> {{ $affiliate->pays }}</div>
            <div class="col-md-4"><strong>Inscrit le :</strong> {{ $affiliate->created_at->format('d/m/Y') }}</div>
            <div class="col-md-4"><strong>Dernier accès :</strong> {{ $affiliate->dernier_acces?->format('d/m/Y H:i') ?? '—' }}</div>
        </div>
    </div>
</div>

{{-- Filleuls (parrainage direct) --}}
<div class="sa-card mb-4">
    <div class="sa-card-header"><span><i class="bi bi-person-lines-fill me-2" style="color:var(--sa-primary);"></i>Filleuls — parrainage direct</span></div>
    <div class="sa-card-body p-0">
        @if($referrals->count() > 0)
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead><tr><th>Organisateur</th><th>1er événement</th><th>Statut</th><th>Validé le</th></tr></thead>
                    <tbody>
                        @foreach($referrals as $referral)
                            <tr>
                                <td>
                                    <strong>{{ $referral->organisateur?->nom ?? 'Organisateur supprimé' }}</strong>
                                    <br><small style="color:var(--sa-text-muted);">{{ $referral->organisateur?->email }}</small>
                                </td>
                                <td>{{ $referral->premierEvenement?->titre ?? '—' }}</td>
                                <td>
                                    @if($referral->statut === 'valide')
                                        <span class="sa-badge sa-badge-success">Validé</span>
                                    @elseif($referral->statut === 'annule')
                                        <span class="sa-badge sa-badge-danger">Annulé</span>
                                    @else
                                        <span class="sa-badge sa-badge-warning">En attente</span>
                                    @endif
                                </td>
                                <td>{{ $referral->valide_le?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-3" style="color:var(--sa-text-muted);">Aucun filleul.</div>
        @endif
    </div>
</div>

{{-- Événements co-parrainés --}}
<div class="sa-card mb-4">
    <div class="sa-card-header">
        <span><i class="bi bi-calendar-check me-2" style="color:var(--sa-primary);"></i>Événements co-parrainés</span>
        <span class="sa-topbar-badge">{{ $evenementsCoparaines }} événement(s)</span>
    </div>
    <div class="sa-card-body p-0">
        @if($liens->count() > 0)
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead>
                        <tr>
                            <th>Événement</th>
                            <th>% commission</th>
                            <th class="text-center">Clics</th>
                            <th class="text-end">Commissions</th>
                            <th class="text-end">En attente</th>
                            <th class="text-end">Payé</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($liens as $lien)
                            @php($statsEvenement = $commissionsParEvenement->get($lien->evenement_id))
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:40px;height:40px;border-radius:8px;overflow:hidden;background:#f1f3f5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            @if($lien->evenement?->image)
                                                <img src="{{ asset('storage/' . $lien->evenement->image) }}" alt="{{ $lien->evenement->titre }}" style="width:100%;height:100%;object-fit:cover;">
                                            @else
                                                <i class="bi bi-calendar-event" style="color:#adb5bd;"></i>
                                            @endif
                                        </div>
                                        <div style="min-width:0;">
                                            @if($lien->evenement)
                                                <a href="{{ route('superadmin.evenements.voir', $lien->evenement) }}" class="text-decoration-none">
                                                    <strong style="color:var(--sa-primary);">{{ $lien->evenement->titre }}</strong>
                                                </a>
                                                <br><small style="color:var(--sa-text-muted);">{{ $lien->evenement->date_event?->format('d/m/Y') ?? '—' }}</small>
                                            @else
                                                <span style="color:var(--sa-text-muted);">Événement supprimé</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ rtrim(rtrim(number_format((float) $lien->pourcentage, 2, ',', ' '), '0'), ',') }} %</td>
                                <td class="text-center">{{ $lien->clics }}</td>
                                <td class="text-end">
                                    <strong>{{ number_format((float) ($statsEvenement->commissions_total ?? 0), 0, ',', ' ') }} F</strong>
                                    <br><small style="color:var(--sa-text-muted);">{{ (int) ($statsEvenement->commissions_count ?? 0) }} commission(s)</small>
                                </td>
                                <td class="text-end">{{ number_format((float) ($statsEvenement->commissions_en_attente ?? 0), 0, ',', ' ') }} F</td>
                                <td class="text-end">{{ number_format((float) ($statsEvenement->commissions_payees ?? 0), 0, ',', ' ') }} F</td>
                                <td>
                                    @if($lien->actif)
                                        <span class="sa-badge sa-badge-success">Actif</span>
                                    @else
                                        <span class="sa-badge sa-badge-danger">Inactif</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-3" style="color:var(--sa-text-muted);">Aucun événement co-parrainé.</div>
        @endif
    </div>
</div>

{{-- Commissions --}}
<div class="sa-card mb-4">
    <div class="sa-card-header">
        <span><i class="bi bi-cash-stack me-2" style="color:var(--sa-primary);"></i>Commissions</span>
        <a href="{{ route('superadmin.affiliations.commissions', ['q' => $affiliate->email]) }}" class="sa-btn sa-btn-sm sa-btn-outline">Tout voir</a>
    </div>
    <div class="sa-card-body p-0">
        @if($commissions->count() > 0)
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead>
                        <tr><th>Date</th><th>Type</th><th>Événement</th><th>Base</th><th>%</th><th class="text-end">Montant</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        @foreach($commissions as $commission)
                            <tr>
                                <td style="white-space:nowrap;">{{ $commission->created_at->format('d/m/Y') }}</td>
                                <td>
                                    @if($commission->type === \App\Models\AffiliateCommission::TYPE_PARRAINAGE)
                                        <span class="sa-badge" style="background:rgba(84,38,128,0.1);color:var(--sa-primary);">Parrainage</span>
                                    @else
                                        <span class="sa-badge" style="background:rgba(52,152,219,0.1);color:#3498db;">Co-parrainage</span>
                                    @endif
                                </td>
                                <td>{{ $commission->evenement?->titre ?? '—' }}</td>
                                <td>{{ number_format((float) $commission->base_montant, 0, ',', ' ') }} F</td>
                                <td>{{ rtrim(rtrim(number_format((float) $commission->pourcentage, 2, ',', ' '), '0'), ',') }} %</td>
                                <td class="text-end"><strong>{{ number_format((float) $commission->montant, 0, ',', ' ') }} F</strong></td>
                                <td>
                                    @include('superadmin.affiliations.partials.statut-commission', ['commission' => $commission])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $commissions->links() }}</div>
        @else
            <div class="p-3" style="color:var(--sa-text-muted);">Aucune commission.</div>
        @endif
    </div>
</div>

{{-- Reversements --}}
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-cash-coin me-2" style="color:var(--sa-primary);"></i>Reversements</span>
        <a href="{{ route('superadmin.affiliations.reversements', ['q' => $affiliate->email]) }}" class="sa-btn sa-btn-sm sa-btn-outline">Tout voir</a>
    </div>
    <div class="sa-card-body p-0">
        @if($payouts->count() > 0)
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead><tr><th>Date</th><th class="text-end">Montant</th><th>Opérateur</th><th>Commissions</th><th>Statut</th></tr></thead>
                    <tbody>
                        @foreach($payouts as $payout)
                            <tr>
                                <td style="white-space:nowrap;">{{ $payout->created_at->format('d/m/Y') }}</td>
                                <td class="text-end"><strong>{{ number_format((float) $payout->montant, 0, ',', ' ') }} F</strong></td>
                                <td>{{ $payout->operateur ?: '—' }}</td>
                                <td>{{ $payout->commissions_count }}</td>
                                <td>
                                    @if($payout->statut === 'demande')
                                        <span class="sa-badge sa-badge-warning">Demandé</span>
                                    @elseif($payout->statut === 'en_cours')
                                        <span class="sa-badge sa-badge-info">En cours</span>
                                    @elseif($payout->statut === 'paye')
                                        <span class="sa-badge sa-badge-success">Payé</span>
                                    @else
                                        <span class="sa-badge sa-badge-danger">Refusé</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-3" style="color:var(--sa-text-muted);">Aucune demande de reversement.</div>
        @endif
    </div>
</div>
@endsection
