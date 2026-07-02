@extends('layouts.agent')

@section('title', 'Scan — Agent PaxEvent')

@push('styles')
<style>
    .scanner-area {
        background: #1a1a2e;
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        min-height: 300px;
    }
    #reader { width: 100%; }
    #reader video { border-radius: 16px; }
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
        <div class="col-4"><div class="stat-scan card-agent p-2"><div class="value">{{ $stats['total'] }}</div><div class="label">Total</div></div></div>
        <div class="col-4"><div class="stat-scan card-agent p-2"><div class="value" style="color:#28a745;">{{ $stats['valides'] }}</div><div class="label">Validés</div></div></div>
        <div class="col-4"><div class="stat-scan card-agent p-2"><div class="value" style="color:#dc3545;">{{ $stats['invalides'] }}</div><div class="label">Invalides</div></div></div>
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
                        <input type="text" id="codeInput" name="code" class="form-control text-center py-2" placeholder="Code du ticket" autocomplete="off" required>
                    </div>
                    <button type="submit" class="btn btn-violet w-100 py-2" id="btnVerify">
                        <i class="bi bi-search me-1"></i> Vérifier
                    </button>
                </form>
            </div>

            <div id="scanResult" style="display:none;" class="mt-3"></div>

            @if($recent->isNotEmpty())
            <div class="card-agent p-0 mt-3">
                <div class="p-2 border-bottom">
                    <small class="fw-bold"><i class="bi bi-clock-history me-1"></i>Derniers scans</small>
                </div>
                <div style="max-height:200px;overflow-y:auto;">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @foreach($recent as $log)
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
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

document.getElementById('btnToggleCamera')?.addEventListener('click', toggleCamera);

function toggleCamera() {
    if (isScanning) { stopCamera(); }
    else { startCamera(); }
}

function startCamera() {
    const btn = document.getElementById('btnToggleCamera');
    const placeholder = document.getElementById('cameraPlaceholder');
    const corners = document.getElementById('scanCorners');
    const reader = document.getElementById('reader');
    if (!reader) return;

    reader.style.display = 'block';
    if (placeholder) placeholder.style.display = 'none';
    if (corners) corners.style.display = 'block';

    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        onScanSuccess
    ).then(() => {
        isScanning = true;
        if (btn) btn.innerHTML = '<i class="bi bi-stop-circle me-1"></i>Arrêter';
    }).catch(() => {
        if (placeholder) { placeholder.style.display = 'flex'; reader.style.display = 'none'; }
        if (corners) corners.style.display = 'none';
        alert('Impossible d\'accéder à la caméra.');
    });
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
    const reader = document.getElementById('reader');
    if (placeholder) placeholder.style.display = 'flex';
    if (reader) reader.style.display = 'none';
    if (corners) corners.style.display = 'none';
}

function onScanSuccess(decodedText) {
    if (scanTimeout) return;
    scanTimeout = setTimeout(() => { scanTimeout = null; }, 2000);
    submitScan(decodedText);
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
            resultDiv.innerHTML = '<div class="result-valid">' +
                '<i class="bi bi-check-circle-fill" style="font-size:2.5rem;color:#28a745;"></i>' +
                '<h5 class="mt-2 mb-1 text-success">Ticket validé !</h5>' +
                '<p class="mb-1 fw-semibold">' + (data.ticket?.nom || '') + '</p>' +
                '<small class="text-muted">' + (data.ticket?.categorie || '') + ' | ' + (data.ticket?.montant || '') + '</small>' +
                '</div>';
        } else {
            let extra = '';
            if (data.ticket) {
                extra = '<div class="mt-2 p-2 bg-light rounded small">' +
                    'Déjà scanné par : <strong>' + (data.ticket.nom || '') + '</strong>' +
                    ' le ' + (data.ticket.date || '') +
                    '</div>';
            }
            resultDiv.innerHTML = '<div class="result-invalid">' +
                '<i class="bi bi-x-circle-fill" style="font-size:2.5rem;color:#dc3545;"></i>' +
                '<h5 class="mt-2 mb-1 text-danger">' + data.message + '</h5>' +
                extra +
                '</div>';
        }
        resultDiv.style.display = 'block';
        setTimeout(() => { resultDiv.style.display = 'none'; }, 5000);
    })
    .catch(() => {
        resultDiv.innerHTML = '<div class="result-invalid"><p class="mb-0 text-danger">Erreur de connexion.</p></div>';
        resultDiv.style.display = 'block';
    });
}
</script>
@endpush
