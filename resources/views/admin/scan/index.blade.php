@extends('layouts.app')

@section('title', 'Scan QR - PaxEvent')

@section('page-title', 'Scan QR Code')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item active" aria-current="page">Scan QR</li>
@endsection

@section('topbar-actions')
    <button type="button" class="btn btn-vert btn-sm" id="btnToggleCamera">
        <i class="bi bi-camera me-1"></i> <span class="btn-text">Camera</span>
    </button>
    <a href="{{ route('scan.clear') }}" class="btn btn-secondary-custom btn-sm ms-2">
        <i class="bi bi-arrow-left me-1"></i> Changer d'événement
    </a>
@endsection

@section('content')
<style>
    .scan-container {
        max-width: 700px;
        margin: 0 auto;
    }
    .scanner-area {
        background: #1a1a2e;
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        height: min(58vh, 480px);
        min-height: 300px;
        transition: box-shadow 0.3s ease;
    }
    #reader {
        width: 100%;
        height: 100%;
    }
    #reader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover;
        border-radius: 16px;
    }
    .scan-frame {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 85%;
        height: 60%;
        border: 2px dashed var(--vert);
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
    .scan-region-highlight { opacity: 0; }
    .scan-line {
        position: absolute;
        left: 6%; right: 6%;
        height: 2px;
        top: 20%;
        z-index: 11;
        pointer-events: none;
        border-radius: 2px;
        display: none;
        background: linear-gradient(90deg, transparent, var(--vert), transparent);
        box-shadow: 0 0 8px var(--vert);
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
    .scan-corners {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 200px;
        height: 200px;
        z-index: 10;
    }
    .scan-corners::before,
    .scan-corners::after,
    .scan-corners .corner-bl,
    .scan-corners .corner-br {
        content: '';
        position: absolute;
        width: 30px;
        height: 30px;
        border-color: var(--vert);
        border-style: solid;
    }
    .scan-corners::before {
        top: 0; left: 0;
        border-width: 3px 0 0 3px;
    }
    .scan-corners::after {
        top: 0; right: 0;
        border-width: 3px 3px 0 0;
    }
    .scan-corners .corner-bl {
        bottom: 0; left: 0;
        border-width: 0 0 3px 3px;
    }
    .scan-corners .corner-br {
        bottom: 0; right: 0;
        border-width: 0 3px 3px 0;
    }
    .result-card {
        border-radius: 12px;
        border: none;
        transition: all 0.3s;
    }
    .result-valid {
        background: rgba(18,151,110,0.08);
        border-left: 4px solid var(--vert);
    }
    .result-invalid {
        background: rgba(231,76,60,0.08);
        border-left: 4px solid var(--danger);
    }
    .result-warning {
        background: rgba(243,156,18,0.08);
        border-left: 4px solid #f39c12;
    }
    .pulse-success {
        animation: pulseGreen 0.5s ease-in-out;
    }
    @keyframes pulseGreen {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }
    .scan-history-item {
        padding: 0.75rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        background: var(--blanc-casse);
        transition: all 0.2s;
    }
    .scan-history-item:hover {
        background: #e5e4e8;
    }
    .code-input {
        font-family: 'Courier New', monospace;
        font-size: 1.1rem;
        letter-spacing: 2px;
        text-transform: uppercase;
    }
    @media (max-width: 767.98px) {
        .scanner-area {
            min-height: 250px;
        }
    }
</style>

<div class="page-content">
    <div class="scan-container">
        {{-- Événement en cours --}}
        <div class="d-flex align-items-center justify-content-between mb-3 p-3" style="background: linear-gradient(135deg, rgba(123,63,160,0.06), rgba(46,125,79,0.06)); border-radius: 14px;">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(123,63,160,0.1); display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-calendar-event" style="color: #7B3FA0; font-size: 1.2rem;"></i>
                </div>
                <div>
                    <div style="font-size: 0.75rem; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Événement en cours</div>
                    <div class="fw-bold" style="font-size: 1rem;">{{ $accessEvenement->titre }}</div>
                </div>
            </div>
            <a href="{{ route('scan.clear') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-size: 0.78rem;">
                <i class="bi bi-box-arrow-right me-1"></i> Quitter
            </a>
        </div>

        {{-- Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="metric-card" style="border-top-color: var(--sombre);">
                    <div class="metric-icon" style="background: rgba(61,67,69,0.1); color: var(--sombre);">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <div class="metric-label">Total scans</div>
                    <div class="metric-value" style="font-size: 1.5rem;" id="statTotal">{{ $stats['total_scans'] }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card" style="border-top-color: var(--vert);">
                    <div class="metric-icon" style="background: rgba(18,151,110,0.1); color: var(--vert);">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="metric-label">Aujourd'hui</div>
                    <div class="metric-value" style="font-size: 1.5rem;" id="statToday">{{ $stats['scans_today'] }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card" style="border-top-color: var(--teal);">
                    <div class="metric-icon" style="background: rgba(66,140,121,0.1); color: var(--teal);">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="metric-label">Validés</div>
                    <div class="metric-value" style="font-size: 1.5rem;" id="statValides">{{ $stats['scans_valides'] }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card" style="border-top-color: var(--danger);">
                    <div class="metric-icon" style="background: rgba(231,76,60,0.1); color: var(--danger);">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="metric-label">Invalides</div>
                    <div class="metric-value" style="font-size: 1.5rem;" id="statInvalides">{{ $stats['scans_invalides'] }}</div>
                </div>
            </div>
        </div>

        {{-- Scanner + Manual Entry --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="panel-card">
                    <div class="panel-card-header">
                        <h5><i class="bi bi-camera me-2" style="color: var(--vert);"></i>Scanner QR Code</h5>
                        <span class="text-muted" style="font-size: 0.78rem;" id="cameraStatus">Camera inactive</span>
                    </div>
                    <div class="panel-card-body p-0">
                        <div class="scanner-area" id="scannerContainer">
                            <div id="reader"></div>
                            <div class="scan-frame" id="scanFrame" style="display: none;"></div>
                            <div class="scan-corners" id="scanCorners" style="display: none;">
                                <div class="corner-bl"></div>
                                <div class="corner-br"></div>
                            </div>
                            <div class="scan-line" id="scanLine"></div>
                            <div class="scan-progress"><span id="scanProgressFill"></span></div>
                            <div class="d-flex align-items-center justify-content-center" style="height: 300px;" id="cameraPlaceholder">
                                <div class="text-center text-muted">
                                    <i class="bi bi-camera" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p class="mt-2 mb-0" style="font-size: 0.85rem;">Cliquez sur "Camera" pour activer le scan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="panel-card h-100">
                    <div class="panel-card-header">
                        <h5><i class="bi bi-keyboard me-2" style="color: var(--violet);"></i>Saisie manuelle</h5>
                    </div>
                    <div class="panel-card-body">
                        <div class="d-flex align-items-center gap-2 mb-3 p-2" style="background: rgba(123,63,160,0.06); border-radius: 10px;">
                            <i class="bi bi-calendar-event" style="color: #7B3FA0; font-size: 1.1rem;"></i>
                            <div class="min-w-0">
                                <div style="font-size: 0.72rem; color: #888;">Événement en cours</div>
                                <div class="fw-semibold" style="font-size: 0.88rem;">{{ $accessEvenement->titre }}</div>
                            </div>
                        </div>

                        <p class="text-muted mb-3" style="font-size: 0.82rem;">Entrez le code unique du ticket manuellement.</p>

                        <form id="manualScanForm">
                            <div class="mb-3">
                                <input type="text" id="codeInput" name="code" class="form-control code-input py-3 text-center" value="PAX-" placeholder="Saisissez la suite du code (ex: GRH5S)" autocomplete="off" required>
                            </div>
                            <button type="submit" class="btn btn-vert w-100 py-3" id="btnVerify" style="border-radius: 8px;">
                                <i class="bi bi-search me-1"></i> Vérifier le ticket
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Result Display --}}
        <div id="scanResult" style="display: none;" class="mb-4">
            <div class="result-card" id="resultCard">
                <div class="panel-card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div id="resultIcon" style="font-size: 2.5rem;"></div>
                        <div class="flex-1 min-w-0">
                            <h5 id="resultTitle" class="fw-bold mb-1"></h5>
                            <p id="resultMessage" class="mb-2" style="font-size: 0.9rem;"></p>
                            <div id="resultDetails" class="row g-2 mt-2" style="font-size: 0.82rem;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Scan History --}}
        <div class="panel-card">
            <div class="panel-card-header">
                <h5><i class="bi bi-clock-history me-2" style="color: var(--sombre);"></i>Historique des scans</h5>
                <span class="text-muted" style="font-size: 0.78rem;" id="historyCount">{{ $scans->count() }} derniers scans</span>
            </div>
            <div class="panel-card-body" id="historyList">
                @if($scans->count() > 0)
                    @foreach($scans as $scan)
                        @php
                            $details = is_array($scan->details) ? $scan->details : json_decode($scan->details, true);
                            $resultat = $details['resultat'] ?? 'inconnu';
                            $colorClass = $resultat === 'valide' ? 'var(--vert)' : ($resultat === 'deja_utilise' ? '#f39c12' : 'var(--danger)');
                            $icon = $resultat === 'valide' ? 'bi-check-circle' : ($resultat === 'deja_utilise' ? 'bi-arrow-repeat' : 'bi-x-circle');
                        @endphp
                        <div class="scan-history-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <i class="bi {{ $icon }}" style="color: {{ $colorClass }}; font-size: 1.1rem;"></i>
                                    <div class="min-w-0">
                                        <div class="fw-semibold" style="font-size: 0.82rem;">
                                            {{ $scan->ticket ? Str::limit($scan->ticket->nom_acheteur, 25) : 'Inconnu' }}
                                        </div>
                                        <div class="text-muted" style="font-size: 0.72rem;">
                                            {{ $scan->ticket && $scan->ticket->evenement ? Str::limit($scan->ticket->evenement->titre, 30) : '' }}
                                            {{ $details['raison'] ?? '' ? ' · ' . str_replace('_', ' ', $details['raison'] ?? '') : '' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end flex-shrink-0">
                                    <div style="font-size: 0.78rem;">{{ $scan->created_at->format('H:i') }}</div>
                                    <div class="text-muted" style="font-size: 0.68rem;">{{ $scan->created_at->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-qr-code-scan" style="font-size: 3rem; color: var(--gris); opacity: 0.3;"></i>
                        <p class="text-muted mt-2 mb-0" style="font-size: 0.85rem;">Aucun scan effectué pour le moment.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('partials.camera-onboarding')
@endsection

@section('scripts')
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
let html5QrcodeScanner = null;
let isCameraActive = false;

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

document.getElementById('btnToggleCamera').addEventListener('click', function() {
    if (isCameraActive) {
        stopCamera();
    } else {
        startCamera();
    }
});

function startCamera() {
    const status = document.getElementById('cameraStatus');

    const blocker = preflightCamera();
    if (blocker) {
        isCameraActive = false;
        status.textContent = 'Erreur camera';
        status.style.color = 'var(--danger)';
        alert(blocker);
        return;
    }

    status.textContent = 'Activation...';
    resetScanHold();

    tryStartCamera(0);
}

// Verifie les prerequis AVANT d'appeler getUserMedia : sinon le navigateur
// echoue silencieusement et l'organisateur ne comprend pas la cause du refus.
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

function tryStartCamera(attemptIndex) {
    if (attemptIndex < CAMERA_ATTEMPTS.length) {
        const instance = createScannerInstance();
        html5QrcodeScanner = instance;
        instance.start(
            CAMERA_ATTEMPTS[attemptIndex],
            SCAN_CONFIG,
            (decodedText) => {
                onScanSuccess(decodedText);
            },
            (errorMessage) => {}
        ).then(() => {
            onCameraStarted();
        }).catch((err) => {
            try { instance.clear(); } catch (e) {}
            html5QrcodeScanner = null;
            // Petit delai entre 2 tentatives : sur certains telephones la
            // camera n'est pas encore liberee (NotReadableError) si on
            // redemarre immediatement.
            setTimeout(() => tryStartCamera(attemptIndex + 1), 400);
        });
        return;
    }

    Html5Qrcode.getCameras()
        .then((cameras) => {
            if (cameras && cameras.length > 0) {
                const back = cameras.find(function (c) {
                    return /back|rear|environment/i.test(c.label || '');
                }) || cameras[0];
                startWithCameraId(back.id);
            } else {
                onCameraFailed();
            }
        })
        .catch(() => onCameraFailed());
}

function startWithCameraId(cameraId) {
    const instance = createScannerInstance();
    html5QrcodeScanner = instance;
    instance.start(
        cameraId,
        SCAN_CONFIG,
        (decodedText) => {
            onScanSuccess(decodedText);
        },
        (errorMessage) => {}
    ).then(() => {
        onCameraStarted();
    }).catch((err) => {
        onCameraFailed(err);
    });
}

function describeCameraError(err) {
    const message = String((err && err.message) || (err && err.name) || err || '');
    if (/NotAllowedError|Permission/i.test(message)) {
        return "Acces camera refuse. Autorisez la camera dans les reglages du navigateur puis reessayez.";
    }
    if (/NotFoundError/i.test(message)) {
        return "Aucune camera trouvee sur cet appareil.";
    }
    if (/NotReadableError|in use|busy/i.test(message)) {
        return "La camera est deja utilisee par une autre application. Fermez-la puis reessayez.";
    }
    if (/NotSupportedError|secure context|https/i.test(message)) {
        return "La camera necessite une connexion securisee (HTTPS).";
    }
    return "Impossible d'activer la camera. Verifiez que le site est en HTTPS et autorisez la camera dans votre navigateur.";
}

function onScanSuccess(decodedText) {
    const now = Date.now();
    lastDetectAt = now;

    if (now < cooldownUntil) return;
    if (blockedCode === decodedText) return;

    if (pendingCode !== decodedText) {
        pendingCode = decodedText;
        pendingSince = now;
        const area = document.getElementById('scannerContainer');
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
    document.getElementById('codeInput').value = code;
    verifyCode(code);
}

function updateProgress(ratio) {
    const fill = document.getElementById('scanProgressFill');
    if (fill) fill.style.width = Math.round(Math.max(0, Math.min(1, ratio)) * 100) + '%';
}

function cancelPending() {
    pendingCode = null;
    pendingSince = 0;
    const area = document.getElementById('scannerContainer');
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

function flashScanOk() {
    const container = document.getElementById('scannerContainer');
    if (container) container.classList.add('scan-ok');
    setTimeout(() => { clearScanOk(); }, 700);
}

function clearScanOk() {
    const container = document.getElementById('scannerContainer');
    if (container) container.classList.remove('scan-ok');
}

function onCameraStarted() {
    const status = document.getElementById('cameraStatus');
    const placeholder = document.getElementById('cameraPlaceholder');
    const scanFrame = document.getElementById('scanFrame');
    const scanCorners = document.getElementById('scanCorners');

    isCameraActive = true;
    resetScanHold();
    if (window.cameraHint) { window.cameraHint.hide(); }
    document.getElementById('scannerContainer').classList.add('scanning');
    status.textContent = 'Camera active';
    status.style.color = 'var(--vert)';
    placeholder.style.display = 'none';
    if (scanFrame) scanFrame.style.display = 'block';
    if (scanCorners) scanCorners.style.display = 'block';
    document.getElementById('btnToggleCamera').innerHTML = '<i class="bi bi-camera-off me-1"></i> <span class="btn-text">Stop</span>';
}

function onCameraFailed(err) {
    const status = document.getElementById('cameraStatus');
    const placeholder = document.getElementById('cameraPlaceholder');

    isCameraActive = false;
    document.getElementById('scannerContainer').classList.remove('scanning');
    cancelPending();
    status.textContent = 'Erreur camera';
    status.style.color = 'var(--danger)';
    placeholder.style.display = 'flex';
    document.getElementById('btnToggleCamera').innerHTML = '<i class="bi bi-camera me-1"></i> <span class="btn-text">Camera</span>';
    alert(describeCameraError(err));
}

function stopCamera() {
    const status = document.getElementById('cameraStatus');
    const placeholder = document.getElementById('cameraPlaceholder');
    const scanFrame = document.getElementById('scanFrame');
    const scanCorners = document.getElementById('scanCorners');

    if (html5QrcodeScanner && isCameraActive) {
        html5QrcodeScanner.stop().then(() => {
            isCameraActive = false;
            document.getElementById('scannerContainer').classList.remove('scanning');
            cancelPending();
            status.textContent = 'Camera inactive';
            status.style.color = '';
            placeholder.style.display = 'flex';
            if (scanFrame) scanFrame.style.display = 'none';
            if (scanCorners) scanCorners.style.display = 'none';
            document.getElementById('btnToggleCamera').innerHTML = '<i class="bi bi-camera me-1"></i> <span class="btn-text">Camera</span>';
        }).catch(() => {});
    }
}

document.getElementById('manualScanForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const code = document.getElementById('codeInput').value.trim();
    if (code) {
        verifyCode(code);
    }
});

function verifyCode(code) {
    const btn = document.getElementById('btnVerify');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Vérification...';

    fetch('{{ route('scan.verifier') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ code: code }),
    })
    .then(res => res.json())
    .then(data => {
        showResult(data);
    })
    .catch(err => {
        showResult({
            success: false,
            message: 'Erreur de connexion. Réessayez.',
            type: 'error'
        });
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-search me-1"></i> Vérifier le ticket';
        document.getElementById('codeInput').value = 'PAX-';
    });
}

function showResult(data) {
    const container = document.getElementById('scanResult');
    const card = document.getElementById('resultCard');
    const icon = document.getElementById('resultIcon');
    const title = document.getElementById('resultTitle');
    const message = document.getElementById('resultMessage');
    const details = document.getElementById('resultDetails');

    container.style.display = 'block';
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    card.className = 'result-card';

    if (data.success) {
        window.ScanSound.success();
        card.classList.add('result-valid', 'pulse-success');
        icon.innerHTML = '<i class="bi bi-check-circle" style="color: var(--vert);"></i>';
        title.textContent = 'Ticket Valide';
        title.style.color = 'var(--vert)';
    } else if (data.type === 'already_used') {
        window.ScanSound.failure();
        card.classList.add('result-warning');
        icon.innerHTML = '<i class="bi bi-arrow-repeat" style="color: #f39c12;"></i>';
        title.textContent = 'Ticket Déjà Utilisé';
        title.style.color = '#f39c12';
    } else {
        window.ScanSound.failure();
        card.classList.add('result-invalid');
        icon.innerHTML = '<i class="bi bi-x-circle" style="color: var(--danger);"></i>';
        title.textContent = 'Ticket Invalide';
        title.style.color = 'var(--danger)';
    }

    message.textContent = data.message;

    let detailsHtml = '';
    if (data.ticket) {
        const t = data.ticket;
        detailsHtml = `
            ${t.code ? `<div class="col-12"><strong>Code:</strong> <code>${escapeHtml(t.code)}</code></div>` : ''}
            ${t.nom ? `<div class="col-md-6"><strong>Acheteur:</strong> ${escapeHtml(t.nom)}</div>` : ''}
            ${t.evenement ? `<div class="col-md-6"><strong>Événement:</strong> ${escapeHtml(t.evenement)}</div>` : ''}
            ${t.nom_tarif ? `<div class="col-md-4"><strong>Tarif:</strong> ${escapeHtml(t.nom_tarif)}</div>` : ''}
            ${t.montant ? `<div class="col-md-4"><strong>Montant:</strong> ${escapeHtml(t.montant)}</div>` : ''}
            ${t.date_scan ? `<div class="col-12"><strong>Scanné le:</strong> ${escapeHtml(t.date_scan)}</div>` : ''}
            ${t.date_event ? `<div class="col-12"><strong>Date événement:</strong> ${escapeHtml(t.date_event)}</div>` : ''}
        `;
    }
    details.innerHTML = detailsHtml;

    setTimeout(() => {
        card.classList.remove('pulse-success');
    }, 500);
}

document.getElementById('codeInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('manualScanForm').dispatchEvent(new Event('submit'));
    }
});

function refreshHistory() {
    fetch('{{ route('scan.historique') }}', {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
    })
    .then(res => res.json())
    .then(data => {
        if (data.stats) {
            document.getElementById('statTotal').textContent = data.stats.total_scans;
            document.getElementById('statToday').textContent = data.stats.scans_today;
            document.getElementById('statValides').textContent = data.stats.scans_valides;
            document.getElementById('statInvalides').textContent = data.stats.scans_invalides;
        }
        if (Array.isArray(data.scans)) {
            renderHistory(data.scans);
        }
    })
    .catch(() => {});
}

function renderHistory(scans) {
    const list = document.getElementById('historyList');
    document.getElementById('historyCount').textContent = scans.length + ' derniers scans';

    if (scans.length === 0) {
        list.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-qr-code-scan" style="font-size: 3rem; color: var(--gris); opacity: 0.3;"></i>
                <p class="text-muted mt-2 mb-0" style="font-size: 0.85rem;">Aucun scan effectué pour le moment.</p>
            </div>`;
        return;
    }

    list.innerHTML = scans.map(s => {
        const color = s.resultat === 'valide' ? 'var(--vert)' : (s.resultat === 'deja_utilise' ? '#f39c12' : 'var(--danger)');
        const icon = s.resultat === 'valide' ? 'bi-check-circle' : (s.resultat === 'deja_utilise' ? 'bi-arrow-repeat' : 'bi-x-circle');
        const raison = s.raison ? ' · ' + s.raison.split('_').join(' ') : '';
        return `
            <div class="scan-history-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <i class="bi ${icon}" style="color: ${color}; font-size: 1.1rem;"></i>
                        <div class="min-w-0">
                            <div class="fw-semibold" style="font-size: 0.82rem;">${s.nom ? escapeHtml(s.nom.substring(0, 25)) : 'Inconnu'}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">
                                ${s.evenement ? escapeHtml(s.evenement.substring(0, 30)) : ''}${raison ? escapeHtml(raison) : ''}
                            </div>
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div style="font-size: 0.78rem;">${escapeHtml(s.heure)}</div>
                        <div class="text-muted" style="font-size: 0.68rem;">${escapeHtml(s.jour)}</div>
                    </div>
                </div>
            </div>`;
    }).join('');
}

refreshHistory();
setInterval(refreshHistory, 10000);
</script>
@endsection
