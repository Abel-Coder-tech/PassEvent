@extends('layouts.agent')

@section('title', 'Scan — Agent PaxEvent')

@push('styles')
<style>
    .scanner-area {
        background: #1a1a2e;
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        height: min(58vh, 480px);
        min-height: 300px;
        transition: box-shadow 0.3s ease;
    }
    #reader { width: 100%; height: 100%; }
    #reader video { width: 100% !important; height: 100% !important; object-fit: cover; border-radius: 16px; }
    .scan-corners {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: 200px; height: 200px;
        z-index: 10;
        pointer-events: none;
    }
    .scan-corners::before, .scan-corners::after,
    .scan-corners .corner-bl, .scan-corners .corner-br {
        content: ''; position: absolute;
        width: 30px; height: 30px;
        border-color: var(--violet-clair);
        border-style: solid;
    }
    .scan-corners::before { top:0; left:0; border-width:3px 0 0 3px; }
    .scan-corners::after { top:0; right:0; border-width:3px 3px 0 0; }
    .scan-corners .corner-bl { bottom:0; left:0; border-width:0 0 3px 3px; }
    .scan-corners .corner-br { bottom:0; right:0; border-width:0 3px 3px 0; }
    .scan-frame {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: 85%; height: 60%;
        border: 2px dashed var(--violet-clair);
        border-radius: 20px;
        z-index: 10;
        pointer-events: none;
        transition: border-color 0.25s ease, box-shadow 0.25s ease;
    }
    .scanner-area.scan-ok .scan-frame {
        border: 2px solid #28a745;
        box-shadow: 0 0 0 3px rgba(40,167,69,0.35), 0 0 22px rgba(40,167,69,0.55);
    }
    .scanner-area.scan-ok .scan-corners::before,
    .scanner-area.scan-ok .scan-corners::after,
    .scanner-area.scan-ok .scan-corners .corner-bl,
    .scanner-area.scan-ok .scan-corners .corner-br { border-color: #28a745; }
    .scan-line {
        position: absolute;
        left: 6%; right: 6%;
        height: 2px;
        top: 20%;
        z-index: 11;
        pointer-events: none;
        border-radius: 2px;
        display: none;
        background: linear-gradient(90deg, transparent, var(--violet-clair), transparent);
        box-shadow: 0 0 8px var(--violet-clair);
        animation: scanSweep 1.8s linear infinite;
    }
    .scanner-area.scanning .scan-line { display: block; }
    .scanner-area.detecting .scan-line {
        background: linear-gradient(90deg, transparent, #28a745, transparent);
        box-shadow: 0 0 10px #28a745;
    }
    @keyframes scanSweep {
        0% { top: 18%; }
        50% { top: 82%; }
        100% { top: 18%; }
    }
    .scan-progress {
        position: absolute;
        left: 0; right: 0; bottom: 0;
        height: 4px;
        z-index: 12;
        display: none;
        background: rgba(255,255,255,0.08);
    }
    .scanner-area.scanning .scan-progress { display: block; }
    .scan-progress span {
        display: block;
        height: 100%;
        width: 0;
        background: #28a745;
        transition: width 0.1s linear;
    }
    .scan-region-highlight { opacity: 0; }
    .result-valid {
        background: #d4edda;
        border: 2px solid #28a745;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        animation: popIn 0.3s ease;
    }
    .result-invalid {
        background: #f8d7da;
        border: 2px solid #dc3545;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        animation: popIn 0.3s ease;
    }
    @keyframes popIn {
        0% { transform: scale(0.9); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .stat-scan { text-align: center; padding: 0.75rem; }
    .stat-scan .value { font-size: 1.3rem; font-weight: 800; color: var(--violet); }
    .stat-scan .label { font-size: 0.72rem; color: #6c757d; }
</style>
@endpush

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="fw-bold mb-0"><i class="bi bi-qr-code-scan me-2" style="color:var(--violet);"></i>Scan</h5>
            <small class="text-muted">{{ $evenement->titre }}</small>
        </div>
        <a href="{{ route('agent.scan.exit') }}" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-stop-circle me-1"></i>Quitter
        </a>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-4"><div class="stat-scan card-agent p-2"><div class="value" id="statTotal">{{ $stats['total'] }}</div><div class="label">Total</div></div></div>
        <div class="col-4"><div class="stat-scan card-agent p-2"><div class="value" id="statValides" style="color:#28a745;">{{ $stats['valides'] }}</div><div class="label">Validés</div></div></div>
        <div class="col-4"><div class="stat-scan card-agent p-2"><div class="value" id="statInvalides" style="color:#dc3545;">{{ $stats['invalides'] }}</div><div class="label">Invalides</div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card-agent p-0">
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0"><i class="bi bi-camera me-2" style="color:var(--violet-clair);"></i>Scanner QR Code</h6>
                    <button type="button" class="btn btn-violet btn-sm" id="btnToggleCamera">
                        <i class="bi bi-camera me-1" id="cameraBtnIcon"></i><span id="cameraBtnText">Activer</span>
                    </button>
                </div>
                <div class="p-0">
                    <div class="scanner-area" id="scannerContainer">
                        <div id="reader"></div>
                        <div class="scan-corners" id="scanCorners" style="display:none;">
                            <div class="corner-bl"></div>
                            <div class="corner-br"></div>
                        </div>
                        <div class="scan-frame" id="scanFrame" style="display:none;"></div>
                        <div class="scan-line" id="scanLine"></div>
                        <div class="scan-progress"><span id="scanProgressFill"></span></div>
                        <div class="d-flex align-items-center justify-content-center" style="height:300px;" id="cameraPlaceholder">
                            <div class="text-center text-muted">
                                <i class="bi bi-camera" style="font-size:3rem;opacity:0.3;"></i>
                                <p class="mt-2 mb-0" style="font-size:0.85rem;">Activez la camera pour scanner</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="cameraStatus" class="px-3 py-2 text-center small" style="display:none;"></div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-agent p-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-keyboard me-2" style="color:var(--violet);"></i>Saisie manuelle</h6>
                <form id="manualScanForm">
                    <div class="mb-3">
                        <input type="text" id="codeInput" name="code" class="form-control text-center py-2" value="PAX-" placeholder="Saisissez la suite du code (ex: GRH5S)" autocomplete="off" style="text-transform:uppercase;" required>
                    </div>
                    <button type="submit" class="btn btn-violet w-100 py-2" id="btnVerify">
                        <i class="bi bi-search me-1"></i> Vérifier
                    </button>
                </form>
            </div>

            <div id="scanResult" style="display:none;" class="mt-3"></div>
        </div>
    </div>

    <div class="card-agent p-0 mt-4">
        <div class="p-2 border-bottom">
            <small class="fw-bold"><i class="bi bi-clock-history me-1"></i>Derniers scans</small>
        </div>
        <div style="max-height:260px;overflow-y:auto;">
            <table class="table table-sm mb-0">
                <tbody id="recentList">
                    @forelse($recent as $log)
                    <tr>
                        <td class="small">{{ $log->created_at->format('H:i:s') }}</td>
                        <td><code style="font-size:0.7rem;">{{ Str::limit($log->details['code'] ?? '', 15) }}</code></td>
                        <td>
                            @if(($log->details['resultat'] ?? '') === 'valide')
                                <span class="badge bg-success">OK</span>
                            @else
                                <span class="badge bg-danger">Non</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr id="recentEmpty">
                        <td colspan="3" class="text-center text-muted small py-3">Aucun scan pour le moment.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('partials.scan-sound')
<script>
// Charge la lib depuis 2 CDN : sur certains reseaux (forfait data bloque en
// salle d'evenement) unpkg seul echoue et la camera devient inutilisable.
(function () {
    var sources = [
        'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js',
        'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js'
    ];
    var index = 0;
    function loadNext() {
        if (index >= sources.length) { return; }
        var script = document.createElement('script');
        script.src = sources[index++];
        script.onerror = loadNext;
        document.head.appendChild(script);
    }
    loadNext();
})();
</script>
<script>
let html5QrCode = null;
let isScanning = false;
let isStarting = false;
let isReleasing = false;
let lastCameraError = null;

// Stabilisation : le QR doit rester lisible ~0,8 s avant d'etre valide, pour
// que l'agent voie la confirmation (ligne verte + barre) et evite un scan
// accidentel. Apres validation : cooldown court, la camera reste active.
const HOLD_MS = 800;
const LOST_MS = 400;
const COOLDOWN_MS = 1000;
let pendingCode = null;
let pendingSince = 0;
let lastDetectAt = 0;
let blockedCode = null;
let cooldownUntil = 0;

const SCAN_CONFIG = {
    fps: 15,
    qrbox: function (viewfinderWidth, viewfinderHeight) {
        return {
            width: Math.round(viewfinderWidth * 0.85),
            height: Math.round(viewfinderHeight * 0.6)
        };
    },
};

function createScannerInstance() {
    // useBarCodeDetectorIfSupported=false force le decodeur ZXing (JS pur) :
    // sur certains telephones (Samsung/Chrome notamment), l'API native
    // BarcodeDetector est cassee et rend l'instance du scanner inutilisable.
    return new Html5Qrcode("reader", { useBarCodeDetectorIfSupported: false });
}

const CAMERA_ATTEMPTS = [
    { facingMode: "environment" },
    { facingMode: { exact: "environment" } },
    { facingMode: "user" },
];

document.getElementById('btnToggleCamera')?.addEventListener('click', toggleCamera);

function toggleCamera() {
    if (isScanning || isStarting) { stopCamera(); }
    else { startCamera(); }
}

function setCameraButton(active) {
    const icon = document.getElementById('cameraBtnIcon');
    const text = document.getElementById('cameraBtnText');
    if (icon) icon.className = 'bi ' + (active ? 'bi-stop-circle' : 'bi-camera') + ' me-1';
    if (text) text.textContent = active ? 'Arrêter' : 'Activer';
}

function setCameraStatus(message, isError) {
    const status = document.getElementById('cameraStatus');
    if (!status) return;
    if (!message) {
        status.style.display = 'none';
        status.textContent = '';
        return;
    }
    status.style.display = 'block';
    status.className = 'px-3 py-2 text-center small ' + (isError ? 'text-danger' : 'text-muted');
    status.textContent = message;
}

function showScannerChrome(active) {
    const reader = document.getElementById('reader');
    const placeholder = document.getElementById('cameraPlaceholder');
    const corners = document.getElementById('scanCorners');
    const frame = document.getElementById('scanFrame');
    if (reader) reader.style.display = active ? 'block' : 'none';
    if (placeholder) placeholder.style.display = active ? 'none' : 'flex';
    if (corners) corners.style.display = active ? 'block' : 'none';
    if (frame) frame.style.display = active ? 'block' : 'none';
    setScanning(active);
}

// Verifie les prerequis AVANT d'appeler getUserMedia : sinon le navigateur
// echoue silencieusement et l'agent ne comprend pas la cause du refus.
function preflightCamera() {
    if (!window.isSecureContext) {
        return "Connexion non securisee : la camera n'est accessible qu'en HTTPS. Recharge la page avec une adresse https://.";
    }
    if (typeof Html5Qrcode === 'undefined') {
        return "Le lecteur de QR codes n'a pas pu etre charge. Verifie la connexion internet puis recharge la page.";
    }
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        return "Ce navigateur ne supporte pas l'acces camera. Essaie avec Chrome ou Safari.";
    }
    return null;
}

async function cameraPermissionState() {
    try {
        if (navigator.permissions && navigator.permissions.query) {
            const status = await navigator.permissions.query({ name: 'camera' });
            return status.state;
        }
    } catch (e) {
        // Safari iOS ne supporte pas la requete 'camera'.
    }
    return 'unknown';
}

function delay(ms) {
    return new Promise(function (resolve) { setTimeout(resolve, ms); });
}

// Libere completement la session camera : stop() + clear() + purge du DOM.
// stop() rejette souvent (scanner jamais demarre ou deja arrete) : sans ce
// try/catch la <video> reste dans #reader et la prochaine activation echoue
// avec un NotReadableError.
async function releaseScanner() {
    isReleasing = true;
    const instance = html5QrCode;
    html5QrCode = null;
    if (instance) {
        try { await instance.stop(); } catch (e) {}
        try { instance.clear(); } catch (e) {}
    }
    const reader = document.getElementById('reader');
    if (reader) {
        while (reader.firstChild) { reader.removeChild(reader.firstChild); }
    }
    isReleasing = false;
}

async function tryStartScanner(constraints) {
    const instance = createScannerInstance();
    try {
        await instance.start(constraints, SCAN_CONFIG, onScanSuccess);
        html5QrCode = instance;
        return true;
    } catch (err) {
        lastCameraError = err;
        try { await instance.stop(); } catch (e) {}
        try { instance.clear(); } catch (e) {}
        const reader = document.getElementById('reader');
        if (reader) {
            while (reader.firstChild) { reader.removeChild(reader.firstChild); }
        }
        return false;
    }
}

async function startCamera() {
    if (isScanning || isStarting || isReleasing) { return; }

    const blocker = preflightCamera();
    if (blocker) { showCameraError(blocker); return; }

    if (await cameraPermissionState() === 'denied') {
        showCameraError("Acces camera refuse pour ce site. Autorise la camera dans les reglages du navigateur (icone cadenas a gauche de l'URL), puis recharge la page.");
        return;
    }

    isStarting = true;
    lastCameraError = null;
    showScannerChrome(true);
    setCameraStatus('Demarrage de la camera...', false);

    // API navigateur d'abord : le deviceId de la camera arriere est plus
    // fiable que les contraintes facingMode sur Android.
    let cameras = [];
    try {
        cameras = await Html5Qrcode.getCameras();
    } catch (err) {
        lastCameraError = err;
        cameras = [];
    }

    if (cameras && cameras.length > 0) {
        const back = cameras.find(function (camera) {
            return /back|rear|environment|arriere/i.test(camera.label || '');
        }) || cameras[cameras.length - 1];

        if (await tryStartScanner(back.id)) { onCameraStarted(); return; }
    }

    for (const attempt of CAMERA_ATTEMPTS) {
        if (await tryStartScanner(attempt)) { onCameraStarted(); return; }
        // Delai entre 2 tentatives : sur certains telephones la camera
        // n'est pas encore liberee (NotReadableError) si on redemarre
        // immediatement.
        await delay(600);
    }

    isStarting = false;
    showScannerChrome(false);
    setCameraButton(false);
    setCameraStatus(describeCameraError(lastCameraError), true);
}

function describeCameraError(err) {
    const text = String((err && (err.name + ' ' + err.message)) || err || '');

    if (/NotAllowedError|PermissionDeniedError/i.test(text)) {
        return "Acces camera refuse par le navigateur. Autorise la camera pour ce site dans les reglages du navigateur puis reessaie.";
    }
    if (/NotFoundError|DevicesNotFoundError/i.test(text)) {
        return "Aucune camera trouvee sur cet appareil.";
    }
    if (/NotReadableError|TrackStartError|AbortError|in use|busy/i.test(text)) {
        return "La camera est deja utilisee par une autre application. Ferme les autres apps puis reessaie.";
    }
    if (/SecurityError|NotSupportedError|secure context|https/i.test(text)) {
        return "La camera necessite une connexion securisee (HTTPS).";
    }
    return "Impossible d'activer la camera. Verifie que le site est en HTTPS et que la camera est autorisee dans ton navigateur.";
}

function onCameraStarted() {
    isStarting = false;
    isScanning = true;
    lastCameraError = null;
    resetScanHold();
    setScanning(true);
    setCameraButton(true);
    setCameraStatus('Camera active. Pointez le QR code du ticket.', false);
}

function showCameraError(message) {
    isStarting = false;
    isScanning = false;
    showScannerChrome(false);
    setCameraButton(false);
    setCameraStatus(message, true);
    if (lastCameraError) { console.warn('Scan agent - erreur camera :', lastCameraError); }
}

async function stopCamera() {
    isScanning = false;
    isStarting = false;
    await releaseScanner();
    showScannerChrome(false);
    setCameraButton(false);
    setCameraStatus('', false);
    clearScanOk();
}

function flashScanOk() {
    const container = document.getElementById('scannerContainer');
    if (container) container.classList.add('scan-ok');
    setTimeout(clearScanOk, 700);
}

function clearScanOk() {
    const container = document.getElementById('scannerContainer');
    if (container) container.classList.remove('scan-ok');
}

function scannerArea() {
    return document.getElementById('scannerContainer');
}

function setScanning(active) {
    const area = scannerArea();
    if (area) area.classList.toggle('scanning', active);
    if (!active) { cancelPending(); }
}

function updateProgress(ratio) {
    const fill = document.getElementById('scanProgressFill');
    if (fill) fill.style.width = Math.round(Math.max(0, Math.min(1, ratio)) * 100) + '%';
}

function cancelPending() {
    pendingCode = null;
    pendingSince = 0;
    const area = scannerArea();
    if (area) area.classList.remove('detecting');
    updateProgress(0);
}

function resetScanHold() {
    pendingCode = null;
    pendingSince = 0;
    lastDetectAt = 0;
    blockedCode = null;
    cooldownUntil = 0;
    cancelPending();
}

// Surveille la continuite de lecture : si le QR disparait du cadre, on annule
// la stabilisation en cours et on oublie le dernier code valide.
setInterval(function () {
    if (!lastDetectAt) return;
    if (Date.now() - lastDetectAt > LOST_MS) {
        if (pendingCode) { cancelPending(); }
        blockedCode = null;
    }
}, 100);

function onScanSuccess(decodedText) {
    const now = Date.now();
    lastDetectAt = now;

    if (now < cooldownUntil) return;
    if (blockedCode === decodedText) return;

    if (pendingCode !== decodedText) {
        pendingCode = decodedText;
        pendingSince = now;
        const area = scannerArea();
        if (area) area.classList.add('detecting');
    }

    updateProgress((now - pendingSince) / HOLD_MS);

    if (now - pendingSince >= HOLD_MS) {
        validatePending();
    }
}

function validatePending() {
    if (!pendingCode) return;
    const code = pendingCode;
    blockedCode = code;
    cooldownUntil = Date.now() + COOLDOWN_MS;
    cancelPending();
    flashScanOk();
    submitScan(code);
}

document.getElementById('manualScanForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const code = document.getElementById('codeInput').value.trim();
    if (!code) return;
    submitScan(code);
    this.reset();
});

function submitScan(code) {
    const resultDiv = document.getElementById('scanResult');
    resultDiv.style.display = 'none';

    fetch('{{ route("agent.scan.verifier") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ code: code })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.ScanSound.success();
            let txn = data.ticket?.transaction_id
                ? '<small class="d-block mt-1 text-muted"> <strong class="text-dark">' + escapeHtml(data.ticket.transaction_id) + '</strong></small>'
                : '';
            resultDiv.innerHTML = '<div class="result-valid">' +
                '<i class="bi bi-check-circle-fill" style="font-size:2.5rem;color:#28a745;"></i>' +
                '<h5 class="mt-2 mb-1 text-success">Ticket validé !</h5>' +
                '<p class="mb-1 fw-semibold">' + escapeHtml(data.ticket?.nom || '') + '</p>' +
                '<small class="text-muted">' + escapeHtml(data.ticket?.nom_tarif || '') + ' | ' + escapeHtml(data.ticket?.montant || '') + '</small>' +
                txn +
                '</div>';
        } else {
            window.ScanSound.failure();
            let extra = '';
            if (data.ticket) {
                extra = '<div class="mt-2 p-2 bg-light rounded small">' +
                    'Déjà scanné par : <strong>' + escapeHtml(data.ticket.nom || '') + '</strong>' +
                    ' le ' + escapeHtml(data.ticket.date || '') +
                    '</div>';
            }
            resultDiv.innerHTML = '<div class="result-invalid">' +
                '<i class="bi bi-x-circle-fill" style="font-size:2.5rem;color:#dc3545;"></i>' +
                '<h5 class="mt-2 mb-1 text-danger">' + escapeHtml(data.message) + '</h5>' +
                extra +
                '</div>';
        }
        resultDiv.style.display = 'block';
        resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    })
    .catch(() => {
        window.ScanSound.failure();
        resultDiv.innerHTML = '<div class="result-invalid"><p class="mb-0 text-danger">Erreur de connexion.</p></div>';
        resultDiv.style.display = 'block';
        resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
}

function refreshHistory() {
    fetch('{{ route("agent.scan.historique") }}', {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.stats) {
            document.getElementById('statTotal').textContent = data.stats.total;
            document.getElementById('statValides').textContent = data.stats.valides;
            document.getElementById('statInvalides').textContent = data.stats.invalides;
        }
        if (Array.isArray(data.recent)) {
            renderRecent(data.recent);
        }
    })
    .catch(() => {});
}

function renderRecent(recent) {
    const tbody = document.getElementById('recentList');
    if (recent.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted small py-3">Aucun scan pour le moment.</td></tr>';
        return;
    }
    tbody.innerHTML = recent.map(s => {
        const badge = s.resultat === 'valide'
            ? '<span class="badge bg-success">OK</span>'
            : '<span class="badge bg-danger">Non</span>';
        return '<tr>' +
            '<td class="small">' + escapeHtml(s.heure) + '</td>' +
            '<td><code style="font-size:0.7rem;">' + escapeHtml(String(s.code).substring(0, 15)) + '</code></td>' +
            '<td>' + badge + '</td>' +
            '</tr>';
    }).join('');
}

refreshHistory();
setInterval(refreshHistory, 10000);
</script>
@endpush
