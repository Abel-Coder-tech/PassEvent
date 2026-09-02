@extends('layouts.app')

@section('title', "Finalisation — Type d'organisation")
@section('page-title', 'Finaliser la création de votre compte')

@section('content')
<style>
    .wizard-card {
        max-width: 640px; margin: 0 auto; background: #fff; border-radius: 16px;
        padding: 1.5rem; box-shadow: 0 4px 24px rgba(0,0,0,0.04);
    }
    .wizard-step { display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 0.5rem; }
    .wizard-step .step { display: flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: #ccc; font-weight: 500; }
    .wizard-step .step.active { color: #542680; font-weight: 700; }
    .wizard-step .step.done { color: #2e7d4f; font-weight: 600; }
    .wizard-step .num { width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; background: #e0dde3; color: #fff; flex-shrink: 0; }
    .wizard-step .active .num { background: #542680; }
    .wizard-step .done .num { background: #2e7d4f; }
    .wizard-step .connector { width: 24px; height: 2px; background: #e0dde3; }
    .wizard-step .connector.done { background: #2e7d4f; }
    .type-card { border: 2px solid #e0dde3; border-radius: 14px; padding: 1.25rem 0.75rem; cursor: pointer; transition: all 0.2s; text-align: center; height: 100%; }
    .type-card:hover { border-color: #9972B0; background: #faf8fb; }
    .type-card.selected { border-color: #542680; background: #f5f0f9; }
    .type-card .icon { font-size: 1.5rem; color: #542680; margin-bottom: 0.4rem; }
    .type-card .name { font-weight: 700; font-size: 0.85rem; color: #1d1d1f; margin-bottom: 0.2rem; }
    .type-card .desc { font-size: 0.72rem; color: #6c757d; line-height: 1.3; }
    input[type="radio"] { display: none; }
    .toggle-group { display: flex; gap: 0.5rem; }
    .toggle-btn { flex: 1; text-align: center; padding: 0.5rem 0.3rem; border-radius: 10px; border: 1.5px solid #e0dde3; cursor: pointer; font-weight: 600; font-size: 0.78rem; background: #fff; transition: 0.2s; color: #495057; }
    .toggle-btn:hover { border-color: #9972B0; }
    .toggle-btn.active { background: #f5f0f9; border-color: #542680; color: #542680; }
    .toggle-btn input { display: none; }
    .doc-info { background: #f8f6f9; border-radius: 10px; padding: 0.75rem 1rem; font-size: 0.82rem; color: #495057; }
    .doc-info i { color: #542680; margin-right: 0.35rem; }
    @media (max-width: 575.98px) {
        .type-card { padding: 0.9rem 0.4rem; }
        .type-card .icon { font-size: 1.15rem; }
        .type-card .name { font-size: 0.72rem; }
        .type-card .desc { font-size: 0.6rem; }
        .wizard-card { padding: 1rem; }
    }
</style>
<div class="page-content">
    <div class="wizard-card">
        <div class="wizard-step">
            <div class="step active"><span class="num">1</span> Type &amp; Justificatifs</div>
            <div class="connector"></div>
            <div class="step"><span class="num">2</span> Récapitulatif</div>
        </div>
        <hr style="margin:0.75rem 0 1.25rem;border-color:#f0eeec;">

        @if($errors->any())
            <div class="alert alert-danger py-2" style="font-size:.85rem;border-radius:10px;">
                @foreach($errors->all() as $e) {{ $e }} @break @endforeach
            </div>
        @endif

        <div style="margin-bottom:1.5rem;">
            <p style="font-size:0.9rem;color:#6c757d;margin:0;">Complétez votre profil pour pouvoir créer et gérer des événements.</p>
        </div>

        <form method="POST" action="{{ route('profil.post-step2') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-4">
                    <label class="type-card @if(old('type', $type) === 'universitaire') selected @endif" onclick="selectType(this, 'universitaire')">
                        <div class="icon"><i class="bi bi-mortarboard-fill"></i></div>
                        <div class="name">Universitaire</div>
                        <div class="desc">Vous représentez une université ou un établissement scolaire</div>
                        <input type="radio" name="type" value="universitaire" @if(old('type', $type) === 'universitaire') checked @endif required>
                    </label>
                </div>
                <div class="col-4">
                    <label class="type-card @if(old('type', $type) === 'particulier') selected @endif" onclick="selectType(this, 'particulier')">
                        <div class="icon"><i class="bi bi-person"></i></div>
                        <div class="name">Particulier</div>
                        <div class="desc">Vous organisez des événements en votre nom propre</div>
                        <input type="radio" name="type" value="particulier" @if(old('type', $type) === 'particulier') checked @endif required>
                    </label>
                </div>
                <div class="col-4">
                    <label class="type-card @if(old('type', $type) === 'organisation') selected @endif" onclick="selectType(this, 'organisation')">
                        <div class="icon"><i class="bi bi-building"></i></div>
                        <div class="name">Organisation</div>
                        <div class="desc">Vous représentez une entreprise ou une association/ONG</div>
                        <input type="radio" name="type" value="organisation" @if(old('type', $type) === 'organisation') checked @endif required>
                    </label>
                </div>
            </div>

            <div id="fields-universitaire" style="display:none;">
                <div class="mb-3">
                    <label class="form-label">Nom de l'université</label>
                    <input type="text" name="organisation" class="form-control @error('organisation') is-invalid @enderror"
                           value="{{ old('organisation', $data['organisation'] ?? '') }}" placeholder="Ex: Université d'Abomey-Calavi">
                    @error('organisation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div id="fields-organisation" style="display:none;">
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <div class="toggle-group">
                        @php $td = old('type_detail', $data['type_detail'] ?? ''); @endphp
                        <label class="toggle-btn {{ $td === 'entreprise' ? 'active' : '' }}">
                            <input type="radio" name="type_detail" value="entreprise" {{ $td === 'entreprise' ? 'checked' : '' }}> Entreprise
                        </label>
                        <label class="toggle-btn {{ $td === 'association' ? 'active' : '' }}">
                            <input type="radio" name="type_detail" value="association" {{ $td === 'association' ? 'checked' : '' }}> Association/ONG
                        </label>
                    </div>
                    @error('type_detail') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nom de l'organisation</label>
                    <input type="text" name="organisation" class="form-control @error('organisation') is-invalid @enderror"
                           value="{{ old('organisation', $data['organisation'] ?? '') }}" placeholder="Ex: ABC SARL">
                    @error('organisation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Fonction du représentant</label>
                    <input type="text" name="fonction" class="form-control @error('fonction') is-invalid @enderror"
                           value="{{ old('fonction', $data['fonction'] ?? '') }}" placeholder="Ex: Directeur Général, Président(e)...">
                    @error('fonction') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div id="fields-particulier" style="display:none;">
                <p class="doc-info"><i class="bi bi-info-circle"></i> Vous organisez en tant que particulier.</p>
            </div>

            <div id="block-doc-simple" style="display:none;">
                <div class="mb-3">
                    <label class="form-label" id="doc-simple-label">Carte CIP</label>
                    @if(!empty($existingDocuments) && !empty($data['document_justificatif']))
                    <div class="alert alert-success py-2 mb-2" style="font-size:0.82rem;border-radius:8px;">
                        <i class="bi bi-check-circle-fill me-1"></i> Fichier déjà fourni. Vous pouvez le remplacer si besoin.
                    </div>
                    @endif
                    <input type="file" name="document_justificatif" id="doc-simple-file" class="form-control @error('document_justificatif') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">Format PDF, JPG ou PNG. Max 2 Mo.</div>
                    @error('document_justificatif') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" id="doc-simple-number-label">Numéro de la carte</label>
                    <input type="text" name="numero_cip" id="doc-simple-num" class="form-control @error('numero_cip') is-invalid @enderror"
                           value="{{ old('numero_cip', $data['numero_cip'] ?? '') }}" placeholder="Ex : numéro figurant sur la pièce">
                    @error('numero_cip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div id="block-doc-organisation" style="display:none;">
                <div class="mb-3">
                    <label class="form-label" id="doc-org-label">Registre de Commerce (RC)</label>
                    @if(!empty($existingDocuments) && !empty($data['document_justificatif']))
                    <div class="alert alert-success py-2 mb-2" style="font-size:0.82rem;border-radius:8px;">
                        <i class="bi bi-check-circle-fill me-1"></i> Fichier déjà fourni. Vous pouvez le remplacer si besoin.
                    </div>
                    @endif
                    <input type="file" name="document_justificatif" id="doc-org-file" class="form-control @error('document_justificatif') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">Format PDF, JPG ou PNG. Max 2 Mo.</div>
                    @error('document_justificatif') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" id="doc-org-number-label">Numéro du Registre de Commerce</label>
                    <input type="text" name="numero_rc" id="doc-org-num-rc" class="form-control @error('numero_rc') is-invalid @enderror"
                           value="{{ old('numero_rc', $data['numero_rc'] ?? '') }}">
                    @error('numero_rc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Carte CIP du représentant</label>
                    <div class="doc-info mb-2"><i class="bi bi-info-circle"></i> Carte d'identité personnelle du représentant / responsable</div>
                    @if(!empty($existingDocuments) && !empty($data['document_cip']))
                    <div class="alert alert-success py-2 mb-2" style="font-size:0.82rem;border-radius:8px;">
                        <i class="bi bi-check-circle-fill me-1"></i> Fichier déjà fourni. Vous pouvez le remplacer si besoin.
                    </div>
                    @endif
                    <input type="file" name="document_cip" id="doc-org-cip-file" class="form-control @error('document_cip') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">Format PDF, JPG ou PNG. Max 2 Mo.</div>
                    @error('document_cip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Numéro CIP du représentant</label>
                    <input type="text" name="numero_cip_rep" id="doc-org-cip-num" class="form-control @error('numero_cip_rep') is-invalid @enderror"
                           value="{{ old('numero_cip_rep', $data['numero_cip'] ?? '') }}" placeholder="Ex : numéro figurant sur la CIP">
                    @error('numero_cip_rep') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Signature <span class="text-danger">*</span></label>
                <div class="doc-info mb-2"><i class="bi bi-pen"></i> Signez clairement sur une feuille blanche et prenez en photo ou scannez</div>
                @if(!empty($existingDocuments) && !empty($data['signature']))
                <div class="alert alert-success py-2 mb-2" style="font-size:0.82rem;border-radius:8px;">
                    <i class="bi bi-check-circle-fill me-1"></i> Signature déjà fournie. Vous pouvez la remplacer si besoin.
                </div>
                @endif
                <input type="file" name="signature" class="form-control @error('signature') is-invalid @enderror" accept=".jpg,.jpeg,.png" {{ empty($existingDocuments) ? 'required' : '' }}>
                <div class="form-text">Format JPG ou PNG. Max 2 Mo.</div>
                @error('signature') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100" style="background:#542680;border:none;border-radius:10px;padding:0.7rem 1rem;font-weight:600;">Suivant</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    @php
        $alreadyJustificatif = !empty($existingDocuments) && !empty($data['document_justificatif']);
        $alreadyCip = !empty($existingDocuments) && !empty($data['document_cip']);
        $alreadySignature = !empty($existingDocuments) && !empty($data['signature']);
    @endphp
    const alreadyJustificatif = {!! $alreadyJustificatif ? 'true' : 'false' !!};
    const alreadyCip = {!! $alreadyCip ? 'true' : 'false' !!};
    const alreadySignature = {!! $alreadySignature ? 'true' : 'false' !!};

    const simpleLabels = {
        universitaire: { doc: 'Carte étudiante du responsable', num: 'Numéro de la carte étudiant' },
        particulier: { doc: 'Carte CIP', num: 'Numéro CIP' }
    };

    const orgDocLabel = {
        entreprise: 'Registre de Commerce (RC)',
        association: 'Récépissé d\'autorisation'
    };

    const orgNumLabel = {
        entreprise: 'Numéro du Registre de Commerce (RC)',
        association: 'Numéro du récépissé d\'autorisation'
    };

    function setRequired(id, required) {
        const el = document.getElementById(id);
        if (!el) return;
        if (required) el.setAttribute('required', '');
        else el.removeAttribute('required');
    }

    function typeDetail() {
        const orgFields = document.getElementById('fields-organisation');
        if (orgFields.style.display === 'block') {
            const checked = orgFields.querySelector('input[name="type_detail"]:checked');
            return checked ? checked.value : null;
        }
        return null;
    }

    function updateBlocks(val) {
        const isOrg = val === 'organisation';
        const simple = document.getElementById('block-doc-simple');
        const org = document.getElementById('block-doc-organisation');
        simple.style.display = isOrg ? 'none' : 'block';
        org.style.display = isOrg ? 'block' : 'none';

        // Nettoyer les attributs required des deux blocs
        ['doc-simple-file','doc-simple-num','doc-org-file','doc-org-num-rc','doc-org-cip-file','doc-org-cip-num'].forEach(id => setRequired(id, false));

        if (!isOrg) {
            const labels = simpleLabels[val] || simpleLabels.particulier;
            document.getElementById('doc-simple-label').textContent = labels.doc;
            document.getElementById('doc-simple-number-label').textContent = labels.num;
            // Seul le bloc visible est requis
            setRequired('doc-simple-file', !alreadyJustificatif);
            setRequired('doc-simple-num', true);
        } else {
            const detail = typeDetail();
            document.getElementById('doc-org-label').textContent = orgDocLabel[detail] || orgDocLabel.entreprise;
            document.getElementById('doc-org-number-label').textContent = orgNumLabel[detail] || orgNumLabel.entreprise;
            setRequired('doc-org-file', !alreadyJustificatif);
            setRequired('doc-org-num-rc', true);
            setRequired('doc-org-cip-file', !alreadyCip);
            setRequired('doc-org-cip-num', true);
        }
    }

    function selectType(el, val) {
        document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        el.querySelector('input[type="radio"]').checked = true;
        document.getElementById('fields-universitaire').style.display = val === 'universitaire' ? 'block' : 'none';
        document.getElementById('fields-organisation').style.display = val === 'organisation' ? 'block' : 'none';
        document.getElementById('fields-particulier').style.display = val === 'particulier' ? 'block' : 'none';
        updateBlocks(val);
    }

    (function init() {
        const selected = document.querySelector('.type-card.selected');
        document.getElementById('fields-universitaire').style.display = selected && selected.querySelector('input[type="radio"]').value === 'universitaire' ? 'block' : 'none';
        document.getElementById('fields-organisation').style.display = selected && selected.querySelector('input[type="radio"]').value === 'organisation' ? 'block' : 'none';
        document.getElementById('fields-particulier').style.display = selected && selected.querySelector('input[type="radio"]').value === 'particulier' ? 'block' : 'none';
        if (selected) {
            updateBlocks(selected.querySelector('input[type="radio"]').value);
        }
        // Signature : requise si non déjà fournie
        const sig = document.querySelector('input[name="signature"]');
        if (sig && !alreadySignature) sig.setAttribute('required', '');
    })();

    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.toggle-group').querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            this.querySelector('input').checked = true;
            updateBlocks('organisation');
        });
    });
</script>
@endsection
