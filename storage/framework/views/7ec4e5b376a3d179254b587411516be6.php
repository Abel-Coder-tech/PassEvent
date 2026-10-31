<?php $__env->startSection('title', 'Paiement Mobile - ' . $agent->evenement->titre); ?>

<?php $__env->startSection('content'); ?>
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

    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-6">
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 16px;">
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 64px; height: 64px; background: #f5f3ff;">
                            <i class="bi bi-wallet2" style="font-size: 1.75rem; color: #7c3aed;"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Paiement Mobile</h5>
                        <p class="text-muted small mb-0">Le client doit payer pour finaliser la vente</p>
                    </div>

                    <div class="p-3 rounded-3 mb-3 text-start" style="background: #f8f6f9;">
                        <div class="row g-2" style="font-size: 0.88rem;">
                            <div class="col-6">
                                <span class="text-muted">Client :</span><br>
                                <strong><?php echo e($ticket->nom_acheteur); ?></strong>
                            </div>
                            <div class="col-6 text-end">
                                <span class="text-muted">Montant :</span><br>
                                <strong style="color: #7c3aed;"><?php echo e(number_format($ticket->montant, 0, ',', ' ')); ?> FCFA</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted">Email :</span><br>
                                <strong><?php echo e($ticket->email_acheteur); ?></strong>
                            </div>
                            <div class="col-6 text-end">
                                <span class="text-muted">Ticket :</span><br>
                                <code class="fw-bold" style="background: #fff; padding: 0.15rem 0.4rem; border-radius: 4px; border: 1px solid #eee;"><?php echo e($ticket->code_unique); ?></code>
                            </div>
                        </div>
                    </div>

                    <?php if($publicKey): ?>
                        <button type="button" id="btnFedaPay" class="btn w-100 py-3" style="background: #7c3aed; color: #fff; border-radius: 10px; font-size: 1rem; font-weight: 700; border: none;">
                            <i class="bi bi-shield-lock me-2"></i> Payer <?php echo e(number_format($ticket->montant, 0, ',', ' ')); ?> FCFA
                        </button>
                    <?php else: ?>
                        <div class="alert alert-warning py-2 mb-0" style="border-radius: 10px; font-size: 0.85rem;">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Le paiement mobile est temporairement indisponible. Utilisez les espèces.
                        </div>
                    <?php endif; ?>

                    <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                        <small class="text-muted" style="font-size: 0.75rem;">Paiement sécurisé par</small>
                        <strong style="color: #7c3aed; font-size: 0.9rem;">FedaPay</strong>
                    </div>

                    <div id="paymentError" class="mt-3" style="display: none; color: #dc3545; font-size: 0.85rem;"></div>

                    <a href="<?php echo e(route('agent-vente.dashboard')); ?>" class="btn btn-sm btn-outline-secondary mt-3">
                        <i class="bi bi-arrow-left"></i> Annuler et revenir
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.fedapay.com/checkout.js?v=1.1.3"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnFedaPay');
    const errorDiv = document.getElementById('paymentError');
    const callbackUrl = '<?php echo e(route('paiement.callback')); ?>?ticket=<?php echo e($ticket->id); ?>&source=agent_vente';

    if (!btn) return;

    if (typeof FedaPay === 'undefined') {
        errorDiv.textContent = 'Erreur de chargement du module de paiement. Actualisez la page.';
        errorDiv.style.display = 'block';
        return;
    }

    const nameParts = '<?php echo e($ticket->nom_acheteur); ?>'.trim().split(' ');
    const firstname = nameParts.slice(0, -1).join(' ') || nameParts[0] || 'Client';
    const lastname = nameParts.length > 1 ? nameParts[nameParts.length - 1] : '';

    FedaPay.init(btn, {
        public_key: '<?php echo e($publicKey); ?>',
        environment: '<?php echo e($sandbox ? 'sandbox' : 'live'); ?>',
        transaction: {
            amount: <?php echo e((int) $ticket->montant); ?>,
            description: 'Ticket - <?php echo e($ticket->evenement->titre); ?>',
            external_id: '<?php echo e($ticket->id); ?>',
            custom_metadata: {
                ticket_id: '<?php echo e($ticket->id); ?>',
                group_transaction_id: '<?php echo e($ticket->transaction_id); ?>'
            }
        },
        customer: {
            email: '<?php echo e($ticket->email_acheteur); ?>',
            firstname: firstname,
            lastname: lastname
        },
        currency: {
            iso: 'XOF'
        },
        onComplete: function(data) {
            if (data.transaction && data.transaction.id) {
                var pm = data.transaction.payment_method || 'mobile_money';
                var ph = data.transaction.phone || '';
                window.location.href = callbackUrl + '&id=' + data.transaction.id + '&status=' + (data.transaction.status || 'approved') + '&payment_method=' + pm + '&phone=' + ph;
            } else {
                errorDiv.textContent = 'Paiement annulé ou fermé. Vous pouvez réessayer.';
                errorDiv.style.display = 'block';
            }
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.agent-vente', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/agent-vente/paiement.blade.php ENDPATH**/ ?>