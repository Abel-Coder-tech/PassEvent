<?php $__env->startSection('title', 'Gestion des tickets'); ?>

<?php $__env->startSection('page-title', 'Gestion des tickets'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Tableau de bord</a></li>
    <li class="breadcrumb-item active" aria-current="page">Tickets</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <!-- Header -->
    <p class="text-muted mb-4" style="font-size: 0.9rem; margin-top: -0.5rem;">
        <?php echo e($totalTickets); ?> ticket<?php echo e($totalTickets > 1 ? 's' : ''); ?> genere<?php echo e($totalTickets > 1 ? 's' : ''); ?> &middot; Tous evenements confondus &middot; Prestataire <strong>FedaPay</strong>
    </p>

    <?php if(session('success')): ?>
    <div class="alert alert-success py-2 small"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <!-- Alertes transactions bloquées / anormales -->
    <?php if(!empty($alertes)): ?>
        <div class="mb-4">
            <?php $__currentLoopData = $alertes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alerte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="alert alert-<?php echo e($alerte['type'] === 'danger' ? 'danger' : 'warning'); ?> d-flex align-items-start gap-2 py-2 small mb-2">
                    <i class="bi <?php echo e($alerte['type'] === 'danger' ? 'bi-exclamation-octagon' : 'bi-exclamation-triangle'); ?> mt-1"></i>
                    <div>
                        <strong><?php echo e($alerte['titre']); ?></strong>
                        <div class="text-muted"><?php echo e($alerte['message']); ?></div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

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
        <div class="col-6 col-md">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.08); color: var(--violet);">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="metric-label">Taux de reussite</div>
                <div class="metric-value" style="color: var(--violet);"><?php echo e($tauxReussite); ?>%</div>
                <div class="metric-subtitle"><?php echo e($transactionsReussies); ?> reussies / <?php echo e($transactionsEchouees); ?> echouees</div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="panel-card mb-4">
        <div class="panel-card-body py-3">
            <form action="<?php echo e(route('tickets.index')); ?>" method="GET">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--gris); font-size: 0.95rem;"></i>
                            <input type="text" name="q" class="form-control ps-5 py-2" placeholder="Rechercher par nom, telephone, email, QR code..." value="<?php echo e($filtres['search']); ?>">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Periode</label>
                        <select name="periode" class="form-select py-2">
                            <option value="tout" <?php echo e($filtres['periode'] === 'tout' ? 'selected' : ''); ?>>Tout</option>
                            <option value="7" <?php echo e($filtres['periode'] === '7' ? 'selected' : ''); ?>>7 derniers jours</option>
                            <option value="30" <?php echo e($filtres['periode'] === '30' ? 'selected' : ''); ?>>30 derniers jours</option>
                            <option value="90" <?php echo e($filtres['periode'] === '90' ? 'selected' : ''); ?>>3 derniers mois</option>
                            <option value="annee" <?php echo e($filtres['periode'] === 'annee' ? 'selected' : ''); ?>>Cette annee</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Operateur</label>
                        <select name="operateur" class="form-select py-2">
                            <option value="">Tous</option>
                            <option value="mobile_money" <?php echo e($filtres['operateur'] === 'mobile_money' ? 'selected' : ''); ?>>Mobile Money</option>
                            <option value="mtn" <?php echo e($filtres['operateur'] === 'mtn' ? 'selected' : ''); ?>>MTN MoMo</option>
                            <option value="moov" <?php echo e($filtres['operateur'] === 'moov' ? 'selected' : ''); ?>>Moov Money</option>
                            <option value="celtiis" <?php echo e($filtres['operateur'] === 'celtiis' ? 'selected' : ''); ?>>Celtiis Cash</option>
                            <option value="orange" <?php echo e($filtres['operateur'] === 'orange' ? 'selected' : ''); ?>>Orange Money</option>
                            <option value="wave" <?php echo e($filtres['operateur'] === 'wave' ? 'selected' : ''); ?>>Wave</option>
                            <option value="bancaire" <?php echo e($filtres['operateur'] === 'bancaire' ? 'selected' : ''); ?>>Carte bancaire</option>
                            <option value="especes" <?php echo e($filtres['operateur'] === 'especes' ? 'selected' : ''); ?>>Especes</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Statut</label>
                        <select name="statut" class="form-select py-2">
                            <option value="">Tous</option>
                            <option value="payé" <?php echo e($filtres['statut'] === 'payé' ? 'selected' : ''); ?>>Paye</option>
                            <option value="en_attente" <?php echo e($filtres['statut'] === 'en_attente' ? 'selected' : ''); ?>>En attente</option>
                            <option value="échoué" <?php echo e($filtres['statut'] === 'échoué' ? 'selected' : ''); ?>>Echoue</option>
                            <option value="remboursé" <?php echo e($filtres['statut'] === 'remboursé' ? 'selected' : ''); ?>>Rembourse</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-vert px-3 flex-grow-1" style="border-radius: 8px;">
                            <i class="bi bi-funnel me-1"></i> Filtrer
                        </button>
                        <?php if($filtres['search'] !== '' || $filtres['periode'] !== 'tout' || $filtres['operateur'] || $filtres['statut']): ?>
                            <a href="<?php echo e(route('tickets.index')); ?>" class="btn btn-outline-secondary px-3" style="border-radius: 8px;" title="Effacer tous les filtres">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <a href="<?php echo e(route('tickets.export-csv', $filtres)); ?>" class="btn btn-sm btn-outline-secondary px-3" style="border-radius: 8px;">
                        <i class="bi bi-filetype-csv me-1"></i> Exporter en CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h5><i class="bi bi-list-ul me-2"></i>Liste des tickets</h5>
            <?php if($filtres['search'] !== ''): ?>
                <span class="badge" style="background: rgba(135,66,139,0.1); color: var(--violet);">
                    <i class="bi bi-search me-1"></i> "<?php echo e($filtres['search']); ?>"
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
                                <th>N° WhatsApp</th>
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
                                        <small class="text-muted">Paiement : <?php echo e($ticket->telephone_paiement ?? $ticket->telephone_acheteur ?? '—'); ?></small>
                                    </td>
                                    <td class="small"><?php echo e($ticket->whatsapp_acheteur ?? '—'); ?></td>
                                    <td>
                                        <div class="fw-semibold" style="font-size: 0.85rem;"><?php echo e($ticket->evenement?->titre ?? '—'); ?></div>
                                        <small class="text-muted"><?php echo e($ticket->evenement?->date_event?->isoFormat('D MMM YYYY') ?? '—'); ?></small>
                                    </td>
                                    <td>
                                        <code class="fw-bold" style="font-size: 0.82rem; color: var(--violet);"><?php echo e($ticket->code_unique); ?></code>
                                    </td>
                                    <?php if($ticket->montant > 0): ?>
                                    <td>
                                        <?php echo e($ticket->nom_tarif); ?>

                                    </td>
                                    <td>
                                        <span class="fw-bold" style="color: var(--vert);"><?php echo e(number_format($ticket->montant, 0, ',', ' ')); ?> F</span>
                                    </td>
                                    <?php else: ?>
                                    <td colspan="2" class="text-muted small">Entrée gratuite</td>
                                    <?php endif; ?>
                                    <td>
                                        <span class="<?php echo e($badgeClass); ?>" style="<?php echo e(is_string($badgeClass) && str_contains($badgeClass, 'status-badge') ? '' : ''); ?>">
                                            <?php echo e($statusLabel); ?>

                                        </span>
                                    </td>
                                    <td class="pe-3">
                                        <div class="d-flex gap-1" style="white-space: nowrap;">
                                            <a href="<?php echo e(route('tickets.pdf', $ticket->id)); ?>" class="btn-icon" title="Telecharger PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                            <a href="<?php echo e(route('tickets.show', $ticket->id)); ?>" class="btn-icon" title="Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if(!$isCancelled): ?>
                                                <form action="<?php echo e(route('tickets.renvoyer', $ticket->id)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="btn-icon btn-icon-vert" title="Renvoyer par email">
                                                        <i class="bi bi-envelope"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if($isPaid || $isPending): ?>
                                                <form action="<?php echo e(route('tickets.annuler', $ticket->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Annuler ce ticket ? Cette action est irreversible.');">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="btn-icon btn-icon-danger" title="Annuler">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if($isCancelled): ?>
                                                <a href="<?php echo e(route('tickets.show', $ticket->id)); ?>" class="btn-icon btn-icon-gris" title="Voir les logs">
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
                        <?php if($filtres['search'] !== ''): ?>
                            Aucun resultat pour "<?php echo e($filtres['search']); ?>". Essayez un autre terme.
                        <?php else: ?>
                            Les tickets generes apparaîtront ici.
                        <?php endif; ?>
                    </p>
                    <?php if($filtres['search'] !== ''): ?>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/tickets/index.blade.php ENDPATH**/ ?>