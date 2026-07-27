@extends('layouts.app')

@section('title', 'Retraits - Finances')
@section('page-title', 'Retraits')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Retraits</li>
@endsection

@section('content')
<div class="page-content">
    <!-- Cartes solde par réseau + bouton retrait -->
    <div class="row g-3 mb-4">
        @php
            $reseauColors = [
                'mtn' => '#f5a623',
                'moov' => '#7B3FA0',
                'celtiis' => '#3498db',
            ];
        @endphp
        @foreach($soldes as $key => $s)
        <div class="col-md-3">
            <div class="metric-card" style="border-top-color: {{ $reseauColors[$key] ?? 'var(--gris)' }}; height:100%;">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:42px;height:42px;background:{{ $reseauColors[$key] ?? '#3498db' }}1a;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi {{ $s['icon'] }}" style="color:{{ $reseauColors[$key] ?? '#3498db' }};font-size:1.15rem;"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;color:var(--sombre);font-size:0.9rem;">{{ $s['label'] }}</div>
                        <div style="font-size:0.72rem;color:var(--gris);">{{ number_format($s['recettes'], 0, ',', ' ') }} F collectés</div>
                    </div>
                </div>
                <div style="border-top:1px solid #f5f5f5;padding-top:0.4rem;margin-top:0.2rem;">
                    <div class="d-flex justify-content-between py-1" style="font-size:0.78rem;">
                        <span style="color:var(--gris);">Commission</span>
                        <span style="color:#e74c3c;">-{{ number_format($s['commission'], 0, ',', ' ') }} F</span>
                    </div>
                    @if($s['retraits'] > 0)
                    <div class="d-flex justify-content-between py-1" style="font-size:0.78rem;">
                        <span style="color:var(--gris);">Déjà retiré</span>
                        <span style="color:#f39c12;">-{{ number_format($s['retraits'], 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between py-1" style="font-size:0.85rem;border-top:1px solid #f5f5f5;margin-top:0.2rem;">
                        <span style="font-weight:700;">Solde</span>
                        <span style="font-weight:800;color:{{ $s['solde'] > 0 ? 'var(--vert)' : 'var(--gris)' }};">{{ number_format($s['solde'], 0, ',', ' ') }} F</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <div class="col-md-3 d-flex align-items-stretch">
            @if($soldeTotalDisponible >= 1000)
                <button type="button" class="btn w-100" style="background: linear-gradient(135deg, #7B3FA0, #9c4db8); color: #fff; font-weight: 700; border-radius: 12px; border: none; box-shadow: 0 4px 16px rgba(123,63,160,0.3); min-height:100%;" data-bs-toggle="modal" data-bs-target="#retraitModal">
                    <i class="bi bi-send d-block mb-1" style="font-size:1.5rem;"></i>
                    <span style="font-size:0.9rem;">Demander un retrait</span>
                </button>
            @else
                <div class="metric-card w-100 text-center d-flex flex-column align-items-center justify-content-center" style="border-top-color: var(--gris);">
                    <i class="bi bi-lock d-block mb-1" style="font-size:1.5rem;color:var(--gris);"></i>
                    <span style="font-size:0.82rem;color:var(--gris);">Solde insuffisant</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Résumé global -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-label">Recettes mobile</div>
                <div class="metric-value" style="font-size:1.3rem;">{{ number_format($mobileRecettes, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Tous réseaux confondus</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card" style="border-top-color: var(--gris);">
                <div class="metric-label">Commission totale ({{ \App\Http\Controllers\RetraitController::COMMISSION_PERCENTAGE }}%)</div>
                <div class="metric-value" style="font-size:1.3rem;">{{ number_format($commissionTotale, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Inclut la part espèces répartie</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-label">Solde total retirable</div>
                <div class="metric-value" style="font-size:1.3rem;color:var(--vert);">{{ number_format($soldeTotalDisponible, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Après commission et retraits</div>
            </div>
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
                    <form action="{{ route('admin.retraits.store') }}" method="POST" id="formRetrait" autocomplete="off">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Réseau <span class="text-danger">*</span></label>
                            <select name="reseau" id="reseauSelect" class="form-select" required autocomplete="off">
                                <option value="">Choisir un réseau —</option>
                                @foreach($soldes as $key => $s)
                                    @if($s['solde'] > 0)
                                    <option value="{{ $key }}" data-solde="{{ $s['solde'] }}" data-label="{{ $s['label'] }}">
                                        {{ $s['label'] }} — {{ number_format($s['solde'], 0, ',', ' ') }} F disponible(s)
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div id="soldeInfo" class="mb-3" style="display:none;">
                            <div class="alert alert-success py-2 px-3 mb-0" style="font-size:0.85rem;border-radius:8px;">
                                <i class="bi bi-wallet2 me-1"></i> Solde disponible : <strong id="soldeAffiche">0</strong> FCFA
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Montant à retirer <span class="text-danger">*</span></label>
                            <input type="number" name="montant" id="montantInput" class="form-control" min="1000" step="100" placeholder="Ex: 50000" required disabled autocomplete="off">
                            <small class="text-muted">Min 1 000 FCFA · Max <span id="maxLabel">0</span> FCFA</small>
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
                        <button type="submit" id="btnSubmit" class="btn w-100 py-2" style="background: linear-gradient(135deg, #7B3FA0, #9c4db8); color: #fff; font-weight:700; border-radius:10px; border:none;" disabled>
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

    const reseauSelect = document.getElementById('reseauSelect');
    const montantInput = document.getElementById('montantInput');
    const soldeInfo = document.getElementById('soldeInfo');
    const soldeAffiche = document.getElementById('soldeAffiche');
    const maxLabel = document.getElementById('maxLabel');
    const btnSubmit = document.getElementById('btnSubmit');

    if (!reseauSelect) return;

    reseauSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (this.value && opt.dataset.solde) {
            const solde = parseFloat(opt.dataset.solde);
            soldeInfo.style.display = 'block';
            soldeAffiche.textContent = new Intl.NumberFormat('fr-FR').format(solde);
            maxLabel.textContent = new Intl.NumberFormat('fr-FR').format(solde);
            montantInput.max = solde;
            montantInput.disabled = false;
            montantInput.required = true;
            montantInput.focus();
        } else {
            soldeInfo.style.display = 'none';
            montantInput.disabled = true;
            montantInput.required = false;
            montantInput.value = '';
        }
        updateBtn();
    });

    montantInput.addEventListener('input', updateBtn);

    function updateBtn() {
        const reseauOk = reseauSelect.value !== '';
        const montantOk = montantInput.value && parseFloat(montantInput.value) >= 1000;
        btnSubmit.disabled = !(reseauOk && montantOk);
    }
});
</script>
@endsection
