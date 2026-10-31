<?php $__env->startSection('title', "Finalisation — Type d'organisation"); ?>
<?php $__env->startSection('page-title', 'Finaliser la création de votre compte'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .wizard-card {
        max-width: 640px; margin: 0 auto; background: #fff; border-radius: 16px;
        padding: 1.5rem; box-shadow: 0 4px 24px rgba(0,0,0,0.04);
    }
    .wizard-step { display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 0.5rem; }
    .wizard-step .step { display: flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: #ccc; font-weight: 500; }
    .wizard-step .step.active { color: #542680; font-weight: 700; }
    .wizard-step .step.done { color: #2e7d4f; font-weight: 600; }
    .wizard-step .num { width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; background: #e0dde3; color: #fff; flex-shrink: 0; }
    .wizard-step .active .num { background: #542680; }
    .wizard-step .done .num { background: #2e7d4f; }
    .wizard-step .connector { width: 24px; height: 2px; background: #e0dde3; }
    .wizard-step .connector.done { background: #2e7d4f; }
    .type-card { border: 2px solid #e0dde3; border-radius: 14px; padding: 1.25rem 0.75rem; cursor: pointer; transition: all 0.2s; text-align: center; height: 100%; }
    .type-card:hover { border-color: #9972B0; background: #faf8fb; }
    .type-card.selected { border-color: #542680; background: #f5f0f9; }
    .type-card .icon { font-size: 1.5rem; color: #542680; margin-bottom: 0.4rem; }
    .type-card .name { font-weight: 700; font-size: 0.85rem; color: #1d1d1f; margin-bottom: 0.2rem; }
    .type-card .desc { font-size: 0.72rem; color: #6c757d; line-height: 1.3; }
    input[type="radio"] { display: none; }
    .toggle-group { display: flex; gap: 0.5rem; }
    .toggle-btn { flex: 1; text-align: center; padding: 0.5rem 0.3rem; border-radius: 10px; border: 1.5px solid #e0dde3; cursor: pointer; font-weight: 600; font-size: 0.78rem; background: #fff; transition: 0.2s; color: #495057; }
    .toggle-btn:hover { border-color: #9972B0; }
    .toggle-btn.active { background: #f5f0f9; border-color: #542680; color: #542680; }
    .toggle-btn input { display: none; }
    .doc-info { background: #f8f6f9; border-radius: 10px; padding: 0.75rem 1rem; font-size: 0.82rem; color: #495057; }
    .doc-info i { color: #542680; margin-right: 0.35rem; }
    @media (max-width: 575.98px) {
        .type-card { padding: 0.9rem 0.4rem; }
        .type-card .icon { font-size: 1.15rem; }
        .type-card .name { font-size: 0.72rem; }
        .type-card .desc { font-size: 0.6rem; }
        .wizard-card { padding: 1rem; }
    }
</style>
<div class="page-content">
    <div class="wizard-card">
        <div class="wizard-step">
            <div class="step active"><span class="num">1</span> Type &amp; Justificatifs</div>
            <div class="connector"></div>
            <div class="step"><span class="num">2</span> Récapitulatif</div>
        </div>
        <hr style="margin:0.75rem 0 1.25rem;border-color:#f0eeec;">

        <?php if($errors->any()): ?>
            <div id="error-summary-server" class="alert alert-danger py-2" style="font-size:.85rem;border-radius:10px;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($e); ?> <?php break; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div id="error-summary-server" class="alert alert-danger py-2" style="font-size:.85rem;border-radius:10px;display:none;"></div>
        <?php endif; ?>

        <div style="margin-bottom:1.5rem;">
            <p style="font-size:0.9rem;color:#6c757d;margin:0;">Complétez votre profil pour pouvoir créer et gérer des événements.</p>
        </div>

        <form id="step2-form" method="POST" action="<?php echo e(route('profil.post-step2')); ?>" enctype="multipart/form-data" novalidate>
            <?php echo csrf_field(); ?>
            <div class="row g-3 mb-4">
                <div class="col-4">
                    <label class="type-card <?php if(old('type', $type) === 'universitaire'): ?> selected <?php endif; ?>" onclick="selectType(this, 'universitaire')">
                        <div class="icon"><i class="bi bi-mortarboard-fill"></i></div>
                        <div class="name">Universitaire</div>
                        <div class="desc">Vous représentez une université ou un établissement scolaire</div>
                        <input type="radio" name="type" value="universitaire" <?php if(old('type', $type) === 'universitaire'): ?> checked <?php endif; ?> required>
                    </label>
                </div>
                <div class="col-4">
                    <label class="type-card <?php if(old('type', $type) === 'particulier'): ?> selected <?php endif; ?>" onclick="selectType(this, 'particulier')">
                        <div class="icon"><i class="bi bi-person"></i></div>
                        <div class="name">Particulier</div>
                        <div class="desc">Vous organisez des événements en votre nom propre</div>
                        <input type="radio" name="type" value="particulier" <?php if(old('type', $type) === 'particulier'): ?> checked <?php endif; ?> required>
                    </label>
                </div>
                <div class="col-4">
                    <label class="type-card <?php if(old('type', $type) === 'organisation'): ?> selected <?php endif; ?>" onclick="selectType(this, 'organisation')">
                        <div class="icon"><i class="bi bi-building"></i></div>
                        <div class="name">Organisation</div>
                        <div class="desc">Vous représentez une entreprise ou une association/ONG</div>
                        <input type="radio" name="type" value="organisation" <?php if(old('type', $type) === 'organisation'): ?> checked <?php endif; ?> required>
                    </label>
                </div>
            </div>

            <div id="fields-universitaire" style="display:none;">
                <div class="mb-3">
                    <label class="form-label">Nom de l'université</label>
                    <input type="text" name="organisation" class="form-control <?php $__errorArgs = ['organisation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           value="<?php echo e(old('organisation', $data['organisation'] ?? '')); ?>" placeholder="Ex: Université d'Abomey-Calavi">
                    <?php $__errorArgs = ['organisation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nom du représentant</label>
                    <input type="text" name="nom_representant" class="form-control <?php $__errorArgs = ['nom_representant'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           value="<?php echo e(old('nom_representant', $data['nom_representant'] ?? '')); ?>" placeholder="Ex: Jean AHOUANVOEKE">
                    <?php $__errorArgs = ['nom_representant'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fonction du représentant</label>
                    <input type="text" name="fonction" class="form-control <?php $__errorArgs = ['fonction'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           value="<?php echo e(old('fonction', $data['fonction'] ?? '')); ?>" placeholder="Ex: Recteur, Président(e), Secrétaire Général...">
                    <?php $__errorArgs = ['fonction'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <div id="fields-organisation" style="display:none;">
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <div class="toggle-group">
                        <?php $td = old('type_detail', $data['type_detail'] ?? ''); ?>
                        <label class="toggle-btn <?php echo e($td === 'entreprise' ? 'active' : ''); ?>">
                            <input type="radio" name="type_detail" value="entreprise" <?php echo e($td === 'entreprise' ? 'checked' : ''); ?>> Entreprise
                        </label>
                        <label class="toggle-btn <?php echo e($td === 'association' ? 'active' : ''); ?>">
                            <input type="radio" name="type_detail" value="association" <?php echo e($td === 'association' ? 'checked' : ''); ?>> Association/ONG
                        </label>
                    </div>
                    <?php $__errorArgs = ['type_detail'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback d-block"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nom de l'organisation</label>
                    <input type="text" name="organisation" class="form-control <?php $__errorArgs = ['organisation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           value="<?php echo e(old('organisation', $data['organisation'] ?? '')); ?>" placeholder="Ex: ABC SARL">
                    <?php $__errorArgs = ['organisation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nom du représentant</label>
                    <input type="text" name="nom_representant" class="form-control <?php $__errorArgs = ['nom_representant'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           value="<?php echo e(old('nom_representant', $data['nom_representant'] ?? '')); ?>" placeholder="Ex: Jean AHOUANVOEKE">
                    <?php $__errorArgs = ['nom_representant'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fonction du représentant</label>
                    <input type="text" name="fonction" class="form-control <?php $__errorArgs = ['fonction'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           value="<?php echo e(old('fonction', $data['fonction'] ?? '')); ?>" placeholder="Ex: Directeur Général, Président(e)...">
                    <?php $__errorArgs = ['fonction'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <div id="fields-particulier" style="display:none;">
                <p class="doc-info"><i class="bi bi-info-circle"></i> Vous organisez en tant que particulier.</p>
            </div>

            <div id="block-doc-simple" style="display:none;">
                <div class="mb-3">
                    <label class="form-label" id="doc-simple-label">Carte CIP</label>
                    <?php if(!empty($existingDocuments) && !empty($data['document_justificatif'])): ?>
                    <div class="alert alert-success py-2 mb-2" style="font-size:0.82rem;border-radius:8px;">
                        <i class="bi bi-check-circle-fill me-1"></i> Fichier déjà fourni. Vous pouvez le remplacer si besoin.
                    </div>
                    <?php endif; ?>
                    <input type="file" name="document_justificatif" id="doc-simple-file" class="form-control <?php $__errorArgs = ['document_justificatif'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">Format PDF, JPG ou PNG. Max 2 Mo.</div>
                    <?php $__errorArgs = ['document_justificatif'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" id="doc-simple-number-label">Numéro de la carte</label>
                    <input type="text" name="numero_cip" id="doc-simple-num" class="form-control <?php $__errorArgs = ['numero_cip'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           value="<?php echo e(old('numero_cip', $data['numero_cip'] ?? '')); ?>" placeholder="Ex : numéro figurant sur la pièce">
                    <?php $__errorArgs = ['numero_cip'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <div id="block-doc-organisation" style="display:none;">
                <div class="mb-3">
                    <label class="form-label" id="doc-org-label">Registre de Commerce (RC)</label>
                    <?php if(!empty($existingDocuments) && !empty($data['document_justificatif'])): ?>
                    <div class="alert alert-success py-2 mb-2" style="font-size:0.82rem;border-radius:8px;">
                        <i class="bi bi-check-circle-fill me-1"></i> Fichier déjà fourni. Vous pouvez le remplacer si besoin.
                    </div>
                    <?php endif; ?>
                    <input type="file" name="document_justificatif" id="doc-org-file" class="form-control <?php $__errorArgs = ['document_justificatif'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">Format PDF, JPG ou PNG. Max 2 Mo.</div>
                    <?php $__errorArgs = ['document_justificatif'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" id="doc-org-number-label">Numéro du Registre de Commerce</label>
                    <input type="text" name="numero_rc" id="doc-org-num-rc" class="form-control <?php $__errorArgs = ['numero_rc'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           value="<?php echo e(old('numero_rc', $data['numero_rc'] ?? '')); ?>">
                    <?php $__errorArgs = ['numero_rc'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Carte CIP du représentant</label>
                    <div class="doc-info mb-2"><i class="bi bi-info-circle"></i> Carte d'identité personnelle du représentant / responsable</div>
                    <?php if(!empty($existingDocuments) && !empty($data['document_cip'])): ?>
                    <div class="alert alert-success py-2 mb-2" style="font-size:0.82rem;border-radius:8px;">
                        <i class="bi bi-check-circle-fill me-1"></i> Fichier déjà fourni. Vous pouvez le remplacer si besoin.
                    </div>
                    <?php endif; ?>
                    <input type="file" name="document_cip" id="doc-org-cip-file" class="form-control <?php $__errorArgs = ['document_cip'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">Format PDF, JPG ou PNG. Max 2 Mo.</div>
                    <?php $__errorArgs = ['document_cip'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Numéro CIP du représentant</label>
                    <input type="text" name="numero_cip_rep" id="doc-org-cip-num" class="form-control <?php $__errorArgs = ['numero_cip_rep'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           value="<?php echo e(old('numero_cip_rep', $data['numero_cip'] ?? '')); ?>" placeholder="Ex : numéro figurant sur la CIP">
                    <?php $__errorArgs = ['numero_cip_rep'];
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
                <label class="form-label">Signature <span class="text-danger">*</span></label>
                <div class="doc-info mb-2"><i class="bi bi-pen"></i> Signez clairement sur une feuille blanche et prenez en photo ou scannez</div>
                <?php if(!empty($existingDocuments) && !empty($data['signature'])): ?>
                <div class="alert alert-success py-2 mb-2" style="font-size:0.82rem;border-radius:8px;">
                    <i class="bi bi-check-circle-fill me-1"></i> Signature déjà fournie. Vous pouvez la remplacer si besoin.
                </div>
                <?php endif; ?>
                <input type="file" name="signature" class="form-control <?php $__errorArgs = ['signature'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" accept=".jpg,.jpeg,.png" <?php echo e(empty($existingDocuments) ? 'required' : ''); ?>>
                <div class="form-text">Format JPG ou PNG. Max 2 Mo.</div>
                <?php $__errorArgs = ['signature'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <button type="submit" class="btn btn-primary w-100" style="background:#542680;border:none;border-radius:10px;padding:0.7rem 1rem;font-weight:600;">Suivant</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    <?php
        $alreadyJustificatif = !empty($existingDocuments) && !empty($data['document_justificatif']);
        $alreadyCip = !empty($existingDocuments) && !empty($data['document_cip']);
        $alreadySignature = !empty($existingDocuments) && !empty($data['signature']);
    ?>
    const alreadyJustificatif = <?php echo $alreadyJustificatif ? 'true' : 'false'; ?>;
    const alreadyCip = <?php echo $alreadyCip ? 'true' : 'false'; ?>;
    const alreadySignature = <?php echo $alreadySignature ? 'true' : 'false'; ?>;

    const simpleLabels = {
        universitaire: { doc: 'Carte étudiante du responsable', num: 'Numéro de la carte étudiant' },
        particulier: { doc: 'Carte CIP', num: 'Numéro CIP' }
    };

    const orgDocLabel = {
        entreprise: 'Registre de Commerce (RC)',
        association: 'Récépissé d\'autorisation'
    };

    const orgNumLabel = {
        entreprise: 'Numéro du Registre de Commerce (RC)',
        association: 'Numéro du récépissé d\'autorisation'
    };

    function setRequired(id, required) {
        const el = document.getElementById(id);
        if (!el) return;
        if (required) el.setAttribute('required', '');
        else el.removeAttribute('required');
    }

    function setDisabled(id, disabled) {
        const el = document.getElementById(id);
        if (!el) return;
        if (disabled) el.setAttribute('disabled', 'disabled');
        else el.removeAttribute('disabled');
    }

    function typeDetail() {
        const orgFields = document.getElementById('fields-organisation');
        if (orgFields.style.display === 'block') {
            const checked = orgFields.querySelector('input[name="type_detail"]:checked');
            return checked ? checked.value : null;
        }
        return null;
    }

    function updateBlocks(val) {
        const isOrg = val === 'organisation';
        const simple = document.getElementById('block-doc-simple');
        const org = document.getElementById('block-doc-organisation');
        simple.style.display = isOrg ? 'none' : 'block';
        org.style.display = isOrg ? 'block' : 'none';

        // Nettoyer les attributs required et disabled des deux blocs
        const allFields = ['doc-simple-file','doc-simple-num','doc-org-file','doc-org-num-rc','doc-org-cip-file','doc-org-cip-num'];
        allFields.forEach(id => setRequired(id, false));
        allFields.forEach(id => setDisabled(id, false));

        if (!isOrg) {
            const labels = simpleLabels[val] || simpleLabels.particulier;
            document.getElementById('doc-simple-label').textContent = labels.doc;
            document.getElementById('doc-simple-number-label').textContent = labels.num;
            // Seul le bloc visible est requis ; le bloc masque est desactive
            ['doc-org-file','doc-org-num-rc','doc-org-cip-file','doc-org-cip-num'].forEach(id => setDisabled(id, true));
            setRequired('doc-simple-file', !alreadyJustificatif);
            setRequired('doc-simple-num', true);
        } else {
            const detail = typeDetail();
            document.getElementById('doc-org-label').textContent = orgDocLabel[detail] || orgDocLabel.entreprise;
            document.getElementById('doc-org-number-label').textContent = orgNumLabel[detail] || orgNumLabel.entreprise;
            // Seul le bloc visible est requis ; le bloc masque est desactive
            ['doc-simple-file','doc-simple-num'].forEach(id => setDisabled(id, true));
            setRequired('doc-org-file', !alreadyJustificatif);
            setRequired('doc-org-num-rc', true);
            setRequired('doc-org-cip-file', !alreadyCip);
            setRequired('doc-org-cip-num', true);
        }
    }

    function setContainerDisabled(containerId, disabled) {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.querySelectorAll('input, select, textarea').forEach(el => {
            if (disabled) el.setAttribute('disabled', 'disabled');
            else el.removeAttribute('disabled');
        });
    }

    function selectType(el, val) {
        document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        el.querySelector('input[type="radio"]').checked = true;
        document.getElementById('fields-universitaire').style.display = val === 'universitaire' ? 'block' : 'none';
        document.getElementById('fields-organisation').style.display = val === 'organisation' ? 'block' : 'none';
        document.getElementById('fields-particulier').style.display = val === 'particulier' ? 'block' : 'none';
        // Désactiver les champs des blocs masqués pour éviter les doublons de nom
        setContainerDisabled('fields-universitaire', val !== 'universitaire');
        setContainerDisabled('fields-organisation', val !== 'organisation');
        setContainerDisabled('fields-particulier', val !== 'particulier');
        updateBlocks(val);
    }

    function setFieldError(name, message) {
        const inputs = document.querySelectorAll('input[name="' + name + '"]');
        let input = null;
        // Privilégier un champ visible (le champ caché de l'autre bloc n'est pas pris en compte)
        inputs.forEach(function(candidate) {
            if (candidate.offsetParent !== null) input = candidate;
        });
        if (!input && inputs.length) input = inputs[0];
        if (!input) return;
        input.classList.add('is-invalid');
        let feedback = input.closest('.mb-3').querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.closest('.mb-3').appendChild(feedback);
        }
        feedback.innerHTML = message;
    }

    function clearFieldErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function showSummary(messages) {
        const box = document.getElementById('error-summary-server');
        box.style.display = 'block';
        box.innerHTML = messages;
    }

    function initServerErrors() {
        <?php if($errors->any()): ?>
            <?php $__currentLoopData = $errors->getMessages(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $messages): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    setFieldError('<?php echo e($field); ?>', '<?php echo e(addslashes($message)); ?>');
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    }

    document.getElementById('step2-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const button = form.querySelector('button[type="submit"]');
        const originalLabel = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Envoi en cours...';
        clearFieldErrors();

        const fd = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
            },
            body: fd
        }).then(function(res) {
            if (res.redirected) {
                window.location.href = res.url;
                return null;
            }
            return res.json().then(function(data) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                    return null;
                }
                return data;
            }).catch(function() {
                return { errors: {} };
            });
        }).then(function(data) {
            if (!data) return;
            if (data.errors) {
                Object.keys(data.errors).forEach(function(field) {
                    setFieldError(field, data.errors[field][0]);
                });
                const firstKey = Object.keys(data.errors)[0];
                showSummary(data.errors[firstKey][0]);
            }
        }).catch(function(err) {
            console.error(err);
            showSummary('Une erreur est survenue. Veuillez réessayer.');
        }).finally(function() {
            button.disabled = false;
            button.innerHTML = originalLabel;
        });
    });

    (function init() {
        const selected = document.querySelector('.type-card.selected');
        const selVal = selected && selected.querySelector('input[type="radio"]').value;
        document.getElementById('fields-universitaire').style.display = selVal === 'universitaire' ? 'block' : 'none';
        document.getElementById('fields-organisation').style.display = selVal === 'organisation' ? 'block' : 'none';
        document.getElementById('fields-particulier').style.display = selVal === 'particulier' ? 'block' : 'none';
        setContainerDisabled('fields-universitaire', selVal !== 'universitaire');
        setContainerDisabled('fields-organisation', selVal !== 'organisation');
        setContainerDisabled('fields-particulier', selVal !== 'particulier');
        if (selected) {
            updateBlocks(selVal);
        }
        // Signature : requise si non déjà fournie
        const sig = document.querySelector('input[name="signature"]');
        if (sig && !alreadySignature) sig.setAttribute('required', '');
        initServerErrors();
    })();

    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.toggle-group').querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            this.querySelector('input').checked = true;
            updateBlocks('organisation');
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/admin/profil/etape2.blade.php ENDPATH**/ ?>