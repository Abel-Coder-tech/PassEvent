@extends('layouts.app')

@section('title', 'Template du ticket - ' . $lot->nom)
@section('page-title', 'Configurer le template')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.lots-physiques.index') }}">Vente physique</a></li>
    <li class="breadcrumb-item active">Template</li>
@endsection

@section('content')
<div class="page-content">
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
    .canvas-area.has-image { border-color: #542680; border-style: solid; }
    .canvas-area.dragover { border-color: #542680; background: rgba(84,38,128,.04); }

    .canvas-empty { text-align: center; color: #6c757d; padding: 2rem; }
    .canvas-empty i { font-size: 3rem; display: block; margin-bottom: .5rem; }

    .canvas-img { max-width: 100%; max-height: 500px; display: block; border-radius: 8px; }

    .qr-overlay {
        position: absolute;
        border: 2.5px solid #e74c3c;
        background:
            repeating-linear-gradient(
                45deg,
                rgba(231, 76, 60, .15),
                rgba(231, 76, 60, .15) 4px,
                rgba(231, 76, 60, .08) 4px,
                rgba(231, 76, 60, .08) 8px
            );
        border-radius: 4px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: #c0392b;
        cursor: move;
        user-select: none;
        z-index: 10;
        box-shadow: 0 0 0 1px rgba(231,76,60,.25), inset 0 0 12px rgba(231,76,60,.1);
    }
    .qr-overlay:hover {
        box-shadow: 0 0 0 2px rgba(231,76,60,.4), inset 0 0 16px rgba(231,76,60,.15);
    }
    .qr-overlay .qr-label {
        background: #e74c3c;
        color: #fff;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        pointer-events: none;
    }
    .qr-overlay .qr-crosshair {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
    }
    .qr-overlay .qr-crosshair::before,
    .qr-overlay .qr-crosshair::after {
        content: '';
        position: absolute;
        background: rgba(231, 76, 60, .5);
    }
    .qr-overlay .qr-crosshair::before {
        width: 1px;
        height: 16px;
        top: -8px;
        left: 0;
    }
    .qr-overlay .qr-crosshair::after {
        width: 16px;
        height: 1px;
        top: 0;
        left: -8px;
    }
    .qr-tooltip {
        position: absolute;
        top: -28px;
        left: 50%;
        transform: translateX(-50%);
        background: #333;
        color: #fff;
        font-size: 10px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
        white-space: nowrap;
        pointer-events: none;
        opacity: 0;
        transition: opacity .15s;
        z-index: 12;
    }
    .qr-tooltip.show { opacity: 1; }

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
    .lot-info strong { color: #542680; }

    .btn-download { background: #542680; color: #fff; border-radius: 8px; font-weight: 600; }
    .btn-download:hover { background: #3d1a5c; color: #fff; }

    .file-error { background: #fff5f5; border: 1px solid #f5c6cb; color: #842029; border-radius: 6px; padding: .5rem .75rem; font-size: .8rem; margin-top: .35rem; display: none; }
    .file-error.show { display: block; }

    .step-badge { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: #542680; color: #fff; font-size: .7rem; font-weight: 700; margin-right: .4rem; flex-shrink: 0; }
    .step-label { font-size: .82rem; font-weight: 600; color: #1d1d1f; }
    .step-hint { font-size: .75rem; color: #888; margin-top: .15rem; }
    </style>

    @if(session('success'))
    <div class="alert alert-success py-2 small d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger py-2 small d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
    </div>
    @endif

    <div class="lot-info mb-3 d-flex flex-wrap gap-3 align-items-center">
        <a href="{{ route('admin.lots-physiques.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
        <span><i class="bi bi-ticket-perforated me-1" style="color:#542680;"></i><strong>{{ $lot->nom }}</strong></span>
        <span><i class="bi bi-calendar me-1"></i>{{ $lot->evenement?->titre ?? '—' }}</span>
        <span><i class="bi bi-tag me-1"></i>{{ $lot->tarif?->nom ?? '—' }}</span>
        <span><i class="bi bi-123 me-1"></i>{{ $tickets }} ticket(s)</span>
        <a href="{{ route('admin.lots-physiques.download', $lot) }}" class="btn btn-sm btn-download ms-auto">
            <i class="bi bi-download me-1"></i>Télécharger la planche
        </a>
    </div>

    <form method="POST" action="{{ route('admin.lots-physiques.template.save', $lot) }}" enctype="multipart/form-data" id="formTemplate">
        @csrf
        <input type="hidden" name="qr_x" id="qr_x" value="{{ old('qr_x', $lot->qr_x ?? 30) }}">
        <input type="hidden" name="qr_y" id="qr_y" value="{{ old('qr_y', $lot->qr_y ?? 60) }}">
        <input type="hidden" name="qr_size" id="qr_size_val" value="{{ old('qr_size', $lot->qr_size ?? 40) }}">

        <div class="template-wrap">
            <div class="canvas-area {{ $lot->template_path ? 'has-image' : '' }}" id="canvasArea">
                @if($lot->template_path)
                    <img src="{{ $lot->template_url }}" alt="Template" class="canvas-img" id="canvasImg">
                    <div class="qr-overlay" id="qrOverlay">
                        <div class="qr-tooltip" id="qrTooltip">30 mm, 60 mm</div>
                        <div class="qr-crosshair"></div>
                        <span class="qr-label">QR Code</span>
                        <div class="qr-resize" id="qrResize"></div>
                    </div>
                @else
                    <div class="canvas-empty" id="canvasEmpty">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <p class="mb-1 fw-semibold">Glissez votre image de ticket ici</p>
                        <p class="small mb-2">ou cliquez pour parcourir</p>
                        <p class="small text-muted">PNG ou JPG — max 10 Mo</p>
                    </div>
                @endif
            </div>

            <div class="config-panel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2">
                        <strong style="font-size:.88rem;"><i class="bi bi-sliders me-1" style="color:#542680;"></i> Configuration</strong>
                    </div>
                    <div class="card-body py-3">
                        @if($errors->any())
                        <div class="alert alert-danger py-2 small d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                            <span>{!! $errors->first() !!}</span>
                        </div>
                        @endif

                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <span class="step-badge">1</span>
                                <span class="step-label">Image du ticket</span>
                            </div>
                            <div class="step-hint mb-2">Importez votre ticket physique pour créer le template.</div>
                            <input type="file" name="template_image" id="templateImageInput" accept="image/jpeg,image/png" class="form-control form-control-sm">
                            <div class="file-error" id="fileError"></div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <span class="step-badge">2</span>
                                <span class="step-label">Positionnez le QR code</span>
                            </div>
                            <div class="step-hint mb-2">Glissez le cadre rouge pour le déplacer, ou utilisez la poignée en bas à droite pour le redimensionner.</div>
                            <div class="row g-2">
                                <div class="col-4">
                                    <label class="form-label">X (mm)</label>
                                    <input type="number" class="form-control form-control-sm" id="qrXInput" min="0" value="{{ old('qr_x', $lot->qr_x ?? 30) }}">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Y (mm)</label>
                                    <input type="number" class="form-control form-control-sm" id="qrYInput" min="0" value="{{ old('qr_y', $lot->qr_y ?? 60) }}">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Taille</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="qrSizeInput" min="20" max="80" value="{{ old('qr_size', $lot->qr_size ?? 40) }}">
                                        <span class="input-group-text">mm</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <span class="step-badge">3</span>
                                <span class="step-label">Enregistrez</span>
                            </div>
                            <div class="step-hint mb-2">Vérifiez l'aperçu puis enregistrez pour appliquer le design à la planche PDF.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-sm btn-download" id="btnSave">
                                <i class="bi bi-check-lg me-1"></i><span id="btnSaveText">Enregistrer le template</span>
                                <span id="btnSaveSpinner" class="spinner-border spinner-border-sm ms-1 d-none" role="status"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

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
    var fileError = document.getElementById('fileError');
    var fileInput = document.getElementById('templateImageInput');
    var form = document.getElementById('formTemplate');
    var btnSave = document.getElementById('btnSave');
    var btnSaveText = document.getElementById('btnSaveText');
    var btnSaveSpinner = document.getElementById('btnSaveSpinner');
    var qrTooltip = document.getElementById('qrTooltip');

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

    function showFileError(msg) {
        fileError.textContent = msg;
        fileError.classList.add('show');
    }
    function hideFileError() {
        fileError.classList.remove('show');
        fileError.textContent = '';
    }

    function updateOverlay() {
        if (!overlay || !img || !img.naturalWidth) return;
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

    function bindOverlayEvents() {
        overlay = document.getElementById('qrOverlay');
        var resizeHandle = document.getElementById('qrResize');
        if (!overlay) return;

        overlay.addEventListener('mousedown', function(e) {
            if (e.target === resizeHandle) return;
            dragging = true;
            offsetX = e.clientX - overlay.offsetLeft;
            offsetY = e.clientY - overlay.offsetTop;
            e.preventDefault();
            e.stopPropagation();
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
    }

    document.addEventListener('mousemove', function(e) {
        if (dragging && overlay) {
            var xPx = e.clientX - offsetX;
            var yPx = e.clientY - offsetY;
            xPx = Math.max(0, Math.min(imgDispW - overlay.clientWidth, xPx));
            yPx = Math.max(0, Math.min(imgDispH - overlay.clientHeight, yPx));

            overlay.style.left = xPx + 'px';
            overlay.style.top = yPx + 'px';

            var xMm = pxToMm(xPx - imgOffX);
            var yMm = pxToMm(yPx - imgOffY);
            qrXInput.value = xMm;
            qrYInput.value = yMm;
            qrXHidden.value = xMm;
            qrYHidden.value = yMm;

            if (qrTooltip) {
                qrTooltip.textContent = xMm + ' mm, ' + yMm + ' mm';
                qrTooltip.classList.add('show');
            }
        }
        if (resizing && overlay) {
            var dx = e.clientX - startResizeX;
            var newW = Math.max(20, Math.min(imgDispW - overlay.offsetLeft, startResizeW + dx));
            var newMm = pxToMm(newW);
            if (newMm >= 20 && newMm <= 80) {
                overlay.style.width = newW + 'px';
                overlay.style.height = newW + 'px';
                qrSizeInput.value = newMm;
                qrSizeHidden.value = newMm;

                if (qrTooltip) {
                    var xMm2 = parseInt(qrXInput.value) || 0;
                    var yMm2 = parseInt(qrYInput.value) || 0;
                    qrTooltip.textContent = xMm2 + ' mm, ' + yMm2 + ' mm — ' + newMm + ' mm';
                    qrTooltip.classList.add('show');
                }
            }
        }
    });

    document.addEventListener('mouseup', function() {
        dragging = false;
        resizing = false;
        if (qrTooltip) qrTooltip.classList.remove('show');
    });

    // Sync inputs
    [qrXInput, qrYInput, qrSizeInput].forEach(function(el) {
        el.addEventListener('input', function() {
            qrXHidden.value = qrXInput.value;
            qrYHidden.value = qrYInput.value;
            qrSizeHidden.value = qrSizeInput.value;
            updateOverlay();
        });
    });

    // Resize observer
    if (typeof ResizeObserver !== 'undefined' && img) {
        new ResizeObserver(updateOverlay).observe(img);
    }

    // Click canvas → open file picker (only on empty area)
    canvas.addEventListener('click', function(e) {
        if (e.target.closest('.qr-overlay') || e.target.closest('.qr-resize')) return;
        if (e.target === canvas || e.target.closest('.canvas-empty')) {
            if (fileInput) fileInput.click();
        }
    });

    // Validate + load file
    function handleFile(file) {
        hideFileError();
        if (!file) return;

        var allowed = ['image/jpeg', 'image/png'];
        if (allowed.indexOf(file.type) === -1) {
            showFileError('Format non accepté. Veuillez choisir un fichier JPG ou PNG.');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            showFileError('Le fichier est trop volumineux (' + (file.size / 1024 / 1024).toFixed(1) + ' Mo). Taille maximale : 10 Mo.');
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
            var tt = document.createElement('div');
            tt.className = 'qr-tooltip';
            tt.id = 'qrTooltip';
            tt.textContent = '30 mm, 60 mm';
            ov.appendChild(tt);
            var ch = document.createElement('div');
            ch.className = 'qr-crosshair';
            ov.appendChild(ch);
            var lb = document.createElement('span');
            lb.className = 'qr-label';
            lb.textContent = 'QR Code';
            ov.appendChild(lb);
            var rh = document.createElement('div');
            rh.className = 'qr-resize';
            rh.id = 'qrResize';
            ov.appendChild(rh);
            canvas.appendChild(ov);
            img = imgEl;
            qrTooltip = tt;
            imgEl.onload = function() { updateOverlay(); bindOverlayEvents(); };

            var dt = new DataTransfer();
            dt.items.add(file);
            if (fileInput) fileInput.files = dt.files;
        };
        reader.readAsDataURL(file);
    }

    // Drag & drop
    canvas.addEventListener('dragover', function(e) {
        e.preventDefault();
        canvas.classList.add('dragover');
    });
    canvas.addEventListener('dragleave', function() {
        canvas.classList.remove('dragover');
    });
    canvas.addEventListener('drop', function(e) {
        e.preventDefault();
        canvas.classList.remove('dragover');
        if (e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0]);
    });

    // File input change
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length) handleFile(this.files[0]);
        });
    }

    // Prevent submit without image
    form.addEventListener('submit', function(e) {
        if (!canvas.classList.contains('has-image')) {
            e.preventDefault();
            showFileError('Veuillez importer une image de ticket avant d\'enregistrer.');
            return;
        }
        btnSaveText.textContent = 'Enregistrement...';
        btnSaveSpinner.classList.remove('d-none');
        btnSave.disabled = true;
    });

    // Init
    if (overlay) { updateOverlay(); bindOverlayEvents(); }
})();
</script>
@endsection
