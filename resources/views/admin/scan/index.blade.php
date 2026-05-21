@extends('layouts.app')

@section('title', 'Scan QR - PassEvent')

@section('page-title', 'Scan QR Code')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
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
        min-height: 300px;
    }
    #reader {
        width: 100%;
    }
    #reader video {
        border-radius: 16px;
    }
    .scan-line {
        position: absolute;
        top: 50%;
        left: 10%;
        right: 10%;
        height: 2px;
        background: var(--vert);
        box-shadow: 0 0 10px var(--vert), 0 0 20px var(--vert);
        animation: scanLine 2s ease-in-out infinite;
        z-index: 10;
    }
    @keyframes scanLine {
        0%, 100% { top: 30%; opacity: 0.5; }
        50% { top: 70%; opacity: 1; }
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
                    <div class="metric-value" style="font-size: 1.5rem;">{{ $stats['total_scans'] }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card" style="border-top-color: var(--vert);">
                    <div class="metric-icon" style="background: rgba(18,151,110,0.1); color: var(--vert);">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="metric-label">Aujourd'hui</div>
                    <div class="metric-value" style="font-size: 1.5rem;">{{ $stats['scans_today'] }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card" style="border-top-color: var(--teal);">
                    <div class="metric-icon" style="background: rgba(66,140,121,0.1); color: var(--teal);">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="metric-label">Validés</div>
                    <div class="metric-value" style="font-size: 1.5rem;">{{ $stats['scans_valides'] }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card" style="border-top-color: var(--danger);">
                    <div class="metric-icon" style="background: rgba(231,76,60,0.1); color: var(--danger);">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="metric-label">Invalides</div>
                    <div class="metric-value" style="font-size: 1.5rem;">{{ $stats['scans_invalides'] }}</div>
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
                            <div class="scan-line" id="scanLine" style="display: none;"></div>
                            <div class="scan-corners" id="scanCorners" style="display: none;">
                                <div class="corner-bl"></div>
                                <div class="corner-br"></div>
                            </div>
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
                                <input type="text" id="codeInput" name="code" class="form-control code-input py-3 text-center" placeholder="EX: PASS-ABC123" autocomplete="off" required>
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
                <span class="text-muted" style="font-size: 0.78rem;">{{ $scans->count() }} derniers scans</span>
            </div>
            <div class="panel-card-body">
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
@endsection

@section('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrcodeScanner = null;
let isCameraActive = false;

document.getElementById('btnToggleCamera').addEventListener('click', function() {
    if (isCameraActive) {
        stopCamera();
    } else {
        startCamera();
    }
});

function startCamera() {
    const status = document.getElementById('cameraStatus');
    const placeholder = document.getElementById('cameraPlaceholder');
    const scanLine = document.getElementById('scanLine');
    const scanCorners = document.getElementById('scanCorners');

    status.textContent = 'Activation...';

    html5QrcodeScanner = new Html5Qrcode("reader");

    html5QrcodeScanner.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        (decodedText) => {
            document.getElementById('codeInput').value = decodedText;
            verifyCode(decodedText);
            stopCamera();
        },
        (errorMessage) => {}
    ).then(() => {
        isCameraActive = true;
        status.textContent = 'Camera active';
        status.style.color = 'var(--vert)';
        placeholder.style.display = 'none';
        scanLine.style.display = 'block';
        scanCorners.style.display = 'block';
        document.getElementById('btnToggleCamera').innerHTML = '<i class="bi bi-camera-off me-1"></i> <span class="btn-text">Stop</span>';
    }).catch((err) => {
        status.textContent = 'Erreur camera';
        status.style.color = 'var(--danger)';
        alert('Impossible d\'activer la camera. Verifiez les permissions.');
    });
}

function stopCamera() {
    const status = document.getElementById('cameraStatus');
    const placeholder = document.getElementById('cameraPlaceholder');
    const scanLine = document.getElementById('scanLine');
    const scanCorners = document.getElementById('scanCorners');

    if (html5QrcodeScanner && isCameraActive) {
        html5QrcodeScanner.stop().then(() => {
            isCameraActive = false;
            status.textContent = 'Camera inactive';
            status.style.color = '';
            placeholder.style.display = 'flex';
            scanLine.style.display = 'none';
            scanCorners.style.display = 'none';
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
        document.getElementById('codeInput').value = '';
        document.getElementById('codeInput').focus();
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
    card.className = 'result-card';

    if (data.success) {
        card.classList.add('result-valid', 'pulse-success');
        icon.innerHTML = '<i class="bi bi-check-circle" style="color: var(--vert);"></i>';
        title.textContent = 'Ticket Valide';
        title.style.color = 'var(--vert)';
    } else if (data.type === 'already_used') {
        card.classList.add('result-warning');
        icon.innerHTML = '<i class="bi bi-arrow-repeat" style="color: #f39c12;"></i>';
        title.textContent = 'Ticket Déjà Utilisé';
        title.style.color = '#f39c12';
    } else {
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
            ${t.code ? `<div class="col-12"><strong>Code:</strong> <code>${t.code}</code></div>` : ''}
            ${t.nom ? `<div class="col-md-6"><strong>Acheteur:</strong> ${t.nom}</div>` : ''}
            ${t.evenement ? `<div class="col-md-6"><strong>Événement:</strong> ${t.evenement}</div>` : ''}
            ${t.categorie ? `<div class="col-md-4"><strong>Catégorie:</strong> ${t.categorie}</div>` : ''}
            ${t.type ? `<div class="col-md-4"><strong>Type:</strong> ${t.type}</div>` : ''}
            ${t.montant ? `<div class="col-md-4"><strong>Montant:</strong> ${t.montant}</div>` : ''}
            ${t.date_scan ? `<div class="col-12"><strong>Scanné le:</strong> ${t.date_scan}</div>` : ''}
            ${t.date_event ? `<div class="col-12"><strong>Date événement:</strong> ${t.date_event}</div>` : ''}
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
</script>
@endsection
