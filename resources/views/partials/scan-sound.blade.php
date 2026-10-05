{{-- Sons de retour du scan : Web Audio API, aucun fichier audio a heberger.
     Scan valide -> double bip aigu, scan rejete / erreur -> buzz grave long. --}}
<script>
window.ScanSound = (function () {
    let ctx = null;

    function context() {
        if (!ctx) {
            const Ctor = window.AudioContext || window.webkitAudioContext;
            if (!Ctor) { return null; }
            ctx = new Ctor();
        }
        if (ctx.state === 'suspended') { ctx.resume(); }
        return ctx;
    }

    function beep(freq, ms, delayMs) {
        const audio = context();
        if (!audio) { return; }

        const start = audio.currentTime + (delayMs || 0) / 1000;
        const osc = audio.createOscillator();
        const gain = audio.createGain();

        osc.type = 'sine';
        osc.frequency.value = freq;

        // Enveloppe volume 0 -> fort -> 0 : evite le claquement sec du haut-parleur.
        gain.gain.setValueAtTime(0, start);
        gain.gain.linearRampToValueAtTime(0.3, start + 0.01);
        gain.gain.linearRampToValueAtTime(0, start + ms / 1000);

        osc.connect(gain);
        gain.connect(audio.destination);

        osc.start(start);
        osc.stop(start + ms / 1000 + 0.02);
    }

    function unlock() {
        context();
    }

    // Chrome et Safari bloquent le son tant que l'utilisateur n'a pas
    // interagi avec la page : on deverrouille des la premiere action
    // (clic sur Activer, saisie manuelle du code, touche clavier).
    ['pointerdown', 'touchstart', 'keydown'].forEach(function (evt) {
        document.addEventListener(evt, unlock, { once: true, passive: true });
    });

    return {
        unlock: unlock,
        success: function () { beep(880, 80); beep(1320, 80, 110); },
        failure: function () { beep(220, 400); },
    };
})();
</script>