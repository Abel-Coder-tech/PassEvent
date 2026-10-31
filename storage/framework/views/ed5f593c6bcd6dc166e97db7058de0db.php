<?php $__env->startSection('title', 'Paiement — ' . $ticket->evenement->titre . ' — PaxEvent'); ?>
<?php $__env->startSection('description', 'Finalisez votre achat de billets pour ' . $ticket->evenement->titre . ' sur PaxEvent. Paiement sécurisé.'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('accueil')); ?>">Accueil</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('evenements.public')); ?>">Evenements</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('evenements.public.show', $ticket->evenement)); ?>"><?php echo e(Str::limit($ticket->evenement->titre, 30)); ?></a></li>
    <li class="breadcrumb-item active" aria-current="page">Paiement</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php $textes = $ticket->evenement->getTextes(); ?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-7 col-lg-6">
                <?php if(session('error')): ?>
                    <div class="alert alert-danger py-2" style="font-size: 0.85rem; border-radius: 10px;">
                        <i class="bi bi-exclamation-circle me-1"></i> <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3" style="color: #333;">
                            <i class="bi bi-receipt me-2" style="color: #542680;"></i>
                            Recapitulatif
                        </h5>

                        <div class="p-3 rounded-3 mb-3" style="background: #f8f6f9;">
                            <div class="mb-2">
                                <strong style="font-size: 1.05rem;"><?php echo e($ticket->evenement->titre); ?></strong>
                            </div>
                            <div class="row g-2" style="font-size: 0.9rem;">
                                <div class="col-6">
                                    <span class="text-muted">Tarif :</span><br>
                                    <strong><?php echo e($ticket->nom_tarif); ?></strong>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="text-muted">Montant :</span><br>
                                    <strong style="color: #542680; font-size: 1.1rem;"><?php echo e(number_format($montantTotal, 0, ',', ' ')); ?> FCFA</strong>
                                </div>
                                <div class="col-12">
                                    <span class="text-muted">Acheteur :</span><br>
                                    <strong><?php echo e($ticket->nom_acheteur); ?></strong>
                                </div>
                        <?php if($ticket->code_promo_utilise): ?>
                            <div class="col-12 mt-2">
                                <span class="badge" style="background: rgba(84,38,128,0.1); color: #542680; font-size: 0.75rem;">
                                    <i class="bi bi-tag me-1"></i>Code promo: <?php echo e($ticket->code_promo_utilise); ?>

                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                        <div class="text-center" style="font-size: 0.82rem; color: #999;">
                            <i class="bi bi-clock me-1"></i>
                            Votre place est reservée pendant 15 minutes
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold mb-3" style="color: #333;">
                            <i class="bi bi-wallet2 me-2" style="color: #542680;"></i>
                            Paiement sécurisé
                        </h5>

                        <?php if($publicKey): ?>
                            <button type="button" id="btnFedaPay" class="btn w-100 py-3" style="background: #542680; color: #fff; border-radius: 10px; font-size: 1rem; font-weight: 700; border: none;">
                                <i class="bi bi-shield-lock me-2"></i> Payer <?php echo e(number_format($montantTotal, 0, ',', ' ')); ?> FCFA
                            </button>
                        <?php else: ?>
                            <div class="alert alert-warning py-2 mb-0" style="border-radius: 10px; font-size: 0.85rem;">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Le paiement est temporairement indisponible. Réessayez dans quelques minutes.
                            </div>
                        <?php endif; ?>

                        <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                            <small class="text-muted" style="font-size: 0.75rem;">Paiement sécurisé par</small>
                            <strong style="color: #542680; font-size: 0.9rem;">FedaPay</strong>
                        </div>

                        <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.78rem;">
                            <i class="bi bi-envelope me-1" style="color: #542680;"></i>
                            Votre <?php echo e(mb_strtolower($textes['billet'])); ?> PDF sera envoye a <strong><?php echo e($ticket->email_acheteur); ?></strong>
                        </p>

                        <div id="paymentError" class="mt-3" style="display: none; color: var(--danger); font-size: 0.85rem;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="https://cdn.fedapay.com/checkout.js?v=1.1.3"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnFedaPay');
    const errorDiv = document.getElementById('paymentError');
    const callbackUrl = '<?php echo e(route('paiement.callback')); ?>?ticket=<?php echo e($ticket->id); ?>';

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
            amount: <?php echo e((int) $montantTotal); ?>,
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/evenement-public/paiement.blade.php ENDPATH**/ ?>