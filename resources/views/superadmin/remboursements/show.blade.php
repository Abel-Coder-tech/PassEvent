@extends('superadmin.layouts.master')

@section('title', 'Demande #' . $demande->id . ' — Super Admin')
@section('page-title', 'Demande #' . $demande->id)

@section('content')
<div class="mb-3">
    <a href="{{ route('superadmin.remboursements.demandes') }}" class="text-decoration-none small" style="color: var(--sa-primary);">
        <i class="bi bi-arrow-left"></i> Toutes les demandes
    </a>
</div>

@if(session('error'))
<div class="alert alert-danger py-2 small">{{ session('error') }}</div>
@endif
@if(session('success'))
<div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="sa-card h-100">
            <div class="sa-card-header">
                <span><i class="bi bi-info-circle me-2" style="color:var(--sa-primary);"></i>Détails de la demande</span>
            </div>
            <div class="sa-card-body">
                <div class="org-detail-row">
                    <span class="org-detail-label">Organisateur</span>
                    <span class="org-detail-value"><strong>{{ $demande->organisateur->nom }}</strong> ({{ $demande->organisateur->email }})</span>
                </div>
                <div class="org-detail-row">
                    <span class="org-detail-label">Événement</span>
                    <span class="org-detail-value">{{ $demande->evenement?->titre ?? '—' }}</span>
                </div>
                <div class="org-detail-row">
                    <span class="org-detail-label">Type</span>
                    <span class="org-detail-value">
                        <span class="sa-badge sa-badge-{{ $demande->type === 'groupe' ? 'warning' : 'info' }}">
                            {{ $demande->type === 'groupe' ? 'Remboursement groupé' : 'Remboursement individuel' }}
                        </span>
                    </span>
                </div>
                <div class="org-detail-row">
                    <span class="org-detail-label">Montant total</span>
                    <span class="org-detail-value fw-bold" style="color:var(--sa-success);font-size:1.1rem;">{{ number_format($demande->montant_total, 0, ',', ' ') }} F</span>
                </div>
                <div class="org-detail-row">
                    <span class="org-detail-label">Solde organisateur</span>
                    <span class="org-detail-value fw-bold" style="color:{{ $soldeOrganisateur >= $demande->montant_total ? 'var(--sa-success)' : 'var(--sa-danger)' }};">
                        {{ number_format($soldeOrganisateur, 0, ',', ' ') }} F
                        @if($soldeOrganisateur < $demande->montant_total)
                            <span class="sa-badge sa-badge-danger ms-2">Insuffisant</span>
                        @else
                            <span class="sa-badge sa-badge-success ms-2">Suffisant</span>
                        @endif
                    </span>
                </div>
                <div class="org-detail-row">
                    <span class="org-detail-label">Statut</span>
                    <span class="org-detail-value">
                        @if($demande->statut === 'en_attente')
                            <span class="sa-badge sa-badge-warning">En attente</span>
                        @elseif($demande->statut === 'en_cours')
                            <span class="sa-badge" style="background:rgba(52,152,219,0.12);color:#2563eb;">En cours (FedaPay)</span>
                        @elseif($demande->statut === 'rembourse')
                            <span class="sa-badge sa-badge-success">Remboursé</span>
                        @else
                            <span class="sa-badge sa-badge-danger">Refusé</span>
                        @endif
                    </span>
                </div>
                <div class="org-detail-row">
                    <span class="org-detail-label">Motif</span>
                    <span class="org-detail-value">{{ $demande->motif }}</span>
                </div>
                @if($demande->notes_admin)
                <div class="org-detail-row">
                    <span class="org-detail-label">Notes admin</span>
                    <span class="org-detail-value">{{ $demande->notes_admin }}</span>
                </div>
                @endif
                @if($demande->traiteePar)
                <div class="org-detail-row">
                    <span class="org-detail-label">Traitée par</span>
                    <span class="org-detail-value">{{ $demande->traiteePar->nom }} ({{ $demande->traiteePar->email }})</span>
                </div>
                @endif
                @if($demande->traitee_le)
                <div class="org-detail-row">
                    <span class="org-detail-label">Traitée le</span>
                    <span class="org-detail-value">{{ $demande->traitee_le->isoFormat('D MMM YYYY HH:mm') }}</span>
                </div>
                @endif
                <div class="org-detail-row">
                    <span class="org-detail-label">Créée le</span>
                    <span class="org-detail-value">{{ $demande->created_at->isoFormat('D MMM YYYY HH:mm') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="sa-card h-100">
            <div class="sa-card-header">
                <span><i class="bi bi-ticket me-2" style="color:var(--sa-primary);"></i>Tickets concernés</span>
                <span class="text-muted" style="font-size:0.8rem;">{{ $demande->tickets->count() }} ticket(s)</span>
            </div>
            <div class="sa-card-body p-0">
                <table class="sa-table">
                    <thead>
                        <tr><th>Code</th><th>Acheteur</th><th>Montant</th><th>Transaction</th></tr>
                    </thead>
                    <tbody>
                        @forelse($demande->tickets as $ticket)
                        <tr>
                            <td><code style="font-size:0.75rem;">{{ $ticket->code_unique }}</code></td>
                            <td style="font-size:0.78rem;">{{ $ticket->nom_acheteur }}</td>
                            <td class="fw-bold" style="color:var(--sa-success);">{{ number_format($ticket->montant, 0, ',', ' ') }} F</td>
                            <td><code style="font-size:0.7rem;">{{ $ticket->transaction_id ?? '—' }}</code></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Aucun ticket</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if($demande->statut === 'en_attente')
<div class="sa-card mb-4" style="border-left:4px solid var(--sa-warning);">
    <div class="sa-card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-8">
                <label class="fw-semibold small">Notes (optionnel)</label>
                <form id="formApprouver" action="{{ route('superadmin.remboursements.approuver', $demande) }}" method="POST" class="d-inline">
                    @csrf
                    <textarea name="notes_admin" class="sa-form-control" rows="2" placeholder="Ajouter une note interne..." style="resize:vertical;"></textarea>
                </form>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" form="formApprouver" class="sa-btn sa-btn-primary">
                    <i class="bi bi-check-lg"></i> Approuver
                </button>
                <button type="button" class="sa-btn sa-btn-danger" onclick="document.getElementById('refuseModal').style.display='flex'">
                    <i class="bi bi-x-lg"></i> Refuser
                </button>
            </div>
        </div>
        <div class="mt-2 small text-muted">
            <i class="bi bi-info-circle"></i>
            L'approbation passe la demande en <strong>« En cours »</strong>. Effectuez le remboursement sur FedaPay puis confirmez.
        </div>
    </div>
</div>
@endif

@if($demande->statut === 'en_cours')
<div class="sa-card mb-4" style="border-left:4px solid #3498db;">
    <div class="sa-card-body">
        <div class="row g-2 align-items-center">
            <div class="col-md-8">
                <p class="mb-1 fw-semibold">Remboursement en cours sur FedaPay</p>
                <p class="mb-0 small text-muted">
                    Après avoir effectué le remboursement sur votre tableau de bord FedaPay, cliquez sur « Confirmer ».
                </p>
                @if($demande->tickets->count() > 1)
                <details class="mt-2">
                    <summary class="small fw-semibold" style="cursor:pointer;">Transactions FedaPay à rembourser</summary>
                    <ul class="mt-2 mb-0" style="font-size:0.72rem;">
                        @foreach($demande->tickets as $ticket)
                        <li><code>{{ $ticket->transaction_id ?? 'N/A' }}</code> — {{ number_format($ticket->montant, 0, ',', ' ') }} F</li>
                        @endforeach
                    </ul>
                </details>
                @endif
            </div>
            <div class="col-md-4 d-flex gap-2">
                <form action="{{ route('superadmin.remboursements.confirmer', $demande) }}" method="POST" onsubmit="return confirm('Confirmer le remboursement de {{ number_format($demande->montant_total, 0, ',', ' ') }} F ? Les acheteurs seront notifiés par email.')">
                    @csrf
                    <button type="submit" class="sa-btn sa-btn-success">
                        <i class="bi bi-check-circle"></i> Confirmer remboursement
                    </button>
                </form>
                <button type="button" class="sa-btn sa-btn-danger" onclick="document.getElementById('refuseModal').style.display='flex'">
                    <i class="bi bi-x-lg"></i> Refuser
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@if($demande->statut === 'rembourse')
<div class="sa-card mb-4" style="border-left:4px solid var(--sa-success);">
    <div class="sa-card-body">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill" style="color:var(--sa-success);font-size:1.5rem;"></i>
            <div>
                <p class="fw-bold mb-0">Remboursement confirmé</p>
                <p class="small text-muted mb-0">Traitée par {{ $demande->traiteePar?->nom ?? '—' }} le {{ $demande->traitee_le?->isoFormat('D MMM YYYY HH:mm') ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>
@endif

@if($demande->statut === 'refuse')
<div class="sa-card mb-4" style="border-left:4px solid var(--sa-danger);">
    <div class="sa-card-body">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-x-circle-fill" style="color:var(--sa-danger);font-size:1.5rem;"></i>
            <div>
                <p class="fw-bold mb-0">Demande refusée</p>
                @if($demande->notes_admin)
                <p class="small text-muted mb-0">Motif : {{ $demande->notes_admin }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal Refus --}}
<div id="refuseModal" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-header">
            <h5><i class="bi bi-x-circle me-2" style="color:var(--sa-danger);"></i>Refuser la demande</h5>
            <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
        </div>
        <form action="{{ route('superadmin.remboursements.refuser', $demande) }}" method="POST">
            @csrf
            <div class="modal-body">
                <p style="font-size:0.85rem;color:#666;margin-bottom:1rem;">Expliquez le motif du refus. L'organisateur recevra un email.</p>
                <textarea name="motif_refus" class="sa-form-control" rows="4" placeholder="Motif du refus..." required style="resize:vertical;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="sa-btn sa-btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Annuler</button>
                <button type="submit" class="sa-btn sa-btn-danger"><i class="bi bi-x-lg"></i> Refuser</button>
            </div>
        </form>
    </div>
</div>

<style>
.org-detail-row {
    display: flex; gap: 1rem; padding: 0.5rem 0;
    border-bottom: 1px solid #f5f5f5; font-size: 0.85rem;
}
.org-detail-row:last-child { border-bottom: none; }
.org-detail-label { font-weight: 600; color: #666; min-width: 130px; flex-shrink: 0; }
.org-detail-value { color: #1a1a1a; }
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.45); z-index: 9999;
    align-items: center; justify-content: center;
}
.modal-box {
    background: #fff; border-radius: 14px; width: 90%; max-width: 500px;
    max-height: 90vh; overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2); animation: modalIn 0.2s ease;
}
@keyframes modalIn { from{transform:scale(0.95);opacity:0} to{transform:scale(1);opacity:1} }
.modal-header { display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #eee; }
.modal-header h5 { margin:0;font-size:1rem;font-weight:700; }
.modal-close { background:none;border:none;font-size:1.5rem;cursor:pointer;color:#999;line-height:1; }
.modal-body { padding:1.25rem; }
.modal-footer { padding:0.75rem 1.25rem;border-top:1px solid #eee;display:flex;justify-content:flex-end;gap:0.5rem; }
.sa-form-control {
    width:100%;padding:0.5rem 0.75rem;border:1px solid #e0e0e0;border-radius:8px;
    font-size:0.85rem;outline:none;transition:border-color 0.15s;background:#fff;
}
.sa-form-control:focus { border-color: var(--sa-primary); }
.sa-btn-success { background:#2e7d4f;border:none;color:#fff;padding:0.4rem 0.8rem;border-radius:8px;font-size:0.82rem;font-weight:600;cursor:pointer; }
.sa-btn-danger { background:#dc3545;border:none;color:#fff;padding:0.4rem 0.8rem;border-radius:8px;font-size:0.82rem;font-weight:600;cursor:pointer; }
</style>
@endsection