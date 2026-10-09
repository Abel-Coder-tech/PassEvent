{{-- Sons de retour du scan : Web Audio API, aucun fichier audio a heberger.
     Scan valide -> double bip aigu, scan rejete / erreur -> double buzz medium. --}}
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

    function play(audio, freq, ms, delayMs, type) {
        const start = audio.currentTime + (delayMs || 0) / 1000;
        const osc = audio.createOscillator();
        const gain = audio.createGain();

        osc.type = type || 'sine';
        osc.frequency.value = freq;

        // Enveloppe volume 0 -> fort -> 0 : evite le claquement sec du haut-parleur.
        gain.gain.setValueAtTime(0, start);
        gain.gain.linearRampToValueAtTime(0.35, start + 0.01);
        gain.gain.linearRampToValueAtTime(0, start + ms / 1000);

        osc.connect(gain);
        gain.connect(audio.destination);

        osc.start(start);
        osc.stop(start + ms / 1000 + 0.02);
    }

    function beep(freq, ms, delayMs, type) {
        const audio = context();
        if (!audio) { return; }

        // Sur iOS/Chrome le contexte peut etre "suspended" : on attend la
        // reprise avant de programmer le son, sinon il reste muet.
        if (audio.state === 'suspended') {
            audio.resume().then(function () {
                play(audio, freq, ms, delayMs, type);
            }).catch(function () {
                play(audio, freq, ms, delayMs, type);
            });
        } else {
            play(audio, freq, ms, delayMs, type);
        }
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
        // Succes : double bip aigu, tres audible sur petit haut-parleur.
        success: function () { beep(880, 90); beep(1320, 90, 120); },
        // Echec : double buzz medium/grave en ondes carrees. Un simple 220 Hz
        // etait quasi inaudible sur un haut-parleur de telephone.
        failure: function () { beep(480, 150, 0, 'square'); beep(320, 280, 170, 'square'); },
    };
})();
</script>