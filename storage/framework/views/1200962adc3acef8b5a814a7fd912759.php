<?php $__env->startSection('title', 'Gestion des tickets'); ?>

<?php $__env->startSection('page-title', 'Gestion des tickets'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Tickets</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <!-- Header -->
    <p class="text-muted mb-4" style="font-size: 0.9rem; margin-top: -0.5rem;">
        <?php echo e($totalTickets); ?> ticket<?php echo e($totalTickets > 1 ? 's' : ''); ?> genere<?php echo e($totalTickets > 1 ? 's' : ''); ?> &middot; Tous evenements confondus &middot; Prestataire <strong>KKiaPay</strong>
    </p>

    <!-- Stat Cards -->
    <div class="row g-2 mt-3 mb-4">
        <div class="col-6 col-md">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.08); color: var(--violet);">
                    <i class="bi bi-ticket-perforated"></i>
                </div>
                <div class="metric-label">Total tickets</div>
                <div class="metric-value"><?php echo e($totalTickets); ?></div>
                <div class="metric-subtitle">tous evenements</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.08); color: var(--vert);">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="metric-label">Valides</div>
                <div class="metric-value" style="color: var(--vert);"><?php echo e($valides); ?></div>
                <div class="metric-subtitle">non encore scannes</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="metric-card" style="border-top-color: var(--teal);">
                <div class="metric-icon" style="background: rgba(66,140,121,0.08); color: var(--teal);">
                    <i class="bi bi-qr-code-scan"></i>
                </div>
                <div class="metric-label">Scannes</div>
                <div class="metric-value" style="color: var(--teal);"><?php echo e($scannes); ?></div>
                <div class="metric-subtitle">entrees validees</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="metric-card" style="border-top-color: var(--aubergine);">
                <div class="metric-icon" style="background: rgba(109,78,114,0.08); color: var(--aubergine);">
                    <i class="bi bi-mortarboard"></i>
                </div>
                <div class="metric-label">Etudiants</div>
                <div class="metric-value" style="color: var(--aubergine);"><?php echo e($etudiants); ?></div>
                <div class="metric-subtitle">avec code promo</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="metric-card" style="border-top-color: var(--danger);">
                <div class="metric-icon" style="background: rgba(231,76,60,0.08); color: var(--danger);">
                    <i class="bi bi-x-octagon"></i>
                </div>
                <div class="metric-label">Annules</div>
                <div class="metric-value" style="color: var(--danger);"><?php echo e($annules); ?></div>
                <div class="metric-subtitle">rembourses</div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="panel-card mb-4">
        <div class="panel-card-body py-3">
            <form action="<?php echo e(route('tickets.index')); ?>" method="GET">
                <div class="row g-2 align-items-center">
                    <div class="col-md-8">
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--gris); font-size: 0.95rem;"></i>
                            <input type="text" name="q" class="form-control ps-5 py-2" placeholder="Rechercher par nom, telephone, QR code..." value="<?php echo e($search ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-vert px-3" style="border-radius: 8px;">
                            <i class="bi bi-funnel me-1"></i> Filtrer
                        </button>
                        <?php if($search): ?>
                            <a href="<?php echo e(route('tickets.index')); ?>" class="btn btn-outline-secondary px-3" style="border-radius: 8px;">
                                <i class="bi bi-x-lg me-1"></i> Effacer
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h5><i class="bi bi-list-ul me-2"></i>Liste des tickets</h5>
            <?php if($search): ?>
                <span class="badge" style="background: rgba(135,66,139,0.1); color: var(--violet);">
                    <i class="bi bi-search me-1"></i> "<?php echo e($search); ?>"
                </span>
            <?php endif; ?>
        </div>
        <div class="panel-card-body p-0">
            <?php if($tickets->isNotEmpty()): ?>
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Participant</th>
                                <th>Evenement</th>
                                <th>QR code</th>
                                <th>Type</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th class="pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $now = now();
                                    $isPaid = $ticket->statut_paiement === 'payé';
                                    $isScanned = $ticket->utilise;
                                    $isCancelled = in_array($ticket->statut_paiement, ['annulé', 'remboursé']);
                                    $isPending = $ticket->statut_paiement === 'en_attente';

                                    if ($isCancelled) {
                                        $badgeClass = 'bg-danger';
                                        $statusLabel = ucfirst($ticket->statut_paiement);
                                    } elseif ($isScanned) {
                                        $badgeClass = 'status-badge status-termine';
                                        $statusLabel = 'Scanne';
                                    } elseif ($isPaid) {
                                        $badgeClass = 'status-badge status-en-cours';
                                        $statusLabel = 'Valide';
                                    } elseif ($isPending) {
                                        $badgeClass = 'badge bg-warning text-dark';
                                        $statusLabel = 'En attente';
                                    } else {
                                        $badgeClass = 'badge bg-secondary';
                                        $statusLabel = ucfirst($ticket->statut_paiement);
                                    }
                                ?>
                                <tr class="border-bottom">
                                    <td class="ps-3">
                                        <div class="fw-bold" style="font-size: 0.85rem;"><?php echo e($ticket->nom_acheteur); ?></div>
                                        <small class="text-muted"><?php echo e($ticket->telephone_acheteur); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold" style="font-size: 0.85rem;"><?php echo e($ticket->evenement?->titre ?? '—'); ?></div>
                                        <small class="text-muted"><?php echo e($ticket->evenement?->date_event?->format('d M Y') ?? '—'); ?></small>
                                    </td>
                                    <td>
                                        <code class="fw-bold" style="font-size: 0.82rem; color: var(--violet);"><?php echo e($ticket->code_unique); ?></code>
                                    </td>
                                    <td>
                                        <span class="badge me-1" style="background: <?php echo e($ticket->categorie === 'etudiant' ? 'rgba(135,66,139,0.1)' : 'rgba(66,140,121,0.1)'); ?>; color: <?php echo e($ticket->categorie === 'etudiant' ? 'var(--violet)' : 'var(--teal)'); ?>;">
                                            <?php echo e(ucfirst($ticket->categorie)); ?>

                                        </span>
                                        <small class="text-muted d-block"><?php echo e(ucfirst($ticket->type)); ?></small>
                                    </td>
                                    <td>
                                        <span class="fw-bold" style="color: var(--vert);"><?php echo e(number_format($ticket->montant, 0, ',', ' ')); ?> F</span>
                                    </td>
                                    <td>
                                        <span class="<?php echo e($badgeClass); ?>" style="<?php echo e(is_string($badgeClass) && str_contains($badgeClass, 'status-badge') ? '' : ''); ?>">
                                            <?php echo e($statusLabel); ?>

                                        </span>
                                    </td>
                                    <td class="pe-3">
                                        <div class="d-flex gap-1">
                                            <a href="<?php echo e(route('tickets.pdf', $ticket->id)); ?>" class="btn btn-sm btn-secondary-custom" style="border-radius: 6px; padding: 0.25rem 0.5rem;" title="Telecharger PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                            <a href="<?php echo e(route('tickets.show', $ticket->id)); ?>" class="btn btn-sm btn-secondary-custom" style="border-radius: 6px; padding: 0.25rem 0.5rem;" title="Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if(!$isCancelled): ?>
                                                <form action="<?php echo e(route('tickets.renvoyer', $ticket->id)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="btn btn-sm" style="border-radius: 6px; padding: 0.25rem 0.5rem; border: 1px solid var(--vert); color: var(--vert); background: transparent;" title="Renvoyer par email">
                                                        <i class="bi bi-envelope"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if($isPaid || $isPending): ?>
                                                <form action="<?php echo e(route('tickets.annuler', $ticket->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Annuler ce ticket ? Cette action est irreversible.');">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="btn btn-sm" style="border-radius: 6px; padding: 0.25rem 0.5rem; border: 1px solid var(--danger); color: var(--danger); background: transparent;" title="Annuler">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if($isCancelled): ?>
                                                <a href="<?php echo e(route('tickets.show', $ticket->id)); ?>" class="btn btn-sm" style="border-radius: 6px; padding: 0.25rem 0.5rem; border: 1px solid var(--gris); color: var(--gris); background: transparent;" title="Voir les logs">
                                                    <i class="bi bi-clock-history"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if($tickets->hasPages()): ?>
                    <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                        <span class="text-muted" style="font-size: 0.82rem;">
                            Affichage <?php echo e($tickets->firstItem()); ?> a <?php echo e($tickets->lastItem()); ?> sur <?php echo e($tickets->total()); ?> tickets
                        </span>
                        <nav>
                            <?php echo e($tickets->links()); ?>

                        </nav>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-ticket-perforated d-block mb-3" style="font-size: 3rem; color: var(--gris);"></i>
                    <h5 class="text-muted">Aucun ticket trouve</h5>
                    <p class="text-muted">
                        <?php if($search): ?>
                            Aucun resultat pour "<?php echo e($search); ?>". Essayez un autre terme.
                        <?php else: ?>
                            Les tickets generes apparaîtront ici.
                        <?php endif; ?>
                    </p>
                    <?php if($search): ?>
                        <a href="<?php echo e(route('tickets.index')); ?>" class="btn btn-violet btn-sm" style="border-radius: 8px;">
                            <i class="bi bi-arrow-left me-1"></i> Voir tous les tickets
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views/tickets/index.blade.php ENDPATH**/ ?>