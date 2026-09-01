@extends('superadmin.layouts.master')

@section('title', 'Template ticket - ' . $lot->nom)
@section('page-title', 'Configurer le template')

@section('content')
<style>
.template-wrap { display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem; align-items: start; }
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
.canvas-col { min-width: 0; }
.canvas-area.has-image { border-color: var(--sa-primary); border-style: solid; }
.canvas-area.dragover { border-color: var(--sa-primary); background: rgba(84,38,128,.04); }

.canvas-empty { text-align: center; color: #6c757d; padding: 2rem; }
.canvas-empty i { font-size: 3rem; display: block; margin-bottom: .5rem; }

.canvas-img { max-width: 100%; max-height: 500px; display: block; border-radius: 8px; }

.remove-img {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 30px;
    height: 30px;
    border: none;
    border-radius: 50%;
    background: #e74c3c;
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
.remove-img:hover { background: #c0392b; transform: scale(1.1); }

.zoom-bar {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .5rem .65rem;
    background: #542680;
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
.zoom-step:hover { background: #3d1a5c; color: #fff; }
.zoom-bar .form-range { flex: 1; min-width: 0; margin: 0; accent-color: #fff; }
.zoom-badge {
    flex: 0 0 52px;
    text-align: center;
    font-size: .8rem;
    font-weight: 700;
    color: #542680;
    background: #fff;
    border-radius: 6px;
    padding: .2rem 0;
}

.file-error.warn { background: #ffd88a; border-color: #f0b429; color: #6b5200; }

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
.lot-info strong { color: var(--sa-primary); }

.btn-download { background: #542680; color: #fff; border-radius: 8px; font-weight: 600; }
.btn-download:hover { background: #3d1a5c; color: #fff; }
.btn-preview { background: #542680; color: #fff; border: none; border-radius: 8px; font-weight: 600; }
.btn-preview:hover { background: #3d1a5c; color: #fff; }

.file-error { background: #fff5f5; border: 1px solid #f5c6cb; color: #842029; border-radius: 6px; padding: .5rem .75rem; font-size: .8rem; margin-top: .35rem; display: none; }
.file-error.show { display: block; }

.help-accordion .accordion-item { border: 1px solid rgba(107,63,160,.18); }
.help-accordion .accordion-button { color: var(--sa-primary); background: #fff; box-shadow: none; font-weight: 600; font-size: .88rem; }
.help-accordion .accordion-button:not(.collapsed) { background: rgba(107,63,160,.06); color: var(--sa-primary); box-shadow: none; }
.help-accordion .accordion-button:focus { box-shadow: none; border-color: rgba(107,63,160,.3); }
.help-accordion .accordion-body { font-size: .84rem; }
.help-alert { background: rgba(107,63,160,.06); border: 1px solid rgba(107,63,160,.18); color: var(--sa-primary); border-radius: .5rem; display: flex; align-items: flex-start; gap: .5rem; padding: .5rem .75rem; font-size: .82rem; }
.help-alert.warn { background: #ffd88a; border-color: #f0b429; color: #6b5200; border-radius: .5rem; display: flex; align-items: flex-start; gap: .5rem; padding: .5rem .75rem; font-size: .82rem; border-width: 1px; border-style: solid; }
.help-line { background: rgba(107,63,160,.05); }

.format-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-size: .75rem;
    font-weight: 600;
    color: var(--sa-primary);
    background: #f0eaf7;
    border: 1px solid #d9c6ee;
    border-radius: 999px;
    padding: .2rem .6rem;
}

.step-badge { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: #1d1d1f; color: #fff; font-size: .7rem; font-weight: 700; margin-right: .4rem; flex-shrink: 0; }
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
    <a href="{{ route('superadmin.tickets-physiques') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
    <span><i class="bi bi-ticket-perforated me-1" style="color:var(--sa-primary);"></i><strong>{{ $lot->nom }}</strong></span>
    <span><i class="bi bi-calendar me-1"></i>{{ $lot->evenement?->titre ?? '—' }}</span>
    <span><i class="bi bi-tag me-1"></i>{{ $lot->tarif?->nom ?? '—' }}</span>
    <span><i class="bi bi-123 me-1"></i>{{ $tickets }} ticket(s)</span>
    <span class="format-badge"><i class="bi bi-aspect-ratio"></i><span id="formatBadgeLabel">{{ $format['label'] }}</span></span>
    <a href="{{ route('superadmin.tickets-physiques.template.preview', $lot) }}" target="_blank" class="btn btn-sm btn-preview">
        <i class="bi bi-eye me-1"></i>Visualiser
    </a>
    <a href="{{ route('superadmin.tickets-physiques.planche', $lot) }}" class="btn btn-sm btn-download ms-auto">
        <i class="bi bi-download me-1"></i>Télécharger la planche
    </a>
</div>

<form method="POST" action="{{ route('superadmin.tickets-physiques.template.save', $lot) }}" enctype="multipart/form-data" id="formTemplate">
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
                    <div class="qr-tooltip" id="qrTooltip">{{ $qrX }} mm, {{ $qrY }} mm</div>
                    <div class="qr-crosshair"></div>
                    <span class="qr-label">QR Code</span>
                    <div class="qr-resize" id="qrResize"></div>
                </div>
            @else
                <div class="canvas-empty" id="canvasEmpty">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <p class="mb-1 fw-semibold">Glissez votre image de ticket ici</p>
                    <p class="small mb-2">ou cliquez pour parcourir</p>
                    <p class="small text-muted">PNG max 10 Mo</p>
                </div>
            @endif
            <button type="button" id="btnRemoveImg" class="remove-img" title="Supprimer l'image" style="display:none;">
                <i class="bi bi-x-lg"></i>
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
            <div class="sa-card">
                <div class="sa-card-header">
                    <span><i class="bi bi-sliders me-2" style="color:#1d1d1f;"></i> Configuration</span>
                </div>
                <div class="sa-card-body py-3">
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
                            @if($format['orientation'] === 'landscape') (paysage) @else (portrait) @endif
                            — {{ $format['largeur'] }}×{{ $format['hauteur'] }} mm.
                        </div>
                        <select name="format" id="formatSelect" class="form-select form-select-sm">
                            @foreach($formats as $key => $label)
                                <option value="{{ $key }}" @selected(($lot->format ?? 's1') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="step-hint mt-1">Le QR passe automatiquement au centre du nouveau format. L'image doit respecter son ratio.</div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-1">
                            <span class="step-badge">2</span>
                            <span class="step-label">Image du ticket</span>
                        </div>
                        <div class="step-hint mb-2">Importez votre ticket physique pour créer le template.</div>
                        <input type="file" name="template_image" id="templateImageInput" accept="image/png" class="form-control form-control-sm">
                        <div class="file-error" id="fileError"></div>
                        <div class="step-hint mt-1">Utilisez la barre « Zoom image » sous l'aperçu : agrandissez si l'image ne couvre pas le ticket, réduisez pour recadrer (le débordement est coupé).</div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-1">
                            <span class="step-badge">3</span>
                            <span class="step-label">Positionnez le QR code</span>
                        </div>
                        <div class="step-hint mb-2">Glissez le cadre rouge pour le déplacer, ou utilisez la poignée en bas à droite pour le redimensionner.</div>
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="form-label">X (mm)</label>
                                <input type="number" class="form-control form-control-sm" id="qrXInput" min="0" value="{{ old('qr_x', $qrX ?? 0) }}">
                                @error('qr_x')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4">
                                <label class="form-label">Y (mm)</label>
                                <input type="number" class="form-control form-control-sm" id="qrYInput" min="0" value="{{ old('qr_y', $qrY ?? 0) }}">
                                @error('qr_y')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4">
                                <label class="form-label">Taille</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" id="qrSizeInput" min="20" max="80" value="{{ old('qr_size', $qrSize ?? 40) }}">
                                    <span class="input-group-text">mm</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-1">
                            <span class="step-badge">4</span>
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

<div class="sa-card mt-4 overflow-hidden">
    <div class="d-flex align-items-center gap-2 px-3 py-3" style="background:rgba(107,63,160,.05);border-bottom:1px solid rgba(107,63,160,.15);">
        <i class="bi bi-question-circle-fill fs-4" style="color:var(--sa-primary);"></i>
        <div>
            <div class="fw-semibold" style="color:var(--sa-primary);">Comment ça marche ?</div>
            <div class="small text-muted">Créez le design de votre ticket physique en quelques clics.</div>
        </div>
        <span class="badge rounded-pill ms-auto" style="background:var(--sa-primary);color:#fff;">Aide</span>
    </div>
    <div class="sa-card-body py-3">
        <div class="accordion help-accordion" id="saHelpAccordion">

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#saCollapseSteps" aria-expanded="true" aria-controls="saCollapseSteps">
                        <i class="bi bi-list-ol me-2" style="color:var(--sa-primary);"></i> Les 4 étapes
                    </button>
                </h2>
                <div id="saCollapseSteps" class="accordion-collapse collapse show" data-bs-parent="#saHelpAccordion">
                    <div class="accordion-body">
                        <ol class="ps-3 mb-0" style="line-height:1.9;">
                            <li><strong>Choisissez le format</strong> : taille du ticket, nombre de tickets par A4 et orientation (Standard 14×5, Standard 2 14×7, VIP 18×7, VIP 2 9,9×7).</li>
                            <li><strong>Importez l'image PNG</strong> (max 10 Mo) de votre ticket. Elle est placée à la taille exacte du ticket sans déformation. Ratio différent ? Ajustez le zoom avec la poignée en bas à droite de l'aperçu (70–150 %) : le débordement est coupé.</li>
                            <li><strong>Positionnez le QR code</strong> : glissez le cadre rouge pour le déplacer, ou tirez la poignée pour le redimensionner.</li>
                            <li><strong>Visualisez puis enregistrez</strong> : le bouton « Visualiser » ouvre le rendu exact (PDF) dans un nouvel onglet.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#saCollapseDims" aria-expanded="false" aria-controls="saCollapseDims">
                        <i class="bi bi-image me-2" style="color:var(--sa-primary);"></i> Dimensions d'image acceptées
                    </button>
                </h2>
                <div id="saCollapseDims" class="accordion-collapse collapse" data-bs-parent="#saHelpAccordion">
                    <div class="accordion-body">
                        <div class="help-alert warn mb-3">
                            <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                            <span><strong>Ratio & zoom :</strong> une image au ratio différent du format déclenche un avertissement (ex. 13,8 × 5,3 cm au lieu de 14 × 5). Ajustez le zoom : proportions conservées, débordement coupé.</span>
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
                                            <td><span class="badge rounded-pill" style="background:rgba(107,63,160,.08);color:var(--sa-primary);border:1px solid rgba(107,63,160,.2);">{{ str_replace('.', ',', rtrim(rtrim(number_format($def['largeur'] / $def['hauteur'], 2, ',', ''), '0'), ',')) }} : 1</span></td>
                                            <td>{{ $def['colonnes'] }} × {{ $def['lignes'] }} = {{ $def['colonnes'] * $def['lignes'] }}</td>
                                            <td>{{ $def['orientation'] === 'landscape' ? 'Paysage' : 'Portrait' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="help-alert mb-0">
                            <i class="bi bi-lightbulb-fill mt-1"></i>
                            <span>À l'écran (96 DPI), 1 cm ≈ 37,8 px : pour le « Standard (14×5) », visez ~1400 × 500 px. Une image plus grande en pixels mais au même ratio reste nette à la taille exacte du ticket.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#saCollapseMargins" aria-expanded="false" aria-controls="saCollapseMargins">
                        <i class="bi bi-scissors me-2" style="color:var(--sa-primary);"></i> Marges & découpe
                    </button>
                </h2>
                <div id="saCollapseMargins" class="accordion-collapse collapse" data-bs-parent="#saHelpAccordion">
                    <div class="accordion-body">
                        <div class="d-flex flex-column gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2 p-2 rounded-3 help-line small">
                                <i class="bi bi-square-half" style="color:var(--sa-primary);"></i>
                                <span><strong>Marge externe :</strong> 4 mm autour du bloc de tickets sur l'A4.</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 p-2 rounded-3 help-line small">
                                <i class="bi bi-border-style" style="color:var(--sa-primary);"></i>
                                <span><strong>Gouttière de découpe :</strong> 2 mm entre chaque ticket (lignes en pointillés sur la planche).</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 p-2 rounded-3 help-line small">
                                <i class="bi bi-qr-code" style="color:var(--sa-primary);"></i>
                                <span><strong>QR code :</strong> zone blanche de 4 mm (quiet zone) pour garantir la lecture.</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 p-2 rounded-3 help-line small">
                                <i class="bi bi-upc-scan" style="color:var(--sa-primary);"></i>
                                <span><strong>Code PAX :</strong> imprimé sous le QR code.</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 p-2 rounded-3 help-line small">
                                <i class="bi bi-ticket-perforated" style="color:var(--sa-primary);"></i>
                                <span><strong>Signature PaxEvent</strong> (événement — tarif, © {{ date('Y') }} PaxEvent) dans la marge basse.</span>
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

@endsection

@push('scripts')
<script>
(function() {
    var FORMATS = @json(\App\Models\LotPhysique::FORMATS);
    var fmtKey = '{{ $lot->format ?? 's1' }}';

    function fmt(key) { return FORMATS[key] || FORMATS.s1; }

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
    var btnRemoveImg = document.getElementById('btnRemoveImg');
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

    function currentFmt() { return fmt(formatSelect ? formatSelect.value : fmtKey); }

    function syncFormatHint(f) {
        if (formatHint) formatHint.textContent = f.colonnes + '×' + f.lignes + ' tickets par page A4 ' + (f.orientation === 'landscape' ? '(paysage)' : '(portrait)') + ' — ' + f.largeur + '×' + f.hauteur + ' mm.';
        if (formatBadgeLabel) formatBadgeLabel.textContent = f.label;
    }

    function zoomFactor() { return (currentZoom || 100) / 100; }

    function pxToMm(px) {
        if (!imgDispW) return 0;
        return Math.round(((px / (imgDispW * zoomFactor())) * TICKET_MM_W));
    }
    function mmToPx(mm) {
        if (!imgDispW) return 0;
        return (mm / TICKET_MM_W) * imgDispW * zoomFactor();
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

        var z = zoomFactor();
        var dispX = imgOffX - (imgDispW * (z - 1)) / 2;
        var dispY = imgOffY - (imgDispH * (z - 1)) / 2;

        var qrMm = parseInt(qrSizeInput.value) || currentFmt().qr_defaut;
        var qrPx = mmToPx(qrMm);
        var xMm = parseInt(qrXInput.value) || 0;
        var yMm = parseInt(qrYInput.value) || 0;
        var xPx = dispX + mmToPx(xMm);
        var yPx = dispY + mmToPx(yMm);

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

            overlay.style.left = xPx + 'px';
            overlay.style.top = yPx + 'px';

            var z = zoomFactor();
            var dispX = imgOffX - (imgDispW * (z - 1)) / 2;
            var dispY = imgOffY - (imgDispH * (z - 1)) / 2;
            var xMm = pxToMm(xPx - dispX);
            var yMm = pxToMm(yPx - dispY);
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
            var newW = Math.max(20, startResizeW + dx);
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

    function cmAffiche(px) { return (px * 2.54 / 96).toFixed(1).replace('.', ','); }
    function checkRatio(f, naturalW, naturalH) {
        if (!naturalW || !naturalH) return true;
        var ratioReel = naturalW / naturalH;
        var ratioAttendu = f.largeur / f.hauteur;
        if (Math.abs(ratioReel - ratioAttendu) / ratioAttendu > 0.01) {
            showFileWarning('Image fournie : ' + cmAffiche(naturalW) + ' × ' + cmAffiche(naturalH) + ' cm. Le format « ' + f.label + ' » attend ≈ ' + (f.largeur / 10).toFixed(1).replace('.', ',') + ' × ' + (f.hauteur / 10).toFixed(1).replace('.', ',') + ' cm. Ajustez le zoom ou recadrez votre image.');
            return true;
        }
        hideFileError();
        return true;
    }

    if (formatSelect) {
        formatSelect.addEventListener('change', function() {
            var f = fmt(this.value);
            TICKET_MM_W = f.largeur;
            TICKET_MM_H = f.hauteur;
            var qrMM = f.qr_defaut;
            qrSizeInput.value = qrMM;
            qrSizeHidden.value = qrMM;
            qrXInput.value = Math.round((f.largeur - qrMM) / 2);
            qrYInput.value = Math.round((f.hauteur - qrMM) / 2);
            qrXHidden.value = qrXInput.value;
            qrYHidden.value = qrYInput.value;
            syncFormatHint(f);
            if (img && img.naturalWidth) checkRatio(f, img.naturalWidth, img.naturalHeight);
            updateOverlay();
        });
    }

    function showRemoveBtn() { if (btnRemoveImg) btnRemoveImg.style.display = 'flex'; }
    function hideRemoveBtn() { if (btnRemoveImg) btnRemoveImg.style.display = 'none'; }
    function clearCanvas() {
        canvas.classList.remove('has-image');
        canvas.innerHTML = '';
        var empty = document.createElement('div');
        empty.className = 'canvas-empty';
        empty.id = 'canvasEmpty';
        empty.innerHTML = '<i class="bi bi-cloud-arrow-up"></i><p class="mb-1 fw-semibold">Glissez votre image de ticket ici</p><p class="small mb-2">ou cliquez pour parcourir</p><p class="small text-muted">PNG max 10 Mo</p>';
        canvas.appendChild(empty);
        canvas.appendChild(btnRemoveImg);
        hideRemoveBtn();
        img = null;
        overlay = null;
    }
    if (btnRemoveImg) {
        btnRemoveImg.addEventListener('click', function() {
            supprimerTemplate.value = '1';
            clearCanvas();
            hideFileError();
        });
    }

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
            tt.textContent = qrXInput.value + ' mm, ' + qrYInput.value + ' mm';
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
            canvas.appendChild(btnRemoveImg);
            showRemoveBtn();
            img = imgEl;
            qrTooltip = tt;
            supprimerTemplate.value = '0';
            imgEl.onload = function() {
                TICKET_MM_W = currentFmt().largeur;
                TICKET_MM_H = currentFmt().hauteur;
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

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length) handleFile(this.files[0]);
        });
    }

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

    syncFormatHint(currentFmt());
    if (overlay) { updateOverlay(); bindOverlayEvents(); }
    if (img && hasStored) { showRemoveBtn(); setZoom(currentZoom); }
})();
</script>
@endpush