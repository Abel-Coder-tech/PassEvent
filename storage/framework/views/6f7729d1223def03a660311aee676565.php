<?php $__env->startSection('title', 'Resultats - PassEvent'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('accueil')); ?>">Accueil</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('tickets.recuperer')); ?>">Mon ticket</a></li>
    <li class="breadcrumb-item active" aria-current="page">Resultats</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h4 class="fw-bold mb-0">
                <i class="bi bi-ticket-perforated me-2" style="color: var(--violet);"></i>
                <?php echo e(count($tickets)); ?> billet(s) trouve(s)
            </h4>
            <a href="<?php echo e(route('tickets.recuperer')); ?>" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px;">
                <i class="bi bi-arrow-left me-1"></i> Nouvelle recherche
            </a>
        </div>

        <div class="row g-3">
            <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $isPaid = $ticket->statut_paiement === 'payé';
                ?>
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid <?php echo e($isPaid ? 'var(--vert)' : 'var(--gris)'); ?>;">
                        <div class="card-body p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                                <div>
                                    <h6 class="fw-bold mb-1"><?php echo e($ticket->evenement->titre); ?></h6>
                                    <small class="text-muted"><?php echo e($ticket->evenement->date_event->format('d M Y')); ?> - <?php echo e($ticket->evenement->lieu); ?></small>
                                </div>
                                <?php
                                    $statusBadge = match($ticket->statut_paiement) {
                                        'payé' => 'badge-publie',
                                        'en_attente' => 'badge bg-warning text-dark',
                                        'annulé', 'remboursé' => 'badge bg-danger',
                                        default => 'badge bg-secondary',
                                    };
                                ?>
                                <span class="<?php echo e($statusBadge); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $ticket->statut_paiement))); ?></span>
                            </div>

                            <hr>

                            <div class="row g-2 mb-3" style="font-size: 0.88rem;">
                                <div class="col-12">
                                    <span class="text-muted">Acheteur :</span><br>
                                    <strong><?php echo e($ticket->nom_acheteur); ?></strong>
                                    <span class="text-muted ms-3">Email :</span>
                                    <strong><?php echo e($ticket->email_acheteur); ?></strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Type :</span><br>
                                    <strong><?php echo e(ucfirst($ticket->categorie)); ?> / <?php echo e(ucfirst($ticket->type)); ?></strong>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="text-muted">Montant :</span><br>
                                    <strong style="color: var(--vert);"><?php echo e(number_format($ticket->montant, 0, ',', ' ')); ?> F</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Code :</span><br>
                                    <code class="fw-bold"><?php echo e($ticket->code_unique); ?></code>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="text-muted">Scan :</span><br>
                                    <strong><?php echo e($ticket->utilise ? 'Scanne' : 'Non utilise'); ?></strong>
                                </div>
                            </div>

                            <?php if($isPaid): ?>
                                <div class="d-flex gap-2">
                                    <a href="<?php echo e(route('tickets.telecharger', $ticket->id)); ?>" class="btn btn-violet btn-sm" style="border-radius: 6px;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Telecharger PDF
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\site\resultats.blade.php ENDPATH**/ ?>