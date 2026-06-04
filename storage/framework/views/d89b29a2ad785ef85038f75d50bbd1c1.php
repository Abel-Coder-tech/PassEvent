<?php $__env->startSection('title', 'Modifier l\'événement'); ?>

<?php $__env->startSection('page-title', 'Modifier l\'événement'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.evenements.index')); ?>">Événements</a></li>
    <li class="breadcrumb-item active" aria-current="page">Modifier</li>
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
            <form action="<?php echo e(route('admin.evenements.update', $evenement->id)); ?>" method="POST" enctype="multipart/form-data" novalidate>
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="mb-3">
                    <label for="titre" class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?php $__errorArgs = ['titre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="titre" name="titre" value="<?php echo e(old('titre', $evenement->titre)); ?>" required>
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
unset($__errorArgs, $__bag); ?>" id="description" name="description" rows="3"><?php echo e(old('description', $evenement->description)); ?></textarea>
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
unset($__errorArgs, $__bag); ?>" id="date_event" name="date_event" value="<?php echo e(old('date_event', $evenement->date_event->format('Y-m-d\TH:i'))); ?>" required>
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
unset($__errorArgs, $__bag); ?>" id="lieu" name="lieu" value="<?php echo e(old('lieu', $evenement->lieu)); ?>" required>
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
                        <option value="Sport" <?php echo e(old('categorie', $evenement->categorie) == 'Sport' ? 'selected' : ''); ?>>Sport</option>
                        <option value="Soiree gala" <?php echo e(old('categorie', $evenement->categorie) == 'Soiree gala' ? 'selected' : ''); ?>>Soiree gala</option>
                        <option value="Ceremonie officielle" <?php echo e(old('categorie', $evenement->categorie) == 'Ceremonie officielle' ? 'selected' : ''); ?>>Ceremonie officielle</option>
                        <option value="Webinaire" <?php echo e(old('categorie', $evenement->categorie) == 'Webinaire' ? 'selected' : ''); ?>>Webinaire</option>
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
unset($__errorArgs, $__bag); ?>" id="capacite" name="capacite" value="<?php echo e(old('capacite', $evenement->capacite)); ?>" min="1" required>
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
unset($__errorArgs, $__bag); ?>" id="date_fin_vente" name="date_fin_vente" value="<?php echo e(old('date_fin_vente', $evenement->date_fin_vente?->format('Y-m-d\TH:i'))); ?>">
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
                    <?php if($evenement->image): ?>
                        <div class="mb-2">
                            <img src="<?php echo e(asset('storage/' . $evenement->image)); ?>" alt="Image actuelle" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                    <?php endif; ?>
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

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="gratuit" name="gratuit" value="1" <?php echo e(old('gratuit', $evenement->gratuit ?? false) ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="gratuit">
                        <strong>Evenement gratuit</strong>
                        <small class="text-muted d-block">Les billets sont gratuits pour tous les participants</small>
                    </label>
                </div>

                <div class="mb-4">
                    <label for="statut" class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
                    <select class="form-select <?php $__errorArgs = ['statut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="statut" name="statut" required>
                        <option value="brouillon" <?php echo e(old('statut', $evenement->statut) == 'brouillon' ? 'selected' : ''); ?>>Brouillon</option>
                        <option value="publié" <?php echo e(old('statut', $evenement->statut) == 'publié' ? 'selected' : ''); ?>>Publié</option>
                        <option value="terminé" <?php echo e(old('statut', $evenement->statut) == 'terminé' ? 'selected' : ''); ?>>Terminé</option>
                        <option value="annulé" <?php echo e(old('statut', $evenement->statut) == 'annulé' ? 'selected' : ''); ?>>Annulé</option>
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
                            <i class="bi bi-cash-coin me-2"></i>Tarifs
                        </h6>

                        <?php if($evenement->tarifs->isEmpty()): ?>
                            <p class="text-muted">Aucun tarif défini pour cet événement.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table custom-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="font-size: 0.75rem;">Catégorie</th>
                                            <th style="font-size: 0.75rem;">Type</th>
                                            <th style="font-size: 0.75rem;">Prix</th>
                                            <th style="font-size: 0.75rem;">Dispo.</th>
                                            <th style="font-size: 0.75rem;">Vendu</th>
                                            <th style="font-size: 0.75rem;">Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $evenement->tarifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tarif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td style="font-size: 0.82rem;">
                                                    <?php echo e(ucfirst($tarif->categorie)); ?>

                                                    <?php if($tarif->categorie === 'etudiant'): ?>
                                                        <span class="badge" style="background: rgba(135,66,139,0.12); color: var(--violet); font-size: 0.6rem;">Étudiant</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="font-size: 0.82rem;"><?php echo e($tarif->type === 'normal' ? 'Standard' : 'VIP'); ?></td>
                                                <td style="font-size: 0.82rem;" class="fw-bold"><?php echo e(number_format($tarif->prix, 0, ',', ' ')); ?> F</td>
                                                <td style="font-size: 0.82rem;"><?php echo e($tarif->quantite_disponible); ?></td>
                                                <td style="font-size: 0.82rem;"><?php echo e($tarif->quantite_vendue); ?></td>
                                                <td style="font-size: 0.82rem;">
                                                    <?php if($tarif->statut === 'actif'): ?>
                                                        <span class="badge" style="background: rgba(18,151,110,0.12); color: var(--vert);">Actif</span>
                                                    <?php else: ?>
                                                        <span class="badge" style="background: rgba(152,145,155,0.15); color: var(--gris);">Inactif</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-muted mt-3 mb-0" style="font-size: 0.78rem;">
                                <i class="bi bi-info-circle me-1"></i>
                                Pour modifier les tarifs, accédez à la section "Tarifs" depuis la page de l'événement.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-md-end gap-2">
                    <a href="<?php echo e(route('admin.evenements.index')); ?>" class="btn btn-secondary-custom w-100 w-md-auto">Annuler</a>
                    <button type="submit" class="btn btn-primary-custom w-100 w-md-auto">
                        <i class="bi bi-check-lg me-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\evenements\edit.blade.php ENDPATH**/ ?>