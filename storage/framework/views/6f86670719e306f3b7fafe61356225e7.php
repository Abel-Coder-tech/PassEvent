<?php $__env->startSection('title', 'Nouvel agent - PaxEvent'); ?>

<?php $__env->startSection('page-title', 'Créer un agent de scan'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Tableau de bord</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.agents.index')); ?>">Agents</a></li>
    <li class="breadcrumb-item active" aria-current="page">Nouvel agent</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-person-plus me-2" style="color: var(--violet);"></i>Informations de l'agent</h5>
                </div>
                <div class="panel-card-body">
                    <form method="POST" action="<?php echo e(route('admin.agents.store')); ?>">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" name="nom" class="form-control <?php $__errorArgs = ['nom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('nom')); ?>" required placeholder="Nom de l'agent">
                            <?php $__errorArgs = ['nom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('email')); ?>" required placeholder="agent@exemple.com">
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <div class="position-relative">
                                <input type="password" name="password" id="agent_password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required placeholder="Min. 8 caractères" minlength="8" oninput="checkPasswordStrengthAgent(this.value)">
                                <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password" style="right: 10px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5;">
                                    <i class="bi bi-eye" style="color: #9a9a9a;"></i>
                                </button>
                            </div>
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <div id="passwordMeterAgent" class="mt-2" style="display:none;">
                                <div style="height:3px;background:#e9ecef;border-radius:2px;overflow:hidden;">
                                    <div id="passwordBarAgent" style="height:100%;width:0;border-radius:2px;transition:width .3s,background .3s;"></div>
                                </div>
                                <div id="passwordLabelAgent" class="mt-1" style="font-size:0.78rem;font-weight:600;"></div>
                            </div>
                            <div class="form-text">L'agent utilisera ce mot de passe pour se connecter.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Événement assigné</label>
                            <select name="evenement_id" class="form-select <?php $__errorArgs = ['evenement_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                <option value="">-- Sélectionner un événement --</option>
                                <?php $__currentLoopData = $evenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($ev->id); ?>" <?php if(old('evenement_id') == $ev->id): echo 'selected'; endif; ?>>
                                        <?php echo e($ev->titre); ?> — <?php echo e($ev->date_event ? $ev->date_event->format('d/m/Y') : ''); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['evenement_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <div class="form-text">L'agent sera affecté à cet événement pour le scan des tickets.</div>
                        </div>

                        <div class="alert alert-info py-2" style="font-size:0.85rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Un code d'accès à 6 chiffres sera généré automatiquement. L'agent recevra un email avec ses identifiants et les détails de l'événement.
                        </div>

                        <button type="submit" class="btn text-white w-100 py-2" style="background: #7c3aed;">
                            <i class="bi bi-person-plus me-1"></i> Créer l'agent de scan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input');
        if (!input) return;
        const icon = this.querySelector('i');
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });
});

function checkPasswordStrengthAgent(pwd) {
    var meter = document.getElementById('passwordMeterAgent');
    var bar = document.getElementById('passwordBarAgent');
    var label = document.getElementById('passwordLabelAgent');
    if (!pwd) { meter.style.display = 'none'; return; }
    meter.style.display = 'block';
    var score = 0;
    if (pwd.length >= 8) score += 25;
    if (pwd.length >= 12) score += 15;
    if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score += 20;
    if (/\d/.test(pwd)) score += 20;
    if (/[^a-zA-Z0-9]/.test(pwd)) score += 20;
    if (pwd.length > 15) score += 10;
    score = Math.min(100, score);
    var color, text, icon;
    if (score < 40) { color = '#dc3545'; text = 'Faible'; icon = 'bi-exclamation-triangle-fill'; }
    else if (score < 70) { color = '#f59e0b'; text = 'Moyen'; icon = 'bi-shield-exclamation'; }
    else { color = '#10b981'; text = 'Fort'; icon = 'bi-shield-fill-check'; }
    bar.style.width = score + '%';
    bar.style.background = color;
    label.innerHTML = '<i class="bi ' + icon + '" style="color:' + color + '"></i> <span style="color:' + color + '">' + text + '</span>';
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/admin/agents/create.blade.php ENDPATH**/ ?>