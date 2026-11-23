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
    .step-btn { min-width: 2.2rem; touch-action: manipulation; user-select: none; flex-shrink: 0; }
    .step-value { min-width: 0; flex: 1 1 auto !important; }
    .template-wrap { display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem; align-items: start; }
    @media (max-width: 900px) { .template-wrap { grid-template-columns: 1fr; } }

    @media (max-width: 576px) {
        .canvas-area { min-height: 280px; }
        .qr-resize { width: 24px; height: 24px; border-width: 3px; }
        .qr-label { font-size: 11px; padding: 3px 9px; }
        .zoom-bar { flex-wrap: wrap; justify-content: center; }
    }

    .canvas-area {
        background: var(--gris-clair);
        border: 2px dashed var(--ligne);
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
    .canvas-col { min-width: 0; }
    .canvas-area.has-image { border-color: var(--violet); border-style: solid; }
    .canvas-area.dragover { border-color: var(--violet); background: rgba(84,38,128,.04); }

    .canvas-empty { text-align: center; color: var(--gris-fonce); padding: 2rem; }
    .canvas-empty i { font-size: 3rem; display: block; margin-bottom: .5rem; }

    .canvas-img { max-width: 100%; max-height: 500px; display: block; border-radius: 8px; transform-origin: center center; }
/* Cadre du ticket : l'extérieur (grisé) est coupé à l'impression */
.ticket-cadre { position: absolute; pointer-events: none; z-index: 1; outline: 1.5px dashed rgba(84,38,128,.8); box-shadow: 0 0 0 9999px rgba(248,249,250,.6); }
#qrOverlay { z-index: 2; }
#btnRemoveImg, #btnChangeImg { z-index: 3; }

    .remove-img {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 30px;
        height: 30px;
        border: none;
        border-radius: 50%;
        background: var(--danger);
        color: #fff;
        font-size: 1rem;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 20;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0,0,0,.25);
        transition: transform .15s, background .15s;
    }
    .remove-img:hover { background: var(--danger); transform: scale(1.1); }

    .change-img {
        position: absolute;
        bottom: 10px;
        left: 10px;
        border: none;
        border-radius: 999px;
        background: var(--violet);
        color: #fff;
        font-size: .78rem;
        font-weight: 600;
        padding: .35rem .75rem;
        display: flex;
        align-items: center;
        gap: .35rem;
        z-index: 20;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0,0,0,.25);
        transition: transform .15s, background .15s;
    }
    .change-img:hover { background: var(--violet-clair); transform: scale(1.05); }

    .zoom-bar {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .5rem .65rem;
        background: var(--violet);
        border-radius: 8px;
    }
    .zoom-bar-label { font-size: .75rem; font-weight: 700; color: #fff; white-space: nowrap; display: flex; align-items: center; gap: .3rem; }
    .zoom-step {
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        border: 1px solid rgba(255,255,255,.4);
        background: rgba(255,255,255,.15);
        color: #fff;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
        cursor: pointer;
        transition: background .15s, color .15s;
    }
    .zoom-step:hover { background: var(--violet-clair); color: #fff; }
    .zoom-bar .form-range { flex: 1; min-width: 0; margin: 0; accent-color: #fff; }
    .zoom-badge {
        flex: 0 0 52px;
        text-align: center;
        font-size: .8rem;
        font-weight: 700;
        color: var(--violet);
        background: #fff;
        border-radius: 6px;
        padding: .2rem 0;
    }

    .file-error.warn { background: var(--warning-pale); border-color: var(--warning); color: #6b5200; }

    .qr-overlay {
        position: absolute;
        border: 2.5px solid var(--danger);
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
        color: var(--danger);
        cursor: move;
        user-select: none;
        z-index: 10;
        box-shadow: 0 0 0 1px rgba(231,76,60,.25), inset 0 0 12px rgba(231,76,60,.1);
    }
    .qr-overlay:hover {
        box-shadow: 0 0 0 2px rgba(231,76,60,.4), inset 0 0 16px rgba(231,76,60,.15);
    }
    .qr-overlay .qr-label {
        position: absolute;
        top: 4px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--danger);
        color: #fff;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        pointer-events: none;
        z-index: 12;
    }
    .qr-overlay .code-vertical-apercu {
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        pointer-events: none;
        z-index: 12;
    }
    .qr-overlay .code-vertical-apercu span {
        transform: rotate(-90deg);
        white-space: nowrap;
        font-weight: 700;
        color: #000;
        line-height: 1;
    }
    .qr-overlay .zone-inner {
        position: absolute;
        border: 1.5px dashed rgba(231, 76, 60, .55);
        background: rgba(255, 255, 255, .55);
        border-radius: 5px;
        pointer-events: none;
        z-index: 11;
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
        border: 2px solid var(--danger);
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
        border-color: transparent transparent var(--danger) transparent;
    }

    .config-panel .form-label { font-size: .82rem; font-weight: 600; color: var(--sombre); }
    .config-panel .form-control, .config-panel .form-select { font-size: .85rem; }

    .lot-info { background: var(--gris-clair); border-radius: 10px; padding: .8rem 1rem; font-size: .82rem; }
    .lot-info strong { color: var(--violet); }

    .btn-download { background: var(--violet); color: #fff; border-radius: 8px; font-weight: 600; }
    .btn-download:hover { background: var(--violet-clair); color: #fff; }
    .btn-preview { background: var(--violet); color: #fff; border: none; border-radius: 8px; font-weight: 600; }
    .btn-preview:hover { background: var(--violet-clair); color: #fff; }

    .file-error { background: #fff5f5; border: 1px solid #f5c6cb; color: #842029; border-radius: 6px; padding: .5rem .75rem; font-size: .8rem; margin-top: .35rem; display: none; }
    .file-error.show { display: block; }

    .format-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .75rem;
        font-weight: 600;
        color: var(--violet);
        background: #f0eaf7;
        border: 1px solid #d9c6ee;
        border-radius: 999px;
        padding: .2rem .6rem;
    }

    .step-badge { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: var(--sombre); color: #fff; font-size: .7rem; font-weight: 700; margin-right: .4rem; flex-shrink: 0; }
    .step-label { font-size: .82rem; font-weight: 600; color: var(--sombre); }
    .step-hint { font-size: .75rem; color: var(--gris); margin-top: .15rem; }

    .help-accordion .accordion-item { border: 1px solid rgba(84,38,128,.15); }
    .help-accordion .accordion-button { color: var(--violet); background: #fff; box-shadow: none; font-weight: 600; font-size: .88rem; }
    .help-accordion .accordion-button:not(.collapsed) { background: rgba(84,38,128,.06); color: var(--violet); box-shadow: none; }
    .help-accordion .accordion-button:focus { box-shadow: none; border-color: rgba(84,38,128,.25); }
    .help-accordion .accordion-body { font-size: .84rem; }
    .help-alert { background: rgba(84,38,128,.06); border: 1px solid rgba(84,38,128,.18); color: var(--violet); border-radius: .5rem; display: flex; align-items: flex-start; gap: .5rem; padding: .5rem .75rem; font-size: .82rem; }
    .help-alert.warn { background: var(--warning-pale); border-color: var(--warning); color: #6b5200; display: flex; align-items: flex-start; gap: .5rem; padding: .5rem .75rem; font-size: .82rem; border-radius: .5rem; border-width: 1px; border-style: solid; }
    .help-line { background: var(--warning-pale); border: 1px solid rgba(84,38,128,.18); color: var(--violet); font-size: .82rem; }
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
        <span><i class="bi bi-ticket-perforated me-1" style="color:var(--violet);"></i><strong>{{ $lot->nom }}</strong></span>
        <span><i class="bi bi-calendar me-1"></i>{{ $lot->evenement?->titre ?? '—' }}</span>
        <span><i class="bi bi-tag me-1"></i>{{ $lot->tarif?->nom ?? '—' }}</span>
        <span><i class="bi bi-123 me-1"></i>{{ $tickets }} ticket(s)</span>
        <span class="format-badge"><i class="bi bi-aspect-ratio"></i><span id="formatBadgeLabel">{{ $format['label'] }}</span></span>
        <a href="{{ route('admin.lots-physiques.template.preview', $lot) }}" target="_blank" class="btn btn-sm btn-preview">
            <i class="bi bi-eye me-1"></i>Visualiser
        </a>
        <a href="{{ route('admin.lots-physiques.download', $lot) }}" class="btn btn-sm btn-download ms-auto">
            <i class="bi bi-download me-1"></i>Télécharger la planche
        </a>
    </div>

    <form method="POST" action="{{ route('admin.lots-physiques.template.save', $lot) }}" enctype="multipart/form-data" id="formTemplate">
        @csrf
        <input type="hidden" name="qr_x" id="qr_x" value="{{ old('qr_x', $qrX ?? 0) }}">
        <input type="hidden" name="qr_y" id="qr_y" value="{{ old('qr_y', $qrY ?? 0) }}">
        <input type="hidden" name="qr_size" id="qr_size_val" value="{{ old('qr_size', $qrSize ?? 40) }}">
        <input type="hidden" name="supprimer_template" id="supprimer_template" value="{{ old('supprimer_template', '0') }}">
        <input type="hidden" name="template_zoom" id="template_zoom" value="{{ old('template_zoom', $zoom) }}">

        <div class="template-wrap">
            <div class="canvas-col">
                <div class="canvas-area {{ $lot->template_path ? 'has-image' : '' }}" id="canvasArea">
                @if($lot->template_path)
                    <img src="{{ $lot->template_url }}" alt="Template" class="canvas-img" id="canvasImg">
                    <div class="qr-overlay" id="qrOverlay">
                        <div class="qr-tooltip" id="qrTooltip">Taille : {{ number_format((float) ($qrSize ?? ($format['qr_defaut'] ?? 40)) / 10, 1, ',', ' ') }} cm</div>
                        <div class="zone-inner" id="zoneInner"></div>
                        <div class="qr-crosshair"></div>
                        <span class="qr-label">QR Code</span>
                        <div class="qr-resize" id="qrResize"></div>
                    </div>
                @else
                    <div class="canvas-empty" id="canvasEmpty">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <p class="mb-1 fw-semibold">Glissez l'image de votre ticket ici</p>
                        <p class="small mb-2">ou cliquez pour parcourir</p>
                        <p class="small text-muted">PNG max 10 Mo</p>
                    </div>
                @endif
                <button type="button" id="btnRemoveImg" class="remove-img" title="Supprimer l'image" style="display:none;">
                    <i class="bi bi-x-lg"></i>
                </button>
                <button type="button" id="btnChangeImg" class="change-img" title="Remplacer l'image (le QR code reste à sa place)" style="display:none;">
                    <i class="bi bi-image"></i> Changer l'image
                </button>
                </div>

                <div class="zoom-bar mt-3">
                    <span class="zoom-bar-label"><i class="bi bi-zoom-in"></i> Zoom image</span>
                    <button type="button" class="zoom-step" id="zoomMinus" title="Réduire (recadre le débordement)">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                    <input type="range" class="form-range" id="zoomRange" min="70" max="150" step="1" value="{{ $zoom }}">
                    <button type="button" class="zoom-step" id="zoomPlus" title="Agrandir">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <span class="zoom-badge" id="zoomValue">{{ $zoom }}%</span>
                </div>
            </div>

            <div class="config-panel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2">
                        <strong style="font-size:.88rem;"><i class="bi bi-sliders me-1" style="color:var(--sombre);"></i> Configuration</strong>
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
                                <span class="step-label">Format du ticket</span>
                            </div>
                            <div class="step-hint mb-2" id="formatHint">
                                {{ $format['colonnes'] }}×{{ $format['lignes'] }} tickets par page A4
                                @if(($format['page_orientation'] ?? $format['orientation']) === 'landscape') (page paysage) @else (page portrait) @endif
                                — ticket {{ $format['largeur'] }}×{{ $format['hauteur'] }} mm.
                            </div>
                            <select name="format" id="formatSelect" class="form-select form-select-sm">
                                @foreach($formats as $key => $label)
                                    <option value="{{ $key }}" @selected(($lot->format ?? 's1') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>

                            <div id="customDims" class="mt-3" style="display:none;">
                                <label class="form-label" style="font-size:0.8rem;">Dimensions réelles du ticket à l'impression</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="number" name="largeur_personnalisee" id="largeurPersoInput" class="form-control form-control-sm" min="20" max="289" step="0.1" placeholder="Largeur" value="{{ old('largeur_personnalisee', $lot->largeur_personnalisee ?? '') }}">
                                    <span class="fw-semibold text-muted" style="font-size:0.85rem;">×</span>
                                    <input type="number" name="hauteur_personnalisee" id="hauteurPersoInput" class="form-control form-control-sm" min="20" max="289" step="0.1" placeholder="Hauteur" value="{{ old('hauteur_personnalisee', $lot->hauteur_personnalisee ?? '') }}" readonly>
                                    <span class="text-muted" style="font-size:0.8rem;">mm</span>
                                </div>
                                @error('largeur_personnalisee')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                @error('hauteur_personnalisee')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="step-hint mt-1">Les dimensions sont automatiquement reprises de l'image importée. L'orientation, la répartition sur l'A4 et le QR par défaut sont recalculés automatiquement.</div>
                            </div>

                            <div class="step-hint mt-1">Le QR passe automatiquement au centre du nouveau format. L'image doit respecter son ratio.</div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <span class="step-badge">2</span>
                                <span class="step-label">Image du ticket</span>
                            </div>
                            <div class="step-hint mb-2">Importez votre ticket physique pour créer le template.</div>
                            <input type="file" name="template_image" id="templateImageInput" accept="image/png" class="form-control form-control-sm">
                            @if($errors->has('template_image'))
                                <div class="text-danger small mt-1">{{ $errors->first('template_image') }}</div>
                            @endif
                            <div class="file-error" id="fileError"></div>
                            <div class="step-hint mt-1">Utilisez la barre « Zoom image » sous l'aperçu : agrandissez si l'image ne couvre pas le ticket, réduisez pour recadrer (le débordement est coupé).</div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <span class="step-badge">3</span>
                                <span class="step-label">Placez le QR code</span>
                            </div>
                            <div class="step-hint mb-2">Glissez le cadre rouge pour le déposer où vous voulez. Utilisez la poignée en bas à droite pour le redimensionner.</div>
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-sm-7">
                                    <label class="form-label">Taille du QR</label>
                                    <div class="input-group input-group-sm">
                                        <button type="button" class="btn btn-outline-secondary step-btn" data-step-for="qrSizeInput" data-step="-0.1" aria-label="Diminuer la taille">−</button>
                                        <input type="number" class="form-control text-center" id="qrSizeInput" min="0.5" max="15" step="0.1" value="{{ number_format((float) old('qr_size', $qrSize ?? ($format['qr_defaut'] ?? 40)) / 10, 1, '.', '') }}">
                                        <button type="button" class="btn btn-outline-secondary step-btn" data-step-for="qrSizeInput" data-step="0.1" aria-label="Augmenter la taille">+</button>
                                        <span class="input-group-text">cm</span>
                                    </div>
                                    @if($errors->has('qr_size'))
                                        <div class="text-danger small mt-1">{{ $errors->first('qr_size') }}</div>
                                    @endif
                                    <div class="step-hint">Ajustez la taille en centimètres.</div>
                                </div>
                                <div class="col-12 col-sm-5">
                                    <button type="button" class="btn btn-sm w-100" id="btnRecenterQr" style="background:#ffc107;color:var(--sombre);font-weight:600;border:1px solid var(--warning);">
                                        <i class="bi bi-bullseye me-1"></i>Recentrer le QR
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <span class="step-badge">4</span>
                                <span class="step-label">Enregistrez</span>
                            </div>
                            <div class="step-hint mb-2">Vos modifications sont enregistrées automatiquement et l'aperçu se met à jour en dessous. Le bouton reste disponible pour forcer l'enregistrement.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-sm btn-download" id="btnSave">
                                <i class="bi bi-check-lg me-1"></i><span id="btnSaveText">Enregistrer le template</span>
                                <span id="btnSaveSpinner" class="spinner-border spinner-border-sm ms-1 d-none" role="status"></span>
                            </button>
                            <div id="autosaveStatus" class="text-center" style="font-size:.75rem;min-height:1em;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm mt-3" id="apercuDirect" style="display:none;">
        <div class="card-body p-2">
            <div class="d-flex align-items-center gap-2 mb-2 px-1" style="font-size:.82rem;">
                <i class="bi bi-eye" style="color:var(--violet, #542680);"></i>
                <strong>Aperçu en direct</strong>
                <span class="text-muted">— mis à jour à chaque modification enregistrée</span>
            </div>
            <iframe id="apercuFrame" title="Aperçu du ticket" style="width:100%;height:320px;border:1px solid #eee;border-radius:6px;background:#f8f9fa;"></iframe>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4 overflow-hidden">
        <div class="d-flex align-items-center gap-2 px-3 py-3" style="background:#faf7fd;border-bottom:1px solid rgba(84,38,128,.12);">
            <i class="bi bi-question-circle-fill fs-4" style="color:var(--violet);"></i>
            <div>
                <div class="fw-semibold" style="color:var(--violet);">Comment ça marche ?</div>
                <div class="small text-muted">Créez le design de votre ticket physique en quelques clics.</div>
            </div>
            <span class="badge rounded-pill ms-auto" style="background:var(--violet);color:#fff;">Aide</span>
        </div>
        <div class="card-body">
            <div class="accordion help-accordion" id="helpAccordion">

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSteps" aria-expanded="true" aria-controls="collapseSteps">
                            <i class="bi bi-list-ol me-2" style="color:var(--violet);"></i> Les 4 étapes
                        </button>
                    </h2>
                    <div id="collapseSteps" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                        <div class="accordion-body">
                            <ol class="ps-3 mb-0" style="line-height:1.9;">
                                <li><strong>Choisissez le format</strong> : taille du ticket, nombre de tickets par A4 et orientation (tailles réelles dans le tableau « Dimensions d'image acceptées »), ou définissez vos <strong>dimensions personnalisées</strong> (jusqu'à 28,9 × 20,2 cm) : la répartition et le QR par défaut sont recalculés.</li>
                                <li><strong>Importez l'image PNG</strong> (max 10 Mo) de votre ticket. Elle est placée à la taille exacte du ticket sans déformation. Ratio différent ? Ajustez le zoom avec la poignée en bas à droite de l'aperçu (70–150 %) : le débordement est coupé.</li>
                                <li><strong>Positionnez le QR code</strong> : glissez le cadre rouge pour le déplacer, ou tirez la poignée pour le redimensionner.</li>
                                <li><strong>Visualisez puis enregistrez</strong> : le bouton « Visualiser » ouvre le rendu exact (PDF) dans un nouvel onglet.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDims" aria-expanded="false" aria-controls="collapseDims">
                            <i class="bi bi-image me-2" style="color:var(--violet);"></i> Dimensions d'image acceptées
                        </button>
                    </h2>
                    <div id="collapseDims" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                        <div class="accordion-body">
                            <div class="help-alert warn mb-3">
                                <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                                <span><strong>Ratio & zoom :</strong> une image aux proportions différentes du format déclenche un avertissement. L'aperçu grise la partie qui sera coupée ; ajustez le zoom : proportions conservées, débordement coupé.</span>
                            </div>
                            <div class="table-responsive mb-2">
                                <table class="table table-sm table-hover table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th><i class="bi bi-layout-three-columns me-1"></i>Format</th>
                                            <th>Ticket (cm)</th>
                                            <th>Ratio</th>
                                            <th>Tickets / A4</th>
                                            <th>Orientation</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(\App\Models\LotPhysique::FORMATS as $def)
                                            <tr>
                                                <td class="fw-semibold">{{ $def['label'] }}</td>
                                                <td>{{ number_format($def['largeur'] / 10, 1, ',', ' ') }} × {{ number_format($def['hauteur'] / 10, 1, ',', ' ') }}</td>
                                                <td><span class="badge rounded-pill" style="background:rgba(84,38,128,.08);color:var(--violet);border:1px solid rgba(84,38,128,.2);">{{ str_replace('.', ',', rtrim(rtrim(number_format($def['largeur'] / $def['hauteur'], 2, ',', ''), '0'), ',')) }} : 1</span></td>
                                                <td>{{ $def['colonnes'] }} × {{ $def['lignes'] }} = {{ $def['colonnes'] * $def['lignes'] }}</td>
                                                <td>{{ $def['orientation'] === 'landscape' ? 'Paysage' : 'Portrait' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="help-alert mb-2">
                                <i class="bi bi-smartwatch mt-1"></i>
                                <span><strong>Bracelet :</strong> image de 22 × 1,8 cm (2598 × 213 px en 300 DPI), 10 bracelets par A4 paysage, calés à gauche. Chaque bracelet fait 25 cm : le visuel puis 3 cm blancs à droite pour le pliage. La zone QR fait 1,90 × 1,42 cm : comme sur les tickets, avec le code pass dedans, tourné à la verticale à droite du QR. Imprimez le PDF à 100 % (taille réelle), sans « ajuster à la page ».</span>
                            </div>
                            <div class="help-alert mb-0">
                                <i class="bi bi-lightbulb-fill mt-1"></i>
                                @php($standard = \App\Models\LotPhysique::FORMATS['s1'])
                                <span>À l'écran (96 DPI), 1 cm ≈ 37,8 px. Le format « {{ $standard['label'] }} » donne un ticket de {{ number_format($standard['largeur'] / 10, 1, ',', ' ') }} × {{ number_format($standard['hauteur'] / 10, 1, ',', ' ') }} cm (le nom indique une taille nominale) : visez environ {{ round($standard['largeur'] / 10 * 37.8) }} × {{ round($standard['hauteur'] / 10 * 37.8) }} px. Une image plus grande en pixels, mais au même ratio, reste nette à la taille exacte du ticket.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMargins" aria-expanded="false" aria-controls="collapseMargins">
                            <i class="bi bi-scissors me-2" style="color:var(--violet);"></i> Marges & découpe
                        </button>
                    </h2>
                    <div id="collapseMargins" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                        <div class="accordion-body">
                            <div class="d-flex flex-column gap-2 mb-3">
                                <div class="d-flex align-items-center gap-2 p-2 rounded-3 help-line small">
                                    <i class="bi bi-square-half" style="color:var(--violet);"></i>
                                    <span><strong>Marge externe :</strong> 4 mm autour du bloc de tickets sur l'A4.</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 p-2 rounded-3 help-line small">
                                    <i class="bi bi-border-style" style="color:var(--violet);"></i>
                                    <span><strong>Gouttière de découpe :</strong> 2 mm entre chaque ticket (lignes en pointillés sur la planche).</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 p-2 rounded-3 help-line small">
                                    <i class="bi bi-qr-code" style="color:var(--violet);"></i>
                                    <span><strong>QR code :</strong> zone blanche de marges identiques (0,25 mm) autour du QR code.</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 p-2 rounded-3 help-line small">
                                    <i class="bi bi-upc-scan" style="color:var(--violet);"></i>
                                    <span><strong>Code PAX :</strong> imprimé dans la zone blanche, sous le QR, en 3 mm de haut.</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 p-2 rounded-3 help-line small">
                                    <i class="bi bi-ticket-perforated" style="color:var(--violet);"></i>
                                    <span><strong>Signature PaxEvent</strong> (événement — tarif — prix, © {{ date('Y') }} PaxEvent) dans la marge basse.</span>
                                </div>
                            </div>
                            <div class="help-alert mb-0">
                                <i class="bi bi-check-circle-fill mt-1"></i>
                                <span>Astuce : rognez d'abord votre image aux dimensions du ticket, puis laissez PaxEvent gérer les marges de découpe automatiquement.</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var FORMATS = @json(\App\Models\LotPhysique::FORMATS);
    var fmtKey = '{{ $lot->format ?? 's1' }}';
    var ZONE = {
        top: {{ \App\Services\LotPhysiqueTemplatePdfService::QR_TOP }},
        side: {{ \App\Services\LotPhysiqueTemplatePdfService::QR_SIDE }},
        gap: {{ \App\Services\LotPhysiqueTemplatePdfService::PAX_GAP }},
        line: {{ \App\Services\LotPhysiqueTemplatePdfService::PAX_LINE_HEIGHT }},
        bottom: {{ \App\Services\LotPhysiqueTemplatePdfService::PAX_BOTTOM }},
        min: {{ \App\Services\LotPhysiqueTemplatePdfService::ZONE_MIN }},
        qrMin: {{ \App\Services\LotPhysiqueTemplatePdfService::qrMin() }},
        qrMax: 80
    };

    // Plus grand QR dont la zone blanche (QR + code pass) tient dans le ticket (même règle que le PDF)
    // Format à zone QR fixe (bracelet) : QR non redimensionnable
    function zoneFixe() { var f = currentFmt(); return f && f.zone_fixe ? f : null; }
    function qrMinMm() { var f = zoneFixe(); return f ? f.qr_defaut : ZONE.qrMin; }
    // Position par défaut du QR : celle du format, sinon centrée
    function qrPosDefaut(f, qrMm) {
        return {
            x: f.qr_x_defaut !== undefined ? f.qr_x_defaut : Math.round((f.largeur - qrMm) / 2),
            y: f.qr_y_defaut !== undefined ? f.qr_y_defaut : Math.round((f.hauteur - qrMm) / 2)
        };
    }

    function qrMaxMm() {
        if (zoneFixe()) return zoneFixe().qr_defaut;
        var parHauteur = (TICKET_MM_H || 0) - (ZONE.top + ZONE.gap + ZONE.line + ZONE.bottom);
        var parLargeur = (TICKET_MM_W || 0) - 2 * Math.max(ZONE.side, ZONE.top);
        var m = Math.min(parHauteur, parLargeur);
        return m > 0 ? Math.max(qrMinMm(), Math.floor(m * 10) / 10) : ZONE.qrMax;
    }
    function syncQrSizeBounds() {
        if (!qrSizeInput) return;
        var maxCm = Math.round((qrMaxMm() / 10) * 10) / 10;
        qrSizeInput.max = maxCm.toFixed(1);
        qrSizeInput.min = (Math.round((qrMinMm() / 10) * 10) / 10).toFixed(1);
    }

    // Zone blanche autour du QR : largeur = QR + 2 marges (haut 0,25, côtés
    // 0,25), hauteur = QR + haut + code pass. Le code pass est imprimé dans la
    // zone, sous le QR : écart 0,1 mm, puis la hauteur de ligne, puis 1,65 mm
    // en dessous avant le bord bas de la zone.
    function zoneDims(qrMm) {
        var fz = zoneFixe();
        if (fz) {
            var zw = fz.zone_fixe.largeur, zh = fz.zone_fixe.hauteur, q = fz.qr_defaut;
            var pt = Math.round((zh - q) / 2 * 100) / 100;
            return { w: zw, h: zh, padX: pt, padTop: pt, gap: 0, bandTop: zh, bandH: 0 };
        }
        var padX = Math.max(ZONE.side, ZONE.top); // côtés = marge du haut
        var w = Math.round((qrMm + padX * 2) * 100) / 100;
        var bandH = Math.round((ZONE.line + ZONE.bottom) * 100) / 100;
        var h = Math.max(Math.round((qrMm + ZONE.top + ZONE.gap + bandH) * 100) / 100, ZONE.min);
        return {
            w: w,
            h: h, // plus haute que large : le code pass est dedans
            padX: padX,
            padTop: ZONE.top,
            gap: ZONE.gap,
            bandTop: Math.round((ZONE.top + qrMm + ZONE.gap) * 100) / 100, // le code commence dans la zone
            bandH: bandH
        };
    }

    function fmt(key) {
        if (key === 'custom') {
            var c = customFmt();
            if (c) return c;
            return { label: 'Personnalisé (invalide)', largeur: 140, hauteur: 50, orientation: 'landscape', colonnes: 2, lignes: 4, qr_defaut: 40 };
        }
        return FORMATS[key] || FORMATS.s1;
    }

    // Gabarit du ticket sur mesure : mêmes constantes que le serveur (lues dans le service PDF)
    function customFmt() {
        var wInput = document.getElementById('largeurPersoInput');
        var hInput = document.getElementById('hauteurPersoInput');
        var w = wInput ? Math.round((parseFloat(wInput.value) || 0) * 10) / 10 : 0;
        var h = hInput ? Math.round((parseFloat(hInput.value) || 0) * 10) / 10 : 0;
        if (w < 1 || h < 1) return null;
        var orientation = w >= h ? 'landscape' : 'portrait';
        var pageW = orientation === 'landscape' ? 297 : 210;
        var pageH = orientation === 'landscape' ? 210 : 297;
        var marge = {{ \App\Services\LotPhysiqueTemplatePdfService::MARGE }}, gouttiere = {{ \App\Services\LotPhysiqueTemplatePdfService::GOUTTIERE }};
        var cols = Math.max(1, Math.floor((pageW - 2 * marge + gouttiere) / (w + gouttiere)));
        var rows = Math.max(1, Math.floor((pageH - 2 * marge + gouttiere) / (h + gouttiere)));
        var qr = Math.max(30, Math.min(48, Math.round(Math.min(w, h) * 0.3)));
        return { label: 'Personnalisé (' + w + '×' + h + ')', largeur: w, hauteur: h, orientation: orientation, page_orientation: orientation, colonnes: cols, lignes: rows, qr_defaut: qr };
    }

    var canvas = document.getElementById('canvasArea');
    var overlay = document.getElementById('qrOverlay');
    var zoneInner = document.getElementById('zoneInner');
    var img = document.getElementById('canvasImg');
    var qrXInput = document.getElementById('qrXInput'); // optionnel (non affiché)
    var qrYInput = document.getElementById('qrYInput'); // optionnel (non affiché)
    var qrSizeInput = document.getElementById('qrSizeInput'); // affiché en cm
    var qrXHidden = document.getElementById('qr_x');
    var qrYHidden = document.getElementById('qr_y');
    var qrSizeHidden = document.getElementById('qr_size_val'); // soumis en mm
    var fileError = document.getElementById('fileError');
    var fileInput = document.getElementById('templateImageInput');
    var form = document.getElementById('formTemplate');
    var btnSave = document.getElementById('btnSave');
    var btnSaveText = document.getElementById('btnSaveText');
    var btnSaveSpinner = document.getElementById('btnSaveSpinner');
    var qrTooltip = document.getElementById('qrTooltip');
    var btnRemoveImg = document.getElementById('btnRemoveImg');
    var btnChangeImg = document.getElementById('btnChangeImg');
    var btnRecenterQr = document.getElementById('btnRecenterQr');
    var formatSelect = document.getElementById('formatSelect');
    var formatHint = document.getElementById('formatHint');
    var formatBadgeLabel = document.getElementById('formatBadgeLabel');
    var supprimerTemplate = document.getElementById('supprimer_template');
    var hasStored = {{ $lot->template_path ? 'true' : 'false' }};

    var zoomRange = document.getElementById('zoomRange');
    var zoomValue = document.getElementById('zoomValue');
    var zoomInput = document.getElementById('template_zoom');
    var zoomMinus = document.getElementById('zoomMinus');
    var zoomPlus = document.getElementById('zoomPlus');
    var currentZoom = parseInt(zoomInput ? zoomInput.value : '100') || 100;

    var dragging = false;
    var resizing = false;
    var startResizeX = 0, startResizeW = 0;
    var offsetX = 0, offsetY = 0;
    var imgDispW = 0, imgDispH = 0;
    var imgOffX = 0, imgOffY = 0;

    var TICKET_MM_W = fmt(fmtKey).largeur;
    var TICKET_MM_H = fmt(fmtKey).hauteur;

    function imageToMm(wPx, hPx) {
        if (!wPx || !hPx) return null;
        // On se base sur le rendu à l'écran : 96 DPI
        var mmPerPx = 25.4 / 96;
        var wMm = Math.max(1, wPx * mmPerPx);
        var hMm = Math.max(1, hPx * mmPerPx);
        // On arrondit au dixième pour un rendu plus fluide, mais on conserve le ratio
        wMm = Math.round(wMm * 10) / 10;
        hMm = Math.round(hMm * 10) / 10;
        return { largeur: wMm, hauteur: hMm };
    }

    function currentFmt() { return fmt(formatSelect ? formatSelect.value : fmtKey); }

    function syncFormatHint(f) {
        var pageOri = f.page_orientation || f.orientation;
        if (formatHint) formatHint.textContent = f.colonnes + '×' + f.lignes + ' tickets par page A4 ' + (pageOri === 'landscape' ? '(page paysage)' : '(page portrait)') + ' — ticket ' + f.largeur + '×' + f.hauteur + ' mm.';
        if (formatBadgeLabel) formatBadgeLabel.textContent = f.label;
    }

    function zoomFactor() { return (currentZoom || 100) / 100; }

    // Dans le PDF, le ticket garde sa taille et l'image est agrandie autour de son centre.
    // L'aperçu fait pareil : 1 mm = largeur de l'image non zoomée / largeur du ticket.
    function pxParMm() { return imgDispW && TICKET_MM_W ? imgDispW / TICKET_MM_W : 0; }
    function pxToMm(px) {
        var r = pxParMm();
        return r ? Math.round((px / r) * 10) / 10 : 0;
    }
    function mmToPx(mm) { return mm * pxParMm(); }

    // Coin haut-gauche du ticket à l'écran : ticket centré sur l'image (comme le PDF)
    function origineTicket() {
        return { x: imgOffX, y: imgOffY + imgDispH / 2 - (TICKET_MM_H * pxParMm()) / 2 };
    }

    // Cadre du ticket : ce qui est en dehors sera coupé à l'impression
    function majCadreTicket(o) {
        if (!canvas || !img) return;
        var cadre = document.getElementById('ticketCadre');
        if (!cadre) {
            cadre = document.createElement('div');
            cadre.id = 'ticketCadre';
            cadre.className = 'ticket-cadre';
            canvas.insertBefore(cadre, overlay || null);
        }
        cadre.style.left = o.x + 'px';
        cadre.style.top = o.y + 'px';
        cadre.style.width = mmToPx(TICKET_MM_W) + 'px';
        cadre.style.height = mmToPx(TICKET_MM_H) + 'px';
    }

    function mmToCm(mm) { return Math.round(((parseFloat(mm) || 0) / 10) * 10) / 10; }
    function cmToMm(cm) { return Math.round(((parseFloat(cm) || 0) * 10) * 10) / 10; }
    function formatCm(mm) { return mmToCm(mm).toFixed(1).replace('.', ',') + ' cm'; }
    function qrMmValue() {
        var cm = qrSizeInput ? parseFloat(qrSizeInput.value) : NaN;
        if (isFinite(cm)) return cmToMm(cm);
        return parseFloat(qrSizeHidden ? qrSizeHidden.value : 0) || currentFmt().qr_defaut;
    }

    function showFileError(msg) {
        fileError.textContent = msg;
        fileError.classList.remove('warn');
        fileError.classList.add('show');
    }
    function showFileWarning(msg) {
        fileError.textContent = msg;
        fileError.classList.add('warn');
        fileError.classList.add('show');
    }
    function hideFileError() {
        fileError.classList.remove('show');
        fileError.classList.remove('warn');
        fileError.textContent = '';
    }

    function setZoom(v) {
        v = Math.max(70, Math.min(150, Math.round(v)));
        currentZoom = v;
        if (zoomInput) zoomInput.value = v;
        if (zoomRange) zoomRange.value = v;
        if (zoomValue) zoomValue.textContent = v + '%';
        modifie();
        if (img) img.style.transform = 'scale(' + (v / 100) + ')';
        updateOverlay();
    }
    function bindZoomRange() {
        if (!zoomRange || zoomRange._bound) return;
        zoomRange._bound = true;
        zoomRange.addEventListener('input', function() { setZoom(parseInt(this.value) || 100); });
    }
    bindZoomRange();
    if (zoomMinus) zoomMinus.addEventListener('click', function() { setZoom(currentZoom - 1); });
    if (zoomPlus) zoomPlus.addEventListener('click', function() { setZoom(currentZoom + 1); });

    function updateOverlay() {
        if (!overlay || !img || !img.naturalWidth) return;
        imgDispW = img.clientWidth;
        imgDispH = img.clientHeight;
        imgOffX = img.offsetLeft;
        imgOffY = img.offsetTop;

        var o = origineTicket();
        var dispX = o.x;
        var dispY = o.y;
        majCadreTicket(o);

        // Taille pilotée en cm (conversion interne mm)
        var qrMm = qrMmValue() || currentFmt().qr_defaut;
        qrMm = Math.max(qrMinMm(), Math.min(qrMaxMm(), qrMm));
        qrMm = Math.round(qrMm * 10) / 10;
        if (qrSizeInput) qrSizeInput.value = mmToCm(qrMm).toFixed(1);
        if (qrSizeHidden) qrSizeHidden.value = qrMm;
        var zone = zoneDims(qrMm);
        var qrPx = mmToPx(qrMm);
        var xMm = qrXInput ? (parseFloat(qrXInput.value) || 0) : (parseFloat(qrXHidden ? qrXHidden.value : 0) || 0);
        var yMm = qrYInput ? (parseFloat(qrYInput.value) || 0) : (parseFloat(qrYHidden ? qrYHidden.value : 0) || 0);
        var xPx = dispX + mmToPx(xMm) - mmToPx(zone.padX);
        var yPx = dispY + mmToPx(yMm) - mmToPx(zone.padTop);

        overlay.style.left = xPx + 'px';
        overlay.style.top = yPx + 'px';
        overlay.style.width = mmToPx(zone.w) + 'px';
        overlay.style.height = mmToPx(zone.h) + 'px';

        if (zoneInner) {
            zoneInner.style.left = mmToPx(zone.padX) + 'px';
            zoneInner.style.top = mmToPx(zone.padTop) + 'px';
            zoneInner.style.width = qrPx + 'px';
            zoneInner.style.height = qrPx + 'px';
        }
        majCodeVertical(zone);
    }

    // Bracelet : code pass imprimé à la verticale, dans la zone, à droite du QR
    var CODE_V = { ecart: {{ \App\Services\LotPhysiqueTemplatePdfService::CODE_V_ECART }}, largeur: {{ \App\Services\LotPhysiqueTemplatePdfService::CODE_V_LARGEUR }} };
    function majCodeVertical(zone) {
        if (!overlay) return;
        var fz = zoneFixe();
        var cv = overlay.querySelector('.code-vertical-apercu');
        var resize = overlay.querySelector('.qr-resize');
        var label = overlay.querySelector('.qr-label');
        if (resize) resize.style.display = fz ? 'none' : '';
        if (label) label.style.display = fz ? 'none' : '';
        if (qrSizeInput) qrSizeInput.readOnly = !!fz;
        if (!fz || !fz.code_vertical) { if (cv) cv.remove(); return; }
        if (!cv) {
            cv = document.createElement('div');
            cv.className = 'code-vertical-apercu';
            cv.innerHTML = '<span>PAX-XXXXX</span>';
            overlay.appendChild(cv);
        }
        var bord = overlay.clientLeft || 0;
        cv.style.left = (mmToPx(zone.padX + fz.qr_defaut + CODE_V.ecart) - bord) + 'px';
        cv.style.top = (-bord) + 'px';
        cv.style.width = mmToPx(CODE_V.largeur) + 'px';
        cv.style.height = mmToPx(zone.h) + 'px';
        cv.firstChild.style.fontSize = Math.max(4, mmToPx(CODE_V.largeur) * 0.55) + 'px';
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

        // Touch (mobile) : déplacement du cadre
        overlay.addEventListener('touchstart', function(e) {
            if (e.target === resizeHandle) return;
            var t = e.touches[0];
            dragging = true;
            offsetX = t.clientX - overlay.offsetLeft;
            offsetY = t.clientY - overlay.offsetTop;
            if (e.cancelable) e.preventDefault();
            e.stopPropagation();
        }, { passive: false });

        if (resizeHandle) {
            resizeHandle.addEventListener('mousedown', function(e) {
                resizing = true;
                startResizeX = e.clientX;
                startResizeW = zoneInner ? zoneInner.clientWidth : overlay.clientWidth;
                e.preventDefault();
                e.stopPropagation();
            });

            // Touch (mobile) : redimensionnement
            resizeHandle.addEventListener('touchstart', function(e) {
                var t = e.touches[0];
                resizing = true;
                startResizeX = t.clientX;
                startResizeW = zoneInner ? zoneInner.clientWidth : overlay.clientWidth;
                if (e.cancelable) e.preventDefault();
                e.stopPropagation();
            }, { passive: false });
        }
    }

    function movePointer(clientX, clientY) {
        if (dragging && overlay) {
            var xPx = clientX - offsetX;
            var yPx = clientY - offsetY;

            overlay.style.left = xPx + 'px';
            overlay.style.top = yPx + 'px';

            var o = origineTicket();
            var dispX = o.x;
            var dispY = o.y;
            var qrMm = qrMmValue() || currentFmt().qr_defaut;
            var zone = zoneDims(qrMm);
            var xMm = pxToMm((xPx - dispX) + mmToPx(zone.padX));
            var yMm = pxToMm((yPx - dispY) + mmToPx(zone.padTop));
            if (qrXInput) qrXInput.value = xMm;
            if (qrYInput) qrYInput.value = yMm;
            qrXHidden.value = xMm;
            qrYHidden.value = yMm;

            if (qrTooltip) {
                qrTooltip.textContent = 'Taille : ' + formatCm(qrMm);
                qrTooltip.classList.add('show');
            }
        }
        if (resizing && overlay) {
            var dx = clientX - startResizeX;
            var newQrPx = startResizeW + dx;
            var newMm = pxToMm(newQrPx);
            var maxMm = qrMaxMm();
            if (newMm >= qrMinMm() && newMm <= maxMm) {
                newMm = Math.round(newMm * 10) / 10;
                if (qrSizeInput) qrSizeInput.value = mmToCm(newMm).toFixed(1);
                if (qrSizeHidden) qrSizeHidden.value = newMm;
                updateOverlay();

                if (qrTooltip) {
                    qrTooltip.textContent = 'Taille : ' + formatCm(newMm);
                    qrTooltip.classList.add('show');
                }
            }
        }
    }

    function stopPointer() {
        if (dragging || resizing) modifie();
        dragging = false;
        resizing = false;
        if (qrTooltip) qrTooltip.classList.remove('show');
    }

    document.addEventListener('mousemove', function(e) { movePointer(e.clientX, e.clientY); });
    document.addEventListener('mouseup', stopPointer);
    document.addEventListener('touchmove', function(e) {
        if (!dragging && !resizing) return;
        var t = e.touches[0];
        movePointer(t.clientX, t.clientY);
        if (e.cancelable) e.preventDefault();
    }, { passive: false });
    document.addEventListener('touchend', stopPointer);
    document.addEventListener('touchcancel', stopPointer);

    // Sync inputs (taille en cm -> mm)
    [qrXInput, qrYInput, qrSizeInput].forEach(function(el) {
        if (!el) return;
        el.addEventListener('input', function() {
            if (qrXInput && qrXHidden) qrXHidden.value = qrXInput.value;
            if (qrYInput && qrYHidden) qrYHidden.value = qrYInput.value;
            if (el === qrSizeInput && qrSizeHidden) qrSizeHidden.value = cmToMm(qrSizeInput.value);
            updateOverlay();
            modifie();
        });
    });

    // Boutons − / + (taille en cm)
    function activerSteppers() {
        document.querySelectorAll('.step-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var input = document.getElementById(btn.getAttribute('data-step-for'));
                if (!input) return;
                var delta = parseFloat(btn.getAttribute('data-step')) || 0;
                var min = input.getAttribute('data-min') !== null && input.getAttribute('data-min') !== '' ? parseFloat(input.getAttribute('data-min')) : null;
                var max = input.getAttribute('data-max') !== null && input.getAttribute('data-max') !== '' ? parseFloat(input.getAttribute('data-max')) : null;
                var val = (parseFloat(input.value) || 0) + delta;
                if (min !== null && val < min) val = min;
                if (max !== null && val > max) val = max;
                val = Math.round(val * 10) / 10;
                input.value = (input.step && parseFloat(input.step) < 1) ? val.toFixed(1) : val;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
        });
    }
    activerSteppers();

    function recentrerQr() {
        var f = currentFmt();
        var qrMm = qrMmValue() || f.qr_defaut;
        qrMm = Math.max(qrMinMm(), Math.min(qrMaxMm(), qrMm));
        var pos = qrPosDefaut(f, qrMm);
        var xMm = pos.x;
        var yMm = pos.y;
        if (qrXInput) qrXInput.value = xMm;
        if (qrYInput) qrYInput.value = yMm;
        qrXHidden.value = xMm;
        qrYHidden.value = yMm;
        updateOverlay();
        modifie();
    }
    if (btnRecenterQr) btnRecenterQr.addEventListener('click', recentrerQr);

    // Resize observer
    if (typeof ResizeObserver !== 'undefined' && img) {
        new ResizeObserver(updateOverlay).observe(img);
    }

    // Validate ratio client-side
    function checkRatio(f, naturalW, naturalH) {
        if (!naturalW || !naturalH) return true;
        var ratioReel = naturalW / naturalH;
        var ratioAttendu = f.largeur / f.hauteur;
        if (Math.abs(ratioReel - ratioAttendu) / ratioAttendu > 0.01) {
            var fmtCm = function (mm) { return (mm / 10).toFixed(2).replace('.', ',').replace(/0+$/, '').replace(/,$/, ''); };
            var message = ratioReel < ratioAttendu
                ? 'Votre image est trop haute (' + fmtCm(f.largeur / ratioReel) + ' cm au lieu de ' + fmtCm(f.hauteur) + ' cm).'
                : 'Votre image est trop large (' + fmtCm(f.hauteur * ratioReel) + ' cm au lieu de ' + fmtCm(f.largeur) + ' cm).';
            showFileWarning(message + ' Elle sera coupée à l\'impression : recadrez l\'image ou ajustez le zoom.');
            return true;
        }
        hideFileError();
        return true;
    }

    function applyFormat(f) {
        TICKET_MM_W = f.largeur;
        TICKET_MM_H = f.hauteur;
        var qrMM = f.qr_defaut;
        if (qrSizeInput) qrSizeInput.value = mmToCm(qrMM).toFixed(1);
        if (qrSizeHidden) qrSizeHidden.value = qrMM;
        var pos = qrPosDefaut(f, qrMM);
        var xMm = pos.x;
        var yMm = pos.y;
        if (qrXInput) qrXInput.value = xMm;
        if (qrYInput) qrYInput.value = yMm;
        qrXHidden.value = xMm;
        qrYHidden.value = yMm;
        syncFormatHint(f);
        if (img && img.naturalWidth) checkRatio(f, img.naturalWidth, img.naturalHeight);
        syncQrSizeBounds();
        updateOverlay();
    }

    function syncCustomDims() {
        var block = document.getElementById('customDims');
        if (!block || !formatSelect) return;
        block.style.display = formatSelect.value === 'custom' ? '' : 'none';
    }

    function applyCustomFormat(resetQr) {
        var c = customFmt();
        if (!c) {
            if (formatHint) formatHint.textContent = 'Dimensions personnalisées invalides.';
            if (formatBadgeLabel) formatBadgeLabel.textContent = 'Personnalisé (invalide)';
            return;
        }
        TICKET_MM_W = c.largeur;
        TICKET_MM_H = c.hauteur;
        if (resetQr) {
            var qrMM = c.qr_defaut;
            if (qrSizeInput) { qrSizeInput.value = mmToCm(qrMM).toFixed(1); }
            if (qrSizeHidden) { qrSizeHidden.value = qrMM; }
            var xMm = Math.round((c.largeur - qrMM) / 2);
            var yMm = Math.round((c.hauteur - qrMM) / 2);
            if (qrXInput) { qrXInput.value = xMm; }
            if (qrYInput) { qrYInput.value = yMm; }
            qrXHidden.value = xMm;
            qrYHidden.value = yMm;
        }
        syncFormatHint(c);
        if (img && img.naturalWidth) checkRatio(c, img.naturalWidth, img.naturalHeight);
        syncQrSizeBounds();
        updateOverlay();
    }

    // Hauteur du ticket = largeur saisie × proportions de l'image
    function hauteurDepuisLargeur() {
        var wInput = document.getElementById('largeurPersoInput');
        var hInput = document.getElementById('hauteurPersoInput');
        if (!wInput || !hInput || !img || !img.naturalWidth) return;
        var w = parseFloat(wInput.value) || 0;
        if (w > 0) hInput.value = Math.round((w * img.naturalHeight / img.naturalWidth) * 10) / 10;
    }

    // Nouvelle image en format personnalisé : largeur déduite des pixels à 96 DPI
    // (référence de rendu), sans tenir compte d'une résolution embarquée différente.
    // La largeur reste modifiable et le ticket est toujours ramené à l'A4.
    function applyCustomFromImage(imgEl, resetQr) {
        if (!imgEl || !imgEl.naturalWidth || !imgEl.naturalHeight) return;
        var wInput = document.getElementById('largeurPersoInput');
        if (!wInput) return;
        var mesures = imageToMm(imgEl.naturalWidth, imgEl.naturalHeight);
        var largeur = mesures ? mesures.largeur : (parseFloat(wInput.value) || 140);
        var ratio = imgEl.naturalWidth / imgEl.naturalHeight;
        var hauteur = largeur / ratio;
        var echelle = Math.min(1, 289 / Math.max(largeur, hauteur), 202 / Math.min(largeur, hauteur));
        wInput.value = Math.round(largeur * echelle * 10) / 10;
        hauteurDepuisLargeur();
        applyCustomFormat(resetQr);
    }

    if (formatSelect) {
        formatSelect.addEventListener('change', function() {
            syncCustomDims();
            if (this.value === 'custom') {
                if (img && img.naturalWidth && img.naturalHeight) {
                    applyCustomFromImage(img, true);
                } else {
                    applyCustomFormat(true);
                }
            }
            else { applyFormat(fmt(this.value)); }
            modifie();
        });
    }

    var largeurPersoInput = document.getElementById('largeurPersoInput');
    var hauteurPersoInput = document.getElementById('hauteurPersoInput');
    if (largeurPersoInput) {
        largeurPersoInput.addEventListener('input', function() {
            if (formatSelect && formatSelect.value === 'custom') { hauteurDepuisLargeur(); applyCustomFormat(false); modifie(); }
        });
    }
    syncCustomDims();

    // Remove image (✕)
    function showRemoveBtn() {
        if (btnRemoveImg) btnRemoveImg.style.display = 'flex';
        if (btnChangeImg) btnChangeImg.style.display = 'flex';
    }
    function hideRemoveBtn() {
        if (btnRemoveImg) btnRemoveImg.style.display = 'none';
        if (btnChangeImg) btnChangeImg.style.display = 'none';
    }
    function clearCanvas() {
        canvas.classList.remove('has-image');
        canvas.innerHTML = '';
        var empty = document.createElement('div');
        empty.className = 'canvas-empty';
        empty.id = 'canvasEmpty';
        empty.innerHTML = '<i class="bi bi-cloud-arrow-up"></i><p class="mb-1 fw-semibold">Glissez l\'image de votre ticket ici</p><p class="small mb-2">ou cliquez pour parcourir</p><p class="small text-muted">PNG max 10 Mo</p>';
        canvas.appendChild(empty);
        canvas.appendChild(btnRemoveImg);
        canvas.appendChild(btnChangeImg);
        hideRemoveBtn();
        img = null;
        overlay = null;
        zoneInner = null;
    }
    if (btnRemoveImg) {
        btnRemoveImg.addEventListener('click', function(e) {
            e.stopPropagation();
            supprimerTemplate.value = '1';
            clearCanvas();
            modifie();
            hideFileError();
        });
    }

    // Changer l'image : ouvre le sélecteur, remplace le visuel sans toucher au QR
    if (btnChangeImg) {
        btnChangeImg.addEventListener('click', function(e) {
            e.stopPropagation();
            if (fileInput) fileInput.click();
        });
    }

    // Validate + load file
    function handleFile(file) {
        hideFileError();
        if (!file) return;

        var allowed = ['image/png'];
        if (allowed.indexOf(file.type) === -1) {
            showFileError('Format non accepté. Veuillez choisir un fichier PNG uniquement.');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            showFileError('Le fichier est trop volumineux (' + (file.size / 1024 / 1024).toFixed(1) + ' Mo). Taille maximale : 10 Mo.');
            return;
        }

        var reader = new FileReader();
        reader.onload = function(ev) {
            modifie();
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
            tt.textContent = 'Taille : ' + formatCm(qrMmValue());
            ov.appendChild(tt);
            var zi = document.createElement('div');
            zi.className = 'zone-inner';
            zi.id = 'zoneInner';
            ov.appendChild(zi);
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
            canvas.appendChild(btnRemoveImg);
            canvas.appendChild(btnChangeImg);
            showRemoveBtn();
            img = imgEl;
            overlay = ov;
            zoneInner = zi;
            qrTooltip = tt;
            supprimerTemplate.value = '0';
            imgEl.onload = function() {
                if (formatSelect && formatSelect.value === 'custom') {
                    applyCustomFromImage(imgEl, false);
                } else {
                    TICKET_MM_W = currentFmt().largeur;
                    TICKET_MM_H = currentFmt().hauteur;
                }
                setZoom(currentZoom);
                checkRatio(currentFmt(), imgEl.naturalWidth, imgEl.naturalHeight);
                updateOverlay();
                bindOverlayEvents();
            };

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

    // Tap sur la zone vide (mobile) : ouvre le sélecteur de fichier
    canvas.addEventListener('click', function(e) {
        if (!canvas.classList.contains('has-image') && fileInput) {
            fileInput.click();
        }
    });

    // File input change
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length) handleFile(this.files[0]);
        });
    }

    // Sauvegarde automatique : chaque modification est enregistrée sans cliquer,
    // puis l'aperçu du ticket est régénéré.
    var pret = false, autoTimer = null, autoEnCours = false, autoRelance = false;
    var autosaveStatus = document.getElementById('autosaveStatus');
    var apercuCard = document.getElementById('apercuDirect');
    var apercuFrame = document.getElementById('apercuFrame');
    var lienApercu = document.querySelector('a.btn-preview');
    function statutAuto(texte, etat) {
        if (!autosaveStatus) return;
        autosaveStatus.textContent = texte;
        autosaveStatus.style.color = etat === 'err' ? 'var(--danger, #c0392b)' : (etat === 'ok' ? 'var(--success, #198754)' : '');
    }
    function rafraichirApercu() {
        if (!apercuFrame || !lienApercu || !hasStored) { if (apercuCard) apercuCard.style.display = hasStored ? '' : 'none'; return; }
        apercuCard.style.display = '';
        apercuFrame.src = lienApercu.href + (lienApercu.href.indexOf('?') === -1 ? '?' : '&') + 't=' + Date.now() + '#toolbar=0&navpanes=0';
    }
    function modifie() {
        if (!pret) return;
        clearTimeout(autoTimer);
        statutAuto('Modifications en attente…');
        autoTimer = setTimeout(sauverAuto, 800);
    }
    function sauverAuto() {
        var hasImage = canvas.classList.contains('has-image');
        if (!hasImage && !hasStored && supprimerTemplate.value !== '1') { statutAuto(''); return; }
        if (autoEnCours) { autoRelance = true; return; }
        autoEnCours = true;
        statutAuto('Enregistrement…');
        var envoiFichier = !!(fileInput && fileInput.files && fileInput.files.length);
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, d: d }; });
        }).then(function (res) {
            if (!res.ok) throw new Error(res.d.message || 'erreur inattendue');
            if (envoiFichier && fileInput) fileInput.value = '';
            hasStored = !!res.d.template_url;
            supprimerTemplate.value = '0';
            statutAuto('Enregistré automatiquement à ' + res.d.heure, 'ok');
            rafraichirApercu();
        }).catch(function (e) {
            statutAuto('Non enregistré : ' + e.message, 'err');
        }).then(function () {
            autoEnCours = false;
            if (autoRelance) { autoRelance = false; sauverAuto(); }
        });
    }

    // Prevent empty submit (only if no image at all and nothing stored and not removing)
    form.addEventListener('submit', function(e) {
        var hasImage = canvas.classList.contains('has-image');
        if (!hasImage && !hasStored && supprimerTemplate.value !== '1') {
            e.preventDefault();
            showFileError('Veuillez importer une image de ticket avant d\'enregistrer.');
            return;
        }
        btnSaveText.textContent = 'Enregistrement...';
        btnSaveSpinner.classList.remove('d-none');
        btnSave.disabled = true;
    });

    // Init
    if (formatSelect && formatSelect.value === 'custom' && img && img.naturalWidth && img.naturalHeight) {
        // Dimensions déjà enregistrées : on les garde ; sinon on les déduit de l'image
        if (largeurPersoInput && parseFloat(largeurPersoInput.value) > 0) { hauteurDepuisLargeur(); applyCustomFormat(false); }
        else { applyCustomFromImage(img, false); }
    } else {
        TICKET_MM_W = currentFmt().largeur;
        TICKET_MM_H = currentFmt().hauteur;
    }
    syncFormatHint(currentFmt());
    syncQrSizeBounds();
    if (overlay) { updateOverlay(); bindOverlayEvents(); }
    if (img && hasStored) { showRemoveBtn(); setZoom(currentZoom); if (img.naturalWidth) { setZoom(currentZoom); } }
    pret = true;
    rafraichirApercu();
})();
</script>
@endsection