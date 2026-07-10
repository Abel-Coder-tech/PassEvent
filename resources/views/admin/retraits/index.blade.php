@extends('layouts.app')

@section('title', 'Retraits - Finances')
@section('page-title', 'Retraits')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Retraits</li>
@endsection

@section('content')
<div class="page-content">

    {{-- Section 1: Revenus par opérateur --}}
    <div class="panel-card mb-4">
        <div class="panel-card-header">
            <h5><i class="bi bi-phone me-1" style="color:#3498db;"></i> Revenus par opérateur</h5>
        </div>
        <div class="panel-card-body p-0">
            <div class="table-responsive">
                <table class="custom-table table mb-0">
                    <thead>
                        <tr>
                            <th>Opérateur</th>
                            <th class="text-end">Tickets</th>
                            <th class="text-end">Brut</th>
                            <th class="text-end">Commission 10%</th>
                            <th class="text-end">Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reseaux as $key => $reseau)
                            <tr>
                                <td><strong>{{ $reseau['label'] }}</strong></td>
                                <td class="text-end">{{ $reseau['count'] }}</td>
                                <td class="text-end">{{ number_format($reseau['brut'], 0, ',', ' ') }} F</td>
                                <td class="text-end" style="color:var(--danger);">− {{ number_format($reseau['brut'] * 10 / 100, 0, ',', ' ') }} F</td>
                                <td class="text-end fw-bold" style="color:var(--vert);">{{ number_format($reseau['net'], 0, ',', ' ') }} F</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Aucune vente mobile</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot style="background:#f8f6f9;">
                        <tr>
                            <th>Total Mobile</th>
                            <th class="text-end">{{ collect($reseaux)->sum('count') }}</th>
                            <th class="text-end">{{ number_format(collect($reseaux)->sum('brut'), 0, ',', ' ') }} F</th>
                            <th class="text-end" style="color:var(--danger);">− {{ number_format(collect($reseaux)->sum('brut') * 10 / 100, 0, ',', ' ') }} F</th>
                            <th class="text-end" style="color:var(--vert);">{{ number_format($totalMobileNet, 0, ',', ' ') }} F</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Ventes espèces + Net retirable --}}
    <div class="row g-3 mb-4">
        <div class="col-md-5">
            <div class="metric-card h-100" style="border-top-color: #f39c12;">
                <div class="metric-icon" style="background: rgba(243,156,18,0.1);"><i class="bi bi-cash" style="color:#f39c12;"></i></div>
                <div class="metric-label">Ventes espèces</div>
                <div class="metric-value" style="font-size:1.3rem;color:#f39c12;">{{ number_format($cashRecettes, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">
                    Commission due :
                    <strong style="color:var(--danger);">{{ number_format($commissionEspeces, 0, ',', ' ') }} F</strong>
                    &middot;
                    @if($commissionEspecesSoldee)
                        <span style="color:var(--vert);"><i class="bi bi-check-circle-fill"></i> Soldée</span>
                    @else
                        <span style="color:#f39c12;"><i class="bi bi-clock-fill"></i> Non soldée</span>
                    @endif
                </div>
                <div style="font-size:0.75rem;color:var(--gris);margin-top:0.25rem;">
                    <i class="bi bi-info-circle me-1"></i> Non retirable via FedaPay
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card h-100" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);"><i class="bi bi-wallet2" style="color:var(--vert);"></i></div>
                <div class="metric-label">Net retirable global</div>
                <div class="metric-value" style="font-size:1.5rem;color:var(--vert);">{{ number_format($netRetirableGlobal, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">
                    Net mobile ({{ number_format($totalMobileNet, 0, ',', ' ') }})
                    − Commission espèces ({{ number_format($commissionEspeces, 0, ',', ' ') }})
                    @if($totalDejaRetire > 0)
                        − Déjà retiré ({{ number_format($totalDejaRetire, 0, ',', ' ') }})
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3 d-flex align-items-center justify-content-center">
            @if($netRetirableGlobal >= 100)
                <button type="button" class="btn w-100" style="background: linear-gradient(135deg, #7B3FA0, #9c4db8); color: #fff; font-weight: 700; padding: 0.85rem 1.5rem; border-radius: 12px; border: none; box-shadow: 0 4px 16px rgba(123,63,160,0.3);" data-bs-toggle="modal" data-bs-target="#retraitModal">
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

    {{-- Section 2: Formulaire de retrait --}}
    <div class="modal fade" id="retraitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 14px; border: none;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold" style="color: #1a1a2e;"><i class="bi bi-send me-2" style="color: var(--violet);"></i> Demande de retrait</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p style="color: #6c757d; font-size: 0.85rem; margin-bottom: 1rem;">
                        Net retirable : <strong style="color: var(--vert);">{{ number_format($netRetirableGlobal, 0, ',', ' ') }} FCFA</strong>
                    </p>

                    <form action="{{ route('admin.retraits.store') }}" method="POST" id="retraitForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Opérateur de réception <span class="text-danger">*</span></label>
                            <select name="operateur" class="form-select" id="operateurSelect" required>
                                <option value="">— Choisir un opérateur —</option>
                                @foreach(\App\Http\Controllers\RetraitController::getOperateurs() as $key => $label)
                                    <option value="{{ $key }}" data-net="{{ number_format(($reseaux[$key]['net'] ?? 0), 0, ',', ' ') }}" data-brut="{{ $reseaux[$key]['brut'] ?? 0 }}" data-net-raw="{{ $reseaux[$key]['net'] ?? 0 }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="operateurInfos" class="mb-3" style="display:none;">
                            <div class="p-3 rounded-3" style="background:#f8f6f9;">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Disponible sur cet opérateur</span>
                                    <strong class="op-net"></strong>
                                </div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Commission espèces à déduire</span>
                                    <strong style="color:var(--danger);">− {{ number_format($commissionEspeces, 0, ',', ' ') }} F</strong>
                                </div>
                                <hr style="margin:0.4rem 0;">
                                <div class="d-flex justify-content-between small fw-bold">
                                    <span>Net effectif (sur cet opérateur)</span>
                                    <span class="op-net-effectif" style="color:var(--vert);"></span>
                                </div>
                            </div>
                            <div class="mt-2" style="font-size:0.78rem;color:#856404;background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:0.4rem 0.7rem;">
                                <i class="bi bi-info-circle me-1"></i>
                                La commission espèces ({{ number_format($commissionEspeces, 0, ',', ' ') }} F) sera déduite intégralement sur l'opérateur choisi avant traitement.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Montant à retirer <span class="text-danger">*</span></label>
                            <input type="number" name="montant" id="montantInput" class="form-control" min="100" max="{{ $netRetirableGlobal }}" step="100" placeholder="Ex: 50000" required>
                            <small class="text-muted">Min 100 FCFA · Max <span id="maxLabel">{{ number_format($netRetirableGlobal, 0, ',', ' ') }}</span> FCFA</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Nom du bénéficiaire <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control" placeholder="Ex: Kofi Mensah" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Numéro Mobile Money <span class="text-danger">*</span></label>
                            <input type="text" name="mobile" class="form-control" placeholder="Ex: +229 XX XX XX XX" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Confirmez votre mot de passe <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Votre mot de passe actuel" required>
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

    {{-- Section 3: Historique --}}
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
                                <th>Opérateur</th>
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
                                        @if($retrait->operateur && isset(\App\Http\Controllers\RetraitController::getOperateurs()[$retrait->operateur]))
                                            {{ \App\Http\Controllers\RetraitController::getOperateurs()[$retrait->operateur] }}
                                        @else
                                            —
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
        <div class="mt-3">{{ $retraits->links() }}</div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const operateurSelect = document.getElementById('operateurSelect');
    const operateurInfos = document.getElementById('operateurInfos');
    const montantInput = document.getElementById('montantInput');
    const maxLabel = document.getElementById('maxLabel');
    const commissionEspeces = {{ $commissionEspeces }};

    if (operateurSelect) {
        operateurSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const netRaw = parseFloat(selected.dataset.netRaw) || 0;
            const netEffectif = Math.max(0, netRaw - commissionEspeces);

            if (this.value && netRaw > 0) {
                operateurInfos.style.display = 'block';
                operateurInfos.querySelector('.op-net').textContent = selected.dataset.net + ' F';
                operateurInfos.querySelector('.op-net-effectif').textContent = numberFormat(netEffectif) + ' F';

                const newMax = Math.min({{ $netRetirableGlobal }}, netEffectif);
                montantInput.max = newMax;
                maxLabel.textContent = numberFormat(newMax);
                if (parseFloat(montantInput.value) > newMax) {
                    montantInput.value = '';
                }
            } else {
                operateurInfos.style.display = 'none';
                montantInput.max = {{ $netRetirableGlobal }};
                maxLabel.textContent = '{{ number_format($netRetirableGlobal, 0, ",", " ") }}';
            }
        });
    }

    function numberFormat(n) {
        return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }
});
</script>
@endsection
