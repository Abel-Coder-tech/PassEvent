{{-- Bulle d'aide : invite l'utilisateur a activer la camera (surtout sur mobile).
     Pointe vers le bouton #btnToggleCamera tant que la camera n'est pas active. --}}
<div id="cameraHint" class="camera-hint" style="display:none;">
    <div class="camera-hint-bubble">
        <i class="bi bi-camera-video-fill"></i>
        <span>Veuillez d'abord activer votre caméra</span>
    </div>
    <div class="camera-hint-arrow"></div>
</div>

<style>
    .camera-hint {
        position: fixed;
        z-index: 2050;
        width: max-content;
        pointer-events: none;
    }
    .camera-hint-bubble {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        max-width: min(280px, calc(100vw - 24px));
        padding: 0.55rem 0.8rem;
        background: #1a1a2e;
        color: #fff;
        font-size: 0.8rem;
        font-weight: 600;
        line-height: 1.2;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.28);
        animation: cameraHintPop 0.28s ease;
    }
    .camera-hint-bubble i { color: #66d9a0; font-size: 1rem; }
    .camera-hint-arrow {
        position: absolute;
        left: 50%;
        width: 12px;
        height: 12px;
        background: #1a1a2e;
        transform: rotate(45deg);
        margin-left: -6px;
    }
    .camera-hint.is-below .camera-hint-arrow { top: -6px; }
    .camera-hint.is-above .camera-hint-arrow { bottom: -6px; }
    @keyframes cameraHintPop {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .camera-btn-pulse {
        animation: cameraBtnPulse 1.4s ease-in-out infinite;
    }
    @keyframes cameraBtnPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(40,167,69,0.55); }
        50% { box-shadow: 0 0 0 9px rgba(40,167,69,0); }
    }
</style>

<script>
(function () {
    var btn = document.getElementById('btnToggleCamera');
    var hint = document.getElementById('cameraHint');
    if (!btn || !hint) { return; }

    var arrow = hint.querySelector('.camera-hint-arrow');

    function showHint() {
        if (btn.dataset.cameraActive === '1') { return; }
        btn.classList.add('camera-btn-pulse');
        hint.style.display = 'block';

        var r = btn.getBoundingClientRect();
        var w = hint.offsetWidth;
        var h = hint.offsetHeight;

        var left = Math.min(Math.max(8, r.left + (r.width / 2) - (w / 2)), window.innerWidth - w - 8);
        var top = r.bottom + 12;
        var above = false;
        if (top + h > window.innerHeight - 8) {
            top = r.top - h - 12;
            above = true;
        }

        hint.style.left = left + 'px';
        hint.style.top = top + 'px';
        hint.classList.toggle('is-above', above);
        hint.classList.toggle('is-below', !above);
        if (arrow) { arrow.style.left = (r.left + (r.width / 2) - left) + 'px'; }
    }

    function hideHint() {
        hint.style.display = 'none';
        btn.classList.remove('camera-btn-pulse');
    }

    btn.addEventListener('click', hideHint);
    window.addEventListener('resize', function () {
        if (hint.style.display !== 'none') { showHint(); }
    });
    window.addEventListener('scroll', function () {
        if (hint.style.display !== 'none') { showHint(); }
    }, true);

    // Expose pour les scripts de la page (masquage a l'activation camera).
    window.cameraHint = { show: showHint, hide: hideHint };

    // Laisse le layout se poser puis affiche la bulle.
    setTimeout(showHint, 500);
})();
</script>
