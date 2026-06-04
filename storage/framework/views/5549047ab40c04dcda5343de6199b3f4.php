<?php $__env->startSection('title', 'Ajouter un tarif'); ?>

<?php $__env->startSection('page-title', 'Ajouter un tarif — ' . $evenement->titre); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.evenements.index')); ?>">Événements</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.evenements.show', $evenement->id)); ?>"><?php echo e($evenement->titre); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.tarifs.index', $evenement->id)); ?>">Tarifs</a></li>
    <li class="breadcrumb-item active" aria-current="page">Ajouter</li>
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
            <form action="<?php echo e(route('admin.tarifs.store', $evenement->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="categorie" class="form-label fw-semibold">Catégorie <span class="text-danger">*</span></label>
                        <select class="form-select <?php $__errorArgs = ['categorie'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="categorie" name="categorie" required>
                            <option value="etudiant" <?php echo e(old('categorie') == 'etudiant' ? 'selected' : ''); ?>>Étudiant</option>
                            <option value="externe" <?php echo e(old('categorie') == 'externe' ? 'selected' : ''); ?>>Externe</option>
                        </select>
                        <?php $__errorArgs = ['categorie'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                        <select class="form-select <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="type" name="type" required>
                            <option value="normal" <?php echo e(old('type') == 'normal' ? 'selected' : ''); ?>>Normal</option>
                            <option value="vip" <?php echo e(old('type') == 'vip' ? 'selected' : ''); ?>>VIP</option>
                        </select>
                        <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="prix" class="form-label fw-semibold">Prix (FCFA) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control <?php $__errorArgs = ['prix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="prix" name="prix" value="<?php echo e(old('prix')); ?>" step="0.01" min="0" required>
                    <?php $__errorArgs = ['prix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-3">
                    <label for="quantite_disponible" class="form-label fw-semibold">Quantité disponible</label>
                    <input type="number" class="form-control <?php $__errorArgs = ['quantite_disponible'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="quantite_disponible" name="quantite_disponible" value="<?php echo e(old('quantite_disponible')); ?>" min="1">
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
                        <i class="bi bi-check-lg me-1"></i> Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\tarifs\create.blade.php ENDPATH**/ ?>