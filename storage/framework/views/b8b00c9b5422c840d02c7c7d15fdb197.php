<?php
    /** @var \App\Models\AffiliateCommission $commission */
?>
<?php if($commission->statut === 'en_attente'): ?>
    <span class="sa-badge sa-badge-warning" title="Disponible le <?php echo e($commission->disponible_le?->format('d/m/Y H:i')); ?>">En attente</span>
<?php elseif($commission->statut === 'disponible'): ?>
    <span class="sa-badge sa-badge-info">Disponible</span>
<?php elseif($commission->statut === 'paye'): ?>
    <span class="sa-badge sa-badge-success" title="Payée le <?php echo e($commission->paye_le?->format('d/m/Y')); ?>">Payée</span>
<?php else: ?>
    <span class="sa-badge sa-badge-danger">Annulée</span>
<?php endif; ?>
<?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/affiliations/partials/statut-commission.blade.php ENDPATH**/ ?>