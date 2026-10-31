<?php $__env->startSection('title', 'Paiement QR codes - Billetterie'); ?>
<?php $__env->startSection('page-title', $estDemande ? 'Paiement de votre demande' : 'Paiement de votre commande'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.lots-physiques.index')); ?>">Vente physique</a></li>
    <li class="breadcrumb-item active" aria-current="page">Paiement</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <style>
    .steps-bar { display: flex; align-items: center; margin-bottom: 1.5rem; overflow-x: auto; padding-bottom: .25rem; }
    .step-item { display: flex; align-items: center; flex: 1; min-width: max-content; }
    .step-dot { width: 30px; height: 30px; border-radius: 50%; background: #e9ecef; color: #6c757d; display: flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 700; flex-shrink: 0; transition: all .3s; }
    .step-label { font-size: .72rem; color: #6c757d; margin-left: .4rem; white-space: nowrap; }
    .step-line { flex: 1; height: 2px; background: #e9ecef; margin: 0 .5rem; min-width: 16px; transition: background .3s; }
    .step-item.done .step-dot { background: #198754; color: #fff; }
    .step-item.done .step-label { color: #198754; }
    .step-item.done .step-line { background: #198754; }
    .step-item.active .step-dot { background: #542680; color: #fff; box-shadow: 0 0 0 4px rgba(84,38,128,.15); }
    .step-item.active .step-label { color: #542680; font-weight: 700; }
    </style>

    <!-- Barre de progression : étape Événement validée, paiement en cours -->
    <div class="steps-bar">
        <div class="step-item done"><span class="step-dot"><i class="bi bi-check"></i></span><span class="step-label">Événement</span><span class="step-line"></span></div>
        <div class="step-item active"><span class="step-dot">2</span><span class="step-label">Paiement</span><span class="step-line"></span></div>
        <div class="step-item"><span class="step-dot">3</span><span class="step-label"><?php echo e($estDemande ? 'Génération' : 'Téléchargement'); ?></span></div>
    </div>

    <?php if(session('error')): ?>
    <div class="alert alert-danger py-2 small"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <strong><i class="bi bi-qr-code me-1" style="color:#542680;"></i> Commande <?php echo e($reference); ?></strong>
                </div>
                <div class="card-body p-3 p-md-4">
                    <?php $__currentLoopData = $lots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom small">
                        <span>
                            <span class="badge me-1" style="background:#542680;color:#fff;"><?php echo e($lot->tarif?->nom ?? 'Pass'); ?></span>
                            <?php echo e($lot->quantite); ?> ticket(s)
                        </span>
                        <span class="fw-semibold"><?php echo e(number_format($lot->montant_commission, 0, ',', ' ')); ?> F</span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <div class="d-flex justify-content-between align-items-center pt-3 pb-2">
                        <span class="fw-semibold">Total à payer</span>
                        <strong style="font-size:1.4rem;color:#542680;"><?php echo e(number_format($total, 0, ',', ' ')); ?> F</strong>
                    </div>

                    <div class="alert alert-light border py-2 mb-3" style="font-size:.78rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Commission de génération (5 % du prix des billets), payée d'avance et non remboursable.
                        <?php if($estDemande): ?>
                            Votre template est déjà envoyé avec la demande : après le paiement, le super admin positionne les QR codes dessus puis vous transmet la planche.
                        <?php else: ?>
                            Vos planches PDF seront disponibles immédiatement après confirmation du paiement.
                        <?php endif; ?>
                        Réception envoyée à <strong><?php echo e($lots->first()->email_reception); ?></strong>.
                    </div>

                    <button type="button" id="btnFedaPay" class="btn w-100 text-white" style="background:#542680;border-radius:10px;font-weight:700;padding:.7rem;">
                        <i class="bi bi-shield-lock me-1"></i> Payer <?php echo e(number_format($total, 0, ',', ' ')); ?> F avec FedaPay
                    </button>
                    <div id="paymentError" class="mt-3" style="display:none;color:#dc3545;font-size:.85rem;"></div>

                    <div class="text-center mt-3">
                        <a href="<?php echo e(route('admin.lots-physiques.index')); ?>" class="text-decoration-none" style="font-size:.82rem;">
                            <i class="bi bi-arrow-left me-1"></i>Modifier ma commande
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="https://cdn.fedapay.com/checkout.js?v=1.1.3"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnFedaPay');
    const errorDiv = document.getElementById('paymentError');
    const callbackUrl = '<?php echo e(route('paiement.callback')); ?>?source=lot_physique&reference=<?php echo e($reference); ?>';

    if (!btn) return;

    if (typeof FedaPay === 'undefined') {
        errorDiv.textContent = 'Erreur de chargement du module de paiement. Actualisez la page.';
        errorDiv.style.display = 'block';
        return;
    }

    FedaPay.init(btn, {
        public_key: '<?php echo e($publicKey); ?>',
        environment: '<?php echo e($sandbox ? 'sandbox' : 'live'); ?>',
        transaction: {
            amount: <?php echo e((int) ceil($total)); ?>,
            description: '<?php echo e($estDemande ? 'Demande de QR codes' : 'QR codes physiques'); ?> - <?php echo e($lots->first()->evenement?->titre ?? 'PaxEvent'); ?>',
            external_id: '<?php echo e($reference); ?>',
            custom_metadata: {
                type: 'lot_physique',
                reference: '<?php echo e($reference); ?>'
            }
        },
        customer: {
            email: '<?php echo e($lots->first()->email_reception); ?>',
            firstname: '<?php echo e(addslashes(Auth::user()->nom)); ?>'
        },
        currency: {
            iso: 'XOF'
        },
        onComplete: function(data) {
            if (data.transaction && data.transaction.id) {
                window.location.href = callbackUrl + '&id=' + data.transaction.id + '&status=' + (data.transaction.status || 'approved');
            } else {
                errorDiv.textContent = 'Paiement annulé ou fermé. Vous pouvez réessayer.';
                errorDiv.style.display = 'block';
            }
        }
    });

    // Ouvre automatiquement la fenêtre de paiement à l'arrivée sur la page
    setTimeout(function () { btn.click(); }, 600);
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/admin/lots-physiques/paiement.blade.php ENDPATH**/ ?>