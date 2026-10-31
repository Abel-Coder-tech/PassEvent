<?php $__env->startSection('title', 'Modifier l\'événement'); ?>

<?php $__env->startSection('page-title', 'Modifier l\'événement'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Tableau de bord</a></li>
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
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label fw-semibold mb-0">Jours supplémentaires (événement multi-jours)</label>
                        <small class="text-muted">Optionnel</small>
                    </div>
                    <small class="text-muted d-block mb-2">
                        Si votre événement dure plusieurs jours, ajoutez la date et l'heure de début de chaque jour. Le QR code restera valable pour tous les jours (1 scan par jour).
                    </small>
                    <div id="dates-supplementaires-container">
                        <?php
                            $datesExistantes = $evenement->dates()->where('date_debut', '!=', $evenement->date_event)->get();
                        ?>
                        <?php $__currentLoopData = old('dates_supplementaires', $datesExistantes->pluck('date_debut')->map(fn($d) => $d->format('Y-m-d\TH:i'))->all()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $dateSupp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="date-supp-row mb-2 d-flex gap-2 align-items-center" id="date-supp-row-<?php echo e($i + 1); ?>">
                                <input type="datetime-local" class="form-control" name="dates_supplementaires[]" value="<?php echo e($dateSupp); ?>">
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
                        <?php $typeActuel = old('type_evenement', $evenement->type_evenement ?? 'spectacle'); ?>
                        <option value="spectacle" <?php echo e($typeActuel == 'spectacle' ? 'selected' : ''); ?>>Spectacle / Soirée</option>
                        <option value="formation" <?php echo e($typeActuel == 'formation' ? 'selected' : ''); ?>>Formation</option>
                        <option value="conference" <?php echo e($typeActuel == 'conference' ? 'selected' : ''); ?>>Conférence</option>
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
                        <?php
                            $catList = ['Sport', 'Soiree gala', 'Ceremonie officielle', 'Webinaire'];
                            $currentCat = old('categorie', $evenement->categorie);
                        ?>
                        <?php $__currentLoopData = $catList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat); ?>" <?php echo e($currentCat == $cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <option value="Autre" <?php echo e(!in_array($currentCat, $catList) && $currentCat ? 'selected' : ''); ?>>Autre</option>
                    </select>
                    <div id="autre-categorie-wrapper" style="display:none;margin-top:0.5rem;">
                        <input type="text" class="form-control" id="autre_categorie" name="autre_categorie" placeholder="Precisez la categorie" value="<?php echo e(old('autre_categorie', !in_array($currentCat, $catList) ? $currentCat : '')); ?>">
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

                <div class="panel-card mt-4 mb-4" style="border-left: 3px solid var(--violet);">
                    <div class="panel-card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold mb-0" style="color: var(--violet);">
                                <i class="bi bi-cash-coin me-2"></i>Tarifs
                            </h6>
                            <a href="<?php echo e(route('admin.tarifs.index', $evenement->id)); ?>" class="btn btn-sm btn-primary-custom">
                                <i class="bi bi-gear me-1"></i> Gérer les tarifs
                            </a>
                        </div>

                        <?php if($evenement->tarifs->isEmpty()): ?>
                            <p class="text-muted">Aucun tarif défini pour cet événement.
                                <a href="<?php echo e(route('admin.tarifs.create', $evenement->id)); ?>">Ajouter un tarif</a>.
                            </p>
                        <?php else: ?>
                            <?php $evenementAVendu = $evenement->tarifs()->sum('quantite_vendue') > 0; ?>
                            <?php $__currentLoopData = $evenement->tarifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tarif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $tarifVendu = $tarif->quantite_vendue > 0;
                                    $dejaDemande = $tarifVendu && $tarif->demandesModification()->where('statut', 'en_attente')->exists();
                                ?>
                                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2 py-2 <?php echo e(!$loop->last ? 'border-bottom' : ''); ?>" style="border-color:#f0f0f0;">
                                    <div style="flex:1;min-width:0;">
                                        <div class="fw-semibold" style="font-size:0.85rem;"><?php echo e($tarif->nom); ?></div>
                                        <div style="font-size:0.72rem;color:var(--gris);">
                                            <?php echo e($tarif->quantite_disponible ?? 'Illimité'); ?> dispo. · <?php echo e($tarif->quantite_vendue); ?> vendu(s)
                                            · <span class="<?php echo e($tarif->statut === 'actif' ? 'text-success' : 'text-muted'); ?>"><?php echo e(ucfirst($tarif->statut)); ?></span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2" style="flex-shrink:0;">
                                        <div style="position:relative;">
                                            <input type="number" step="0.01" min="0"
                                                   name="prix_tarifs[<?php echo e($tarif->id); ?>]"
                                                   id="prix-tarif-<?php echo e($tarif->id); ?>"
                                                   value="<?php echo e(old('prix_tarifs.'.$tarif->id, $tarif->prix)); ?>"
                                                   class="form-control form-control-sm"
                                                   style="width:130px;text-align:right;"
                                                   <?php echo e($tarifVendu ? 'data-demande="'.$tarif->id.'" oninput="syncDemande(this)"' : ''); ?>

                                                   <?php $__errorArgs = ['prix_tarifs.'.$tarif->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>>
                                            <span style="position:absolute;right:0.5rem;top:50%;transform:translateY(-50%);font-size:0.7rem;color:var(--gris);pointer-events:none;">F</span>
                                        </div>

                                        <?php if($tarifVendu): ?>
                                            <?php if($dejaDemande): ?>
                                                <span class="badge" style="background:rgba(224,168,0,0.15);color:#8b6914;font-size:0.7rem;white-space:nowrap;">
                                                    <i class="bi bi-hourglass-split me-1"></i>Demande en attente
                                                </span>
                                            <?php else: ?>
                                                <button type="button" onclick="soumettreDemande(event, <?php echo e($tarif->id); ?>)" class="btn btn-sm" style="background:var(--violet);color:#fff;border-radius:8px;font-weight:600;white-space:nowrap;">
                                                    <i class="bi bi-send me-1"></i> Envoyer pour approbation
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <p class="text-muted mt-3 mb-0" style="font-size:0.78rem;">
                                <i class="bi bi-info-circle me-1"></i>
                                <?php if($evenementAVendu): ?>
                                    Un tarif ayant déjà des ventes ne se modifie plus directement : saisissez le nouveau prix puis cliquez sur « Envoyer pour approbation » ; PaxEvent devra le valider. Les autres tarifs se modifient directement avec « Enregistrer » ci-dessous.
                                <?php else: ?>
                                    Aucun billet vendu pour l'instant : vous pouvez modifier librement le prix des tarifs (sauvegardé via « Enregistrer »).
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-4" id="co-parrainage-section">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="co_parrainage_actif" name="co_parrainage_actif" value="1" <?php echo e(old('co_parrainage_actif', $evenement->co_parrainage_actif) ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-semibold" for="co_parrainage_actif">
                            <i class="bi bi-share me-1"></i>Ouvrir le co-parrainage
                        </label>
                    </div>
                    <small class="text-muted d-block">Autorisez des affiliés à promouvoir cet événement : ils touchent un pourcentage du prix de chaque billet vendu via leur lien.</small>

                    <div id="co-parrainage-fields" class="mt-3" style="max-width:280px; <?php echo e(old('co_parrainage_actif', $evenement->co_parrainage_actif) ? '' : 'display:none;'); ?>">
                        <label class="form-label fw-semibold" for="co_parrainage_pourcentage">Commission par billet <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.5" min="1" max="<?php echo e($maxCoParrainage); ?>" class="form-control <?php $__errorArgs = ['co_parrainage_pourcentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="co_parrainage_pourcentage" name="co_parrainage_pourcentage" value="<?php echo e(old('co_parrainage_pourcentage', $evenement->co_parrainage_pourcentage)); ?>" placeholder="ex. 10">
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

                <div class="mb-4">
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
unset($__errorArgs, $__bag); ?>" id="statut_publie" name="statut" value="publié" <?php echo e(old('statut', $evenement->statut) == 'publié' ? 'checked' : ''); ?>>
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
unset($__errorArgs, $__bag); ?>" id="statut_brouillon" name="statut" value="brouillon" <?php echo e(old('statut', $evenement->statut) == 'brouillon' ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="statut_brouillon">
                                <i class="bi bi-pencil-square me-1"></i>Brouillon
                            </label>
                        </div>
                    </div>
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
                        <i class="bi bi-check-lg me-1"></i> Enregistrer
                    </button>
                </div>
            </form>

            
            <?php $__currentLoopData = $evenement->tarifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tarif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $tarifVendu = $tarif->quantite_vendue > 0; ?>
                <?php if($tarifVendu): ?>
                    <form id="demande-form-<?php echo e($tarif->id); ?>" action="<?php echo e(route('admin.tarifs.demande-modification', [$evenement->id, $tarif->id])); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="nouveau_prix" id="demande-prix-<?php echo e($tarif->id); ?>" value="<?php echo e($tarif->prix); ?>">
                    </form>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

const gratuitToggle = document.getElementById('gratuit');
const coParrainageSection = document.getElementById('co-parrainage-section');
function majSectionGratuit() {
    if (coParrainageSection) {
        coParrainageSection.style.display = gratuitToggle?.checked ? 'none' : 'block';
    }
}
gratuitToggle?.addEventListener('change', function() {
    if (this.checked && coParrainageToggle) {
        coParrainageToggle.checked = false;
        if (coParrainageFields) coParrainageFields.style.display = 'none';
    }
    majSectionGratuit();
});
majSectionGratuit();

let dateSuppCount = document.querySelectorAll('#dates-supplementaires-container .date-supp-row').length || 0;
const maxDatesSupp = 6;

function addDateSupp() {
    if (dateSuppCount >= maxDatesSupp) return;
    dateSuppCount++;
    const html = `
        <div class="date-supp-row mb-2 d-flex gap-2 align-items-center" id="date-supp-row-${dateSuppCount}">
            <input type="datetime-local" class="form-control" name="dates_supplementaires[]">
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

function syncDemande(input) {
    const tarifId = input.getAttribute('data-demande');
    const hidden = document.getElementById('demande-prix-' + tarifId);
    if (hidden) hidden.value = input.value;
}

function soumettreDemande(event, tarifId) {
    const visible = document.getElementById('prix-tarif-' + tarifId);
    const hidden = document.getElementById('demande-prix-' + tarifId);
    if (!visible || !hidden) return;
    const valeur = visible.value;
    if (valeur === '' || isNaN(parseFloat(valeur)) || parseFloat(valeur) < 0) {
        alert('Veuillez saisir un prix valide avant d\'envoyer la demande.');
        return;
    }
    hidden.value = valeur;
    const form = document.getElementById('demande-form-' + tarifId);
    if (form) form.submit();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/evenements/edit.blade.php ENDPATH**/ ?>