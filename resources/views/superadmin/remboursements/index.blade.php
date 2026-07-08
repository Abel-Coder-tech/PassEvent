@extends('superadmin.layouts.master')

@section('title', 'Demandes de remboursement - Super Admin')
@section('page-title', 'Demandes de remboursement')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(243,156,18,0.1);color:var(--sa-warning);"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-info"><div class="kpi-value">{{ $stats['en_attente'] }}</div><div class="kpi-label">En attente</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(52,152,219,0.1);color:#3498db;"><i class="bi bi-arrow-repeat"></i></div>
            <div class="kpi-info"><div class="kpi-value">{{ $stats['en_cours'] }}</div><div class="kpi-label">En cours</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(39,174,96,0.1);color:var(--sa-success);"><i class="bi bi-cash-coin"></i></div>
            <div class="kpi-info"><div class="kpi-value">{{ number_format($stats['total_montant'], 0, ',', ' ') }} F</div><div class="kpi-label">Montant à traiter</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(107,63,160,0.1);color:var(--sa-primary);"><i class="bi bi-check-circle"></i></div>
            <div class="kpi-info"><div class="kpi-value">{{ number_format($stats['rembourse_mois'], 0, ',', ' ') }} F</div><div class="kpi-label">Remboursé ce mois</div></div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-receipt me-2" style="color:var(--sa-primary);"></i>Toutes les demandes</span>
        <span class="text-muted" style="font-size:0.8rem;">{{ $demandes->total() }} demande(s)</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Organisateur</th>
                    <th>Événement</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Motif</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandes as $d)
                <tr>
                    <td>{{ $d->id }}</td>
                    <td><strong>{{ $d->organisateur->nom }}</strong><br><small style="font-size:0.7rem;">{{ $d->organisateur->email }}</small></td>
                    <td>{{ $d->evenement?->titre ?? '—' }}</td>
                    <td><span class="sa-badge sa-badge-{{ $d->type === 'groupe' ? 'warning' : 'info' }}">{{ $d->type === 'groupe' ? 'Groupé' : 'Individuel' }}</span></td>
                    <td class="fw-bold" style="color:var(--sa-success);">{{ number_format($d->montant_total, 0, ',', ' ') }} F</td>
                    <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:0.78rem;">{{ $d->motif }}</td>
                    <td>
                        @if($d->statut === 'en_attente')
                            <span class="sa-badge sa-badge-warning">En attente</span>
                        @elseif($d->statut === 'en_cours')
                            <span class="sa-badge" style="background:rgba(52,152,219,0.12);color:#2563eb;">En cours</span>
                        @elseif($d->statut === 'rembourse')
                            <span class="sa-badge sa-badge-success">Remboursé</span>
                        @else
                            <span class="sa-badge sa-badge-danger">Refusé</span>
                        @endif
                    </td>
                    <td style="font-size:0.75rem;">{{ $d->created_at->isoFormat('D MMM YYYY HH:mm') }}</td>
                    <td>
                        <a href="{{ route('superadmin.remboursements.voir', $d) }}" class="sa-btn sa-btn-sm" style="background:#3b82f6;border:none;color:#fff;padding:0.25rem 0.5rem;border-radius:6px;font-size:0.75rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">Aucune demande de remboursement</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $demandes->links() }}</div>
<style>
.kpi-card {
    background: #fff; border-radius: 10px; padding: 1rem;
    display: flex; align-items: center; gap: 0.75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.kpi-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    flex-shrink: 0;
}
.kpi-info { min-width: 0; }
.kpi-value { font-size: 1.2rem; font-weight: 800; line-height: 1.2; }
.kpi-label { font-size: 0.72rem; color: #888; font-weight: 500; }
</style>
@endsection