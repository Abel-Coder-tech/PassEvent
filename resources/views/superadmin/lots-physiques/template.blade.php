@extends('superadmin.layouts.master')

@section('title', 'Template ticket - ' . $lot->nom)
@section('page-title', 'Configurer le template')

@section('content')
<style>
.template-wrap { display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; align-items: start; }
@media (max-width: 900px) { .template-wrap { grid-template-columns: 1fr; } }

.canvas-area {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    min-height: 420px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    cursor: crosshair;
    transition: border-color .2s;
}
.canvas-area.has-image { border-color: var(--sa-primary); border-style: solid; }
.canvas-area.dragover { border-color: var(--sa-primary); background: rgba(84,38,128,.04); }

.canvas-empty { text-align: center; color: #6c757d; padding: 2rem; }
.canvas-empty i { font-size: 3rem; display: block; margin-bottom: .5rem; }

.canvas-img { max-width: 100%; max-height: 500px; display: block; border-radius: 8px; }

.qr-overlay {
    position: absolute;
    border: 2px dashed #e74c3c;
    background: rgba(231, 76, 60, .12);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    color: #c0392b;
    cursor: move;
    user-select: none;
    transition: background .15s;
    z-index: 10;
}
.qr-overlay:hover { background: rgba(231, 76, 60, .22); }
.qr-overlay::after {
    content: 'QR';
    background: rgba(255,255,255,.85);
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
}

.qr-resize {
    position: absolute;
    bottom: -5px;
    right: -5px;
    width: 14px;
    height: 14px;
    background: #fff;
    border: 2px solid #e74c3c;
    border-radius: 3px;
    cursor: nwse-resize;
    z-index: 11;
}
.qr-resize::after {
    content: '';
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 0 0 6px 6px;
    border-color: transparent transparent #e74c3c transparent;
}

.config-panel .form-label { font-size: .82rem; font-weight: 600; color: #1d1d1f; }
.config-panel .form-control, .config-panel .form-select { font-size: .85rem; }

.lot-info { background: #f8f7fa; border-radius: 10px; padding: .8rem 1rem; font-size: .82rem; }
.lot-info strong { color: var(--sa-primary); }

.btn-download { background: var(--sa-primary); color: #fff; border-radius: 8px; font-weight: 600; }
.btn-download:hover { background: #3d1a5c; color: #fff; }
</style>

@if(session('success'))
<div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger py-2 small">{{ session('error') }}</div>
@endif

<div class="lot-info mb-3 d-flex flex-wrap gap-3 align-items-center">
    <a href="{{ route('superadmin.tickets-physiques') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
    <span><i class="bi bi-ticket-perforated me-1" style="color:var(--sa-primary);"></i><strong>{{ $lot->nom }}</strong></span>
    <span><i class="bi bi-calendar me-1"></i>{{ $lot->evenement?->titre ?? '—' }}</span>
    <span><i class="bi bi-tag me-1"></i>{{ $lot->tarif?->nom ?? '—' }}</span>
    <span><i class="bi bi-123 me-1"></i>{{ $tickets }} ticket(s)</span>
    <a href="{{ route('superadmin.tickets-physiques.planche', $lot) }}" class="btn btn-sm btn-download ms-auto">
        <i class="bi bi-download me-1"></i>Télécharger la planche
    </a>
</div>

<form method="POST" action="{{ route('superadmin.tickets-physiques.template.save', $lot) }}" enctype="multipart/form-data" id="formTemplate">
    @csrf
    <input type="hidden" name="qr_x" id="qr_x" value="{{ old('qr_x', $lot->qr_x ?? 30) }}">
    <input type="hidden" name="qr_y" id="qr_y" value="{{ old('qr_y', $lot->qr_y ?? 60) }}">
    <input type="hidden" name="qr_size" id="qr_size_val" value="{{ old('qr_size', $lot->qr_size ?? 40) }}">

    <div class="template-wrap">
        <div class="canvas-area {{ $lot->template_path ? 'has-image' : '' }}" id="canvasArea">
            @if($lot->template_path)
                <img src="{{ $lot->template_url }}" alt="Template" class="canvas-img" id="canvasImg">
                <div class="qr-overlay" id="qrOverlay"><div class="qr-resize" id="qrResize"></div></div>
            @else
                <div class="canvas-empty" id="canvasEmpty">
                    <i class="bi bi-image"></i>
                    <p class="mb-1 fw-semibold">Glissez votre image de ticket ici</p>
                    <p class="small mb-2">ou cliquez pour parcourir</p>
                    <p class="small text-muted">PNG ou JPG — max 10 Mo</p>
                </div>
            @endif
        </div>

        <div class="config-panel">
            <div class="sa-card">
                <div class="sa-card-header">
                    <span><i class="bi bi-sliders me-2" style="color:var(--sa-primary);"></i> Configuration</span>
                </div>
                <div class="sa-card-body py-3">
                    @if($errors->any())
                    <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Image du ticket</label>
                        <input type="file" name="template_image" id="templateImageInput" accept="image/jpeg,image/png" class="form-control form-control-sm">
                        <small class="text-muted">PNG ou JPG — max 10 Mo</small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label">Position X</label>
                            <input type="number" class="form-control form-control-sm" id="qrXInput" min="0" value="{{ old('qr_x', $lot->qr_x ?? 30) }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Position Y</label>
                            <input type="number" class="form-control form-control-sm" id="qrYInput" min="0" value="{{ old('qr_y', $lot->qr_y ?? 60) }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Taille QR</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="qrSizeInput" min="20" max="80" value="{{ old('qr_size', $lot->qr_size ?? 40) }}">
                                <span class="input-group-text">mm</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tickets par page</label>
                        <select class="form-select form-select-sm" id="parPageSelect">
                            <option value="4" selected>4 par page (2×2) — A4</option>
                        </select>
                        <small class="text-muted">Format A4 portrait</small>
                    </div>

                    <div class="mb-3">
                        <p class="small text-muted mb-2"><i class="bi bi-info-circle me-1"></i> Glissez le cadre rouge pour le déplacer. Utilisez la poignée en bas à droite pour redimensionner le QR.</p>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-sm btn-download">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer le template
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function() {
    var canvas = document.getElementById('canvasArea');
    var overlay = document.getElementById('qrOverlay');
    var img = document.getElementById('canvasImg');
    var qrXInput = document.getElementById('qrXInput');
    var qrYInput = document.getElementById('qrYInput');
    var qrSizeInput = document.getElementById('qrSizeInput');
    var qrXHidden = document.getElementById('qr_x');
    var qrYHidden = document.getElementById('qr_y');
    var qrSizeHidden = document.getElementById('qr_size_val');

    var dragging = false;
    var resizing = false;
    var startResizeX = 0, startResizeW = 0;
    var offsetX = 0, offsetY = 0;
    var imgDispW = 0, imgDispH = 0;
    var imgOffX = 0, imgOffY = 0;

    var TICKET_MM_W = {{ $ticketLargeur ?? 95 }};
    var TICKET_MM_H = {{ $ticketHauteur ?? 138 }};

    function pxToMm(px) {
        if (!imgDispW) return 0;
        return Math.round((px / imgDispW) * TICKET_MM_W);
    }
    function mmToPx(mm) {
        if (!imgDispW) return 0;
        return (mm / TICKET_MM_W) * imgDispW;
    }

    function updateOverlay() {
        if (!img || !img.naturalWidth) return;
        imgDispW = img.clientWidth;
        imgDispH = img.clientHeight;
        imgOffX = img.offsetLeft;
        imgOffY = img.offsetTop;

        var qrMm = parseInt(qrSizeInput.value) || 40;
        var qrPx = mmToPx(qrMm);
        var xMm = parseInt(qrXInput.value) || 0;
        var yMm = parseInt(qrYInput.value) || 0;
        var xPx = mmToPx(xMm) + imgOffX;
        var yPx = mmToPx(yMm) + imgOffY;

        overlay.style.left = xPx + 'px';
        overlay.style.top = yPx + 'px';
        overlay.style.width = qrPx + 'px';
        overlay.style.height = qrPx + 'px';
    }

    if (overlay) {
        var resizeHandle = document.getElementById('qrResize');

        overlay.addEventListener('mousedown', function(e) {
            if (e.target === resizeHandle) return;
            dragging = true;
            offsetX = e.clientX - overlay.offsetLeft;
            offsetY = e.clientY - overlay.offsetTop;
            e.preventDefault();
        });

        if (resizeHandle) {
            resizeHandle.addEventListener('mousedown', function(e) {
                resizing = true;
                startResizeX = e.clientX;
                startResizeW = overlay.clientWidth;
                e.preventDefault();
                e.stopPropagation();
            });
        }

        document.addEventListener('mousemove', function(e) {
            if (dragging) {
                var xPx = e.clientX - offsetX;
                var yPx = e.clientY - offsetY;
                xPx = Math.max(0, Math.min(imgDispW - overlay.clientWidth, xPx));
                yPx = Math.max(0, Math.min(imgDispH - overlay.clientHeight, yPx));

                overlay.style.left = xPx + 'px';
                overlay.style.top = yPx + 'px';

                qrXInput.value = pxToMm(xPx - imgOffX);
                qrYInput.value = pxToMm(yPx - imgOffY);
                qrXHidden.value = qrXInput.value;
                qrYHidden.value = qrYInput.value;
            }
            if (resizing) {
                var dx = e.clientX - startResizeX;
                var newW = Math.max(20, Math.min(imgDispW - overlay.offsetLeft, startResizeW + dx));
                var newMm = pxToMm(newW);
                if (newMm >= 20 && newMm <= 80) {
                    overlay.style.width = newW + 'px';
                    overlay.style.height = newW + 'px';
                    qrSizeInput.value = newMm;
                    qrSizeHidden.value = newMm;
                }
            }
        });

        document.addEventListener('mouseup', function() {
            dragging = false;
            resizing = false;
        });
    }

    [qrXInput, qrYInput, qrSizeInput].forEach(function(el) {
        el.addEventListener('input', function() {
            qrXHidden.value = qrXInput.value;
            qrYHidden.value = qrYInput.value;
            qrSizeHidden.value = qrSizeInput.value;
            updateOverlay();
        });
    });

    if (typeof ResizeObserver !== 'undefined' && img) {
        new ResizeObserver(updateOverlay).observe(img);
    }

    function handleFile(file) {
        if (!file || !file.type.match(/^image\/(jpeg|png)$/)) {
            alert('Format accepté : JPG ou PNG.');
            return;
        }
        var reader = new FileReader();
        reader.onload = function(ev) {
            canvas.innerHTML = '';
            canvas.classList.add('has-image');
            var imgEl = document.createElement('img');
            imgEl.src = ev.target.result;
            imgEl.className = 'canvas-img';
            imgEl.id = 'canvasImg';
            canvas.appendChild(imgEl);
            var ov = document.createElement('div');
            ov.className = 'qr-overlay';
            ov.id = 'qrOverlay';
            var rh = document.createElement('div');
            rh.className = 'qr-resize';
            rh.id = 'qrResize';
            ov.appendChild(rh);
            canvas.appendChild(ov);
            imgEl.onload = function() { updateOverlay(); };

            var dt = new DataTransfer();
            dt.items.add(file);
            var fileInput = document.getElementById('templateImageInput');
            if (fileInput) fileInput.files = dt.files;
        };
        reader.readAsDataURL(file);
    }

    canvas.addEventListener('click', function(e) {
        if (e.target === canvas || e.target.closest('.canvas-empty')) {
            var input = document.getElementById('templateImageInput');
            if (input) input.click();
        }
    });

    canvas.addEventListener('dragover', function(e) {
        e.preventDefault();
        canvas.classList.add('dragover');
    });
    canvas.addEventListener('dragleave', function() { canvas.classList.remove('dragover'); });
    canvas.addEventListener('drop', function(e) {
        e.preventDefault();
        canvas.classList.remove('dragover');
        if (e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0]);
    });

    var fileInput = document.getElementById('templateImageInput');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length) handleFile(this.files[0]);
        });
    }

    if (overlay) updateOverlay();
})();
</script>
@endpush
