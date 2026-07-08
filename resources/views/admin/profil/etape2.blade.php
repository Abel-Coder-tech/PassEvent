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
                <div class="col-md-4">
                    <label class="type-card @if(old('type', $type) === 'universitaire') selected @endif" onclick="selectType(this, 'universitaire')">
                        <div class="icon"><i class="bi bi-mortarboard-fill"></i></div>
                        <div class="name">Universitaire</div>
                        <div class="desc">Vous représentez une université ou un établissement scolaire</div>
                        <input type="radio" name="type" value="universitaire" @if(old('type', $type) === 'universitaire') checked @endif required>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="type-card @if(old('type', $type) === 'particulier') selected @endif" onclick="selectType(this, 'particulier')">
                        <div class="icon"><i class="bi bi-person"></i></div>
                        <div class="name">Particulier</div>
                        <div class="desc">Vous organisez des événements en votre nom propre</div>
                        <input type="radio" name="type" value="particulier" @if(old('type', $type) === 'particulier') checked @endif required>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="type-card @if(old('type', $type) === 'organisation') selected @endif" onclick="selectType(this, 'organisation')">
                        <div class="icon"><i class="bi bi-building"></i></div>
                        <div class="name">Organisation</div>
                        <div class="desc">Vous représentez une entreprise, une association ou un club</div>
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
                    <label class="form-label">Nom de l'organisation</label>
                    <input type="text" name="organisation" class="form-control @error('organisation') is-invalid @enderror"
                           value="{{ old('organisation', $data['organisation'] ?? '') }}" placeholder="Ex: ABC SARL">
                    @error('organisation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <div class="toggle-group">
                        @php $td = old('type_detail', $data['type_detail'] ?? ''); @endphp
                        <label class="toggle-btn {{ $td === 'entreprise' ? 'active' : '' }}">
                            <input type="radio" name="type_detail" value="entreprise" {{ $td === 'entreprise' ? 'checked' : '' }}> Entreprise
                        </label>
                        <label class="toggle-btn {{ $td === 'association' ? 'active' : '' }}">
                            <input type="radio" name="type_detail" value="association" {{ $td === 'association' ? 'checked' : '' }}> Association
                        </label>
                        <label class="toggle-btn {{ $td === 'club' ? 'active' : '' }}">
                            <input type="radio" name="type_detail" value="club" {{ $td === 'club' ? 'checked' : '' }}> Club
                        </label>
                    </div>
                    @error('type_detail') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>

            <div id="fields-particulier" style="display:none;">
                <p class="doc-info"><i class="bi bi-info-circle"></i> Vous organisez en tant que particulier.</p>
            </div>

            <div class="mb-3">
                <label class="form-label" id="doc-label">Pièce justificative</label>
                <div id="doc-helper" class="doc-info mb-2"><i class="bi bi-file-earmark-text"></i> <span id="doc-text">Carte étudiante ou lettre de l'université</span></div>
                <input type="file" name="document_justificatif" class="form-control @error('document_justificatif') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                <div class="form-text">Format PDF, JPG ou PNG. Max 5 Mo.</div>
                @error('document_justificatif') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Signature <span class="text-danger">*</span></label>
                <div class="doc-info mb-2"><i class="bi bi-pen"></i> Signez clairement sur une feuille blanche et prenez en photo ou scannez</div>
                <input type="file" name="signature" class="form-control @error('signature') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                <div class="form-text">Format PDF, JPG ou PNG. Max 5 Mo.</div>
                @error('signature') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100" style="background:#542680;border:none;border-radius:10px;padding:0.7rem 1rem;font-weight:600;">Suivant</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const docLabels = {
        universitaire: 'Carte étudiante ou lettre de l\'université',
        particulier: 'CIP (Carte d\'identité personnelle)',
        organisation: 'IFU ou RCCM'
    };

    function selectType(el, val) {
        document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        el.querySelector('input[type="radio"]').checked = true;
        document.getElementById('fields-universitaire').style.display = val === 'universitaire' ? 'block' : 'none';
        document.getElementById('fields-organisation').style.display = val === 'organisation' ? 'block' : 'none';
        document.getElementById('fields-particulier').style.display = val === 'particulier' ? 'block' : 'none';
        document.getElementById('doc-text').textContent = docLabels[val] || 'Document justificatif';
    }

    (function init() {
        const selected = document.querySelector('.type-card.selected');
        if (selected) {
            const val = selected.querySelector('input[type="radio"]').value;
            document.getElementById('fields-universitaire').style.display = val === 'universitaire' ? 'block' : 'none';
            document.getElementById('fields-organisation').style.display = val === 'organisation' ? 'block' : 'none';
            document.getElementById('fields-particulier').style.display = val === 'particulier' ? 'block' : 'none';
            document.getElementById('doc-text').textContent = docLabels[val] || 'Document justificatif';
        }
    })();

    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.toggle-group').querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            this.querySelector('input').checked = true;
        });
    });
</script>
@endsection
