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
                        <i class="bi bi-camera me-1"></i><span id="cameraBtnText">Activer</span>
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
                        <div class="d-flex align-items-center justify-content-center" style="height:300px;" id="cameraPlaceholder">
                            <div class="text-center text-muted">
                                <i class="bi bi-camera" style="font-size:3rem;opacity:0.3;"></i>
                                <p class="mt-2 mb-0" style="font-size:0.85rem;">Activez la camera pour scanner</p>
                            </div>
                        </div>
                    </div>
                </div>
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
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrCode = null;
let isScanning = false;
let scanTimeout = null;

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
    if (isScanning) { stopCamera(); }
    else { startCamera(); }
}

function startCamera() {
    const btn = document.getElementById('btnToggleCamera');
    const placeholder = document.getElementById('cameraPlaceholder');
    const corners = document.getElementById('scanCorners');
    const frame = document.getElementById('scanFrame');
    const reader = document.getElementById('reader');
    if (!reader) return;

    reader.style.display = 'block';
    if (placeholder) placeholder.style.display = 'none';
    if (corners) corners.style.display = 'block';
    if (frame) frame.style.display = 'block';

    tryStartCamera(0);
}

function tryStartCamera(attemptIndex) {
    if (attemptIndex < CAMERA_ATTEMPTS.length) {
        const instance = createScannerInstance();
        html5QrCode = instance;
        instance.start(
            CAMERA_ATTEMPTS[attemptIndex],
            SCAN_CONFIG,
            onScanSuccess
        ).then(() => {
            onCameraStarted();
        }).catch(() => {
            try { instance.clear(); } catch (e) {}
            html5QrCode = null;
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
                const instance = createScannerInstance();
                html5QrCode = instance;
                instance.start(back.id, SCAN_CONFIG, onScanSuccess)
                    .then(onCameraStarted)
                    .catch(onCameraFailed);
            } else {
                onCameraFailed();
            }
        })
        .catch(onCameraFailed);
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
        return "La camera necessite une connexion securisée (HTTPS).";
    }
    return "Impossible d'activer la camera. Verifiez que le site est en HTTPS et autorisez la camera dans votre navigateur.";
}

function onCameraStarted() {
    isScanning = true;
    const btn = document.getElementById('btnToggleCamera');
    if (btn) btn.innerHTML = '<i class="bi bi-stop-circle me-1"></i>Arrêter';
}

function onCameraFailed(err) {
    const btn = document.getElementById('btnToggleCamera');
    const placeholder = document.getElementById('cameraPlaceholder');
    const corners = document.getElementById('scanCorners');
    const frame = document.getElementById('scanFrame');
    const reader = document.getElementById('reader');
    if (placeholder) placeholder.style.display = 'flex';
    if (reader) reader.style.display = 'none';
    if (corners) corners.style.display = 'none';
    if (frame) frame.style.display = 'none';
    if (btn) btn.innerHTML = '<i class="bi bi-camera me-1"></i>Activer';
    alert(describeCameraError(err));
}

function stopCamera() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            html5QrCode.clear();
        });
    }
    isScanning = false;
    const btn = document.getElementById('btnToggleCamera');
    if (btn) btn.innerHTML = '<i class="bi bi-camera me-1"></i>Activer';
    const placeholder = document.getElementById('cameraPlaceholder');
    const corners = document.getElementById('scanCorners');
    const frame = document.getElementById('scanFrame');
    const reader = document.getElementById('reader');
    if (placeholder) placeholder.style.display = 'flex';
    if (reader) reader.style.display = 'none';
    if (corners) corners.style.display = 'none';
    if (frame) frame.style.display = 'none';
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

function onScanSuccess(decodedText) {
    if (scanTimeout) return;
    scanTimeout = setTimeout(() => { scanTimeout = null; }, 4000);
    flashScanOk();
    submitScan(decodedText);
    stopCamera();
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
