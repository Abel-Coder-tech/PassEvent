<?php $__env->startSection('title', 'Paiement confirme - PassEvent'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('accueil')); ?>">Accueil</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('evenements.public')); ?>">Evenements</a></li>
    <li class="breadcrumb-item active" aria-current="page">Confirmation</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4 text-center">
                        <div class="mb-4">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; background: rgba(18,151,110,0.1);">
                                <i class="bi bi-check-circle-fill" style="font-size: 2.5rem; color: var(--vert);"></i>
                            </div>
                            <h4 class="fw-bold mb-2"><?php echo e($ticket->montant <= 0 ? 'Inscription confirmee !' : 'Paiement confirme !'); ?></h4>
                            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                <?php echo e($ticket->montant <= 0 ? 'Votre participation a ete enregistree avec succes.' : 'Votre billet a ete genere avec succes.'); ?><br>
                                Il a ete envoye par email a <strong><?php echo e($ticket->email_acheteur); ?></strong>
                            </p>
                        </div>

                        <!-- Ticket details -->
                        <div class="p-3 rounded mb-4 text-start" style="background: var(--blanc-casse);">
                            <div class="row g-2" style="font-size: 0.88rem;">
                                <div class="col-12">
                                    <span class="text-muted">Evenement :</span><br>
                                    <strong><?php echo e($ticket->evenement->titre); ?></strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Date :</span><br>
                                    <strong><?php echo e($ticket->evenement->date_event->format('d/m/Y')); ?></strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Type :</span><br>
                                    <strong><?php echo e(ucfirst($ticket->categorie)); ?> / <?php echo e(ucfirst($ticket->type)); ?></strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Code :</span><br>
                                    <code class="fw-bold"><?php echo e($ticket->code_unique); ?></code>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="text-muted">Montant :</span><br>
                                    <strong style="color: var(--vert);"><?php echo e(number_format($ticket->montant, 0, ',', ' ')); ?> F</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2">
                            <a href="<?php echo e(route('tickets.telecharger', $ticket->id)); ?>" class="btn btn-violet py-3" style="border-radius: 8px;">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Telecharger mon billet PDF
                            </a>
                            <a href="<?php echo e(route('accueil')); ?>" class="btn btn-vert py-2" style="border-radius: 8px;">
                                <i class="bi bi-house me-1"></i> Retour a l'accueil
                            </a>
                            <a href="<?php echo e(route('evenements.public')); ?>" class="btn btn-outline-secondary py-2" style="border-radius: 8px;">
                                <i class="bi bi-calendar-event me-1"></i> Voir d'autres evenements
                            </a>
                        </div>

                        <p class="text-muted mt-3 mb-0" style="font-size: 0.78rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Presentez votre billet (impression ou ecran) a l'entree de l'evenement
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views/evenement-public/confirmation.blade.php ENDPATH**/ ?>