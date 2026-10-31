<?php $__env->startSection('title', 'Tableau de bord - Équipe'); ?>
<?php $__env->startSection('page-title', 'Mon tableau de bord'); ?>

<?php $__env->startSection('content'); ?>

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" style="border-radius: 10px; font-size: 0.85rem;">
        <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h4 class="mb-1 fw-bold">Bonjour <?php echo e(auth('superadmin')->user()->prenom); ?> <?php echo e(auth('superadmin')->user()->nom); ?> </h4>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">Voici votre périmètre. Toute section hors périmètre est bloquée.</p>
    </div>
    <div>
        <?php $roleDefEquipe = \App\Models\User::ROLES_EQUIPE[auth('superadmin')->user()->roleEquipe() ?? ''] ?? null; ?>
        <?php if($roleDefEquipe): ?>
            <span class="sa-badge sa-badge-secondary"><i class="bi <?php echo e($roleDefEquipe['icone']); ?> me-1"></i><?php echo e($roleDefEquipe['libelle']); ?></span>
        <?php else: ?>
            <span class="sa-badge sa-badge-danger">Aucun rôle attribué</span>
        <?php endif; ?>
    </div>
</div>

<?php if(!$roleDefEquipe): ?>
    <div class="sa-card">
        <div class="sa-card-body text-center py-5">
            <i class="bi bi-person-x-fill" style="font-size: 2.5rem; color: #adb5bd;"></i>
            <p class="text-muted mt-3 mb-0" style="font-size: 0.9rem;">
                Aucun rôle ne vous a encore été attribué.<br>
                Contactez l'administrateur pour définir votre périmètre.
            </p>
        </div>
    </div>
<?php else: ?>
<div class="row g-3">

    <?php if($nbOrganisateursEnAttente > 0): ?>
    <div class="col-lg-6">
        <div class="sa-card h-100">
            <div class="sa-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person-check-fill me-2" style="color: var(--sa-primary);"></i>Comptes organisateurs à valider</span>
                <a href="<?php echo e(route('equipe.organisateurs')); ?>" class="sa-badge sa-badge-amber text-decoration-none">
                    <?php echo e($nbOrganisateursEnAttente); ?> en attente
                </a>
            </div>
            <div class="sa-card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php $__currentLoopData = $organisateursEnAttente; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $org): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size: 0.83rem; border-color: rgba(255,255,255,0.06);">
                            <div>
                                <strong><?php echo e($org->organisation ?: ($org->prenom ? $org->prenom.' '.$org->nom : $org->nom)); ?></strong><br>
                                <small class="text-muted"><?php echo e($org->email); ?></small>
                            </div>
                            <small class="text-muted"><?php echo e($org->created_at->diffForHumans()); ?></small>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <div class="text-center py-2">
                    <a href="<?php echo e(route('equipe.organisateurs')); ?>" style="font-size: 0.78rem; color: var(--sa-primary); text-decoration: none;">Voir tous les organisateurs →</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($nbRetraitsEnAttente > 0): ?>
    <div class="col-lg-6">
        <div class="sa-card h-100">
            <div class="sa-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cash-coin me-2" style="color: var(--sa-primary);"></i>Demandes de retrait à traiter</span>
                <a href="<?php echo e(route('equipe.retraits')); ?>" class="sa-badge sa-badge-amber text-decoration-none">
                    <?php echo e($nbRetraitsEnAttente); ?> en attente
                </a>
            </div>
            <div class="sa-card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php $__currentLoopData = $retraitsEnAttente; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $retrait): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size: 0.83rem; border-color: rgba(255,255,255,0.06);">
                            <div>
                                <strong><?php echo e($retrait->nom ?? optional($retrait->user)->nom); ?></strong><br>
                                <small class="text-muted"><?php echo e($retrait->reseau ?? ''); ?> · <?php echo e($retrait->mobile ?? ''); ?></small>
                            </div>
                            <strong><?php echo e(number_format((float) $retrait->montant, 0, ',', ' ')); ?> F</strong>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <div class="text-center py-2">
                    <a href="<?php echo e(route('equipe.retraits')); ?>" style="font-size: 0.78rem; color: var(--sa-primary); text-decoration: none;">Toutes les demandes →</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($nbMessagesNonLus > 0): ?>
    <div class="col-lg-6">
        <div class="sa-card h-100">
            <div class="sa-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-headset me-2" style="color: var(--sa-primary);"></i>Messages à traiter (Support)</span>
                <a href="<?php echo e(route('equipe.notifications')); ?>" class="sa-badge sa-badge-secondary text-decoration-none">
                    <?php echo e($nbMessagesNonLus); ?> non lus
                </a>
            </div>
            <div class="sa-card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php $__currentLoopData = $messagesNonLus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="list-group-item" style="font-size: 0.83rem; border-color: rgba(255,255,255,0.06);">
                            <div class="d-flex justify-content-between">
                                <strong><?php echo e($message->objet ?? 'Sans objet'); ?></strong>
                                <small class="text-muted"><?php echo e($message->created_at->diffForHumans()); ?></small>
                            </div>
                            <small class="text-muted"><?php echo e(\Illuminate\Support\Str::limit(strip_tags($message->message), 90)); ?></small>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <div class="text-center py-2">
                    <a href="<?php echo e(route('equipe.notifications')); ?>" style="font-size: 0.78rem; color: var(--sa-primary); text-decoration: none;">Tous les messages →</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($nbIncidentsTechniques > 0): ?>
    <div class="col-lg-6">
        <div class="sa-card h-100">
            <div class="sa-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-tools me-2" style="color: #e74c3c;"></i>Incidents techniques ouverts</span>
                <a href="<?php echo e(route('equipe.support')); ?>" class="sa-badge sa-badge-danger text-decoration-none">
                    <?php echo e($nbIncidentsTechniques); ?>

                </a>
            </div>
            <div class="sa-card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php $__currentLoopData = $incidentsTechniques; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size: 0.83rem; border-color: rgba(255,255,255,0.06);">
                            <div>
                                <strong><?php echo e($ticket->code_unique); ?></strong><br>
                                <small class="text-muted"><?php echo e($ticket->email_achat ?: $ticket->email); ?></small>
                            </div>
                            <small class="text-muted"><?php echo e($ticket->created_at->diffForHumans()); ?></small>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <div class="text-center py-2">
                    <a href="<?php echo e(route('equipe.support')); ?>" style="font-size: 0.78rem; color: var(--sa-primary); text-decoration: none;">Tous les incidents →</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-12">
        <div class="sa-card">
            <div class="sa-card-body text-center py-3" style="font-size: 0.8rem;">
                <i class="bi bi-shield-lock-fill me-1" style="color: var(--sa-primary);"></i>
                Votre accès est limité à votre périmètre. Les statistiques financières globales sont réservées à l'administrateur.
            </div>
        </div>
    </div>

</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/dashboard-equipe.blade.php ENDPATH**/ ?>