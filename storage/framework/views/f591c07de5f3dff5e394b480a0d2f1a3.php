<?php $__env->startSection('title', 'Paiement confirmé — PaxEvent'); ?>
<?php $__env->startSection('description', 'Votre paiement a été confirmé. Votre ticket vous sera envoyé par email.'); ?>

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
                                <i class="bi bi-check-circle-fill" style="font-size: 2.5rem; color: var(--violet);"></i>
                            </div>
                            <?php $quantite = $groupTickets->count(); $textes = $ticket->evenement->getTextes(); ?>
                            <h4 class="fw-bold mb-2"><?php echo e($ticket->montant <= 0 ? ($textes['type'] === 'spectacle' ? 'Réservation confirmée !' : 'Inscription confirmée !') : 'Paiement confirmé !'); ?></h4>
                            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                <?php echo e($quantite > 1 ? "Vos {$quantite} " . mb_strtolower($textes['billet_pluriel']) . ' ont été générés avec succès.' : 'Votre ' . mb_strtolower($textes['billet']) . ' a été réservé avec succès.'); ?><br>
                                <?php echo e($quantite > 1 ? 'Ils ont été envoyés par email à' : 'Il a été envoyé par email à'); ?> <strong><?php echo e($ticket->email_acheteur); ?></strong>
                            </p>
                        </div>

                        <!-- Ticket details -->
                        <div class="p-3 rounded mb-4 text-start" style="background: var(--blanc-casse);">
                            <div class="row g-2" style="font-size: 0.88rem;">
                                <div class="col-6">
                                    <span class="text-muted">Evenement</span><br>
                                    <strong><?php echo e($ticket->evenement->titre); ?></strong>
                                </div>
                                <?php if($ticket->montant > 0): ?>
                                <div class="col-6">
                                    <span class="text-muted">Type</span><br>
                                    <strong><?php echo e($ticket->nom_tarif); ?></strong>
                                </div>
                                <?php endif; ?>
                                <div class="col-6">
                                    <span class="text-muted">Date</span><br>
                                    <strong><?php echo e($ticket->evenement->date_event->format('d/m/Y')); ?></strong>
                                </div>
                                <?php if($ticket->montant > 0): ?>
                                <div class="col-6">
                                    <span class="text-muted">Total paye</span><br>
                                    <?php if($quantite > 1): ?>
                                        <strong style="color: var(--violet);"><?php echo e(number_format($groupTickets->sum('montant'), 0, ',', ' ')); ?> F</strong>
                                        <small class="d-block text-muted" style="font-size: 0.72rem;"><?php echo e(number_format($ticket->montant, 0, ',', ' ')); ?> F × <?php echo e($quantite); ?></small>
                                    <?php else: ?>
                                        <strong style="color: var(--violet);"><?php echo e(number_format($ticket->montant, 0, ',', ' ')); ?> F</strong>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if($quantite > 1): ?>
                            <div class="mt-3 pt-2" style="border-top: 1px solid #eee;">
                                <span class="text-muted" style="font-size: 0.82rem;"><?php echo e($textes['billet_pluriel']); ?> (<?php echo e($quantite); ?>) :</span><br>
                                <?php $__currentLoopData = $groupTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span style="font-size: 0.78rem; display: block; margin-top: 2px;"><?php echo e($textes['billet']); ?> n&deg;<?php echo e($loop->iteration); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2">
                            <?php if($quantite > 1): ?>
                                <a href="<?php echo e(\Illuminate\Support\Facades\URL::signedRoute('tickets.telecharger-tous', ['ticket' => $ticket->id])); ?>" class="btn btn-violet py-3" style="border-radius: 8px;">
                                    <i class="bi bi-file-earmark-zip me-1"></i> Telecharger tous mes <?php echo e($textes['billet_pluriel']); ?> (ZIP)
                                </a>
                                <hr class="my-1">
                                <?php $__currentLoopData = $groupTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e(\Illuminate\Support\Facades\URL::signedRoute('tickets.telecharger', ['ticket' => $gt->id])); ?>" class="btn btn-outline-secondary py-2" style="border-radius: 8px;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Telecharger <?php echo e(mb_strtolower($textes['billet'])); ?> <?php echo e($loop->iteration); ?>

                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <a href="<?php echo e(\Illuminate\Support\Facades\URL::signedRoute('tickets.telecharger', ['ticket' => $ticket->id])); ?>" class="btn btn-violet py-3" style="border-radius: 8px;">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Telecharger <?php echo e(mb_strtolower($textes['billet'])); ?> PDF
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo e(route('accueil')); ?>" class="btn btn-accent py-2" style="border-radius: 8px;">
                                <i class="bi bi-house me-1"></i> Retour a l'accueil
                            </a>
                            <a href="<?php echo e(route('evenements.public')); ?>" class="btn btn-outline-secondary py-2" style="border-radius: 8px;">
                                <i class="bi bi-calendar-event me-1"></i> Voir d'autres evenements
                            </a>
                        </div>

                        <p class="text-muted mt-3 mb-0" style="font-size: 0.78rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Presentez votre <?php echo e(mb_strtolower($textes['billet'])); ?> (impression ou ecran) a l'entrée de l'evenement
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/evenement-public/confirmation.blade.php ENDPATH**/ ?>