@extends('superadmin.layouts.master')

@section('title', 'Retraits - Super Admin PaxEvent')
@section('page-title', 'Gestion des retraits')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(243,156,18,0.1); color: var(--sa-warning);"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ $stats['en_attente'] }}</div>
                <div class="kpi-label">Demandes en attente</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-check-circle-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ $stats['total'] }}</div>
                <div class="kpi-label">Retraits approuvés</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-cash-coin"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($stats['approuve'], 0, ',', ' ') }} F</div>
                <div class="kpi-label">Total retiré</div>
            </div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-list-check me-2" style="color: var(--sa-primary);"></i>Toutes les demandes</span>
        <span class="sa-topbar-badge">{{ $retraits->total() }} demandes</span>
    </div>
    <div class="sa-card-body p-0">
        @if($retraits->count() > 0)
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Organisateur</th>
                            <th>Montant</th>
                            <th>Commission</th>
                            <th>Bénéficiaire</th>
                            <th>Opérateur</th>
                            <th>Mobile</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($retraits as $retrait)
                            <tr>
                                <td style="white-space:nowrap;">{{ $retrait->created_at->isoFormat('DD/MM/YYYY HH:mm') }}</td>
                                <td>
                                    <strong>{{ $retrait->user->nom }}</strong>
                                    <br><small style="color:var(--sa-text-muted);">{{ $retrait->user->email }}</small>
                                </td>
                                <td><strong>{{ number_format($retrait->montant, 0, ',', ' ') }} F</strong></td>
                                <td><small>{{ $retrait->commission_percentage }}%</small></td>
                                <td>{{ $retrait->nom }}</td>
                                <td>
                                    @if($retrait->operateur && isset(\App\Http\Controllers\RetraitController::getOperateurs()[$retrait->operateur]))
                                        {{ \App\Http\Controllers\RetraitController::getOperateurs()[$retrait->operateur] }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $retrait->mobile }}</td>
                                <td>
                                    @if($retrait->status === 'en_attente')
                                        <span class="sa-badge sa-badge-warning">En attente</span>
                                    @elseif($retrait->status === 'approuvé')
                                        <span class="sa-badge sa-badge-success">Approuvé</span>
                                    @else
                                        <span class="sa-badge sa-badge-danger">Rejeté</span>
                                    @endif
                                </td>
                                <td>
                                    @if($retrait->status === 'en_attente')
                                        <form action="{{ route('superadmin.retraits.approuver', $retrait) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="sa-btn sa-btn-primary sa-btn-sm" onclick="return confirm('Approuver ce retrait de {{ number_format($retrait->montant, 0, ',', ' ') }} FCFA ?')">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="sa-btn sa-btn-outline sa-btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $retrait->id }}">
                                            <i class="bi bi-x-lg" style="color:var(--sa-danger);"></i>
                                        </button>
                                        <!-- Modal Rejet -->
                                        <div class="modal fade" id="rejectModal{{ $retrait->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content" style="border-radius:12px;">
                                                    <form action="{{ route('superadmin.retraits.rejeter', $retrait) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header border-0 pb-0">
                                                            <h6 class="fw-bold">Rejeter la demande</h6>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p style="font-size:0.85rem; color:#6c757d;">Motif du rejet (optionnel) :</p>
                                                            <textarea name="admin_notes" class="sa-form-control" rows="3" placeholder="Expliquez pourquoi..."></textarea>
                                                        </div>
                                                        <div class="modal-footer border-0 pt-0">
                                                            <button type="button" class="sa-btn sa-btn-outline" data-bs-dismiss="modal">Annuler</button>
                                                            <button type="submit" class="sa-btn sa-btn-danger">Rejeter</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <button class="sa-btn sa-btn-sm sa-btn-info" title="Voir les details"
                                        onclick="document.getElementById('voirModal{{ $retrait->id }}').style.display='flex'">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    {{-- Modal Voir --}}
                                    <div id="voirModal{{ $retrait->id }}" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
                                        <div class="modal-box">
                                            <div class="modal-header">
                                                <h5><i class="bi bi-cash-coin me-2" style="color:var(--sa-primary);"></i>Demande de retrait</h5>
                                                <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Organisateur</span>
                                                    <span class="org-detail-value"><strong>{{ $retrait->user->nom }}</strong></span>
                                                </div>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Email</span>
                                                    <span class="org-detail-value">{{ $retrait->user->email }}</span>
                                                </div>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Montant</span>
                                                    <span class="org-detail-value"><strong>{{ number_format($retrait->montant, 0, ',', ' ') }} FCFA</strong></span>
                                                </div>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Commission</span>
                                                    <span class="org-detail-value">{{ $retrait->commission_percentage }} %</span>
                                                </div>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Beneficiaire</span>
                                                    <span class="org-detail-value">{{ $retrait->nom }}</span>
                                                </div>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Operateur</span>
                                                    <span class="org-detail-value">
                                                        @if($retrait->operateur && isset(\App\Http\Controllers\RetraitController::getOperateurs()[$retrait->operateur]))
                                                            {{ \App\Http\Controllers\RetraitController::getOperateurs()[$retrait->operateur] }}
                                                        @else
                                                            —
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Mobile</span>
                                                    <span class="org-detail-value">{{ $retrait->mobile }}</span>
                                                </div>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Statut</span>
                                                    <span class="org-detail-value">
                                                        @if($retrait->status === 'en_attente')<span class="sa-badge sa-badge-warning">En attente</span>
                                                        @elseif($retrait->status === 'approuvé')<span class="sa-badge sa-badge-success">Approuvé</span>
                                                        @else<span class="sa-badge sa-badge-danger">Rejeté</span>@endif
                                                    </span>
                                                </div>
                                                @if($retrait->admin_notes)
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Note admin</span>
                                                    <span class="org-detail-value">{{ $retrait->admin_notes }}</span>
                                                </div>
                                                @endif
                                                @if($retrait->processed_at)
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Traite le</span>
                                                    <span class="org-detail-value">{{ $retrait->processed_at->format('d M Y à H:i') }}</span>
                                                </div>
                                                @endif
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Demande le</span>
                                                    <span class="org-detail-value">{{ $retrait->created_at->format('d M Y à H:i') }}</span>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                @if($retrait->status === 'en_attente')
                                                    <form action="{{ route('superadmin.retraits.approuver', $retrait) }}" method="POST" style="display:inline;margin-right:auto;">
                                                        @csrf
                                                        <button type="submit" class="sa-btn sa-btn-primary" onclick="return confirm('Approuver ce retrait de {{ number_format($retrait->montant, 0, ',', ' ') }} FCFA ?')">
                                                            <i class="bi bi-check-lg"></i> Valider la demande
                                                        </button>
                                                    </form>
                                                    <button class="sa-btn sa-btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Fermer</button>
                                                @else
                                                    <button class="sa-btn sa-btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Fermer</button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox d-block mb-2" style="font-size: 2.5rem; color: var(--sa-text-muted);"></i>
                <p style="color: var(--sa-text-muted);">Aucune demande de retrait.</p>
            </div>
        @endif
    </div>
</div>

@if($retraits->hasPages())
    <div class="mt-3">{{ $retraits->links() }}</div>
@endif

<style>
.sa-btn-info {
    background: #3b82f6; border: none; color: #fff; padding: 0.3rem 0.6rem;
    border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s;
}
.sa-btn-info:hover { opacity: 0.85; }
.sa-btn-secondary {
    background: #6c757d; border: none; color: #fff; padding: 0.4rem 1rem;
    border-radius: 6px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s;
}
.sa-btn-secondary:hover { opacity: 0.85; }

.modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.modal-box {
    background: #fff;
    border-radius: 14px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    animation: modalIn 0.2s ease;
}
@keyframes modalIn {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #eee;
}
.modal-header h5 { margin: 0; font-size: 1rem; font-weight: 700; }
.modal-close {
    background: none; border: none;
    font-size: 1.5rem; cursor: pointer;
    color: #999; line-height: 1;
}
.modal-close:hover { color: #333; }
.modal-body { padding: 1.25rem; }
.modal-footer {
    padding: 0.75rem 1.25rem;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
}
.org-detail-row {
    display: flex;
    gap: 1rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f5f5f5;
    font-size: 0.85rem;
}
.org-detail-row:last-child { border-bottom: none; }
.org-detail-label {
    font-weight: 600;
    color: #666;
    min-width: 120px;
    flex-shrink: 0;
}
.org-detail-value { color: #1a1a1a; }

.modal-content .sa-form-control {
    border: 1px solid var(--sa-border);
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    font-size: 0.82rem;
    width: 100%;
    transition: border-color 0.15s;
}
.modal-content .sa-form-control:focus {
    border-color: var(--sa-primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(107,63,160,0.1);
}
</style>
@endsection
