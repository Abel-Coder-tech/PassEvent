<?php $__env->startSection('title', 'Connexion — PaxEvent'); ?>
<?php $__env->startSection('page-title', 'Connexion'); ?>
<?php $__env->startSection('page-subtitle', 'Accédez à votre tableau de bord'); ?>

<?php $__env->startSection('card-content'); ?>
<?php if(session('success')): ?>
    <div class="success-icon text-center"><i class="bi bi-check-circle-fill"></i></div>
    <h2 style="font-size:1.3rem;font-weight:800;color:#211C31;text-align:center;margin-bottom:0.5rem;">Inscription réussie !</h2>
    <p style="text-align:center;color:#6c757d;font-size:.9rem;line-height:1.5;">Votre compte a été créé avec succès.<br>Vous recevrez sous peu un email de confirmation une fois votre compte validé par PaxEvent.</p>
    <div style="margin-top:1.5rem;display:flex;flex-direction:column;gap:0.6rem;">
        <a href="<?php echo e(route('evenements.public')); ?>" class="btn-primary" style="text-align:center;text-decoration:none;display:block;">
            <i class="bi bi-calendar-event me-1"></i> Explorer les événements
        </a>
        <a href="<?php echo e(route('login')); ?>" class="btn-secondary" style="text-align:center;margin-top:0;">
            <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
        </a>
    </div>
<?php else: ?>
    <?php if(config('services.google.client_id')): ?>
        <a href="<?php echo e(route('google.redirect')); ?>" class="btn-google w-100 mb-3">
            <i class="bi bi-google" style="color:#4285F4;font-size:1.1rem;"></i> Se connecter avec Google
        </a>
        <div class="divider mb-3">
            <span style="background:#fff;padding:0 12px;color:#6c757d;font-size:.85rem;">ou</span>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('login.post')); ?>">
        <?php echo csrf_field(); ?>
        <div class="mb-3 text-start">
            <label class="form-label">Email</label>
            <input type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="email"
                   placeholder="Entrez votre email" value="<?php echo e(old('email')); ?>" required autofocus>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="mb-2 text-start">
            <label class="form-label">Mot de passe</label>
            <div style="position:relative;">
                <input type="password" class="form-control <?php $__errorArgs = ['mot_de_passe'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       id="mot_de_passe" name="mot_de_passe" placeholder="Entrez votre mot de passe" required>
                <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password"
                        style="right:4px;top:50%;transform:translateY(-50%);padding:4px;z-index:5;">
                    <i class="bi bi-eye" style="color:#9a9a9a;"></i>
                </button>
            </div>
            <?php $__errorArgs = ['mot_de_passe'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?php echo e(route('password.request')); ?>" style="color:#542680;font-size:0.82rem;font-weight:600;">Mot de passe oublié ?</a>
        </div>
        <button type="submit" class="btn-primary" style="margin-top:0;">Se connecter</button>
    </form>
    <p class="text-center mt-3" style="font-size:0.85rem;color:#6c757d;">
        Pas encore de compte ? <a href="<?php echo e(route('inscriptions.organisateur')); ?>" style="color:#542680;font-weight:600;text-decoration:underline;">Créer un compte</a>
    </p>
<?php endif; ?>
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
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('auth.register.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/auth/login.blade.php ENDPATH**/ ?>