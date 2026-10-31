<?php $__env->startSection('title', 'Historique des ventes - ' . $agent->evenement->titre); ?>

<?php $__env->startSection('content'); ?>
<?php $textes = $agent->evenement->getTextes(); ?>
<div class="container py-4">
    
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <img src="<?php echo e(asset_v('images/logo_paxevent.png')); ?>" alt="PaxEvent" height="60">
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small"><i class="bi bi-person-circle me-1"></i><?php echo e($agent->nom); ?></span>
            <form method="POST" action="<?php echo e(route('agent-vente.logout')); ?>" class="m-0">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Déconnecter
                </button>
            </form>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h5 class="fw-bold mb-0"><i class="bi bi-clock-history"></i> Historique des ventes</h5>
        <a href="<?php echo e(route('agent-vente.dashboard')); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour au tableau de bord
        </a>
    </div>

    
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body py-2">
            <form method="GET" action="<?php echo e(route('agent-vente.historique')); ?>" class="d-flex flex-wrap align-items-center gap-2 mb-0">
                <?php $__currentLoopData = [
                    'aujourdhui' => 'Aujourd\'hui',
                    'hier' => 'Hier',
                    'semaine' => 'Cette semaine',
                    'mois' => 'Ce mois',
                    'tout' => 'Tout',
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="submit" name="periode" value="<?php echo e($value); ?>"
                    class="btn btn-sm <?php echo e($periode === $value ? 'text-white' : 'btn-outline-secondary'); ?>"
                    style="<?php echo e($periode === $value ? 'background: #7c3aed; border-color: #7c3aed;' : ''); ?>">
                    <?php echo e($label); ?>

                </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </form>
        </div>
    </div>

    
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-purple-50">
                <div class="card-body text-center py-3">
                    <div class="text-purple-700 fw-bold fs-3"><?php echo e($tickets->total()); ?></div>
                    <small class="text-muted">Ventes (page)</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-green-50">
                <div class="card-body text-center py-3">
                    <div class="text-green-700 fw-bold fs-3"><?php echo e(number_format($montantFiltre, 0, ',', ' ')); ?> F</div>
                    <small class="text-muted">Montant de la page</small>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body px-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Acheteur</th>
                            <th>WhatsApp</th>
                            <th>N° paiement</th>
                            <th>Tarif</th>
                            <th>Montant</th>
                            <th>Paiement</th>
                            <th class="pe-3">PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="ps-3 small"><?php echo e($ticket->date_achat->format('d/m/Y H:i')); ?></td>
                            <td class="small"><?php echo e($ticket->nom_acheteur); ?></td>
                            <td class="small"><?php echo e($ticket->whatsapp_acheteur ?? '—'); ?></td>
                            <td class="small"><?php echo e($ticket->telephone_paiement ?? $ticket->telephone_acheteur ?? '—'); ?></td>
                            <td class="small"><?php echo e($ticket->tarif?->getLabel() ?? 'N/A'); ?></td>
                            <?php if($ticket->montant > 0): ?>
                            <td class="small fw-medium"><?php echo e(number_format($ticket->montant, 0, ',', ' ')); ?> F</td>
                            <td class="small"><?php echo e(\App\Models\Ticket::methodePaiementLabel($ticket->methode_paiement)); ?></td>
                            <?php else: ?>
                            <td class="small text-muted">Gratuit</td>
                            <td class="small">—</td>
                            <?php endif; ?>
                            <td class="pe-3">
                                <a href="<?php echo e(route('agent-vente.ticket.pdf', $ticket)); ?>"
                                    class="btn btn-sm btn-outline-secondary py-0 px-2" target="_blank">
                                    <i class="bi bi-filetype-pdf"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4 small">
                                <i class="bi bi-inbox"></i> Aucune vente pour cette période
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="small text-muted">
                    <?php echo e($tickets->total()); ?> vente(s) au total
                    <?php if($periode !== 'tout'): ?>
                    <span class="text-muted">— période sélectionnée</span>
                    <?php endif; ?>
                </div>
                <div>
                    <?php echo e($tickets->appends(['periode' => $periode])->links()); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-purple-50 { background: #f5f3ff; }
.text-purple-700 { color: #7c3aed; }
.bg-green-50 { background: #f0fdf4; }
.text-green-700 { color: #16a34a; }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent-vente', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/agent-vente/historique.blade.php ENDPATH**/ ?>