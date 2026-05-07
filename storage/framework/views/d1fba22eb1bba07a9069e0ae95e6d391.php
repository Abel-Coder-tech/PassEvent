<?php $__env->startSection('title', 'Connexion — PassEvent'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex flex-column flex-md-row min-vh-100">
    <!-- Left Panel -->
    <div class="col-md-6 d-none d-md-flex flex-column justify-content-center align-items-center p-5 position-relative overflow-hidden" style="background: var(--blanc);">
        <!-- Illustration Area -->
        <div class="mb-4 position-relative" style="max-width: 420px;">
            <svg viewBox="0 0 400 350" xmlns="http://www.w3.org/2000/svg" class="w-100" style="max-height: 380px;">
                <!-- Background circle -->
                <circle cx="200" cy="175" r="140" fill="rgba(135,66,139,0.08)"/>

                <!-- Desk -->
                <rect x="80" y="220" width="240" height="8" rx="4" fill="var(--teal)"/>
                <rect x="100" y="228" width="12" height="80" rx="3" fill="var(--teal)" opacity="0.7"/>
                <rect x="288" y="228" width="12" height="80" rx="3" fill="var(--teal)" opacity="0.7"/>

                <!-- Chair -->
                <ellipse cx="200" cy="180" rx="45" ry="50" fill="var(--violet)" opacity="0.15"/>
                <rect x="165" y="140" width="70" height="80" rx="12" fill="var(--violet)" opacity="0.2"/>

                <!-- Person - Body -->
                <ellipse cx="200" cy="170" rx="30" ry="40" fill="var(--teal)" opacity="0.8"/>

                <!-- Person - Head -->
                <circle cx="200" cy="115" r="25" fill="var(--aubergine)" opacity="0.6"/>

                <!-- Tablet -->
                <rect x="160" y="155" width="50" height="35" rx="4" fill="var(--violet)" opacity="0.7"/>
                <rect x="163" y="158" width="44" height="28" rx="2" fill="var(--blanc)" opacity="0.9"/>

                <!-- Coffee cup -->
                <rect x="290" y="195" width="20" height="25" rx="3" fill="var(--menthe)"/>
                <path d="M310 200 Q318 200 318 210 Q318 220 310 220" fill="none" stroke="var(--teal)" stroke-width="2" opacity="0.6"/>

                <!-- Plant -->
                <rect x="90" y="195" width="16" height="25" rx="3" fill="var(--teal)" opacity="0.5"/>
                <circle cx="98" cy="185" r="12" fill="var(--vert)" opacity="0.6"/>
                <circle cx="90" cy="175" r="10" fill="var(--menthe)" opacity="0.5"/>
                <circle cx="106" cy="178" r="9" fill="var(--vert)" opacity="0.4"/>

                <!-- Lamp -->
                <rect x="125" y="180" width="6" height="40" rx="2" fill="var(--gris)" opacity="0.5"/>
                <ellipse cx="128" cy="175" rx="15" ry="8" fill="var(--menthe)" opacity="0.7"/>

                <!-- Decorative dots -->
                <circle cx="310" cy="130" r="6" fill="var(--violet)" opacity="0.2"/>
                <circle cx="330" cy="160" r="4" fill="var(--vert)" opacity="0.3"/>
                <circle cx="100" cy="140" r="5" fill="var(--teal)" opacity="0.25"/>
                <circle cx="340" cy="250" r="7" fill="var(--menthe)" opacity="0.2"/>
            </svg>
        </div>

        <!-- Description text -->
        <p class="text-center text-muted" style="font-size: 0.95rem; max-width: 320px;">
            Simplifiez la gestion de vos événements et offrez une expérience d'achat fluide à vos participants.
        </p>
    </div>

    <!-- Right Panel -->
    <div class="col-12 col-md-6 d-flex align-items-center justify-content-center py-5" style="background: var(--blanc);">
        <div style="width: 100%; max-width: 400px;" class="px-3 px-md-4">
            <!-- Mobile Logo -->
            <div class="d-md-none text-center mb-4">
                <div class="d-inline-flex align-items-center gap-2 mb-2">
                    <div style="width: 32px; height: 32px; background: var(--vert); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 1rem; position: relative;">
                        P
                        <span style="position: absolute; width: 6px; height: 6px; background: var(--sombre); border-radius: 50%; left: -3px; top: 50%; transform: translateY(-50%);"></span>
                        <span style="position: absolute; width: 6px; height: 6px; background: var(--sombre); border-radius: 50%; right: -3px; top: 50%; transform: translateY(-50%);"></span>
                    </div>
                    <span style="color: var(--menthe); font-weight: 700; font-size: 1.1rem;">Pass</span><span style="color: var(--sombre); font-weight: 700; font-size: 1.1rem;">Event</span>
                </div>
            </div>

            <!-- Header -->
            <div class="mb-4 mb-md-5">
                <h1 class="fw-bold mb-1 d-none d-md-block" style="font-size: 2rem; color: var(--violet-dark);">
                    PassEvent
                </h1>
                <p class="text-muted mb-0" style="font-size: 1.1rem; color: var(--sombre);">
                    Connexion Admin
                </p>
            </div>

            <!-- Form -->
            <form method="POST" action="<?php echo e(route('login.post')); ?>">
                <?php echo csrf_field(); ?>

                <!-- Email -->
                <div class="mb-3">
                    <div class="position-relative">
                        <i class="bi bi-envelope position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--gris); font-size: 1.1rem;"></i>
                        <input type="email" class="form-control ps-5 py-3 border rounded-3 <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="email" name="email" placeholder="Email" value="<?php echo e(old('email')); ?>" required autofocus style="border-color: #e2e0e4;">
                    </div>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="text-danger mt-1" style="font-size: 0.85rem;"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <div class="position-relative">
                        <i class="bi bi-lock position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--gris); font-size: 1.1rem;"></i>
                        <input type="password" class="form-control ps-5 pe-5 py-3 border rounded-3 <?php $__errorArgs = ['mot_de_passe'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="mot_de_passe" name="mot_de_passe" placeholder="Mot de passe" required style="border-color: #e2e0e4;">
                        <button type="button" class="btn position-absolute border-0 bg-transparent" id="togglePassword" style="right: 10px; top: 50%; transform: translateY(-50%); padding: 4px;">
                            <i class="bi bi-eye" id="toggleIcon" style="color: var(--gris); font-size: 1.1rem;"></i>
                        </button>
                    </div>
                    <?php $__errorArgs = ['mot_de_passe'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="text-danger mt-1" style="font-size: 0.85rem;"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Options row -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" style="border-color: var(--gris);">
                        <label class="form-check-label text-muted" for="remember" style="font-size: 0.9rem;">Se souvenir de moi</label>
                    </div>
                    <a href="#" class="text-decoration-underline" style="color: var(--violet); font-size: 0.9rem;">Mot de passe oublié ?</a>
                </div>

                <!-- Submit button -->
                <button type="submit" class="btn w-100 py-3 fw-bold text-white" style="background: var(--violet-dark); border: none; border-radius: 8px; transition: background 0.2s ease;">
                    Se connecter
                </button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('mot_de_passe');
    const toggleIcon = document.getElementById('toggleIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views/auth/login.blade.php ENDPATH**/ ?>