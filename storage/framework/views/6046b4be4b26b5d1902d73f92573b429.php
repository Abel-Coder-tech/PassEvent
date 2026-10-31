<?php $__env->startSection('title', 'Mes liens de co-parrainage — Espace affilié PaxEvent'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="h4 fw-bold mb-1">Mes liens de co-parrainage</h1>
    <p class="text-muted small mb-4">Générez un lien dédié pour les événements ouverts au co-parrainage et touchez la commission sur chaque billet vendu via ce lien.</p>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Mes liens</div>
                <div class="value"><?php echo e($stats['liens']); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Liens actifs</div>
                <div class="value"><?php echo e($stats['liens_actifs']); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Clics cumulés</div>
                <div class="value"><?php echo e(number_format($stats['clics'], 0, ',', ' ')); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Événements ouverts</div>
                <div class="value"><?php echo e($stats['evenements_ouverts']); ?></div>
            </div>
        </div>
    </div>

    <div class="card aff-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-muted mb-1">Rechercher un événement</label>
                    <input type="text" name="q" class="form-control form-control-sm" value="<?php echo e($recherche); ?>" placeholder="Nom de l'événement…">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Statut de mes liens</label>
                    <select name="statut" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="actif" <?php if($statut === 'actif'): echo 'selected'; endif; ?>>Actif</option>
                        <option value="inactif" <?php if($statut === 'inactif'): echo 'selected'; endif; ?>>Inactif</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-violet btn-sm flex-grow-1"><i class="bi bi-search me-1"></i> Rechercher</button>
                    <?php if($recherche !== '' || $statut !== null): ?>
                        <a href="<?php echo e(route('affiliate.liens')); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card aff-card mb-4">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3"><i class="bi bi-broadcast me-1"></i> Événements ouverts au co-parrainage</h2>
            <?php if($evenementsOuverts->isEmpty()): ?>
                <?php echo $__env->make('affiliate.partials.empty-state', [
                    'icon' => 'bi-broadcast',
                    'title' => 'Aucun événement disponible',
                    'text' => $recherche !== '' ? 'Aucun événement ouvert ne correspond à votre recherche.' : 'Aucun nouvel événement ouvert au co-parrainage pour le moment.',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php else: ?>
                <div class="row g-3">
                    <?php $__currentLoopData = $evenementsOuverts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-12 col-md-6">
                            <div class="border rounded-3 h-100 d-flex flex-column overflow-hidden">
                                <a href="<?php echo e(route('affiliate.evenements.show', $ev)); ?>" class="aff-offer-cover" title="Voir le détail de l'événement">
                                    <?php if($ev->image): ?>
                                        <img src="<?php echo e(asset('storage/' . $ev->image)); ?>" alt="<?php echo e($ev->titre); ?>" loading="lazy">
                                    <?php else: ?>
                                        <i class="bi bi-calendar-event"></i>
                                    <?php endif; ?>
                                </a>
                                <div class="p-3 d-flex flex-column flex-grow-1">
                                    <a href="<?php echo e(route('affiliate.evenements.show', $ev)); ?>" class="fw-semibold text-decoration-none" style="color:inherit;"><?php echo e($ev->titre); ?></a>
                                    <div class="text-muted small mb-2">
                                        <i class="bi bi-person me-1"></i><?php echo e($ev->user?->nom ?? 'Organisateur'); ?>

                                        <?php if($ev->date_event): ?> · <i class="bi bi-calendar-event me-1"></i><?php echo e($ev->date_event->format('d/m/Y')); ?><?php endif; ?>
                                        <?php if($ev->tarifs->isNotEmpty()): ?>
                                            · À partir de <?php echo e(number_format((float) $ev->tarifs->min('prix'), 0, ',', ' ')); ?> F
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
            <?php endif; ?>
        </div>
    </div>

    <div class="card aff-card">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3">Mes liens générés</h2>
            <?php if($liens->isEmpty()): ?>
                <?php echo $__env->make('affiliate.partials.empty-state', [
                    'icon' => 'bi-link-45deg',
                    'title' => 'Aucun lien',
                    'text' => ($recherche !== '' || $statut !== null)
                        ? 'Aucun de vos liens ne correspond à ces filtres.'
                        : 'Vous n\'avez encore généré aucun lien.',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php else: ?>
                <div class="row g-3">
                    <?php $__currentLoopData = $liens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lien): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $detailUrl = $lien->evenement ? route('affiliate.evenements.show', $lien->evenement) : null;
                            $url = $lien->evenement ? route('evenements.public.show', $lien->evenement).'?ref='.$lien->code : null;
                        ?>
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="border rounded-3 h-100 d-flex flex-column overflow-hidden bg-white">
                                <?php if($detailUrl): ?>
                                    <a href="<?php echo e($detailUrl); ?>" class="aff-offer-cover" style="height:130px;" title="Voir le détail de l'événement">
                                        <?php if($lien->evenement->image): ?>
                                            <img src="<?php echo e(asset('storage/' . $lien->evenement->image)); ?>" alt="<?php echo e($lien->evenement->titre); ?>" loading="lazy">
                                        <?php else: ?>
                                            <i class="bi bi-calendar-event"></i>
                                        <?php endif; ?>
                                    </a>
                                <?php endif; ?>
                                <div class="p-3 d-flex flex-column flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                        <?php if($detailUrl): ?>
                                            <a href="<?php echo e($detailUrl); ?>" class="fw-semibold text-decoration-none" style="color:inherit;"><?php echo e($lien->evenement->titre); ?></a>
                                        <?php else: ?>
                                            <span class="fw-semibold">—</span>
                                        <?php endif; ?>
                                        <span class="badge flex-shrink-0 <?php echo e($lien->actif ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'); ?>">
                                            <?php echo e($lien->actif ? 'Actif' : 'Inactif'); ?>

                                        </span>
                                    </div>
                                    <div class="text-muted small mb-2">
                                        <?php if($lien->evenement?->user): ?>
                                            <i class="bi bi-person me-1"></i><?php echo e($lien->evenement->user->nom); ?>

                                        <?php endif; ?>
                                        <?php if($lien->evenement?->date_event): ?>
                                            · <i class="bi bi-calendar-event me-1"></i><?php echo e($lien->evenement->date_event->format('d/m/Y')); ?>

                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex flex-wrap gap-3 small text-muted mb-2">
                                        <span>
                                            <i class="bi bi-percent me-1"></i><?php echo e(rtrim(rtrim(number_format((float) $lien->pourcentage, 2, ',', ' '), '0'), ',')); ?> %
                                        </span>
                                        <span><i class="bi bi-cursor me-1"></i><?php echo e($lien->clics); ?> clic(s)</span>
                                    </div>
                                    <?php if($url): ?>
                                        <div class="input-group input-group-sm mb-2">
                                            <input type="text" class="form-control" value="<?php echo e($url); ?>" readonly onclick="this.select()">
                                            <button class="btn btn-outline-secondary" type="button" onclick="copier(this)" title="Copier le lien">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                            <a href="<?php echo e($url); ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary" title="Tester le lien tracé">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mt-auto d-flex gap-2">
                                        <?php if($detailUrl): ?>
                                            <a href="<?php echo e($detailUrl); ?>" class="btn btn-outline-secondary btn-sm flex-grow-1">
                                                <i class="bi bi-eye me-1"></i> Détails
                                            </a>
                                        <?php endif; ?>
                                        <form method="POST" action="<?php echo e(route('affiliate.liens.toggle', $lien)); ?>" class="flex-shrink-0">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm <?php echo e($lien->actif ? 'btn-outline-danger' : 'btn-outline-success'); ?>">
                                                <?php echo e($lien->actif ? 'Désactiver' : 'Réactiver'); ?>

                                            </button>
                                        </form>
                                        <form method="POST" action="<?php echo e(route('affiliate.liens.destroy', $lien)); ?>" class="flex-shrink-0" onsubmit="return confirm('Retirer cet événement de vos liens ? Vos commissions déjà générées seront conservées.')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-outline-danger btn-sm" aria-label="Retirer cet événement de mes liens" title="Retirer de mes liens">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="mt-3 d-flex justify-content-center"><?php echo e($liens->links()); ?></div>
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

<?php echo $__env->make('layouts.affiliate', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/affiliate/liens.blade.php ENDPATH**/ ?>