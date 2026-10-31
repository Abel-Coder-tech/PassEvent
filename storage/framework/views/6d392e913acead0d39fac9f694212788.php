<?php $__env->startSection('title', 'Vérification — PaxEvent'); ?>
<?php $__env->startSection('step', 0); ?>
<?php $__env->startSection('page-title', 'Vérification'); ?>
<?php $__env->startSection('page-subtitle'); ?>
Un code de vérification a été envoyé à&nbsp;:
<?php $__env->stopSection(); ?>

<?php $__env->startSection('card-content'); ?>
<div class="info-email"><?php echo e($email); ?></div>
<p style="font-size:.8rem;margin-top:-.75rem;margin-bottom:1.5rem;color:#6c757d;text-align:center;">Ce code est valable 10 minutes.</p>

<?php if($errors->any()): ?>
    <div class="alert-danger">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($e); ?> <?php break; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<?php if(session('success')): ?>
    <div class="alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<form method="POST" action="<?php echo e(route('inscriptions.verify-otp')); ?>">
    <?php echo csrf_field(); ?>
    <div class="mb-3 text-start">
        <label class="form-label small fw-semibold text-muted">Code à 4 chiffres</label>
        <input type="text" name="code" class="form-control <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
               maxlength="4" inputmode="numeric" pattern="[0-9]*" required autofocus
               placeholder="••••" style="text-align:center;font-size:1.5rem;letter-spacing:6px;font-weight:700;">
        <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <button type="submit" class="btn btn-primary">Vérifier mon code</button>
</form>

<p class="resend-link" id="resend-section">
    <span id="resend-text">Renvoyer dans <strong id="timer">60</strong>s</span>
    <a id="resend-btn" class="disabled" onclick="resendCode()">Renvoyer le code</a>
</p>

<p class="back-link">
    <a href="<?php echo e(route('inscriptions.organisateur')); ?>"><i class="bi bi-arrow-left"></i> Modifier l'email</a>
</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    let seconds = 60;
    const timerEl = document.getElementById('timer');
    const textEl = document.getElementById('resend-text');
    const btnEl = document.getElementById('resend-btn');

    const interval = setInterval(() => {
        seconds--;
        timerEl.textContent = seconds;
        if (seconds <= 0) {
            clearInterval(interval);
            textEl.style.display = 'none';
            btnEl.classList.remove('disabled');
        }
    }, 1000);

    function afficherErreurResend(message) {
        const box = document.createElement('div');
        box.id = 'resend-error';
        box.className = 'alert-danger';
        box.textContent = message;
        document.querySelector('.card-register').prepend(box);
    }

    function resendCode() {
        btnEl.classList.add('disabled');
        textEl.style.display = 'inline';
        seconds = 60;
        timerEl.textContent = seconds;

        const alertBox = document.getElementById('resend-error');
        if (alertBox) alertBox.remove();

        fetch('<?php echo e(route("inscriptions.resend-otp")); ?>', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' }
        }).then(r => r.json()).then(d => {
            if (d.success) {
                location.reload();
                return;
            }
            // Erreur renvoyee par le serveur (SMTP...) : on l'affiche.
            afficherErreurResend(d.message || "Le renvoi du code a échoué.");
        }).catch(() => {
            afficherErreurResend("Le renvoi du code a échoué : aucune réponse du serveur.");
        });

        const interval2 = setInterval(() => {
            seconds--;
            timerEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(interval2);
                textEl.style.display = 'none';
                btnEl.classList.remove('disabled');
            }
        }, 1000);
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('auth.register.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/auth/register/verify.blade.php ENDPATH**/ ?>