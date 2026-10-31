<?php $__env->startSection('title', 'Tableau de bord — Espace affilié PaxEvent'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-bold mb-1">Bonjour <?php echo e(auth('affiliate')->user()->prenom ?: auth('affiliate')->user()->nom); ?></h1>
            <p class="text-muted mb-0 small">Voici l'activité de votre programme d'affiliation.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Solde disponible</div>
                <div class="value"><?php echo e(number_format((float) $stats['solde_disponible'], 0, ',', ' ')); ?> <small class="fs-6">FCFA</small></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">En attente</div>
                <div class="value"><?php echo e(number_format((float) $stats['solde_en_attente'], 0, ',', ' ')); ?> <small class="fs-6">FCFA</small></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Total gagné</div>
                <div class="value"><?php echo e(number_format((float) $stats['total_gagne'], 0, ',', ' ')); ?> <small class="fs-6">FCFA</small></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Parrainages historiques</div>
                <div class="value"><?php echo e($stats['filleuls']); ?></div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="h6 fw-bold mb-1"><i class="bi bi-share me-1"></i> Événements ouverts au co-parrainage</h2>
                <p class="text-muted small mb-0">Générez votre lien personnel et touchez un pourcentage sur chaque billet vendu via celui-ci.</p>
            </div>
            <a href="<?php echo e(route('affiliate.liens')); ?>" class="small text-decoration-none">
                Voir tous les événements <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <?php if($evenementsOuverts->isNotEmpty()): ?>
            <div class="row g-3">
                <?php $__currentLoopData = $evenementsOuverts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="border rounded-3 h-100 d-flex flex-column overflow-hidden bg-white">
                            <a href="<?php echo e(route('affiliate.evenements.show', $ev)); ?>" class="aff-offer-cover" title="Voir le détail de l'événement">
                                <?php if($ev->image): ?>
                                    <img src="<?php echo e(asset('storage/' . $ev->image)); ?>" alt="<?php echo e($ev->titre); ?>" loading="lazy">
                                <?php else: ?>
                                    <i class="bi bi-calendar-event"></i>
                                <?php endif; ?>
                            </a>
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <a href="<?php echo e(route('affiliate.evenements.show', $ev)); ?>" class="fw-semibold text-decoration-none" style="color:inherit;">
                                    <?php echo e($ev->titre); ?>

                                </a>
                                <div class="text-muted small mb-2">
                                    <i class="bi bi-person me-1"></i><?php echo e($ev->user?->nom ?? 'Organisateur'); ?>

                                    <?php if($ev->date_event): ?>
                                        · <i class="bi bi-calendar-event me-1"></i><?php echo e($ev->date_event->format('d/m/Y')); ?>

                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <span class="badge bg-success-subtle text-success-emphasis">
                                        Commission : <?php echo e(rtrim(rtrim(number_format((float) $ev->co_parrainage_pourcentage, 2, ',', ' '), '0'), ',')); ?> % du billet
                                    </span>
                                </div>
                                <div class="mt-auto d-flex flex-column gap-2">
                                    <a href="<?php echo e(route('affiliate.evenements.show', $ev)); ?>" class="btn btn-outline-secondary btn-sm w-100">
                                        <i class="bi bi-eye me-1"></i> Voir le détail
                                    </a>
                                    <form action="<?php echo e(route('affiliate.liens.generer', $ev)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-violet btn-sm w-100">
                                            <i class="bi bi-link-45deg"></i> Générer mon lien
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light border small mb-0">
                Aucun événement à promouvoir pour le moment. Les événements apparaissent ici lorsqu'ils sont publiés, payants, à venir et ouverts au co-parrainage.
                <a href="<?php echo e(route('affiliate.liens')); ?>" class="alert-link">Voir tous les événements</a>.
            </div>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card aff-card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-link-45deg me-1"></i> Vos liens de co-parrainage</h2>
                    <?php if($liens->isEmpty()): ?>
                        <?php echo $__env->make('affiliate.partials.empty-state', [
                            'icon' => 'bi-link-45deg',
                            'title' => 'Aucun lien de co-parrainage',
                            'text' => "Rendez-vous sur un événement ouvert au co-parrainage pour générer votre lien dédié.",
                            'action' => route('affiliate.liens'),
                            'actionLabel' => 'Générer un lien',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php else: ?>
                        <ul class="list-unstyled mb-3">
                            <?php $__currentLoopData = $liens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lien): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $detailUrl = $lien->evenement ? route('affiliate.evenements.show', $lien->evenement) : null;
                                    $url = $lien->evenement ? route('evenements.public.show', $lien->evenement).'?ref='.$lien->code : null;
                                ?>
                                <li class="d-flex justify-content-between align-items-center gap-2 py-2 <?php echo e(!$loop->last ? 'border-bottom' : ''); ?>">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <?php if($detailUrl): ?>
                                            <a href="<?php echo e($detailUrl); ?>" class="aff-ev-thumb" style="width:48px;height:48px;border-radius:10px;" title="Voir le détail de l'événement">
                                                <?php if($lien->evenement->image): ?>
                                                    <img src="<?php echo e(asset('storage/' . $lien->evenement->image)); ?>" alt="<?php echo e($lien->evenement->titre); ?>" loading="lazy">
                                                <?php else: ?>
                                                    <i class="bi bi-calendar-event"></i>
                                                <?php endif; ?>
                                            </a>
                                        <?php endif; ?>
                                        <div class="min-w-0">
                                            <?php if($detailUrl): ?>
                                                <a href="<?php echo e($detailUrl); ?>" class="small fw-semibold text-truncate d-block text-decoration-none" style="color:inherit;"><?php echo e($lien->evenement->titre); ?></a>
                                            <?php else: ?>
                                                <div class="small fw-semibold text-truncate">—</div>
                                            <?php endif; ?>
                                            <div class="text-muted" style="font-size:0.75rem;">
                                                <?php echo e(rtrim(rtrim(number_format((float) $lien->pourcentage, 2), '0'), '.')); ?> % · <?php echo e($lien->clics); ?> clic(s)
                                                <?php if (! ($lien->actif)): ?> · <span class="text-danger">Inactif</span><?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1 flex-shrink-0">
                                        <?php if($detailUrl): ?>
                                            <a href="<?php echo e($detailUrl); ?>" class="btn btn-sm btn-outline-secondary" title="Détails de l'événement"><i class="bi bi-eye"></i></a>
                                        <?php endif; ?>
                                        <?php if($url): ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copier(this)" title="Copier le lien"><i class="bi bi-clipboard"></i></button>
                                            <input type="text" class="d-none" value="<?php echo e($url); ?>" readonly>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                        <a href="<?php echo e(route('affiliate.liens')); ?>" class="small text-decoration-none">
                            Voir tous mes liens <i class="bi bi-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card aff-card">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3">Dernières commissions</h2>
            <?php if($commissions->isEmpty()): ?>
                <?php echo $__env->make('affiliate.partials.empty-state', [
                    'icon' => 'bi-cash-coin',
                    'title' => 'Aucune commission',
                    'text' => 'Vos gains apparaîtront ici dès vos premières ventes tracées.',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="small text-muted">
                                <th>Date</th>
                                <th>Type</th>
                                <th>Événement</th>
                                <th class="text-end">Montant</th>
                                <th class="text-end">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $commission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="small"><?php echo e($commission->created_at?->format('d/m/Y')); ?></td>
                                    <td class="small">
                                        Co-parrainage
                                    </td>
                                    <td class="small"><?php echo e($commission->evenement?->titre ?? '—'); ?></td>
                                    <td class="text-end fw-semibold"><?php echo e(number_format((float) $commission->montant, 0, ',', ' ')); ?> FCFA</td>
                                    <td class="text-end">
                                        <?php
                                            $badge = [
                                                'en_attente' => 'bg-warning-subtle text-warning-emphasis',
                                                'disponible' => 'bg-info-subtle text-info-emphasis',
                                                'paye' => 'bg-success-subtle text-success-emphasis',
                                                'annule' => 'bg-secondary-subtle text-secondary-emphasis',
                                            ][$commission->statut] ?? 'bg-secondary-subtle text-secondary-emphasis';
                                            $labels = [
                                                'en_attente' => 'En attente',
                                                'disponible' => 'Disponible',
                                                'paye' => 'Payé',
                                                'annule' => 'Annulé',
                                            ];
                                        ?>
                                        <span class="badge <?php echo e($badge); ?>"><?php echo e($labels[$commission->statut] ?? $commission->statut); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function copier(btn) {
    var input = btn.parentElement.querySelector('input');
    var val = input.value;
    function done() {
        var html = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i>';
        setTimeout(function () { btn.innerHTML = html; }, 1500);
    }
    function fallback() {
        input.select();
        input.setSelectionRange(0, 99999);
        try { document.execCommand('copy'); } catch (e) {}
        done();
    }
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(val).then(done).catch(fallback);
    } else {
        fallback();
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.affiliate', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/affiliate/dashboard.blade.php ENDPATH**/ ?>