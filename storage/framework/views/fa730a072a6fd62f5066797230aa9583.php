<?php $__env->startSection('title', 'Inscription — PaxEvent'); ?>
<?php $__env->startSection('step', 0); ?>
<?php $__env->startSection('page-title', 'Créer un compte organisateur'); ?>
<?php $__env->startSection('page-subtitle', 'Entrez votre adresse email pour commencer'); ?>

<?php $__env->startSection('card-content'); ?>
<?php if($errors->any()): ?>
    <div class="alert-danger">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($e); ?> <?php break; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<?php if(config('services.google.client_id')): ?>
<a href="<?php echo e(route('google.redirect')); ?>" class="btn-google w-100 mb-3">
    <i class="bi bi-google me-2"></i> S'inscrire avec Google
</a>
<div class="divider mb-3">
    <span style="background:#fff;padding:0 12px;color:#6c757d;font-size:.85rem;">ou</span>
</div>
<?php endif; ?>

<form method="POST" action="<?php echo e(route('inscriptions.send-otp')); ?>">
    <?php echo csrf_field(); ?>
    <div class="mb-3 text-start">
        <label class="form-label small fw-semibold text-muted">Adresse email</label>
        <input type="email" name="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
               value="<?php echo e(old('email')); ?>" required autofocus placeholder="exemple@email.com">
        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <button type="submit" class="btn btn-primary">Recevoir mon code</button>
</form>

<p class="login-link" style="text-align:center;">
    Déjà un compte ? <a href="<?php echo e(route('login')); ?>">Connectez-vous</a>
</p>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('auth.register.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/auth/register/step0.blade.php ENDPATH**/ ?>