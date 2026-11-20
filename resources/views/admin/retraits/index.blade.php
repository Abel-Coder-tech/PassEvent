@extends('layouts.app')

@section('title', 'Retraits - Finances')
@section('page-title', 'Retraits')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Retraits</li>
@endsection

@section('styles')
<style>
    .split-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .split-row { display: grid; grid-template-columns: 150px 1fr 82px; gap: .7rem; align-items: center; margin-bottom: .9rem; font-size: .8rem; }
    .split-row:last-child { margin-bottom: 0; }
    .split-name { display: flex; align-items: center; gap: .45rem; font-weight: 700; min-width: 0; }
    .split-name > span { width: 9px; height: 9px; border-radius: 50%; background: var(--c); flex-shrink: 0; }
    .split-name > div { min-width: 0; }
    .split-name small { display: block; font-size: .66rem; color: var(--gris); font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .split-amount { text-align: right; font-weight: 800; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .op-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .6rem; }
    .op-tile { display: flex; align-items: center; gap: .7rem; padding: .7rem; border-radius: 11px; background: var(--gris-clair); min-width: 0; }
    .op-logo { width: 34px; height: 34px; border-radius: 9px; background: var(--info-pale); color: var(--info); display: grid; place-items: center; flex-shrink: 0; }
    .op-tile strong { display: block; font-size: .8rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .op-tile small { font-size: .7rem; color: var(--gris); }

    @media (max-width: 991.98px) {
        .split-grid { grid-template-columns: 1fr; gap: 1.25rem; }
    }
    @media (max-width: 575.98px) {
        .split-row { grid-template-columns: 120px 1fr 62px; gap: .45rem; }
        .split-name small { font-size: .62rem; }
        .op-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <!-- Cartes stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(123,63,160,0.1);"><i class="bi bi-phone" style="color: var(--violet);"></i></div>
                <div class="metric-label">Mobile Money (FedaPay)</div>
                <div class="metric-value" style="font-size:1.3rem;">{{ number_format($mobileRecettes, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Recettes totales</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--gris);">
                <div class="metric-icon" style="background: rgba(152,145,155,0.1);"><i class="bi bi-percent" style="color: var(--gris);"></i></div>
                <div class="metric-label">Commission ({{ $commissionPct }}%)</div>
                <div class="metric-value" style="font-size:1.3rem;">{{ number_format($commissionTotale, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">
                    Sur tous les tickets
                    @if($commissionAffiliation > 0)
                        <br>+ {{ number_format($commissionAffiliation, 0, ',', ' ') }} F affiliés
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);"><i class="bi bi-wallet2" style="color: var(--vert);"></i></div>
                <div class="metric-label">Solde disponible</div>
                <div class="metric-value" style="font-size:1.3rem; color: var(--vert);">{{ number_format($soldeDisponible, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">
                    Net ({{ number_format(max(0, $mobileRecettes - $commissionTotale - $commissionAffiliation), 0, ',', ' ') }})
                    - Retiré ({{ number_format($totalRetraits, 0, ',', ' ') }})
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 d-flex align-items-center justify-content-center">
            @if($soldeDisponible > 0)
                <button type="button" class="btn" style="background: linear-gradient(135deg, var(--violet-clair), var(--violet-clair)); color: #fff; font-weight: 700; padding: 0.85rem 1rem; border-radius: 12px; border: none; width: 100%; box-shadow: 0 4px 16px rgba(123,63,160,0.3); font-size: 0.82rem; white-space: normal;" data-bs-toggle="modal" data-bs-target="#retraitModal">
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
                    <h5 class="fw-bold" style="color: var(--sombre);"><i class="bi bi-send me-2" style="color: var(--violet);"></i> Demande de retrait</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p style="color: var(--gris-fonce); font-size: 0.85rem; margin-bottom: 1rem;">
                        Solde disponible : <strong style="color: var(--vert);">{{ number_format($soldeDisponible, 0, ',', ' ') }} FCFA</strong>
                    </p>
                    <form action="{{ route('admin.retraits.store') }}" method="POST" id="formRetrait" autocomplete="none">
                        @csrf
                        @if(!$numerosRetrait->count())
                            <div class="alert alert-warning d-flex align-items-start gap-2 py-3 px-3 mb-0" style="border-radius:10px; font-size:0.85rem;">
                                <i class="bi bi-exclamation-triangle me-1" style="color:var(--warning); font-size:1.1rem;"></i>
                                <div>
                                    <strong>Ajoutez d'abord un numéro de retrait.</strong>
                                    <p class="mb-0 mt-1 text-muted">Pour demander un retrait, enregistrez d'abord votre numéro Mobile Money dans vos <a href="{{ route('parametres.index') }}#paiement" style="color:var(--violet); font-weight:600;">paramètres (section Paiement)</a>.</p>
                                </div>
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label" style="font-size:0.82rem; font-weight:600;">Réseau cible <span class="text-danger">*</span></label>
                                <select name="reseau" id="reseauSelect" class="form-select" required autocomplete="one-time-code">
                                    <option value="">Choisir le réseau du retrait —</option>
                                    @foreach($numerosRetrait->unique('reseau') as $grpReseau)
                                    <option value="{{ $grpReseau->operateur }}">{{ \App\Http\Controllers\RetraitController::getLabelReseau($grpReseau->operateur) }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Réseau sur lequel vous souhaitez recevoir le montant</small>
                            </div>
                            <div class="mb-3" id="numeroSelectWrap" style="display:none;">
                                <label class="form-label" style="font-size:0.82rem; font-weight:600;">Numéro de retrait <span class="text-danger">*</span></label>
                                <select name="numero_id" id="numeroSelect" class="form-select" required autocomplete="one-time-code">
                                    <option value="">Choisir le numéro —</option>
                                </select>
                                <small class="text-muted">Sélectionnez le numéro enregistré à utiliser</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="font-size:0.82rem; font-weight:600;">Montant à retirer <span class="text-danger">*</span></label>
                                <input type="number" name="montant" class="form-control" min="1000" max="{{ $soldeDisponible }}" step="100" placeholder="Ex: 50000" required autocomplete="one-time-code">
                                <small class="text-muted">Min 1 000 FCFA · Max {{ number_format($soldeDisponible, 0, ',', ' ') }} FCFA</small>
                                @error('montant')<div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="font-size:0.82rem; font-weight:600;">Nom du bénéficiaire</label>
                                <input type="text" name="nom" id="beneficiaireNom" class="form-control" readonly style="background:var(--gris-clair); cursor:not-allowed;">
                                <small class="text-muted">Renseigné automatiquement depuis vos numéros enregistrés</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="font-size:0.82rem; font-weight:600;">Numéro Mobile Money</label>
                                <input type="text" name="mobile" id="beneficiaireMobile" class="form-control" readonly style="background:var(--gris-clair); cursor:not-allowed;">
                                <small class="text-muted">Renseigné automatiquement depuis vos numéros enregistrés</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="font-size:0.82rem; font-weight:600;">Confirmez votre mot de passe <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="retraitPassword" class="form-control @error('password') is-invalid @enderror" placeholder="Saisissez votre mot de passe" required autocomplete="new-password" style="border-radius:8px 0 0 8px;">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleRetraitPwd" style="border-color:#ede5f0; color:var(--gris-fonce);">
                                        <i class="bi bi-eye" id="retraitPwdIcon"></i>
                                    </button>
                                </div>
                                @error('password')<div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn w-100 py-2" style="background: linear-gradient(135deg, var(--violet-clair), var(--violet-clair)); color: #fff; font-weight:700; border-radius:10px; border:none;">
                                <i class="bi bi-send me-1"></i> Envoyer la demande
                            </button>
                        @endif
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
                    <h5 class="fw-bold mb-2" style="color:var(--sombre);">Demande envoyée !</h5>
                    <p style="color:var(--gris-fonce);font-size:0.9rem;margin-bottom:1rem;" id="successMessage"></p>
                    <div class="alert alert-warning py-2 px-3 mb-0" style="font-size:0.85rem;border-radius:8px;text-align:left;">
                        <i class="bi bi-info-circle me-1"></i> <strong>Rappel :</strong> Les retraits sont effectués dans un délai de <strong>0 à 72 heures</strong> après la demande. Vous recevrez une notification une fois votre retrait traité.
                    </div>
                    <button type="button" class="btn w-100 py-2 fw-bold text-white mt-3" style="background: var(--vert); border: none; border-radius: 8px;" data-bs-dismiss="modal">
                        Compris
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal erreur retrait -->
    <div class="modal fade" id="errorRetraitModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 14px; border: none;">
                <div class="modal-body text-center p-4">
                    <div style="width:64px;height:64px;background:rgba(231,76,60,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                        <i class="bi bi-x-circle" style="font-size:2rem;color:var(--danger);"></i>
                    </div>
                    <h5 class="fw-bold mb-2" style="color:var(--sombre);">Échec de la demande</h5>
                    <p style="color:var(--gris-fonce);font-size:0.9rem;margin-bottom:1rem;" id="errorMessage"></p>
                    <button type="button" class="btn w-100 py-2 fw-bold text-white mt-3" style="background: var(--danger); border: none; border-radius: 8px;" data-bs-dismiss="modal">
                        Réessayer
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Répartition des paiements : moyens + opérateurs mobiles --}}
    <div class="panel-card mb-4">
        <div class="panel-card-header">
            <h5><i class="bi bi-pie-chart" style="color:var(--violet);"></i> Répartition des paiements</h5>
            <span class="status-badge status-a-venir">{{ number_format($ticketsVendus, 0, ',', ' ') }} tickets</span>
        </div>
        <div class="panel-card-body split-grid">
            <div>
                <div class="section-title"><i class="bi bi-wallet2"></i> Moyen de paiement</div>
                @php
                    $moyensConfig = [
                        'mobile_money' => ['label' => 'Mobile money', 'icon' => 'bi-phone', 'c' => 'var(--info)'],
                        'bancaire' => ['label' => 'Carte bancaire', 'icon' => 'bi-credit-card', 'c' => 'var(--violet)'],
                        'especes' => ['label' => 'Espèces', 'icon' => 'bi-cash', 'c' => 'var(--warning)'],
                    ];
                    $totalMoyens = $moyensPaiement->sum('total');
                @endphp
                @foreach($moyensConfig as $mKey => $mCfg)
                    @php
                        $mDonnees = $moyensPaiement->get($mKey);
                        $mCount = $mDonnees ? (int) $mDonnees->total : 0;
                        $mMontant = $mDonnees ? (int) $mDonnees->montant : 0;
                        $mPct = $totalMoyens > 0 ? round(($mCount / $totalMoyens) * 100) : 0;
                    @endphp
                    <div class="split-row" style="--c:{{ $mCfg['c'] }};">
                        <div class="split-name"><span></span>
                            <div><i class="bi {{ $mCfg['icon'] }}" style="color:var(--c);"></i> {{ $mCfg['label'] }}
                                <small>{{ $mCount }} ticket{{ $mCount > 1 ? 's' : '' }} · {{ number_format($mMontant, 0, ',', ' ') }} F</small>
                            </div>
                        </div>
                        <div class="progress-bar-custom"><div class="progress-bar-fill" style="width:{{ $mPct }}%;background:var(--c);"></div></div>
                        <div class="split-amount">{{ $mPct }} %</div>
                    </div>
                @endforeach
            </div>
            <div>
                <div class="section-title"><i class="bi bi-phone"></i> Opérateurs mobiles</div>
                <div class="op-grid">
                    @foreach($reseauxConfig as $key => $cfg)
                        @php
                            $donnees = $reseauxPaiement->get($key);
                            $count = $donnees ? (int) $donnees->total : 0;
                            $montant = $donnees ? (int) $donnees->montant : 0;
                        @endphp
                        <div class="op-tile">
                            <div class="op-logo"><i class="bi bi-phone"></i></div>
                            <div>
                                <strong>{{ $cfg['label'] }}</strong>
                                <small>{{ $count }} ticket{{ $count > 1 ? 's' : '' }} · {{ number_format($montant, 0, ',', ' ') }} F</small>
                            </div>
                        </div>
                    @endforeach
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
                                            <span class="status-badge" style="background: rgba(214,137,16,0.12); color: var(--warning);">En attente</span>
                                        @elseif($retrait->status === 'en_cours')
                                            <span class="status-badge" style="background: rgba(47,128,184,0.12); color: var(--info);">En cours</span>
                                        @elseif($retrait->status === 'payé')
                                            <span class="status-badge" style="background: rgba(18,151,110,0.12); color: var(--vert);">Payé</span>
                                        @elseif($retrait->status === 'rejeté')
                                            <span class="status-badge" style="background: rgba(231,76,60,0.12); color: var(--danger);">Rejeté</span>
                                        @else
                                            <span class="status-badge" style="background: rgba(152,145,155,0.12); color: var(--gris);">{{ $retrait->status }}</span>
                                        @endif
                                    </td>
                                    <td style="max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--gris);">
                                        {{ $retrait->admin_notes ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:var(--gris-clair);">
                                <td colspan="2" class="text-end fw-bold" style="font-size:0.88rem;">Total</td>
                                <td><strong>{{ number_format($retraits->sum('montant'), 0, ',', ' ') }} F</strong></td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
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
    const toggleBtn = document.getElementById('toggleRetraitPwd');
    const pwdInput = document.getElementById('retraitPassword');
    const pwdIcon = document.getElementById('retraitPwdIcon');
    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', function() {
            const isPassword = pwdInput.type === 'password';
            pwdInput.type = isPassword ? 'text' : 'password';
            pwdIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }

    const form = document.getElementById('formRetrait');
    const retraitModal = document.getElementById('retraitModal');

    const numerosData = @json($numerosRetrait);
    const reseauSelect = document.getElementById('reseauSelect');
    const numeroSelect = document.getElementById('numeroSelect');
    const numeroSelectWrap = document.getElementById('numeroSelectWrap');
    const benefNom = document.getElementById('beneficiaireNom');
    const benefMobile = document.getElementById('beneficiaireMobile');

    function resetBeneficiaire() {
        if (benefNom) benefNom.value = '';
        if (benefMobile) benefMobile.value = '';
        if (numeroSelect) {
            numeroSelect.innerHTML = '<option value="">Choisir le numéro —</option>';
        }
        if (numeroSelectWrap) numeroSelectWrap.style.display = 'none';
    }

    if (reseauSelect) {
        reseauSelect.addEventListener('change', function() {
            resetBeneficiaire();
            const reseau = this.value;
            if (!reseau) return;
            const nums = numerosData.filter(n => n.reseau === reseau);
            if (nums.length === 0) return;
            nums.forEach(n => {
                const opt = document.createElement('option');
                opt.value = n.id;
                opt.dataset.nom = n.nom;
                opt.dataset.mobile = n.mobile;
                opt.textContent = n.nom + ' — ' + n.mobile;
                numeroSelect.appendChild(opt);
            });
            numeroSelectWrap.style.display = '';
            if (nums.length === 1) {
                numeroSelect.value = nums[0].id;
                numeroSelect.dispatchEvent(new Event('change'));
            }
        });

        numeroSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            benefNom.value = opt ? (opt.dataset.nom || '') : '';
            benefMobile.value = opt ? (opt.dataset.mobile || '') : '';
        });
    }

    retraitModal.addEventListener('show.bs.modal', function() {
        setTimeout(function() {
            form.reset();
            if (reseauSelect) reseauSelect.value = '';
            resetBeneficiaire();
            document.getElementById('retraitPassword').value = '';
            document.getElementById('retraitPassword').type = 'password';
            document.getElementById('retraitPwdIcon').className = 'bi bi-eye';
        }, 100);
    });
    const successModal = document.getElementById('successRetraitModal');
    const errorModal = document.getElementById('errorRetraitModal');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const btn = form.querySelector('button[type="submit"]');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Envoi en cours…';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: JSON.stringify(Object.fromEntries(new FormData(form))),
        })
        .then(r => r.json().then(body => ({ ok: r.ok, body })))
        .then(({ ok, body }) => {
            const bsRetrait = bootstrap.Modal.getInstance(retraitModal);
            if (bsRetrait) bsRetrait.hide();

            if (body.success) {
                document.getElementById('successMessage').textContent = body.message;
                const bsSuccess = new bootstrap.Modal(successModal);
                bsSuccess.show();
                form.reset();
            } else {
                document.getElementById('errorMessage').textContent = body.message || 'Une erreur est survenue.';
                const bsError = new bootstrap.Modal(errorModal);
                bsError.show();
            }
        })
        .catch(() => {
            const bsRetrait = bootstrap.Modal.getInstance(retraitModal);
            if (bsRetrait) bsRetrait.hide();
            document.getElementById('errorMessage').textContent = 'Erreur réseau. Veuillez réessayer.';
            const bsError = new bootstrap.Modal(errorModal);
            bsError.show();
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = origText;
        });
    });
});
</script>
@endsection
