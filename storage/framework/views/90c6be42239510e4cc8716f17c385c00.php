<?php $__env->startSection('title', 'Tableau de bord'); ?>

<?php $__env->startSection('page-title', 'Tableau de bord'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active" aria-current="page">Tableau de bord</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('topbar-actions'); ?>
<span class="badge-scans"><?php echo e($ticketsScannes); ?> scans récents</span>
<a href="<?php echo e(route('tickets.index')); ?>" class="btn btn-secondary-custom btn-sm">Voir les tickets</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <!-- Metrics Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.1);">⛺</div>
                <div class="metric-label">Événements créés</div>
                <div class="metric-value"><?php echo e($totalEvenements); ?></div>
                <div class="metric-subtitle">
                    <?php
                        $thisWeek = App\Models\Evenement::where('user_id', auth()->id())
                            ->where('statut', 'publié')
                            ->where('created_at', '>=', now()->startOfWeek())
                            ->count();
                    ?>
                    <?php echo e($evenementsActifs); ?> actifs · <?php echo e($thisWeek); ?> cette semaine
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);">🎫</div>
                <div class="metric-label">Tickets vendus</div>
                <div class="metric-value"><?php echo e(number_format($ticketsVendus, 0, ',', ' ')); ?></div>
                <div class="metric-subtitle">
                    <?php
                        $thisMonth = App\Models\Ticket::whereHas('evenement', fn($q) => $q->where('user_id', auth()->id()))
                            ->where('statut_paiement', 'payé')
                            ->where('date_achat', '>=', now()->startOfMonth())
                            ->count();
                        $lastMonth = App\Models\Ticket::whereHas('evenement', fn($q) => $q->where('user_id', auth()->id()))
                            ->where('statut_paiement', 'payé')
                            ->whereBetween('date_achat', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
                            ->count();
                        $pct = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100) : 0;
                    ?>
                    <?php if($pct > 0): ?>
                        +<?php echo e($pct); ?>% ce mois
                    <?php elseif($pct < 0): ?>
                        <?php echo e($pct); ?>% ce mois
                    <?php else: ?>
                        — ce mois
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--teal);">
                <div class="metric-icon" style="background: rgba(66,140,121,0.1);">💰</div>
                <div class="metric-label">Revenus encaissés</div>
                <div class="metric-value" style="font-size: 1.5rem;"><?php echo e(number_format($recettesTotales, 0, ',', ' ')); ?></div>
                <div class="metric-subtitle">FCFA via <span style="color: var(--vert); font-weight: 700;">KKiaPay</span></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--aubergine);">
                <div class="metric-icon" style="background: rgba(109,78,114,0.1);"><span style="color: var(--vert);">✓</span></div>
                <div class="metric-label">Taux de scan</div>
                <div class="metric-value"><?php echo e($tauxScan); ?>%</div>
                <div class="metric-subtitle"><?php echo e(number_format($ticketsScannes, 0, ',', ' ')); ?> entrées validées</div>
            </div>
        </div>
    </div>

    <!-- Ligne 2 : Graphique + Actualités -->
    <div class="row g-3 mb-4">
        <!-- Chart -->
        <div class="col-lg-7">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5>Ventes de tickets — 7 derniers jours</h5>
                    <a href="<?php echo e(route('tickets.index')); ?>">Voir tout</a>
                </div>
                <div class="panel-card-body">
                    <canvas id="ventesChart" height="120"></canvas>
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <div class="d-flex gap-3">
                            <div class="d-flex align-items-center gap-1">
                                <div style="width: 10px; height: 10px; border-radius: 2px; background: #12976e;"></div>
                                <small style="color: #98919b; font-size: 0.7rem;">Aujourd'hui</small>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <div style="width: 10px; height: 10px; border-radius: 2px; background: #b2e0d6;"></div>
                                <small style="color: #98919b; font-size: 0.7rem;">Jours précédents</small>
                            </div>
                        </div>
                        <span class="badge-scans" style="font-size: 0.68rem;">
                            <i class="bi bi-credit-card me-1"></i>Paiements KKiaPay
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Événements récents -->
        <div class="col-lg-5">
            <div class="panel-card" style="height: 100%;">
                <div class="panel-card-header">
                    <h5>Événements</h5>
                    <a href="<?php echo e(route('admin.evenements.index')); ?>">Voir tout</a>
                </div>
                <div class="panel-card-body" style="padding: 0;">
                    <?php $__empty_1 = true; $__currentLoopData = $evenementsRecents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evenement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $now = now();
                            $isPast = $evenement->date_event < $now;
                            $isToday = $evenement->date_event->isToday();

                            if ($evenement->statut === 'terminé' || $evenement->statut === 'annulé') {
                                $statusLabel = 'Terminé';
                                $statusClass = 'status-termine';
                                $dotColor = '#98919b';
                            } elseif ($isPast && $evenement->statut === 'publié') {
                                $statusLabel = 'Terminé';
                                $statusClass = 'status-termine';
                                $dotColor = '#98919b';
                            } elseif ($isToday || $evenement->statut === 'publié') {
                                if ($isToday) {
                                    $statusLabel = 'En cours';
                                    $statusClass = 'status-en-cours';
                                    $dotColor = '#12976e';
                                } else {
                                    $statusLabel = 'À venir';
                                    $statusClass = 'status-a-venir';
                                    $dotColor = '#87428b';
                                }
                            } else {
                                $statusLabel = ucfirst($evenement->statut);
                                $statusClass = 'status-brouillon';
                                $dotColor = '#98919b';
                            }
                        ?>
                        <div class="event-row" style="padding: 0.75rem 1.25rem;">
                            <div class="event-dot" style="background: <?php echo e($dotColor); ?>;"></div>
                            <div class="event-info">
                                <div class="event-name"><?php echo e($evenement->titre); ?></div>
                                <div class="event-meta">
                                    <?php echo e($evenement->date_event->format('d M Y')); ?> — <?php echo e($evenement->quota_vendu); ?> tickets
                                </div>
                            </div>
                            <span class="status-badge <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-4" style="color: #98919b;">
                            <i class="bi bi-calendar-x d-block mb-2" style="font-size: 2rem;"></i>
                            <small>Aucun événement</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Ligne 3 : Scan + Promotions + Activité -->
    <div class="row g-3">
        <!-- Scan en temps réel -->
        <div class="col-lg-4">
            <div class="panel-card" style="height: 100%;">
                <div class="panel-card-header">
                    <h5>Scan en temps réel</h5>
                </div>
                <div class="panel-card-body">
                    <?php if($eventEnCours): ?>
                        <div style="background: #f5f5f5; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                            <div style="font-size: 0.75rem; color: #98919b; margin-bottom: 0.25rem;"><?php echo e($eventEnCours->titre); ?></div>
                            <div style="font-size: 1.5rem; font-weight: 800; color: #3d4345;"><?php echo e($scanValides); ?> / <?php echo e($scanTotal); ?></div>
                            <div class="progress-bar-custom mt-2">
                                <div class="progress-bar-fill" style="width: <?php echo e($scanPct); ?>%;"></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="background: #f5f5f5; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; text-align: center;">
                            <div style="font-size: 0.82rem; color: #98919b;">Aucun événement actif</div>
                        </div>
                    <?php endif; ?>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="mini-stat">
                                <div class="mini-stat-value"><?php echo e(number_format($ticketsScannes, 0, ',', ' ')); ?></div>
                                <div class="mini-stat-label">Entrées</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mini-stat">
                                <div class="mini-stat-value text-rouge"><?php echo e(number_format($ticketsAbsents, 0, ',', ' ')); ?></div>
                                <div class="mini-stat-label">Absents</div>
                            </div>
                        </div>
                    </div>

                    <a href="<?php echo e(route('scan.index')); ?>" class="btn btn-secondary-custom w-100" style="font-size: 0.82rem;">
                        <i class="bi bi-camera me-1"></i> Ouvrir le scanner
                    </a>
                    <div class="text-center mt-2" style="font-size: 0.65rem; color: #98919b;">
                        Partagez l'accès à un agent sans compte
                    </div>
                </div>
            </div>
        </div>

        <!-- Codes promos -->
        <div class="col-lg-4">
            <div class="panel-card" style="height: 100%;">
                <div class="panel-card-header">
                    <h5>Codes promos</h5>
                    <a href="#">+ Générer</a>
                </div>
                <div class="panel-card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="mini-stat">
                                <div class="mini-stat-value"><?php echo e($codesGeneres); ?></div>
                                <div class="mini-stat-label">Générés</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mini-stat">
                                <div class="mini-stat-value text-vert"><?php echo e($codesUtilises); ?></div>
                                <div class="mini-stat-label">Utilisés</div>
                            </div>
                        </div>
                    </div>

                    <?php if($codesPromos->isNotEmpty()): ?>
                        <div style="max-height: 150px; overflow-y: auto;">
                            <?php $__currentLoopData = $codesPromos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid #f5f5f5;">
                                    <span class="promo-code"><?php echo e($code->code); ?></span>
                                    <?php if($code->nb_utilisations > 0): ?>
                                        <span class="promo-status promo-utilise">Utilisé</span>
                                    <?php else: ?>
                                        <span class="promo-status promo-disponible">Disponible</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3" style="color: #98919b; font-size: 0.82rem;">
                            Aucun code promo généré
                        </div>
                    <?php endif; ?>

                    <button class="btn btn-secondary-custom w-100 mt-3" style="font-size: 0.82rem;">
                        <i class="bi bi-download me-1"></i> Exporter CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Activité & Logs -->
        <div class="col-lg-4">
            <div class="panel-card" style="height: 100%;">
                <div class="panel-card-header">
                    <h5>Activité & Logs</h5>
                    <a href="#">Logs</a>
                </div>
                <div class="panel-card-body">
                    <?php $__currentLoopData = $activiteRecents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="activity-item">
                            <div class="activity-dot" style="background: <?php echo e($act['color']); ?>;"></div>
                            <div class="activity-text"><?php echo $act['text']; ?></div>
                            <span class="activity-time"><?php echo e($act['time']); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <?php
                        $refundsPending = App\Models\Ticket::whereHas('evenement', fn($q) => $q->where('user_id', auth()->id()))
                            ->where('statut_paiement', 'en_attente')
                            ->count();
                    ?>
                    <?php if($refundsPending > 0): ?>
                        <button class="btn btn-outline-rouge w-100 mt-3" style="font-size: 0.82rem;">
                            <i class="bi bi-exclamation-triangle me-1"></i> <?php echo e($refundsPending); ?> remboursement<?php echo e($refundsPending > 1 ? 's' : ''); ?> en attente
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    const ctx = document.getElementById('ventesChart').getContext('2d');

    const data = <?php echo json_encode($ventes7Jours, 15, 512) ?>;
    const labels = <?php echo json_encode($joursLabels, 15, 512) ?>;
    const colors = labels.map((_, i) => i === 6 ? '#12976e' : '#b2e0d6');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderRadius: 4,
                borderSkipped: false,
                barPercentage: 0.6,
                categoryPercentage: 0.7,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#3d4345',
                    padding: 10,
                    cornerRadius: 6,
                    titleFont: { size: 11 },
                    bodyFont: { size: 12, weight: 'bold' },
                    callbacks: {
                        label: function(ctx) {
                            return ctx.parsed.y + ' tickets';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                    ticks: {
                        color: '#98919b',
                        font: { size: 10 },
                        stepSize: 1,
                    },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#98919b',
                        font: { size: 10, weight: '500' }
                    },
                    border: { display: false }
                }
            }
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\dashboard.blade.php ENDPATH**/ ?>