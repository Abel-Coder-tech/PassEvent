@extends('layouts.app')

@section('title', 'Retraits - Finances')
@section('page-title', 'Retraits')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Retraits</li>
@endsection

@section('content')
<div class="page-content">
    <!-- Cartes stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.1);"><i class="bi bi-phone" style="color: var(--violet);"></i></div>
                <div class="metric-label">Mobile Money (FedaPay)</div>
                <div class="metric-value" style="font-size:1.3rem;">{{ number_format($mobileRecettes, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Recettes totales</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--gris);">
                <div class="metric-icon" style="background: rgba(152,145,155,0.1);"><i class="bi bi-percent" style="color: var(--gris);"></i></div>
                <div class="metric-label">Commission ({{ \App\Http\Controllers\RetraitController::COMMISSION_PERCENTAGE }}%)</div>
                <div class="metric-value" style="font-size:1.3rem;">{{ number_format($commissionTotale, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Sur tous les tickets</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);"><i class="bi bi-wallet2" style="color: var(--vert);"></i></div>
                <div class="metric-label">Solde disponible</div>
                <div class="metric-value" style="font-size:1.3rem; color: var(--vert);">{{ number_format($soldeDisponible, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">
                    Net ({{ number_format(max(0, $mobileRecettes - $commissionTotale), 0, ',', ' ') }})
                    - Retiré ({{ number_format($totalRetraits, 0, ',', ' ') }})
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 d-flex align-items-center justify-content-center">
            @if($soldeDisponible > 0)
                <button type="button" class="btn" style="background: linear-gradient(135deg, #7B3FA0, #9c4db8); color: #fff; font-weight: 700; padding: 0.85rem 1.5rem; border-radius: 12px; border: none; width: 100%; box-shadow: 0 4px 16px rgba(123,63,160,0.3);" data-bs-toggle="modal" data-bs-target="#retraitModal">
                    <i class="bi bi-send me-1"></i> Demander un retrait
                </button>
            @else
                <div class="text-center" style="color: var(--gris); font-size: 0.82rem;">
                    <i class="bi bi-lock d-block mb-1" style="font-size:1.5rem;"></i>
                    Solde insuffisant
                </div>
            @endif
        </div>
    </div>

    <!-- Modal demande retrait -->
    <div class="modal fade" id="retraitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 14px; border: none;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold" style="color: #1a1a2e;"><i class="bi bi-send me-2" style="color: var(--violet);"></i> Demande de retrait</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p style="color: #6c757d; font-size: 0.85rem; margin-bottom: 1rem;">
                        Solde disponible : <strong style="color: var(--vert);">{{ number_format($soldeDisponible, 0, ',', ' ') }} FCFA</strong>
                    </p>
                    <form action="{{ route('admin.retraits.store') }}" method="POST" id="formRetrait" autocomplete="off">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Réseau cible <span class="text-danger">*</span></label>
                            <select name="reseau" id="reseauSelect" class="form-select" required autocomplete="off">
                                <option value="">Choisir le réseau du retrait —</option>
                                @foreach(\App\Http\Controllers\RetraitController::RESEAUX_CONFIG as $key => $cfg)
                                <option value="{{ $key }}">{{ $cfg['label'] }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Réseau sur lequel vous souhaitez recevoir le montant</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Montant à retirer <span class="text-danger">*</span></label>
                            <input type="number" name="montant" class="form-control" min="1000" max="{{ $soldeDisponible }}" step="100" placeholder="Ex: 50000" required autocomplete="off">
                            <small class="text-muted">Min 1 000 FCFA · Max {{ number_format($soldeDisponible, 0, ',', ' ') }} FCFA</small>
                            @error('montant')<div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Nom du bénéficiaire <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control" placeholder="Ex: Kofi Mensah" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Numéro Mobile Money <span class="text-danger">*</span></label>
                            <input type="text" name="mobile" class="form-control" placeholder="Ex: +229 6X XX XX XX" required autocomplete="off">
                            <small class="text-muted">Doit correspondre au réseau sélectionné</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Confirmez votre mot de passe <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Saisissez votre mot de passe" required autocomplete="new-password">
                            @error('password')<div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn w-100 py-2" style="background: linear-gradient(135deg, #7B3FA0, #9c4db8); color: #fff; font-weight:700; border-radius:10px; border:none;">
                            <i class="bi bi-send me-1"></i> Envoyer la demande
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal succès retrait -->
    <div class="modal fade" id="successRetraitModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 14px; border: none;">
                <div class="modal-body text-center p-4">
                    <div style="width:64px;height:64px;background:rgba(18,151,110,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                        <i class="bi bi-check-circle" style="font-size:2rem;color:var(--vert);"></i>
                    </div>
                    <h5 class="fw-bold mb-2" style="color:#1a1a2e;">Demande envoyée !</h5>
                    <p style="color:#6c757d;font-size:0.9rem;margin-bottom:1rem;">{{ session('success') }}</p>
                    <div class="alert alert-warning py-2 px-3 mb-0" style="font-size:0.85rem;border-radius:8px;text-align:left;">
                        <i class="bi bi-info-circle me-1"></i> <strong>Rappel :</strong> Les retraits sont effectués sous un délai minimum de <strong>72 heures</strong> après la demande. Vous recevrez une notification une fois votre retrait traité.
                    </div>
                    <button type="button" class="btn w-100 py-2 fw-bold text-white mt-3" style="background: var(--vert); border: none; border-radius: 8px;" data-bs-dismiss="modal">
                        Compris
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Historique des retraits -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h5>Historique des demandes</h5>
        </div>
        <div class="panel-card-body p-0">
            @if($retraits->count() > 0)
                <div class="table-responsive">
                    <table class="custom-table table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Réseau</th>
                                <th>Montant</th>
                                <th>Bénéficiaire</th>
                                <th>Mobile</th>
                                <th>Statut</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($retraits as $retrait)
                                <tr>
                                    <td style="white-space:nowrap;">{{ $retrait->created_at->isoFormat('D MMM YYYY HH:mm') }}</td>
                                    <td>
                                        @if($retrait->reseau && isset(\App\Http\Controllers\RetraitController::RESEAUX_CONFIG[$retrait->reseau]))
                                            <span style="font-weight:600;">{{ \App\Http\Controllers\RetraitController::RESEAUX_CONFIG[$retrait->reseau]['label'] }}</span>
                                        @else
                                            <span style="color:var(--gris);">—</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ number_format($retrait->montant, 0, ',', ' ') }} F</strong></td>
                                    <td>{{ $retrait->nom }}</td>
                                    <td>{{ $retrait->mobile }}</td>
                                    <td>
                                        @if($retrait->status === 'en_attente')
                                            <span class="status-badge" style="background: rgba(243,156,18,0.12); color: #f39c12;">En attente</span>
                                        @elseif($retrait->status === 'approuvé')
                                            <span class="status-badge" style="background: rgba(18,151,110,0.12); color: var(--vert);">Approuvé</span>
                                        @else
                                            <span class="status-badge" style="background: rgba(231,76,60,0.12); color: #e74c3c;">Rejeté</span>
                                        @endif
                                    </td>
                                    <td style="max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--gris);">
                                        {{ $retrait->admin_notes ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5" style="color: var(--gris);">
                    <i class="bi bi-inbox d-block mb-2" style="font-size: 2.5rem;"></i>
                    <p>Aucune demande de retrait pour le moment.</p>
                </div>
            @endif
        </div>
    </div>

    @if($retraits->hasPages())
        <div class="mt-3 pagination-wrap">{{ $retraits->links() }}</div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        const successModal = new bootstrap.Modal(document.getElementById('successRetraitModal'));
        successModal.show();
    @endif
});
</script>
@endsection
