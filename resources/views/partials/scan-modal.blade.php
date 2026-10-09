{{-- Modal de scan : carte centree qui affiche "verification en cours" puis
     le resultat (valide / deja scanne / invalide). API : window.scanModal.
     La camera reste allumee derriere ; le bouton "Continuer" relance le scan. --}}
<div id="scanModal" class="scan-modal" aria-hidden="true">
    <div class="scan-modal-backdrop"></div>
    <div class="scan-modal-card" role="dialog" aria-modal="true">
        <div id="scanModalIcon" class="scan-modal-icon"></div>
        <h5 id="scanModalTitle" class="scan-modal-title"></h5>
        <p id="scanModalMessage" class="scan-modal-message"></p>
        <div id="scanModalDetails" class="scan-modal-details"></div>
        <button type="button" id="scanModalContinue" class="scan-modal-btn">
            Suivant <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </div>
</div>

<style>
    .scan-modal {
        position: fixed;
        inset: 0;
        z-index: 3000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .scan-modal.is-open { display: flex; }
    .scan-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 15, 30, 0.5);
        backdrop-filter: blur(2px);
    }
    .scan-modal-card {
        position: relative;
        width: 100%;
        max-width: 360px;
        background: #fff;
        border-radius: 18px;
        padding: 1.6rem 1.4rem 1.3rem;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
        animation: scanModalPop 0.22s ease;
    }
    @keyframes scanModalPop {
        from { transform: scale(0.92); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .scan-modal-icon { font-size: 3rem; line-height: 1; margin-bottom: 0.6rem; }
    .scan-modal.is-verifying .scan-modal-icon { color: #7B3FA0; }
    .scan-modal.is-success .scan-modal-icon { color: #28a745; }
    .scan-modal.is-warning .scan-modal-icon { color: #f39c12; }
    .scan-modal.is-failure .scan-modal-icon { color: #dc3545; }
    .scan-modal-title { font-weight: 800; margin: 0 0 0.35rem; font-size: 1.15rem; }
    .scan-modal.is-success .scan-modal-title { color: #28a745; }
    .scan-modal.is-warning .scan-modal-title { color: #f39c12; }
    .scan-modal.is-failure .scan-modal-title { color: #dc3545; }
    .scan-modal-message { font-size: 0.9rem; color: #444; margin: 0 0 0.6rem; }
    .scan-modal-details {
        font-size: 0.8rem;
        color: #555;
        text-align: left;
        background: #f7f7fb;
        border-radius: 10px;
        padding: 0.6rem 0.75rem;
        margin-bottom: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }
    .scan-modal-details:empty { display: none; }
    .scan-modal-btn {
        width: 100%;
        font-weight: 700;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 0.7rem 1rem;
        background: #7B3FA0;
    }
    .scan-modal.is-success .scan-modal-btn { background: #28a745; }
    .scan-modal.is-warning .scan-modal-btn { background: #f39c12; }
    .scan-modal.is-failure .scan-modal-btn { background: #dc3545; }
    .scan-spinner {
        display: inline-block;
        width: 2.6rem;
        height: 2.6rem;
        border: 4px solid rgba(123, 63, 160, 0.2);
        border-top-color: #7B3FA0;
        border-radius: 50%;
        animation: scanSpin 0.8s linear infinite;
    }
    @keyframes scanSpin { to { transform: rotate(360deg); } }
</style>

<script>
(function () {
    var el = document.getElementById('scanModal');
    if (!el) { return; }
    var icon = document.getElementById('scanModalIcon');
    var titleEl = document.getElementById('scanModalTitle');
    var messageEl = document.getElementById('scanModalMessage');
    var detailsEl = document.getElementById('scanModalDetails');
    var btn = document.getElementById('scanModalContinue');

    function show(state, opts) {
        opts = opts || {};
        el.classList.remove('is-verifying', 'is-success', 'is-warning', 'is-failure');
        el.classList.add('is-' + state, 'is-open');
        titleEl.textContent = opts.title || '';
        messageEl.textContent = opts.message || '';
        messageEl.style.display = opts.message ? 'block' : 'none';
        detailsEl.innerHTML = opts.details || '';

        if (state === 'verifying') {
            icon.innerHTML = '<span class="scan-spinner"></span>';
            btn.style.display = 'none';
        } else {
            var glyph = state === 'success' ? 'bi-check-circle-fill'
                : (state === 'warning' ? 'bi-arrow-repeat' : 'bi-x-circle-fill');
            icon.innerHTML = '<i class="bi ' + glyph + '"></i>';
            btn.style.display = 'block';
        }
    }

    window.scanModal = {
        onContinue: null,
        verifying: function (opts) { show('verifying', opts); },
        success: function (opts) { show('success', opts); },
        warning: function (opts) { show('warning', opts); },
        failure: function (opts) { show('failure', opts); },
        close: function () { el.classList.remove('is-open'); }
    };

    btn.addEventListener('click', function () {
        window.scanModal.close();
        if (typeof window.scanModal.onContinue === 'function') {
            window.scanModal.onContinue();
        }
    });
})();
</script>
