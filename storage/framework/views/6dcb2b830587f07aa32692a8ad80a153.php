<?php $__env->startSection('title', 'Créer un événement'); ?>

<?php $__env->startSection('page-title', 'Créer un événement'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Tableau de bord</a></li>
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

                <?php if($errors->any()): ?>
                    <div class="alert alert-danger py-2 px-3" style="font-size: 0.85rem;">
                        <strong><i class="bi bi-exclamation-triangle-fill me-1"></i>L'événement n'a pas pu être enregistré :</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erreur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($erreur); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

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
unset($__errorArgs, $__bag); ?>" id="date_event" name="date_event" value="<?php echo e(old('date_event')); ?>" min="<?php echo e(now()->format('Y-m-d\TH:i')); ?>" required>
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
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label fw-semibold mb-0">Jours supplémentaires (événement multi-jours)</label>
                        <small class="text-muted">Optionnel</small>
                    </div>
                    <small class="text-muted d-block mb-2">
                        Si votre événement dure plusieurs jours, ajoutez la date et l'heure de début de chaque jour. Le QR code restera valable pour tous les jours (1 scan par jour).
                    </small>
                    <div id="dates-supplementaires-container">
                        <?php $__currentLoopData = old('dates_supplementaires', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $dateSupp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="date-supp-row mb-2 d-flex gap-2 align-items-center" id="date-supp-row-<?php echo e($i + 1); ?>">
                                <input type="datetime-local" class="form-control" name="dates_supplementaires[]" value="<?php echo e($dateSupp); ?>" min="<?php echo e(now()->format('Y-m-d\TH:i')); ?>">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDateSupp(this)" style="flex-shrink:0;">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="addDateSuppBtn" onclick="addDateSupp()">
                        <i class="bi bi-plus-lg me-1"></i> Ajouter un jour
                    </button>
                </div>

                <div class="mb-3">
                    <label for="type_evenement" class="form-label fw-semibold">Type d'événement <span class="text-danger">*</span></label>
                    <select class="form-select <?php $__errorArgs = ['type_evenement'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="type_evenement" name="type_evenement" required>
                        <option value="spectacle" <?php echo e(old('type_evenement', 'spectacle') == 'spectacle' ? 'selected' : ''); ?>>Spectacle / Soirée</option>
                        <option value="formation" <?php echo e(old('type_evenement') == 'formation' ? 'selected' : ''); ?>>Formation</option>
                        <option value="conference" <?php echo e(old('type_evenement') == 'conference' ? 'selected' : ''); ?>>Conférence</option>
                    </select>
                    <div class="form-text">Les textes d'achat s'adaptent au type (acheter un billet / s'inscrire).</div>
                    <?php $__errorArgs = ['type_evenement'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                        <option value="Autre" <?php echo e(old('categorie') == 'Autre' ? 'selected' : ''); ?>>Autre</option>
                    </select>
                    <div id="autre-categorie-wrapper" style="display:none;margin-top:0.5rem;">
                        <input type="text" class="form-control" id="autre_categorie" name="autre_categorie" placeholder="Precisez la categorie" value="<?php echo e(old('autre_categorie')); ?>">
                    </div>
                    <?php $__errorArgs = ['categorie'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php $__errorArgs = ['autre_categorie'];
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
                    <div id="alerteTailleImage" class="text-danger small mt-1 d-none">
                        <i class="bi bi-exclamation-circle me-1"></i>Image trop lourde : le maximum est 512 Ko. Choisissez une image plus légère.
                    </div>
                    <div class="form-text">Format JPG ou PNG, 512 Ko maximum.</div>
                    <?php $__errorArgs = ['image'];
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
                            <div id="tarifs-container">
                                <div class="tarif-row mb-3 p-3 rounded" id="tarif-row-1" data-auto="<?php echo e(old('tarif_qte_1') ? '0' : '1'); ?>" style="background: #f8f6f9; border: 1px solid #ede5f0;">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold" style="font-size:0.8rem;">Nom du tarif <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="tarif_nom_1" value="<?php echo e(old('tarif_nom_1')); ?>" placeholder="Ex: Enfant" required>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label fw-semibold" style="font-size:0.8rem;">Prix (FCFA) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="tarif_prix_1" value="<?php echo e(old('tarif_prix_1')); ?>" min="0" step="100" placeholder="Ex: 1000" required>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label fw-semibold" style="font-size:0.8rem;">Places <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control <?php $__errorArgs = ['tarif_qte_1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="tarif_qte_1" value="<?php echo e(old('tarif_qte_1')); ?>" min="1" max="<?php echo e(old('capacite', 500000)); ?>" placeholder="Ex: 100" required>
                                            <?php $__errorArgs = ['tarif_qte_1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <small class="text-muted">Obligatoire</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-secondary mb-2" id="addTarifBtn" onclick="addTarif()">
                                <i class="bi bi-plus-lg me-1"></i> Ajouter un tarif
                            </button>

                            <div id="places-hint" class="small mb-3"></div>
                            <?php $__errorArgs = ['tarifs'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="alert" style="background: rgba(220,53,69,0.08); border: 1px solid rgba(220,53,69,0.3); color: #b02a37; border-radius: 8px; padding: 0.6rem 0.9rem; font-size: 0.82rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="generer_vip" name="generer_vip" value="1" <?php echo e(old('generer_vip') ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="generer_vip">
                                    <strong>Générer automatiquement le tarif VIP</strong>
                                    <small class="text-muted d-block">Crée un 2ème tarif à 2× le prix du premier tarif saisi</small>
                                </label>
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

                <div class="mb-4" id="co-parrainage-section">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="co_parrainage_actif" name="co_parrainage_actif" value="1" <?php echo e(old('co_parrainage_actif') ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-semibold" for="co_parrainage_actif">
                            <i class="bi bi-share me-1"></i>Ouvrir le co-parrainage
                        </label>
                    </div>
                    <small class="text-muted d-block">Autorisez des affiliés à promouvoir cet événement : ils touchent un pourcentage du prix de chaque billet vendu via leur lien.</small>

                    <div id="co-parrainage-fields" class="mt-3" style="max-width:280px; <?php echo e(old('co_parrainage_actif') ? '' : 'display:none;'); ?>">
                        <label class="form-label fw-semibold" for="co_parrainage_pourcentage">Commission par billet <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.5" min="1" max="<?php echo e($maxCoParrainage); ?>" class="form-control <?php $__errorArgs = ['co_parrainage_pourcentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="co_parrainage_pourcentage" name="co_parrainage_pourcentage" value="<?php echo e(old('co_parrainage_pourcentage')); ?>" placeholder="ex. 10">
                            <span class="input-group-text">%</span>
                        </div>
                        <?php $__errorArgs = ['co_parrainage_pourcentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback d-block"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="text-muted">Maximum <?php echo e($maxCoParrainage); ?> % (100 % moins votre commission PaxEvent).</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold d-block">Statut <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input type="radio" class="form-check-input <?php $__errorArgs = ['statut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="statut_publie" name="statut" value="publié" <?php echo e(old('statut', 'publié') == 'publié' ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="statut_publie">
                                <i class="bi bi-globe2 me-1"></i>Publié
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input <?php $__errorArgs = ['statut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="statut_brouillon" name="statut" value="brouillon" <?php echo e(old('statut') == 'brouillon' ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="statut_brouillon">
                                <i class="bi bi-pencil-square me-1"></i>Brouillon
                            </label>
                        </div>
                    </div>
                    <small class="text-muted">Le statut pourra être modifié plus tard.</small>
                    <?php $__errorArgs = ['statut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback d-block"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
document.getElementById('categorie')?.addEventListener('change', function() {
    const wrapper = document.getElementById('autre-categorie-wrapper');
    if (this.value === 'Autre') {
        wrapper.style.display = 'block';
    } else {
        wrapper.style.display = 'none';
    }
});
if (document.getElementById('categorie')?.value === 'Autre') {
    document.getElementById('autre-categorie-wrapper').style.display = 'block';
}

const coParrainageToggle = document.getElementById('co_parrainage_actif');
const coParrainageFields = document.getElementById('co-parrainage-fields');
coParrainageToggle?.addEventListener('change', function() {
    coParrainageFields.style.display = this.checked ? 'block' : 'none';
});

let tarifCount = 1;
const maxTarifs = 4;
let bloqueSoumission = false;

let dateSuppCount = document.querySelectorAll('#dates-supplementaires-container .date-supp-row').length || 0;
const maxDatesSupp = 6;

function addDateSupp() {
    if (dateSuppCount >= maxDatesSupp) return;
    dateSuppCount++;
    const min = document.getElementById('date_event')?.getAttribute('min') || '';
    const html = `
        <div class="date-supp-row mb-2 d-flex gap-2 align-items-center" id="date-supp-row-${dateSuppCount}">
            <input type="datetime-local" class="form-control" name="dates_supplementaires[]" min="${min}">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDateSupp(this)" style="flex-shrink:0;">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `;
    document.getElementById('dates-supplementaires-container').insertAdjacentHTML('beforeend', html);
    if (dateSuppCount >= maxDatesSupp) {
        document.getElementById('addDateSuppBtn').style.display = 'none';
    }
}

function removeDateSupp(btn) {
    const row = btn.closest('.date-supp-row');
    if (row) row.remove();
    dateSuppCount--;
    document.getElementById('addDateSuppBtn').style.display = dateSuppCount >= maxDatesSupp ? 'none' : 'inline-flex';
}

function addTarif() {
    if (tarifCount >= maxTarifs) return;
    // Fige le champ en mode "auto" courant : la nouvelle ligne devient la seule "auto"
    document.querySelectorAll('#tarifs-container .tarif-row').forEach(r => { r.dataset.auto = '0'; });
    tarifCount++;
    const num = tarifCount;
    const html = `
        <div class="tarif-row mb-3 p-3 rounded" style="background: #f8f6f9; border: 1px solid #ede5f0;" id="tarif-row-${num}" data-auto="1">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="fw-bold text-muted">Tarif ${num}</small>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTarif(${num})" style="font-size:0.7rem; padding:0.15rem 0.5rem;">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.8rem;">Nom du tarif</label>
                    <input type="text" class="form-control" name="tarif_nom_${num}" placeholder="Ex: VIP">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold" style="font-size:0.8rem;">Prix (FCFA)</label>
                    <input type="number" class="form-control" name="tarif_prix_${num}" min="0" step="100" placeholder="Ex: 5000">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold" style="font-size:0.8rem;">Places <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="tarif_qte_${num}" min="1" placeholder="Restantes" required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-12 col-md-2"></div>
            </div>
        </div>
    `;
    document.getElementById('tarifs-container').insertAdjacentHTML('beforeend', html);
    if (tarifCount >= maxTarifs) {
        document.getElementById('addTarifBtn').style.display = 'none';
    }
    majPlaces();
    updatePreview();
}

function removeTarif(num) {
    const row = document.getElementById('tarif-row-' + num);
    if (row) row.remove();
    tarifCount--;
    document.getElementById('addTarifBtn').style.display = tarifCount >= maxTarifs ? 'none' : 'inline-flex';
    majPlaces();
    updatePreview();
}

function updatePreview() {
    const gratuit = document.getElementById('gratuit')?.checked;
    if (gratuit) {
        document.getElementById('preview-tarifs').innerHTML = '<strong>Gratuit :</strong> 0 F';
        return;
    }

    let lines = [];
    for (let i = 1; i <= 4; i++) {
        const nomInput = document.querySelector(`[name="tarif_nom_${i}"]`);
        const prixInput = document.querySelector(`[name="tarif_prix_${i}"]`);
        if (nomInput && prixInput && nomInput.value.trim() && prixInput.value) {
            const nom = nomInput.value.trim();
            const prix = Math.round(parseFloat(prixInput.value));
            lines.push(`<strong>${nom}:</strong> ${formatPrice(prix)}`);
        }
    }

    if (document.getElementById('generer_vip')?.checked && lines.length > 0) {
        const firstPrix = parseFloat(document.querySelector('[name="tarif_prix_1"]')?.value || 0);
        if (firstPrix > 0) {
            lines.push(`<strong>VIP (auto):</strong> ${formatPrice(firstPrix * 2)}`);
        }
    }

    document.getElementById('preview-tarifs').innerHTML = lines.length > 0 ? lines.join('<br>') : 'Ajoutez un tarif pour voir l\'aperçu';
}

function formatPrice(price) {
    return Math.round(price).toLocaleString('fr-FR') + ' F';
}

// Somme des places déjà attribuées aux autres tarifs (exclut la ligne courante)
function totalAttribue(rows, ligneExclue) {
    let total = 0;
    rows.forEach(row => {
        if (row === ligneExclue) return;
        const qte = row.querySelector('[name^="tarif_qte_"]');
        if (!qte) return;
        const val = parseInt(qte.value);
        if (!isNaN(val) && val > 0) total += val;
    });
    return total;
}

// Gère la répartition des places entre la capacité et les tarifs
function majPlaces() {
    const capacite = parseInt(document.getElementById('capacite')?.value) || 0;
    const rows = Array.from(document.querySelectorAll('#tarifs-container .tarif-row'));

    // Réinitialise l'état visuel + repère le dernier champ en mode "auto"
    let autoRow = null;
    rows.forEach(row => {
        const qte = row.querySelector('[name^="tarif_qte_"]');
        if (!qte) return;
        qte.classList.remove('is-invalid');
        const fb = row.querySelector('.invalid-feedback');
        if (fb) fb.textContent = '';
        qte.max = capacite > 0 ? capacite : '';
        if (row.dataset.auto === '1') autoRow = row;
    });

    // 1) Remplit automatiquement le champ "auto" avec les places restantes
    let plusDePlaces = false;
    if (autoRow) {
        const qte = autoRow.querySelector('[name^="tarif_qte_"]');
        const fb = autoRow.querySelector('.invalid-feedback');
        const restant = capacite - totalAttribue(rows, autoRow);
        if (capacite > 0 && restant > 0) {
            qte.value = restant;
        } else if (capacite > 0) {
            qte.value = '';
            plusDePlaces = true;
            qte.classList.add('is-invalid');
            if (fb) fb.textContent = 'Plus de places disponibles.';
        }
    }

    // 2) Vérifie chaque champ et la somme globale
    let total = 0;
    let depassement = false;
    rows.forEach(row => {
        const qte = row.querySelector('[name^="tarif_qte_"]');
        if (!qte) return;
        if (qte.value === '') {
            const dispo = capacite > 0 ? Math.max(0, capacite - totalAttribue(rows, row)) : null;
            qte.placeholder = dispo !== null ? `Restantes (${dispo})` : 'Restantes';
        } else {
            qte.placeholder = 'Restantes';
        }
        const val = parseInt(qte.value);
        const fb = row.querySelector('.invalid-feedback');
        if (!isNaN(val) && val > 0) {
            if (capacite > 0 && val > capacite) {
                qte.classList.add('is-invalid');
                if (fb) fb.textContent = `Les places ne peuvent pas dépasser la capacité (${capacite}).`;
                depassement = true;
            }
            total += val;
        }
    });

    const over = depassement || (capacite > 0 && total > capacite);

    const hint = document.getElementById('places-hint');
    if (hint) {
        if (capacite <= 0) {
            hint.innerHTML = '<span class="text-muted">Renseignez d\'abord la capacité (nombre de places) de l\'événement.</span>';
        } else if (over) {
            const reste = capacite - total;
            hint.innerHTML = `<span class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Places attribuées (${total}) supérieures à la capacité (${capacite}). Répartissez au maximum ${capacite} place(s)${reste < 0 ? ` — dépassement de ${Math.abs(reste)}` : ''}.</span>`;
        } else if (plusDePlaces) {
            hint.innerHTML = `<span class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Plus de places disponibles : la capacité (${capacite}) est déjà entièrement répartie. Réduisez les places d'un tarif existant ou augmentez la capacité de l'événement.</span>`;
        } else {
            const reste = capacite - total;
            hint.innerHTML = `<span class="text-success"><i class="bi bi-check-circle me-1"></i>${total} place(s) attribuée(s) · ${reste} restante(s) sur ${capacite}.</span>`;
        }
    }

    // Le bouton d'ajout reste toujours accessible tant qu'on n'a pas atteint le maximum
    const addBtn = document.getElementById('addTarifBtn');
    if (addBtn) {
        addBtn.style.display = tarifCount < maxTarifs ? 'inline-flex' : 'none';
    }

    // Empêche la soumission si incohérence
    bloqueSoumission = over || plusDePlaces;
    const submitBtn = document.querySelector('#pricing-fields')?.closest('form')?.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = bloqueSoumission && !document.getElementById('gratuit')?.checked;
}

function toggleGratuit() {
    const checked = document.getElementById('gratuit')?.checked;
    const fields = document.getElementById('pricing-fields');
    fields.style.display = checked ? 'none' : 'block';

    const coSection = document.getElementById('co-parrainage-section');
    if (coSection) coSection.style.display = checked ? 'none' : 'block';
    if (checked) {
        if (coParrainageToggle) coParrainageToggle.checked = false;
        if (coParrainageFields) coParrainageFields.style.display = 'none';
    }
    // Désactive les champs masqués pour éviter la validation native et l'envoi inutile
    fields.querySelectorAll('input').forEach(el => { el.disabled = checked; });
    if (coSection) coSection.querySelectorAll('input').forEach(el => { el.disabled = checked; });

    updatePreview();
    majPlaces();
}

document.getElementById('gratuit')?.addEventListener('change', toggleGratuit);
document.getElementById('generer_vip')?.addEventListener('change', updatePreview);
document.getElementById('capacite')?.addEventListener('input', majPlaces);
document.querySelectorAll('[name^="tarif_prix_"]').forEach(el => el.addEventListener('input', updatePreview));
document.querySelectorAll('[name^="tarif_nom_"]').forEach(el => el.addEventListener('input', updatePreview));
// Délégation : couvre les lignes ajoutées dynamiquement et fige le champ dès saisie manuelle
document.getElementById('tarifs-container')?.addEventListener('input', function (e) {
    if (e.target.matches('[name^="tarif_qte_"]')) {
        const row = e.target.closest('.tarif-row');
        if (row) row.dataset.auto = '0';
        majPlaces();
    }
});
toggleGratuit();
majPlaces();

// Alerte immediate si l image depasse 512 Ko (avant meme de soumettre)
document.getElementById('image')?.addEventListener('change', function () {
    var alerte = document.getElementById('alerteTailleImage');
    if (!alerte) return;
    alerte.classList.toggle('d-none', !(this.files[0] && this.files[0].size > 512 * 1024));
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/evenements/create.blade.php ENDPATH**/ ?>