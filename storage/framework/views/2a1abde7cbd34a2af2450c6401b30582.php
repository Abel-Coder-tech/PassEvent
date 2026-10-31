<?php $__env->startSection('title', 'Récupérer mon ticket — PaxEvent'); ?>
<?php $__env->startSection('description', 'Vous avez perdu votre billet ? Récupérez votre ticket PaxEvent en quelques clics.'); ?>
<?php $__env->startSection('og_title', 'Récupérer mon ticket — PaxEvent'); ?>
<?php $__env->startSection('og_description', 'Retrouvez votre billet d\'événement PaxEvent facilement.'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('accueil')); ?>">Accueil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Récupérer mon ticket</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4 text-center">
                        <div class="mb-4">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px; background: rgba(135,66,139,0.08);">
                                <i class="bi bi-ticket-perforated" style="font-size: 1.8rem; color: var(--violet);"></i>
                            </div>
                            <h4 class="fw-bold mb-2">Récupérer mon ticket</h4>
                            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                Entrez votre ID de transaction : vos billets seront renvoyés à l'adresse email de votre choix.
                            </p>
                        </div>

                        <form action="<?php echo e(route('tickets.rechercher')); ?>" method="POST">
                            <?php echo csrf_field(); ?>

                            <div class="mb-3 text-start">
                                <label for="transaction_id" class="form-label fw-bold">ID de transaction</label>
                                <input type="text" class="form-control <?php $__errorArgs = ['transaction_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="transaction_id" name="transaction_id" placeholder="ex : 2847331" value="<?php echo e(old('transaction_id')); ?>" required>
                                <small class="text-muted">Il figure sur le SMS ou le reçu de paiement FedaPay.</small>
                                <?php $__errorArgs = ['transaction_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger mt-1" style="font-size:0.82rem;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="mb-4 text-start">
                                <label for="email" class="form-label fw-bold">Email de récupération</label>
                                <input type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="email" name="email" placeholder="vous@exemple.com" value="<?php echo e(old('email')); ?>" required>
                                <small class="text-muted">Vous recevrez vos billets à cette adresse. Elle peut être différente de l'email utilisé lors de l'achat.</small>
                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger mt-1" style="font-size:0.82rem;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <button type="submit" class="btn btn-violet w-100 py-3" style="border-radius: 8px;">
                                <i class="bi bi-envelope-arrow-up me-2"></i> Renvoyer mes billets
                            </button>
                        </form>

                        <div class="alert text-start mt-4 mb-0" style="font-size: 0.82rem; background: rgba(254,213,20,0.14); color: #7a5b00; border: 1px solid rgba(254,213,20,0.5); border-radius: 8px;">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>Important :</strong> les tickets gratuits (0 FCFA) ne sont pas récupérables en cas de perte. Conservez précieusement l'email contenant votre e-ticket.
                        </div>

                        <div class="mt-4">
                            <a href="<?php echo e(route('accueil')); ?>" class="text-decoration-underline" style="color: var(--violet); font-size: 0.88rem;">
                                <i class="bi bi-arrow-left me-1"></i> Retour a l'accueil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/site/recuperer.blade.php ENDPATH**/ ?>