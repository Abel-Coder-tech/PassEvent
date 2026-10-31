<?php $__env->startSection('title', 'Récapitulatif'); ?>
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
    .wizard-step .done .num { background: #2e7d4f; }
    .wizard-step .active .num { background: #542680; }
    .wizard-step .connector { width: 24px; height: 2px; background: #e0dde3; }
    .wizard-step .connector.done { background: #2e7d4f; }
    .recap-card { background: #f8f6f9; border-radius: 14px; padding: 1rem 1.25rem; margin-bottom: 1rem; border: 1px solid #eeedeb; }
    .recap-card-title { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #9972B0; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
    .recap-card-title i { font-size: 0.85rem; }
    .recap-row { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid rgba(0,0,0,0.04); font-size: 0.88rem; }
    .recap-row:last-child { border-bottom: none; }
    .recap-label { color: #6c757d; font-weight: 500; display: flex; align-items: center; gap: 0.35rem; }
    .recap-value { font-weight: 600; color: #1d1d1f; text-align: right; max-width: 55%; word-break: break-word; }
    .recap-badge { background: #e8f5e9; color: #2e7d4f; font-size: 0.72rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 20px; display: inline-flex; align-items: center; gap: 0.25rem; }
</style>
<div class="page-content">
    <div class="wizard-card">
        <div class="wizard-step">
            <div class="step done"><span class="num"><i class="bi bi-check" style="font-size:0.7rem;"></i></span> Type &amp; Justificatifs</div>
            <div class="connector done"></div>
            <div class="step active"><span class="num">2</span> Récapitulatif</div>
        </div>
        <hr style="margin:0.75rem 0 1.25rem;border-color:#f0eeec;">

        <?php if($errors->any()): ?>
            <div class="alert alert-danger py-2" style="font-size:.85rem;border-radius:10px;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($e); ?> <?php break; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <div class="recap-card">
            <div class="recap-card-title"><i class="bi bi-person-badge"></i> Identité</div>
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-person" style="color:#9972B0;font-size:0.8rem;"></i> Nom</span>
                <span class="recap-value"><?php echo e($user->nom); ?></span>
            </div>
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-envelope" style="color:#9972B0;font-size:0.8rem;"></i> Email</span>
                <span class="recap-value"><?php echo e($user->email); ?></span>
            </div>
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-telephone" style="color:#9972B0;font-size:0.8rem;"></i> Téléphone</span>
                <span class="recap-value"><?php echo e($user->telephone); ?></span>
            </div>

        </div>

        <div class="recap-card">
            <div class="recap-card-title"><i class="bi bi-building"></i> Organisation</div>
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-tag" style="color:#9972B0;font-size:0.8rem;"></i> Type</span>
                <span class="recap-value">
                    <?php if($data['type'] === 'universitaire'): ?> <i class="bi bi-building-columns"></i>
                    <?php elseif($data['type'] === 'particulier'): ?> <i class="bi bi-person"></i>
                    <?php else: ?> <i class="bi bi-building"></i>
                    <?php endif; ?>
                    <?php echo e(ucfirst($data['type'])); ?>

                </span>
            </div>
            <?php if($data['type'] === 'universitaire' || $data['type'] === 'organisation'): ?>
                <div class="recap-row">
                    <span class="recap-label"><i class="bi bi-house" style="color:#9972B0;font-size:0.8rem;"></i> <?php echo e($data['type'] === 'universitaire' ? 'Université' : 'Organisation'); ?></span>
                    <span class="recap-value"><?php echo e($data['organisation']); ?></span>
                </div>
                <?php if($data['type'] === 'organisation'): ?>
                <div class="recap-row">
                    <span class="recap-label"><i class="bi bi-briefcase" style="color:#9972B0;font-size:0.8rem;"></i> Structure</span>
                    <span class="recap-value">
                        <?php
                            $labels = ['entreprise' => 'Entreprise', 'association' => 'Association/ONG'];
                        ?>
                        <?php echo e($labels[$data['type_detail']] ?? ucfirst($data['type_detail'])); ?>

                    </span>
                </div>
                <?php endif; ?>
                <?php if(!empty($data['nom_representant'])): ?>
                <div class="recap-row">
                    <span class="recap-label"><i class="bi bi-person" style="color:#9972B0;font-size:0.8rem;"></i> Nom du représentant</span>
                    <span class="recap-value"><?php echo e($data['nom_representant']); ?></span>
                </div>
                <?php endif; ?>
                <?php if(!empty($data['fonction'])): ?>
                <div class="recap-row">
                    <span class="recap-label"><i class="bi bi-person-badge" style="color:#9972B0;font-size:0.8rem;"></i> Fonction du représentant</span>
                    <span class="recap-value"><?php echo e($data['fonction']); ?></span>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-file-earmark-pdf" style="color:#9972B0;font-size:0.8rem;"></i> Justificatif</span>
                <span class="recap-value"><span class="recap-badge"><i class="bi bi-check-circle-fill"></i> Fourni</span></span>
            </div>
            <?php if(!empty($data['document_cip'])): ?>
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-person-vcard" style="color:#9972B0;font-size:0.8rem;"></i> CIP représentant</span>
                <span class="recap-value"><span class="recap-badge"><i class="bi bi-check-circle-fill"></i> Fournie</span></span>
            </div>
            <?php endif; ?>
            <?php
                $cipNum = !empty($data['numero_cip']) ? $data['numero_cip'] : ($data['numero_cip_rep'] ?? '');
            ?>
            <?php if(!empty($cipNum)): ?>
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-key" style="color:#9972B0;font-size:0.8rem;"></i> N° CIP</span>
                <span class="recap-value"><?php echo e($cipNum); ?></span>
            </div>
            <?php endif; ?>
            <?php if(!empty($data['numero_rc'])): ?>
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-key" style="color:#9972B0;font-size:0.8rem;"></i> N° RC / Récépissé</span>
                <span class="recap-value"><?php echo e($data['numero_rc']); ?></span>
            </div>
            <?php endif; ?>
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-pen" style="color:#9972B0;font-size:0.8rem;"></i> Signature</span>
                <span class="recap-value"><span class="recap-badge"><i class="bi bi-check-circle-fill"></i> Fournie</span></span>
            </div>
        </div>

        <form method="POST" action="<?php echo e(route('profil.submit')); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="cgu" id="cgu" value="1" required>
                    <label class="form-check-label" for="cgu">
                        J'accepte les <a href="<?php echo e(route('cgu')); ?>" target="_blank">conditions générales d'utilisation</a>
                        et la <a href="<?php echo e(route('confidentialite')); ?>" target="_blank">politique de confidentialité</a>
                    </label>
                    <?php $__errorArgs = ['cgu'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback d-block"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100" style="background:#542680;border:none;border-radius:10px;padding:0.7rem 1rem;font-weight:600;">Soumettre pour validation</button>
            <a href="<?php echo e(route('profil.step2')); ?>" class="btn btn-secondary w-100 d-block text-center" style="border-radius:10px;padding:0.6rem 1rem;font-weight:600;margin-top:0.5rem;text-decoration:none;">Modifier les informations</a>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/admin/profil/etape3.blade.php ENDPATH**/ ?>