@extends('superadmin.layouts.master')

@section('title', 'Retraits - Super Admin PaxEvent')
@section('page-title', 'Gestion des retraits')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(243,156,18,0.1); color: var(--sa-warning);"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ $stats['en_attente'] }}</div>
                <div class="kpi-label">En attente</div>
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
                <div class="kpi-value">{{ $stats['total'] }}</div>
                <div class="kpi-label">Payés</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
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
                            <th>Réseau</th>
                            <th>Montant</th>
                            <th>Commission</th>
                            <th>Bénéficiaire</th>
                            <th>Mobile</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($retraits as $retrait)
                            @php
                                $labelReseau = \App\Http\Controllers\RetraitController::getLabelReseau($retrait->reseau);
                            @endphp
                            <tr>
                                <td style="white-space:nowrap;">{{ $retrait->created_at->isoFormat('DD/MM/YYYY HH:mm') }}</td>
                                <td>
                                    <strong>{{ $retrait->user->nom }}</strong>
                                    <br><small style="color:var(--sa-text-muted);">{{ $retrait->user->email }}</small>
                                </td>
                                <td>
                                    <span class="sa-badge" style="background:rgba(52,152,219,0.1);color:#3498db;">{{ $labelReseau }}</span>
                                </td>
                                <td><strong>{{ number_format($retrait->montant, 0, ',', ' ') }} F</strong></td>
                                <td><small>{{ $retrait->commission_percentage }}%</small></td>
                                <td>{{ $retrait->nom }}</td>
                                <td>{{ $retrait->mobile }}</td>
                                <td>
                                    @if($retrait->status === 'en_attente')
                                        <span class="sa-badge sa-badge-warning">En attente</span>
                                    @elseif($retrait->status === 'en_cours')
                                        <span class="sa-badge sa-badge-info">En cours</span>
                                    @elseif($retrait->status === 'payé')
                                        <span class="sa-badge sa-badge-success">Payé</span>
                                    @else
                                        <span class="sa-badge sa-badge-danger">Rejeté</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="sa-btn sa-btn-sm sa-btn-info" title="Voir les détails"
                                        onclick="document.getElementById('detailModal{{ $retrait->id }}').style.display='flex'">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <div id="detailModal{{ $retrait->id }}" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
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
                                                    <span class="org-detail-label">Bénéficiaire</span>
                                                    <span class="org-detail-value">{{ $retrait->nom }}</span>
                                                </div>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Mobile</span>
                                                    <span class="org-detail-value">{{ $retrait->mobile }}</span>
                                                </div>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Statut</span>
                                                    <span class="org-detail-value">
                                                        @if($retrait->status === 'en_attente')
                                                            <span class="sa-badge sa-badge-warning">En attente</span>
                                                        @elseif($retrait->status === 'en_cours')
                                                            <span class="sa-badge sa-badge-info">En cours</span>
                                                        @elseif($retrait->status === 'payé')
                                                            <span class="sa-badge sa-badge-success">Payé</span>
                                                        @else
                                                            <span class="sa-badge sa-badge-danger">Rejeté</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                @if($retrait->admin_notes)
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Notes</span>
                                                    <span class="org-detail-value" style="white-space:pre-line;">{{ $retrait->admin_notes }}</span>
                                                </div>
                                                @endif
                                                @if($retrait->processed_at)
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Traité le</span>
                                                    <span class="org-detail-value">{{ $retrait->processed_at->format('d M Y à H:i') }}</span>
                                                </div>
                                                @endif
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Demandé le</span>
                                                    <span class="org-detail-value">{{ $retrait->created_at->format('d M Y à H:i') }}</span>
                                                </div>
                                            </div>
                                            <div class="modal-footer" style="gap:0.5rem; flex-wrap:wrap;">
                                                @if($retrait->status === 'en_attente')
                                                    <form action="{{ route('superadmin.retraits.approuver', $retrait) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="sa-btn sa-btn-primary" onclick="return confirm('Approuver ce retrait ?')">
                                                            <i class="bi bi-check-lg"></i> Approuver
                                                        </button>
                                                    </form>
                                                    <button type="button" class="sa-btn sa-btn-danger" onclick="this.closest('.modal-overlay').style.display='none'; document.getElementById('rejectModal{{ $retrait->id }}').style.display='flex';">
                                                        <i class="bi bi-x-lg"></i> Rejeter
                                                    </button>
                                                @elseif($retrait->status === 'en_cours')
                                                    <form action="{{ route('superadmin.retraits.confirmer', $retrait) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="sa-btn sa-btn-primary" style="background:var(--sa-success);border-color:var(--sa-success);" onclick="return confirm('Confirmer que le paiement a été effectué ?')">
                                                            <i class="bi bi-check-circle"></i> Confirmer le paiement
                                                        </button>
                                                    </form>
                                                @endif
                                                <button class="sa-btn sa-btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Fermer</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="rejectModal{{ $retrait->id }}" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
                                        <div class="modal-box">
                                            <div class="modal-header">
                                                <h5><i class="bi bi-x-circle me-2" style="color:var(--sa-danger);"></i>Rejeter la demande</h5>
                                                <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('superadmin.retraits.rejeter', $retrait) }}" method="POST" id="rejectForm{{ $retrait->id }}">
                                                    @csrf
                                                    <p style="font-size:0.85rem;color:#6c757d;margin-bottom:1rem;">
                                                        Sélectionnez la ou les raisons du rejet :
                                                    </p>
                                                    <div class="mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="motifs[]" value="numero_invalide" id="motif1_{{ $retrait->id }}">
                                                            <label class="form-check-label" for="motif1_{{ $retrait->id }}" style="font-size:0.85rem;">Numéro invalide — Format incorrect ou inactif</label>
                                                        </div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="motifs[]" value="doublon" id="motif2_{{ $retrait->id }}">
                                                            <label class="form-check-label" for="motif2_{{ $retrait->id }}" style="font-size:0.85rem;">Doublon de demande — Demande en attente non traitée</label>
                                                        </div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="motifs[]" value="numero_reseau" id="motif3_{{ $retrait->id }}">
                                                            <label class="form-check-label" for="motif3_{{ $retrait->id }}" style="font-size:0.85rem;">Numéro ne correspond pas au réseau sélectionné</label>
                                                        </div>
                                                    </div>
                                                    <hr style="margin:0.75rem 0;">
                                                    <div class="mb-0">
                                                        <label style="font-size:0.82rem;font-weight:600;color:#666;">Autres raisons</label>
                                                        <textarea name="autre_raison" class="sa-form-control" rows="2" placeholder="Précisez une autre raison..."></textarea>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="sa-btn sa-btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Annuler</button>
                                                <button type="submit" form="rejectForm{{ $retrait->id }}" class="sa-btn sa-btn-danger" onclick="return confirm('Confirmer le rejet ?')">
                                                    <i class="bi bi-x-lg"></i> Confirmer le rejet
                                                </button>
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
.sa-btn-danger {
    background: var(--sa-danger); border: none; color: #fff; padding: 0.4rem 1rem;
    border-radius: 6px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s; display: inline-flex; align-items: center; gap: 0.3rem;
}
.sa-btn-danger:hover { opacity: 0.85; }

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

.modal-overlay .sa-form-control {
    border: 1px solid var(--sa-border);
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    font-size: 0.82rem;
    width: 100%;
    transition: border-color 0.15s;
}
.modal-overlay .sa-form-control:focus {
    border-color: var(--sa-primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(107,63,160,0.1);
}
</style>
@endsection
