<?php $__env->startSection('title', 'Créer un événement'); ?>

<?php $__env->startSection('page-title', 'Créer un événement'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.evenements.index')); ?>">Événements</a></li>
    <li class="breadcrumb-item active" aria-current="page">Créer</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('topbar-actions'); ?>
<a href="<?php echo e(route('admin.evenements.index')); ?>" class="btn btn-secondary-custom">
    <i class="bi bi-arrow-left me-1"></i> Retour
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="panel-card" style="max-width: 700px;">
        <div class="panel-card-body p-3 p-md-4">
            <form action="<?php echo e(route('admin.evenements.store')); ?>" method="POST" enctype="multipart/form-data" novalidate>
                <?php echo csrf_field(); ?>

                <div class="mb-3">
                    <label for="titre" class="form-label fw-semibold">Titre de l'événement <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?php $__errorArgs = ['titre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="titre" name="titre" value="<?php echo e(old('titre')); ?>" required>
                    <?php $__errorArgs = ['titre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="description" name="description" rows="3"><?php echo e(old('description')); ?></textarea>
                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="date_event" class="form-label fw-semibold">Date et heure <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control <?php $__errorArgs = ['date_event'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="date_event" name="date_event" value="<?php echo e(old('date_event')); ?>" required>
                        <?php $__errorArgs = ['date_event'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="lieu" class="form-label fw-semibold">Lieu <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php $__errorArgs = ['lieu'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="lieu" name="lieu" value="<?php echo e(old('lieu')); ?>" required>
                        <?php $__errorArgs = ['lieu'];
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
                    <label for="categorie" class="form-label fw-semibold">Categorie <span class="text-danger">*</span></label>
                    <select class="form-select <?php $__errorArgs = ['categorie'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="categorie" name="categorie" required>
                        <option value="">Selectionner une categorie</option>
                        <option value="Sport" <?php echo e(old('categorie') == 'Sport' ? 'selected' : ''); ?>>Sport</option>
                        <option value="Soiree gala" <?php echo e(old('categorie') == 'Soiree gala' ? 'selected' : ''); ?>>Soiree gala</option>
                        <option value="Ceremonie officielle" <?php echo e(old('categorie') == 'Ceremonie officielle' ? 'selected' : ''); ?>>Ceremonie officielle</option>
                        <option value="Webinaire" <?php echo e(old('categorie') == 'Webinaire' ? 'selected' : ''); ?>>Webinaire</option>
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

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="capacite" class="form-label fw-semibold">Capacité <span class="text-danger">*</span></label>
                        <input type="number" class="form-control <?php $__errorArgs = ['capacite'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="capacite" name="capacite" value="<?php echo e(old('capacite')); ?>" min="1" required>
                        <?php $__errorArgs = ['capacite'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="date_fin_vente" class="form-label fw-semibold">Date fin de vente</label>
                        <input type="datetime-local" class="form-control <?php $__errorArgs = ['date_fin_vente'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="date_fin_vente" name="date_fin_vente" value="<?php echo e(old('date_fin_vente')); ?>">
                        <?php $__errorArgs = ['date_fin_vente'];
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
                    <label for="image" class="form-label fw-semibold">Image d'illustration</label>
                    <input type="file" class="form-control <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="image" name="image" accept="image/*">
                    <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-3">
                    <label for="statut" class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
                    <select class="form-select <?php $__errorArgs = ['statut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="statut" name="statut" required>
                        <option value="brouillon" <?php echo e(old('statut') == 'brouillon' ? 'selected' : ''); ?>>Brouillon</option>
                        <option value="publié" <?php echo e(old('statut') == 'publié' ? 'selected' : ''); ?>>Publié</option>
                        <option value="terminé" <?php echo e(old('statut') == 'terminé' ? 'selected' : ''); ?>>Terminé</option>
                        <option value="annulé" <?php echo e(old('statut') == 'annulé' ? 'selected' : ''); ?>>Annulé</option>
                    </select>
                    <?php $__errorArgs = ['statut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="panel-card mt-4 mb-4" style="border-left: 3px solid var(--violet);">
                    <div class="panel-card-body p-3">
                        <h6 class="fw-bold mb-3" style="color: var(--violet);">
                            <i class="bi bi-cash-coin me-2"></i>Tarification
                        </h6>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="gratuit" name="gratuit" value="1" <?php echo e(old('gratuit') ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="gratuit">
                                <strong>Evenement gratuit</strong>
                                <small class="text-muted d-block">Les billets seront gratuits pour tous les participants (aucun paiement requis)</small>
                            </label>
                        </div>

                        <div id="pricing-fields">
                            <div class="mb-3">
                                <label for="prix_base" class="form-label fw-semibold">Prix de base (Externe Simple) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control <?php $__errorArgs = ['prix_base'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="prix_base" name="prix_base" value="<?php echo e(old('prix_base')); ?>" min="0" step="100" placeholder="Ex: 10000" required>
                                <small class="text-muted">Prix du billet externe simple</small>
                                <?php $__errorArgs = ['prix_base'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="multiplicateur_vip" class="form-label fw-semibold">Multiplicateur VIP</label>
                                    <select class="form-select" id="multiplicateur_vip" name="multiplicateur_vip">
                                        <option value="1.5" <?php echo e(old('multiplicateur_vip') == '1.5' ? 'selected' : ''); ?>>x1.5 (50% plus cher)</option>
                                        <option value="2" <?php echo e(old('multiplicateur_vip') == '2' ? 'selected' : ''); ?>>x2 (2x plus cher)</option>
                                    </select>
                                    <small class="text-muted">Les billets VIP seront calculés automatiquement</small>
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="reduction_etudiant" class="form-label fw-semibold">Réduction étudiant (%)</label>
                                    <input type="number" class="form-control" id="reduction_etudiant" name="reduction_etudiant" value="<?php echo e(old('reduction_etudiant', 30)); ?>" min="0" max="100">
                                    <small class="text-muted">Les étudiants bénéficient d'une réduction sur tous les tarifs</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert" style="background: rgba(135,66,139,0.08); border: 1px solid rgba(135,66,139,0.2); border-radius: 8px; padding: 0.75rem 1rem;">
                            <small class="text-muted">
                                <strong>Aperçu des tarifs :</strong><br>
                                <span id="preview-tarifs">Configurez les paramètres pour voir l'aperçu</span>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-md-end gap-2">
                    <a href="<?php echo e(route('admin.evenements.index')); ?>" class="btn btn-secondary-custom w-100 w-md-auto">Annuler</a>
                    <button type="submit" class="btn btn-primary-custom w-100 w-md-auto">
                        <i class="bi bi-check-lg me-1"></i> Créer l'événement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function updatePreview() {
    const gratuit = document.getElementById('gratuit')?.checked;
    if (gratuit) {
        document.getElementById('preview-tarifs').innerHTML =
            '<strong>Étudiant Simple:</strong> Gratuit<br>' +
            '<strong>Étudiant VIP:</strong> Gratuit<br>' +
            '<strong>Externe Simple:</strong> Gratuit<br>' +
            '<strong>Externe VIP:</strong> Gratuit';
        return;
    }

    const base = parseFloat(document.getElementById('prix_base')?.value || 0);
    const mult = parseFloat(document.getElementById('multiplicateur_vip')?.value || 1.5);
    const reduc = parseFloat(document.getElementById('reduction_etudiant')?.value || 0) / 100;

    if (base <= 0) {
        document.getElementById('preview-tarifs').textContent = 'Entrez un prix de base pour voir l\'aperçu';
        return;
    }

    const extSimple = base;
    const extVip = base * mult;
    const etuSimple = base * (1 - reduc);
    const etuVip = base * mult * (1 - reduc);

    document.getElementById('preview-tarifs').innerHTML = 
        '<strong>Étudiant Simple:</strong> ' + formatPrice(etuSimple) + '<br>' +
        '<strong>Étudiant VIP:</strong> ' + formatPrice(etuVip) + '<br>' +
        '<strong>Externe Simple:</strong> ' + formatPrice(extSimple) + '<br>' +
        '<strong>Externe VIP:</strong> ' + formatPrice(extVip);
}

function formatPrice(price) {
    return Math.round(price).toLocaleString('fr-FR') + ' F';
}

function toggleGratuit() {
    const checked = document.getElementById('gratuit')?.checked;
    const fields = document.getElementById('pricing-fields');
    fields.style.display = checked ? 'none' : 'block';
    updatePreview();
}

document.getElementById('gratuit')?.addEventListener('change', toggleGratuit);
document.getElementById('prix_base')?.addEventListener('input', updatePreview);
document.getElementById('multiplicateur_vip')?.addEventListener('change', updatePreview);
document.getElementById('reduction_etudiant')?.addEventListener('input', updatePreview);
toggleGratuit();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views/evenements/create.blade.php ENDPATH**/ ?>