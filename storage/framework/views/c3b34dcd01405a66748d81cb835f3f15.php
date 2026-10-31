<?php $__env->startSection('title', $evenement->titre . ' — Espace affilié PaxEvent'); ?>

<?php
    $fmt = fn ($v) => number_format((float) $v, 0, ',', ' ');
    $fmtPct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ' '), '0'), ',');
    $placesRestantes = max(0, (int) $evenement->capacite - (int) $evenement->quota_vendu);
?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="<?php echo e(route('affiliate.liens')); ?>" class="small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Retour à mes liens
            </a>
            <h1 class="h4 fw-bold mb-1 mt-2"><?php echo e($evenement->titre); ?></h1>
            <div class="text-muted small d-flex flex-wrap gap-3">
                <?php if($evenement->date_event): ?>
                    <span><i class="bi bi-calendar3 me-1"></i><?php echo e($evenement->date_event->format('d/m/Y à H:i')); ?></span>
                <?php endif; ?>
                <?php if($evenement->lieu): ?>
                    <span><i class="bi bi-geo-alt me-1"></i><?php echo e($evenement->lieu); ?></span>
                <?php endif; ?>
                <?php if($evenement->categorie): ?>
                    <span><i class="bi bi-tag me-1"></i><?php echo e(ucfirst($evenement->categorie)); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?php if($evenement->statut === 'publié'): ?>
                <span class="badge bg-success-subtle text-success-emphasis">Co-parrainage actif</span>
            <?php else: ?>
                <span class="badge bg-secondary-subtle text-secondary-emphasis">Événement <?php echo e($evenement->statut); ?></span>
            <?php endif; ?>
            <?php if($lien): ?>
                <span class="badge <?php echo e($lien->actif ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'); ?>">
                    <?php echo e($lien->actif ? 'Votre lien est actif' : 'Votre lien est inactif'); ?>

                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card aff-card overflow-hidden mb-4">
                <?php if($evenement->image): ?>
                    <img src="<?php echo e(asset('storage/' . $evenement->image)); ?>" alt="<?php echo e($evenement->titre); ?>" class="aff-detail-cover">
                <?php else: ?>
                    <div class="aff-detail-placeholder"><i class="bi bi-calendar-event"></i></div>
                <?php endif; ?>
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-2"><i class="bi bi-info-circle me-1"></i> À propos de l'événement</h2>
                    <p class="text-muted mb-4" style="white-space:pre-line;"><?php echo e($evenement->description ?: 'Aucune description fournie par l\'organisateur.'); ?></p>

                    <div class="row g-3">
                        <div class="col-6 col-md-4">
                            <div class="aff-meta-box">
                                <div class="label">Date</div>
                                <div class="value"><?php echo e($evenement->date_event?->format('d/m/Y') ?? '—'); ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="aff-meta-box">
                                <div class="label">Heure</div>
                                <div class="value"><?php echo e($evenement->date_event?->format('H:i') ?? '—'); ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="aff-meta-box">
                                <div class="label">Lieu</div>
                                <div class="value text-truncate" title="<?php echo e($evenement->lieu); ?>"><?php echo e($evenement->lieu ?: '—'); ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="aff-meta-box">
                                <div class="label">Catégorie</div>
                                <div class="value"><?php echo e($evenement->categorie ? ucfirst($evenement->categorie) : '—'); ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="aff-meta-box">
                                <div class="label">Capacité</div>
                                <div class="value"><?php echo e($fmt($evenement->capacite)); ?> places</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="aff-meta-box">
                                <div class="label">Places restantes</div>
                                <div class="value"><?php echo e($fmt($placesRestantes)); ?> places</div>
                            </div>
                        </div>
                    </div>

                    <?php if($evenement->dates->count() > 1): ?>
                        <hr class="my-4">
                        <h3 class="h6 fw-bold mb-2"><i class="bi bi-calendar-week me-1"></i> Sessions</h3>
                        <div class="d-flex flex-wrap gap-2">
                            <?php $__currentLoopData = $evenement->dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="badge bg-light text-dark border"><?php echo e($date->date_debut->format('d/m/Y H:i')); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card aff-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h2 class="h6 fw-bold mb-1"><i class="bi bi-cash-coin me-1"></i> Combien vous rapporte chaque billet ?</h2>
                            <p class="text-muted small mb-0">Votre gain estimé est calculé à partir du prix du billet et du taux de co-parrainage fixé par l'organisateur.</p>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="text-muted small">Taux PaxEvent</div>
                            <div class="fw-bold" style="color:var(--violet);"><?php echo e($fmtPct($commissionPax)); ?> %</div>
                        </div>
                    </div>

                    <?php if($noteLignes->isEmpty()): ?>
                        <div class="alert alert-light border small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            L'organisateur n'a pas encore publié de tarif actif. La note détaillée sera disponible dès que les tarifs seront en ligne.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-3 aff-note-table">
                                <thead>
                                    <tr class="small text-muted">
                                        <th>Tarif</th>
                                        <th class="text-end">Prix du billet</th>
                                        <th class="text-end">Commission PaxEvent</th>
                                        <th class="text-end">Votre gain estimé par billet</th>
                                        <th class="text-end">Net organisateur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $noteLignes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ligne): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="small fw-semibold"><?php echo e($ligne['nom']); ?></td>
                                            <td class="text-end small"><?php echo e($fmt($ligne['prix'])); ?> F</td>
                                            <td class="text-end small">
                                                <?php echo e($fmt($ligne['part_pax'])); ?> F
                                                <span class="text-muted">(<?php echo e($fmtPct($commissionPax)); ?> %)</span>
                                            </td>
                                            <td class="text-end small fw-semibold" style="color:var(--violet);">
                                                <?php echo e($fmt($ligne['part_affilie'])); ?> F
                                            </td>
                                            <td class="text-end small"><?php echo e($fmt($ligne['part_organisateur'])); ?> F</td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-light border small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Chaque vente payée attribuée à votre lien génère le gain affiché pour le tarif concerné. Le montant réel peut varier si une réduction est appliquée. La commission de co-parrainage est <strong>à la charge de l'organisateur</strong> : elle est prélevée sur ses recettes et s'ajoute à la commission PaxEvent. Elle n'est jamais facturée à l'acheteur du billet.
                            <?php if($commissionAffilie <= 0): ?>
                                <br><span class="text-danger">Aucune commission de co-parrainage n'est définie pour cet événement.</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card aff-card">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-person-badge me-1"></i> L'organisateur</h2>
                    <?php if($organisateur): ?>
                        <div class="d-flex flex-column flex-sm-row gap-3">
                            <?php if($organisateur->avatar): ?>
                                <img src="<?php echo e(asset('storage/' . $organisateur->avatar)); ?>" alt="<?php echo e($organisateur->nom); ?>" class="aff-organizer-avatar">
                            <?php else: ?>
                                <div class="aff-organizer-avatar"><?php echo e(strtoupper(substr($organisateur->prenom ?: $organisateur->nom, 0, 1))); ?></div>
                            <?php endif; ?>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold"><?php echo e(trim(($organisateur->prenom ?? '') . ' ' . ($organisateur->nom ?? ''))); ?></div>
                                <?php if($organisateur->organisation): ?>
                                    <div class="text-muted small"><?php echo e($organisateur->organisation); ?></div>
                                <?php endif; ?>
                                <?php if($organisateur->description): ?>
                                    <p class="text-muted small mt-2 mb-2" style="white-space:pre-line;"><?php echo e($organisateur->description); ?></p>
                                <?php endif; ?>
                                <div class="d-flex flex-wrap gap-3 small text-muted">
                                    <span><i class="bi bi-calendar-check me-1"></i><?php echo e($nbEvenementsOrganisateur); ?> événement(s) publié(s)</span>
                                    <?php if($organisateur->created_at): ?>
                                        <span><i class="bi bi-clock-history me-1"></i>Membre depuis <?php echo e($organisateur->created_at->format('m/Y')); ?></span>
                                    <?php endif; ?>
                                    <?php if($organisateur->type_detail): ?>
                                        <span><i class="bi bi-briefcase me-1"></i><?php echo e(ucfirst($organisateur->type_detail)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex flex-wrap gap-3 small mt-1">
                                    <?php if($organisateur->email): ?>
                                        <a href="mailto:<?php echo e($organisateur->email); ?>" class="text-decoration-none">
                                            <i class="bi bi-envelope me-1"></i><?php echo e($organisateur->email); ?>

                                        </a>
                                    <?php endif; ?>
                                    <?php if($organisateur->telephone): ?>
                                        <a href="tel:<?php echo e($organisateur->telephone); ?>" class="text-decoration-none">
                                            <i class="bi bi-telephone me-1"></i><?php echo e($organisateur->telephone); ?>

                                        </a>
                                    <?php endif; ?>
                                </div>
                                <?php if($organisateur->facebook_url || $organisateur->instagram_url || $organisateur->tiktok_url || $organisateur->twitter_url || $organisateur->youtube_url || $organisateur->linkedin_url || $organisateur->website_url): ?>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <?php if($organisateur->facebook_url): ?>
                                            <a href="<?php echo e($organisateur->facebook_url); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Facebook"><i class="bi bi-facebook"></i></a>
                                        <?php endif; ?>
                                        <?php if($organisateur->instagram_url): ?>
                                            <a href="<?php echo e($organisateur->instagram_url); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Instagram"><i class="bi bi-instagram"></i></a>
                                        <?php endif; ?>
                                        <?php if($organisateur->tiktok_url): ?>
                                            <a href="<?php echo e($organisateur->tiktok_url); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="TikTok"><i class="bi bi-tiktok"></i></a>
                                        <?php endif; ?>
                                        <?php if($organisateur->twitter_url): ?>
                                            <a href="<?php echo e($organisateur->twitter_url); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="X / Twitter"><i class="bi bi-twitter-x"></i></a>
                                        <?php endif; ?>
                                        <?php if($organisateur->youtube_url): ?>
                                            <a href="<?php echo e($organisateur->youtube_url); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="YouTube"><i class="bi bi-youtube"></i></a>
                                        <?php endif; ?>
                                        <?php if($organisateur->linkedin_url): ?>
                                            <a href="<?php echo e($organisateur->linkedin_url); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                                        <?php endif; ?>
                                        <?php if($organisateur->website_url): ?>
                                            <a href="<?php echo e($organisateur->website_url); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Site web"><i class="bi bi-globe"></i></a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small mb-0">Les informations de l'organisateur ne sont pas disponibles.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card aff-card mb-4">
                <div class="card-body p-4">
                    <div class="text-muted small">Votre commission</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <div class="display-6 fw-bold" style="color:var(--violet);"><?php echo e($fmtPct($commissionAffilie)); ?> %</div>
                        <div class="text-muted small">du prix du billet</div>
                    </div>
                    <?php if($noteLignes->isNotEmpty()): ?>
                        <div class="text-muted small mb-3">
                            Soit <?php echo e($fmt($noteLignes->min('part_affilie'))); ?> à <?php echo e($fmt($noteLignes->max('part_affilie'))); ?> FCFA par billet vendu via votre lien.
                        </div>
                    <?php endif; ?>
                    <hr>
                    <div class="aff-stat-row"><span>Clics sur votre lien</span><strong><?php echo e($lien?->clics ?? 0); ?></strong></div>
                    <div class="aff-stat-row"><span>Commissions générées</span><strong><?php echo e($statsCommissions['nombre']); ?></strong></div>
                    <div class="aff-stat-row"><span>Total gagné sur cet événement</span><strong><?php echo e($fmt($statsCommissions['total'])); ?> F</strong></div>
                    <div class="aff-stat-row"><span>En attente</span><strong><?php echo e($fmt($statsCommissions['en_attente'])); ?> F</strong></div>
                    <div class="aff-stat-row"><span>Disponible</span><strong><?php echo e($fmt($statsCommissions['disponible'])); ?> F</strong></div>
                    <div class="aff-stat-row"><span>Déjà payé</span><strong><?php echo e($fmt($statsCommissions['paye'])); ?> F</strong></div>
                </div>
            </div>

            <div class="card aff-card">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-link-45deg me-1"></i> Vos liens tracés</h2>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted mb-1">Lien d'achat / d'inscription à l'événement</label>
                        <?php if($lien): ?>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" value="<?php echo e($lienAchat); ?>" readonly onclick="this.select()">
                                <button class="btn btn-outline-secondary" type="button" onclick="copierLien(this)" title="Copier le lien">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                                <a href="<?php echo e($lienAchat); ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary" title="Tester le lien tracé">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                            <div class="form-text">
                                Code : <span class="fw-semibold"><?php echo e($lien->code); ?></span> ·
                                <?php echo e($lien->clics); ?> clic(s) ·
                                <?php echo e($lien->actif ? 'Actif' : 'Inactif'); ?>

                            </div>
                            <p class="text-muted small mb-0 mt-1">
                                Chaque visite via ce lien est comptée (une fois par session). Les billets payés sont rattachés à votre lien et génèrent votre commission après la tenue de l'événement.
                            </p>
                            <form method="POST" action="<?php echo e(route('affiliate.liens.toggle', $lien)); ?>" class="mt-2">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-sm <?php echo e($lien->actif ? 'btn-outline-danger' : 'btn-outline-success'); ?>">
                                    <?php echo e($lien->actif ? 'Désactiver mon lien' : 'Réactiver mon lien'); ?>

                                </button>
                            </form>
                            <form method="POST" action="<?php echo e(route('affiliate.liens.destroy', $lien)); ?>" class="mt-2" onsubmit="return confirm('Retirer cet événement de vos liens ? Vos commissions déjà générées seront conservées.')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash me-1"></i> Retirer cet événement de mes liens
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="text-muted small">Générez votre lien personnel pour suivre les visites et achats attribués à cet événement.</p>
                            <form action="<?php echo e(route('affiliate.liens.generer', $evenement)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-violet w-100">
                                    <i class="bi bi-link-45deg me-1"></i> Générer mon lien
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .aff-detail-cover { width: 100%; height: 320px; object-fit: cover; display: block; }
    .aff-detail-placeholder {
        width: 100%; height: 240px; display: flex; align-items: center; justify-content: center;
        background: #f0ecf6; color: var(--violet-clair); font-size: 3rem;
    }
    .aff-organizer-avatar {
        width: 64px; height: 64px; border-radius: 50%; object-fit: cover; flex: 0 0 auto;
        background: #f0ecf6; color: var(--violet); display: flex; align-items: center;
        justify-content: center; font-weight: 800; font-size: 1.4rem;
    }
    .aff-stat-row {
        display: flex; justify-content: space-between; gap: 1rem;
        padding: .4rem 0; border-bottom: 1px solid #f0eef2; font-size: .84rem;
    }
    .aff-stat-row:last-child { border-bottom: 0; }
    .aff-note-table th { font-weight: 600; white-space: nowrap; }
    .aff-note-table td, .aff-note-table th { padding: .55rem .5rem; }
    .aff-meta-box { background: #f8f7fa; border-radius: 12px; padding: .8rem .9rem; height: 100%; }
    .aff-meta-box .label { font-size: .72rem; color: var(--gris); text-transform: uppercase; letter-spacing: .04em; }
    .aff-meta-box .value { font-weight: 700; font-size: .9rem; }
    @media (max-width: 575.98px) {
        .aff-detail-cover { height: 200px; }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function copierLien(btn) {
    var group = btn.closest('.input-group');
    var input = group ? group.querySelector('input') : null;
    if (!input) return;
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

<?php echo $__env->make('layouts.affiliate', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/affiliate/evenements/show.blade.php ENDPATH**/ ?>