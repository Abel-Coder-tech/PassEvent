<?php $__env->startSection('title', 'Modifier le tarif'); ?>

<?php $__env->startSection('page-title', 'Modifier le tarif'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Tableau de bord</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.evenements.index')); ?>">Événements</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.evenements.show', $evenement->id)); ?>"><?php echo e($evenement->titre); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.tarifs.index', $evenement->id)); ?>">Tarifs</a></li>
    <li class="breadcrumb-item active" aria-current="page">Modifier</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('topbar-actions'); ?>
<a href="<?php echo e(route('admin.tarifs.index', $evenement->id)); ?>" class="btn btn-secondary-custom">
    <i class="bi bi-arrow-left me-1"></i> Retour
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="panel-card" style="max-width: 600px;">
        <div class="panel-card-body p-3 p-md-4">
            <form action="<?php echo e(route('admin.tarifs.update', [$evenement->id, $tarif->id])); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="mb-3">
                    <label for="nom" class="form-label fw-semibold">Nom du tarif <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?php $__errorArgs = ['nom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="nom" name="nom" value="<?php echo e(old('nom', $tarif->nom)); ?>" maxlength="100" required>
                    <?php $__errorArgs = ['nom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <?php
                    $aVendu = $evenement->tarifs()->sum('quantite_vendue') > 0;
                ?>

                <div class="mb-3">
                    <label for="prix" class="form-label fw-semibold">Prix (FCFA)</label>
                    <input type="number" class="form-control <?php $__errorArgs = ['prix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="prix" name="prix" value="<?php echo e(old('prix', $tarif->prix)); ?>" step="0.01" min="0" <?php echo e($aVendu ? 'disabled readonly' : ''); ?>>
                    <?php $__errorArgs = ['prix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php if($aVendu): ?>
                        <small class="text-muted d-block mt-1">Des billets ont déjà été vendus : le prix ne peut pas être modifié directement. Utilisez le bouton « Demander une modification » ci-dessous, qui sera validé par PaxEvent.</small>
                    <?php else: ?>
                        <small class="text-muted">Aucun billet vendu : vous pouvez modifier librement le prix.</small>
                    <?php endif; ?>
                </div>

                <?php if($aVendu): ?>
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary-custom" onclick="document.getElementById('demandeModificationModal').style.display='flex'">
                            <i class="bi bi-send me-1"></i> Demander une modification
                        </button>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="quantite_disponible" class="form-label fw-semibold">Quantité disponible</label>
                    <input type="number" class="form-control <?php $__errorArgs = ['quantite_disponible'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="quantite_disponible" name="quantite_disponible" value="<?php echo e(old('quantite_disponible', $tarif->quantite_disponible)); ?>" min="1">
                    <small class="text-muted">Laissez vide pour illimité</small>
                    <?php $__errorArgs = ['quantite_disponible'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-md-end gap-2">
                    <a href="<?php echo e(route('admin.tarifs.index', $evenement->id)); ?>" class="btn btn-secondary-custom w-100 w-md-auto">Annuler</a>
                    <button type="submit" class="btn btn-primary-custom w-100 w-md-auto">
                        <i class="bi bi-check-lg me-1"></i> Enregistrer
                    </button>
                </div>
            </form>

            <?php if($aVendu): ?>
            <div id="demandeModificationModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
                <div class="modal-box">
                    <div class="modal-header">
                        <h5><i class="bi bi-send me-2" style="color:var(--violet);"></i>Demander une modification de prix</h5>
                        <button class="modal-close" onclick="document.getElementById('demandeModificationModal').style.display='none'">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p style="font-size:0.85rem;color:#6c757d;margin-bottom:1rem;">
                            Le tarif « <?php echo e($tarif->nom); ?> » a des billets vendus (prix actuel : <strong><?php echo e(number_format($tarif->prix, 0, ',', ' ')); ?> F</strong>).
                            Indiquez le nouveau prix souhaité : PaxEvent devra le valider.
                        </p>
                        <form action="<?php echo e(route('admin.tarifs.demande-modification', [$evenement->id, $tarif->id])); ?>" method="POST" id="demandeForm">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label for="nouveau_prix" class="form-label fw-semibold">Nouveau prix (FCFA) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control <?php $__errorArgs = ['nouveau_prix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="nouveau_prix" name="nouveau_prix" value="<?php echo e(old('nouveau_prix')); ?>" step="0.01" min="0" required>
                                <?php $__errorArgs = ['nouveau_prix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-custom" onclick="document.getElementById('demandeModificationModal').style.display='none'">Annuler</button>
                        <button type="submit" form="demandeForm" class="btn btn-primary-custom">
                            <i class="bi bi-send me-1"></i> Envoyer la demande
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
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
    gap: 0.5rem;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/tarifs/edit.blade.php ENDPATH**/ ?>