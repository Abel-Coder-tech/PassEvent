<?php $__env->startSection('title', 'Modifier l\'agent de vente'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-3">
    <div class="mb-3">
        <a href="<?php echo e(route('admin.agents-vente.show', $agentVente)); ?>" class="text-decoration-none small">
            <i class="bi bi-arrow-left"></i> Retour à l'agent
        </a>
        <h5 class="fw-bold mt-2">
            <i class="bi bi-<?php echo e($reaffectable ? 'arrow-repeat' : 'pencil-square'); ?>"></i>
            <?php echo e($reaffectable ? 'Réaffecter l\'agent de vente' : 'Modifier l\'agent de vente'); ?>

        </h5>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="<?php echo e(route('admin.agents-vente.update', $agentVente)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="mb-3">
                            <label class="form-label small fw-medium">Nom complet</label>
                            <input type="text" name="nom" class="form-control <?php $__errorArgs = ['nom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                value="<?php echo e(old('nom', $agentVente->nom)); ?>" required>
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
                            <label class="form-label small fw-medium">Email</label>
                            <input type="email" name="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                value="<?php echo e(old('email', $agentVente->email)); ?>" required>
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <div class="form-text">Un email ne peut pas servir à la fois pour un agent de scan et un agent de vente.</div>
                        </div>

                        <?php if($reaffectable): ?>
                        <?php
                            $selection = old('evenement_id', $agentVente->evenement_id);
                            $autresEvenements = $evenements->reject(fn ($ev) => $ev->id === $agentVente->evenement_id)->values();
                        ?>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Événement</label>
                            <select name="evenement_id" class="form-select <?php $__errorArgs = ['evenement_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                <option value="<?php echo e($agentVente->evenement_id); ?>" <?php if($selection == $agentVente->evenement_id): echo 'selected'; endif; ?>>
                                    <?php echo e($agentVente->evenement->titre); ?> — événement actuel
                                </option>
                                <?php $__currentLoopData = $autresEvenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($ev->id); ?>" <?php if($selection == $ev->id): echo 'selected'; endif; ?>>
                                    <?php echo e($ev->titre); ?> — <?php echo e($ev->date_event ? $ev->date_event->format('d/m/Y') : 'Date libre'); ?>

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
                            <div class="form-text">L'agent conservera l'historique de ses ventes, désormais visible sur tous ses événements.</div>
                        </div>

                        <div class="alert alert-info py-2 small">
                            <i class="bi bi-info-circle me-1"></i>
                            Un agent ne peut être réaffecté que s'il est inactif ou si son événement est passé, annulé ou en brouillon.
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="evenement_id" value="<?php echo e($agentVente->evenement_id); ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Événement</label>
                            <div class="form-control d-flex align-items-center justify-content-between" style="background:#f8f9fa;">
                                <span class="small"><?php echo e($agentVente->evenement->titre); ?></span>
                                <span class="badge bg-secondary">Affectation actuelle</span>
                            </div>
                            <div class="form-text">
                                Cet agent est actif sur un événement à venir : désactivez-le pour pouvoir le réaffecter à un autre événement.
                            </div>
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="btn text-white w-100 py-2" style="background: #7c3aed;">
                            <i class="bi bi-check-lg me-1"></i> Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/admin/agents-vente/edit.blade.php ENDPATH**/ ?>