<?php $__env->startSection('title', 'Retraits - Finances'); ?>
<?php $__env->startSection('page-title', 'Retraits'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active" aria-current="page">Retraits</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <!-- Cartes stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.1);"><i class="bi bi-phone" style="color: var(--violet);"></i></div>
                <div class="metric-label">Mobile Money (FedaPay)</div>
                <div class="metric-value" style="font-size:1.3rem;"><?php echo e(number_format($mobileRecettes, 0, ',', ' ')); ?> F</div>
                <div class="metric-subtitle">Recettes totales</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--gris);">
                <div class="metric-icon" style="background: rgba(152,145,155,0.1);"><i class="bi bi-percent" style="color: var(--gris);"></i></div>
                <div class="metric-label">Commission (<?php echo e($commissionPct); ?>%)</div>
                <div class="metric-value" style="font-size:1.3rem;"><?php echo e(number_format($commissionTotale, 0, ',', ' ')); ?> F</div>
                <div class="metric-subtitle">Sur tous les tickets</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);"><i class="bi bi-wallet2" style="color: var(--vert);"></i></div>
                <div class="metric-label">Solde disponible</div>
                <div class="metric-value" style="font-size:1.3rem; color: var(--vert);"><?php echo e(number_format($soldeDisponible, 0, ',', ' ')); ?> F</div>
                <div class="metric-subtitle">
                    Net (<?php echo e(number_format(max(0, $mobileRecettes - $commissionTotale), 0, ',', ' ')); ?>)
                    - Retiré (<?php echo e(number_format($totalRetraits, 0, ',', ' ')); ?>)
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 d-flex align-items-center justify-content-center">
            <?php if($soldeDisponible > 0): ?>
                <button type="button" class="btn" style="background: linear-gradient(135deg, #7B3FA0, #9c4db8); color: #fff; font-weight: 700; padding: 0.85rem 1rem; border-radius: 12px; border: none; width: 100%; box-shadow: 0 4px 16px rgba(123,63,160,0.3); font-size: 0.82rem; white-space: normal;" data-bs-toggle="modal" data-bs-target="#retraitModal">
                    <i class="bi bi-send me-1"></i> Demander un retrait
                </button>
            <?php else: ?>
                <div class="text-center" style="color: var(--gris); font-size: 0.82rem;">
                    <i class="bi bi-lock d-block mb-1" style="font-size:1.5rem;"></i>
                    Solde insuffisant
                </div>
            <?php endif; ?>
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
                        Solde disponible : <strong style="color: var(--vert);"><?php echo e(number_format($soldeDisponible, 0, ',', ' ')); ?> FCFA</strong>
                    </p>
                    <form action="<?php echo e(route('admin.retraits.store')); ?>" method="POST" id="formRetrait" autocomplete="none">
                        <?php echo csrf_field(); ?>
                        <?php if(!$numerosRetrait->count()): ?>
                            <div class="alert alert-warning d-flex align-items-start gap-2 py-3 px-3 mb-0" style="border-radius:10px; font-size:0.85rem;">
                                <i class="bi bi-exclamation-triangle me-1" style="color:#f39c12; font-size:1.1rem;"></i>
                                <div>
                                    <strong>Ajoutez d'abord un numéro de retrait.</strong>
                                    <p class="mb-0 mt-1 text-muted">Pour demander un retrait, enregistrez d'abord votre numéro Mobile Money dans vos <a href="<?php echo e(route('parametres.index')); ?>#paiement" style="color:var(--violet); font-weight:600;">paramètres (section Paiement)</a>.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <label class="form-label" style="font-size:0.82rem; font-weight:600;">Réseau cible <span class="text-danger">*</span></label>
                                <select name="reseau" id="reseauSelect" class="form-select" required autocomplete="one-time-code">
                                    <option value="">Choisir le réseau du retrait —</option>
                                    <?php $__currentLoopData = $numerosRetrait->unique('reseau'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grpReseau): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($grpReseau->operateur); ?>"><?php echo e(\App\Http\Controllers\RetraitController::getLabelReseau($grpReseau->operateur)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                <input type="number" name="montant" class="form-control" min="1000" max="<?php echo e($soldeDisponible); ?>" step="100" placeholder="Ex: 50000" required autocomplete="one-time-code">
                                <small class="text-muted">Min 1 000 FCFA · Max <?php echo e(number_format($soldeDisponible, 0, ',', ' ')); ?> FCFA</small>
                                <?php $__errorArgs = ['montant'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger mt-1" style="font-size:0.78rem;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="font-size:0.82rem; font-weight:600;">Nom du bénéficiaire</label>
                                <input type="text" name="nom" id="beneficiaireNom" class="form-control" readonly style="background:#f5f2f6; cursor:not-allowed;">
                                <small class="text-muted">Renseigné automatiquement depuis vos numéros enregistrés</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="font-size:0.82rem; font-weight:600;">Numéro Mobile Money</label>
                                <input type="text" name="mobile" id="beneficiaireMobile" class="form-control" readonly style="background:#f5f2f6; cursor:not-allowed;">
                                <small class="text-muted">Renseigné automatiquement depuis vos numéros enregistrés</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="font-size:0.82rem; font-weight:600;">Confirmez votre mot de passe <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="retraitPassword" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Saisissez votre mot de passe" required autocomplete="new-password" style="border-radius:8px 0 0 8px;">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleRetraitPwd" style="border-color:#ede5f0; color:#6c757d;">
                                        <i class="bi bi-eye" id="retraitPwdIcon"></i>
                                    </button>
                                </div>
                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger mt-1" style="font-size:0.78rem;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <button type="submit" class="btn w-100 py-2" style="background: linear-gradient(135deg, #7B3FA0, #9c4db8); color: #fff; font-weight:700; border-radius:10px; border:none;">
                                <i class="bi bi-send me-1"></i> Envoyer la demande
                            </button>
                        <?php endif; ?>
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
                    <p style="color:#6c757d;font-size:0.9rem;margin-bottom:1rem;" id="successMessage"></p>
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
                        <i class="bi bi-x-circle" style="font-size:2rem;color:#e74c3c;"></i>
                    </div>
                    <h5 class="fw-bold mb-2" style="color:#1a1a2e;">Échec de la demande</h5>
                    <p style="color:#6c757d;font-size:0.9rem;margin-bottom:1rem;" id="errorMessage"></p>
                    <button type="button" class="btn w-100 py-2 fw-bold text-white mt-3" style="background: #e74c3c; border: none; border-radius: 8px;" data-bs-dismiss="modal">
                        Réessayer
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
            <?php if($retraits->count() > 0): ?>
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
                            <?php $__currentLoopData = $retraits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $retrait): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td style="white-space:nowrap;"><?php echo e($retrait->created_at->isoFormat('D MMM YYYY HH:mm')); ?></td>
                                    <td>
                                        <?php if($retrait->reseau && isset(\App\Http\Controllers\RetraitController::RESEAUX_CONFIG[$retrait->reseau])): ?>
                                            <span style="font-weight:600;"><?php echo e(\App\Http\Controllers\RetraitController::RESEAUX_CONFIG[$retrait->reseau]['label']); ?></span>
                                        <?php else: ?>
                                            <span style="color:var(--gris);">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo e(number_format($retrait->montant, 0, ',', ' ')); ?> F</strong></td>
                                    <td><?php echo e($retrait->nom); ?></td>
                                    <td><?php echo e($retrait->mobile); ?></td>
                                    <td>
                                        <?php if($retrait->status === 'en_attente'): ?>
                                            <span class="status-badge" style="background: rgba(243,156,18,0.12); color: #f39c12;">En attente</span>
                                        <?php elseif($retrait->status === 'en_cours'): ?>
                                            <span class="status-badge" style="background: rgba(52,152,219,0.12); color: #3498db;">En cours</span>
                                        <?php elseif($retrait->status === 'payé'): ?>
                                            <span class="status-badge" style="background: rgba(18,151,110,0.12); color: var(--vert);">Payé</span>
                                        <?php elseif($retrait->status === 'rejeté'): ?>
                                            <span class="status-badge" style="background: rgba(231,76,60,0.12); color: #e74c3c;">Rejeté</span>
                                        <?php else: ?>
                                            <span class="status-badge" style="background: rgba(152,145,155,0.12); color: var(--gris);"><?php echo e($retrait->status); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--gris);">
                                        <?php echo e($retrait->admin_notes ?? '-'); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                        <tfoot>
                            <tr style="background:#f8f6f9;">
                                <td colspan="2" class="text-end fw-bold" style="font-size:0.88rem;">Total</td>
                                <td><strong><?php echo e(number_format($retraits->sum('montant'), 0, ',', ' ')); ?> F</strong></td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5" style="color: var(--gris);">
                    <i class="bi bi-inbox d-block mb-2" style="font-size: 2.5rem;"></i>
                    <p>Aucune demande de retrait pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if($retraits->hasPages()): ?>
        <div class="mt-3 pagination-wrap"><?php echo e($retraits->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
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

    const numerosData = <?php echo json_encode($numerosRetrait, 15, 512) ?>;
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/admin/retraits/index.blade.php ENDPATH**/ ?>