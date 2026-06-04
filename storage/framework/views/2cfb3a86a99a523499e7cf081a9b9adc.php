<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser — PassEvent</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f7;
            padding: 1rem;
        }
        .card {
            width: 100%;
            max-width: 440px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            background: #fff;
        }
        .header {
            background: linear-gradient(135deg, #5a3d5e, #3d2a40);
            color: #fff;
            padding: 2rem;
            text-align: center;
        }
        .header h1 { font-size: 1.3rem; font-weight: 800; margin: 0; }
        .header p { font-size: 0.85rem; opacity: 0.85; margin: 6px 0 0; }
        .body { padding: 2rem; }
        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.7rem 1rem 0.7rem 2.5rem;
            font-size: 0.9rem;
        }
        .form-control:focus {
            border-color: #87428b;
            box-shadow: 0 0 0 3px rgba(135,66,139,0.12);
        }
        .input-group-custom { position: relative; }
        .input-group-custom .icon {
            position: absolute; left: 0.8rem; top: 50%;
            transform: translateY(-50%);
            color: #9a9a9a; font-size: 1rem; z-index: 5;
        }
        .btn-submit {
            background: #6d3570; border: none; border-radius: 8px;
            color: #fff; font-weight: 700; font-size: 0.9rem;
            padding: 0.7rem; width: 100%;
            transition: background 0.2s;
        }
        .btn-submit:hover { background: #5a2d5d; }
        .back-link { color: #87428b; font-size: 0.85rem; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>PassEvent</h1>
            <p>Nouveau mot de passe</p>
        </div>
        <div class="body">
            <form method="POST" action="<?php echo e(route('password.update')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="token" value="<?php echo e($token); ?>">
                <div class="mb-2">
                    <label class="form-label" style="font-size:0.82rem; font-weight:600;">Email</label>
                    <div class="input-group-custom">
                        <i class="bi bi-envelope icon"></i>
                        <input type="email" name="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Votre email" value="<?php echo e(old('email')); ?>" required>
                    </div>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger mt-1" style="font-size:0.8rem;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:0.82rem; font-weight:600;">Nouveau mot de passe</label>
                    <div class="input-group-custom">
                        <i class="bi bi-lock icon"></i>
                        <input type="password" name="mot_de_passe" class="form-control <?php $__errorArgs = ['mot_de_passe'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Minimum 8 caractères" required>
                    </div>
                    <?php $__errorArgs = ['mot_de_passe'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger mt-1" style="font-size:0.8rem;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:0.82rem; font-weight:600;">Confirmer le mot de passe</label>
                    <div class="input-group-custom">
                        <i class="bi bi-lock icon"></i>
                        <input type="password" name="mot_de_passe_confirmation" class="form-control" placeholder="Répétez le mot de passe" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit">Réinitialiser</button>
            </form>

            <p class="text-center mt-3">
                <a href="<?php echo e(route('login')); ?>" class="back-link">Retour à la connexion</a>
            </p>
        </div>
    </div>
</body>
</html><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\auth\reset.blade.php ENDPATH**/ ?>