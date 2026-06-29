@php
    $fromGoogle = $reg['from_google'] ?? false;
    $identity   = $reg['identity'] ?? [];
    $orgData    = $reg['org_data'] ?? [];
    $type       = $reg['type'] ?? null;
    $hasAvatar  = !empty($identity['avatar']);
@endphp
@extends('layouts.register')

@section('title', 'Inscription — PaxEvent')

@section('progress-bar')
<div class="d-flex justify-content-center align-items-center" style="max-width:480px;margin:0 auto;padding:0 1rem;">
    <div class="progress-step" data-step="1">
        <div class="step-dot done">1</div>
        <div class="step-line done" data-line="1"></div>
    </div>
    <div class="progress-step" data-step="2">
        <div class="step-dot active" id="dot2">2</div>
        <div class="step-line" data-line="2" id="line2"></div>
    </div>
    <div class="progress-step" data-step="3">
        <div class="step-dot" id="dot3">3</div>
        <div class="step-line" data-line="3" id="line3"></div>
    </div>
    <div class="progress-step" data-step="4">
        <div class="step-dot" id="dot4">4</div>
    </div>
</div>
<p class="step-label active text-center mt-2 mb-0" id="stepDesc" style="font-size:0.78rem;color:var(--gris);">
    <span id="stepLabel"><strong>Étape 2 – Identité :</strong></span>
    <span id="stepHint">Veuillez compléter votre profil</span>
</p>
@endsection

@section('content')
<div class="container py-4" style="max-width:600px;">
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" style="border-radius:12px;font-size:.85rem;">
            <i class="bi bi-exclamation-circle me-1"></i>
            @foreach($errors->all() as $e) {{ $e }} @break @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- STEP 1 — Identité --}}
    <div class="register-card mx-auto" id="step1">
        <h1 class="h4 fw-bold text-center mb-1">Votre identité</h1>
        <p class="text-muted text-center small mb-3">Qui êtes-vous ?</p>

        <form id="formStep1" enctype="multipart/form-data">
            @csrf
            @if($fromGoogle && !empty($reg['google_name']))
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Nom complet</label>
                <input type="text" name="nom" class="form-control" value="{{ $reg['google_name'] }}" readonly style="background:#f8f6f9;cursor:not-allowed;">
                <div class="form-text" style="font-size:.72rem;">Récupéré de votre compte Google</div>
            </div>
            @else
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Nom complet</label>
                <input type="text" name="nom" class="form-control" value="{{ old('nom', $identity['nom'] ?? '') }}" required placeholder="Votre nom et prénoms">
                <div class="invalid-feedback"></div>
            </div>
            @endif

            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Téléphone</label>
                <input type="tel" name="telephone" class="form-control" value="{{ old('telephone', $identity['telephone'] ?? '') }}" required placeholder="+229 XX XX XX XX">
                <div class="invalid-feedback"></div>
            </div>

            @if(!$fromGoogle)
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Mot de passe</label>
                <input type="password" name="mot_de_passe" class="form-control" minlength="8" required placeholder="Min. 8 caractères">
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Confirmer le mot de passe</label>
                <input type="password" name="mot_de_passe_confirmation" class="form-control" required placeholder="Répétez le mot de passe">
                <div class="invalid-feedback"></div>
            </div>
            @endif

            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Photo de profil <span class="text-muted fw-normal">(optionnel)</span></label>
                <input type="file" name="avatar" class="form-control" accept="image/*">
                <div class="form-text" style="font-size:.72rem;">Formats acceptés : JPG, PNG. Max 2 Mo.</div>
                <div class="invalid-feedback"></div>
            </div>

            <button type="submit" class="btn btn-primary w-100" style="border-radius:10px;" id="btnStep1">Continuer</button>
            <a href="{{ route('inscriptions.create') }}" class="btn btn-outline-secondary w-100 mt-2" style="border-radius:10px;">Recommencer</a>
        </form>
    </div>

    {{-- STEP 2 — Organisation --}}
    <div class="register-card mx-auto" id="step2" style="display:none;">
        <h1 class="h4 fw-bold text-center mb-1">Type d'organisation</h1>
        <p class="text-muted text-center small mb-3">Choisissez votre type et fournissez les justificatifs</p>

        <form id="formStep2" enctype="multipart/form-data">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="type-card @if($type === 'universitaire') selected @endif" onclick="selectType(this,'universitaire')">
                        <div class="icon"><i class="bi bi-building-columns"></i></div>
                        <div class="name">Universitaire</div>
                        <div class="desc">Université ou établissement scolaire</div>
                        <input type="radio" name="type" value="universitaire" @if($type === 'universitaire') checked @endif required>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="type-card @if($type === 'particulier') selected @endif" onclick="selectType(this,'particulier')">
                        <div class="icon"><i class="bi bi-person"></i></div>
                        <div class="name">Particulier</div>
                        <div class="desc">Vous organisez en votre nom propre</div>
                        <input type="radio" name="type" value="particulier" @if($type === 'particulier') checked @endif required>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="type-card @if($type === 'organisation') selected @endif" onclick="selectType(this,'organisation')">
                        <div class="icon"><i class="bi bi-building"></i></div>
                        <div class="name">Organisation</div>
                        <div class="desc">Entreprise, association ou club</div>
                        <input type="radio" name="type" value="organisation" @if($type === 'organisation') checked @endif required>
                    </label>
                </div>
            </div>

            <div id="fields-universitaire" style="display:none;">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Nom de l'université</label>
                    <input type="text" name="organisation" class="form-control" value="{{ old('organisation', $orgData['organisation'] ?? '') }}" placeholder="Ex: Université d'Abomey-Calavi">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div id="fields-organisation" style="display:none;">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Nom de l'organisation</label>
                    <input type="text" name="organisation" class="form-control" value="{{ old('organisation', $orgData['organisation'] ?? '') }}" placeholder="Ex: ABC SARL">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Type</label>
                    @php $td = old('type_detail', $orgData['type_detail'] ?? ''); @endphp
                    <div class="d-flex gap-2">
                        <label class="toggle-btn flex-fill {{ $td==='entreprise'?'active':'' }}" onclick="toggleDetail(this)">
                            <input type="radio" name="type_detail" value="entreprise" {{ $td==='entreprise'?'checked':'' }}> Entreprise
                        </label>
                        <label class="toggle-btn flex-fill {{ $td==='association'?'active':'' }}" onclick="toggleDetail(this)">
                            <input type="radio" name="type_detail" value="association" {{ $td==='association'?'checked':'' }}> Association
                        </label>
                        <label class="toggle-btn flex-fill {{ $td==='club'?'active':'' }}" onclick="toggleDetail(this)">
                            <input type="radio" name="type_detail" value="club" {{ $td==='club'?'checked':'' }}> Club
                        </label>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div id="fields-particulier" style="display:none;">
                <p class="doc-info"><i class="bi bi-info-circle"></i> Vous organisez en tant que particulier.</p>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Description <span class="text-muted fw-normal">(optionnelle)</span></label>
                <textarea name="description" class="form-control" rows="3" placeholder="Parlez-nous de votre activité..." style="resize:vertical;">{{ old('description', $orgData['description'] ?? '') }}</textarea>
                <div class="invalid-feedback"></div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted" id="doc-label">Pièce justificative</label>
                <div class="doc-info mb-2"><i class="bi bi-file-earmark-text"></i> <span id="doc-text">{{ $docLabels[$type ?? 'universitaire'] ?? 'Carte étudiante ou lettre de l\'université' }}</span></div>
                <input type="file" name="document_justificatif" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                <div class="form-text" style="font-size:.72rem;">Format PDF, JPG ou PNG. Max 5 Mo.</div>
                <div class="invalid-feedback"></div>
            </div>

            <button type="submit" class="btn btn-primary w-100" style="border-radius:10px;" id="btnStep2">Continuer</button>
            <button type="button" class="btn btn-outline-secondary w-100 mt-2" style="border-radius:10px;" onclick="goToStep(1)">Précédent</button>
        </form>
    </div>

    {{-- STEP 3 — Récapitulatif --}}
    <div class="register-card mx-auto" id="step3" style="display:none;">
        <h1 class="h4 fw-bold text-center mb-3">Récapitulatif</h1>

        <div class="recap-section">
            <div class="recap-section-title">Identité</div>
            <div class="recap-row">
                <span class="recap-label">Nom</span>
                <span class="recap-value" id="recapNom">{{ $identity['nom'] ?? '—' }}</span>
            </div>
            <div class="recap-row">
                <span class="recap-label">Email</span>
                <span class="recap-value" id="recapEmail">{{ $reg['email'] ?? '—' }}</span>
            </div>
            <div class="recap-row">
                <span class="recap-label">Téléphone</span>
                <span class="recap-value" id="recapTel">{{ $identity['telephone'] ?? '—' }}</span>
            </div>
        </div>

        <div class="recap-section">
            <div class="recap-section-title">Organisation</div>
            <div class="recap-row">
                <span class="recap-label">Type</span>
                <span class="recap-value" id="recapType">{{ $type ? ucfirst($type) : '—' }}</span>
            </div>
            <div class="recap-row" id="recapOrgRow" style="display:none;">
                <span class="recap-label">Organisation</span>
                <span class="recap-value" id="recapOrg"></span>
            </div>
            <div class="recap-row" id="recapDetailRow" style="display:none;">
                <span class="recap-label">Type détail</span>
                <span class="recap-value" id="recapDetail"></span>
            </div>
            <div class="recap-row">
                <span class="recap-label">Justificatif</span>
                <span class="recap-value" id="recapDoc">Fourni</span>
            </div>
        </div>

        <form method="POST" action="{{ route('inscriptions.confirm') }}" id="formStep3">
            @csrf
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="cgu" id="cgu" value="1" required>
                    <label class="form-check-label" for="cgu" style="font-size:.85rem;">
                        J'accepte les <a href="{{ route('cgu') }}" target="_blank">conditions générales d'utilisation</a>
                        et la <a href="{{ route('confidentialite') }}" target="_blank">politique de confidentialité</a>
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100" style="border-radius:10px;">Créer mon compte</button>
            <button type="button" class="btn btn-outline-secondary w-100 mt-2" style="border-radius:10px;" onclick="goToStep(2)">Précédent</button>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .type-card {
        border: 2px solid #e0dde3; border-radius: 14px; padding: 1.25rem .75rem;
        cursor: pointer; transition: all .2s; text-align: center; height: 100%;
    }
    .type-card:hover { border-color: #9972B0; background: #faf8fb; }
    .type-card.selected { border-color: #542680; background: #f5f0f9; }
    .type-card .icon { font-size: 1.6rem; color: #542680; margin-bottom: .4rem; }
    .type-card .name { font-weight: 700; font-size: .85rem; color: #1d1d1f; }
    .type-card .desc { font-size: .75rem; color: #6c757d; line-height: 1.3; }
    .type-card input[type="radio"] { display: none; }
    .toggle-btn {
        text-align: center; padding: .55rem .5rem; border-radius: 10px;
        border: 1.5px solid #e0dde3; cursor: pointer; font-weight: 600; font-size: .82rem;
        background: #fff; transition: .2s; color: #495057;
    }
    .toggle-btn:hover { border-color: #9972B0; }
    .toggle-btn.active { background: #f5f0f9; border-color: #542680; color: #542680; }
    .toggle-btn input { display: none; }
    .doc-info { background: #f8f6f9; border-radius: 10px; padding: .65rem 1rem; font-size: .8rem; color: #495057; }
    .doc-info i { color: #542680; margin-right: .3rem; }
    .recap-section { background: #f8f6f9; border-radius: 12px; padding: .75rem 1rem; margin-bottom: .75rem; }
    .recap-section-title { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #9972B0; margin-bottom: .4rem; }
    .recap-row { display: flex; justify-content: space-between; padding: .5rem 0; border-bottom: 1px solid #f0eeec; font-size: .85rem; }
    .recap-row:last-child { border-bottom: none; }
    .recap-label { color: #6c757d; }
    .recap-value { font-weight: 600; color: #1d1d1f; }
    .form-check-input:checked { background-color: #542680; border-color: #542680; }
    .form-check-input:focus { box-shadow: 0 0 0 3px rgba(84,38,128,.12); border-color: #542680; }
    .btn-primary { background: #542680; border: none; }
    .btn-primary:hover { background: #451e68; }
    .btn-primary:disabled { opacity: .5; cursor: not-allowed; transform: none; }
    .btn-outline-secondary { border-color: #e0dde3; color: #6c757d; }
    .btn-outline-secondary:hover { background: #f8f6f9; border-color: #9972B0; color: #495057; }
</style>
@endpush

@push('scripts')
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

function toggleDetail(el) {
    el.closest('.d-flex').querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    el.querySelector('input').checked = true;
}

(function init(){
    var sel = document.querySelector('.type-card.selected');
    if (sel) {
        var v = sel.querySelector('input[type="radio"]').value;
        document.getElementById('fields-universitaire').style.display = v === 'universitaire' ? 'block' : 'none';
        document.getElementById('fields-organisation').style.display = v === 'organisation' ? 'block' : 'none';
        document.getElementById('fields-particulier').style.display = v === 'particulier' ? 'block' : 'none';
        document.getElementById('doc-text').textContent = docLabels[v] || 'Document justificatif';
    }
})();

/* Progress bar helpers */
function updateProgress(step) {
    for (var i = 1; i <= 4; i++) {
        var dot = document.querySelector('.progress-step[data-step="' + i + '"] .step-dot');
        var line = document.querySelector('.progress-step .step-line[data-line="' + i + '"]');
        dot.classList.remove('active', 'done');
        if (i < step) { dot.classList.add('done'); }
        else if (i === step) { dot.classList.add('active'); }
        if (line) {
            line.classList.remove('done');
            if (i < step) line.classList.add('done');
        }
    }
    var desc = document.getElementById('stepLabel');
    var hint = document.getElementById('stepHint');
    var labels = {
        2: ['<strong>Étape 2 – Identité :</strong>', 'Veuillez compléter votre profil'],
        3: ['<strong>Étape 3 – Organisation :</strong>', 'Choisissez votre type et fournissez les justificatifs'],
        4: ['<strong>Étape 4 – Récapitulatif :</strong>', 'Vérifiez vos informations avant de valider']
    };
    if (labels[step]) { desc.innerHTML = labels[step][0]; hint.textContent = labels[step][1]; }
}

function showErrorMsg(msg) {
    var c = document.querySelector('.register-card:not([style*="display: none"])');
    var a = c.querySelector('.alert-danger');
    if (a) a.remove();
    if (msg) {
        var d = document.createElement('div');
        d.className = 'alert alert-danger py-2';
        d.style.cssText = 'border-radius:10px;font-size:.85rem;';
        d.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>' + msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        c.insertBefore(d, c.querySelector('form') || c.children[2]);
    }
}

function goToStep(step) {
    document.getElementById('step1').style.display = step === 1 ? 'block' : 'none';
    document.getElementById('step2').style.display = step === 2 ? 'block' : 'none';
    document.getElementById('step3').style.display = step === 3 ? 'block' : 'none';
    updateProgress(step + 1);
}

/* Submit Step 1 */
document.getElementById('formStep1').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('btnStep1');
    btn.disabled = true; btn.textContent = 'Envoi en cours…';
    var fd = new FormData(this);
    fetch('{{ route("inscriptions.post-identity") }}', {
        method: 'POST', body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(function(r) { return r.json().then(function(d) { return { status: r.status, body: d }; }); })
    .then(function(res) {
        if (res.status === 422) {
            var firstMsg = '';
            for (var k in res.body.errors) { firstMsg = res.body.errors[k][0]; break; }
            showErrorMsg(firstMsg || 'Veuillez vérifier les champs.');
        } else if (res.body.success) {
            goToStep(2);
            document.getElementById('recapNom').textContent = res.body.nom;
            document.getElementById('recapTel').textContent = res.body.telephone;
        } else {
            showErrorMsg(res.body.message || 'Une erreur est survenue.');
        }
    }).catch(function() { showErrorMsg('Erreur réseau. Réessayez.'); })
    .finally(function() { btn.disabled = false; btn.textContent = 'Continuer'; });
});

/* Submit Step 2 */
document.getElementById('formStep2').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('btnStep2');
    btn.disabled = true; btn.textContent = 'Envoi en cours…';
    var fd = new FormData(this);
    fetch('{{ route("inscriptions.post-org") }}', {
        method: 'POST', body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(function(r) { return r.json().then(function(d) { return { status: r.status, body: d }; }); })
    .then(function(res) {
        if (res.status === 422) {
            var firstMsg = '';
            for (var k in res.body.errors) { firstMsg = res.body.errors[k][0]; break; }
            showErrorMsg(firstMsg || 'Veuillez vérifier les champs.');
        } else if (res.body.success) {
            document.getElementById('recapType').textContent = res.body.type;
            document.getElementById('recapOrgRow').style.display = res.body.organisation ? 'flex' : 'none';
            document.getElementById('recapOrg').textContent = res.body.organisation || '';
            document.getElementById('recapDetailRow').style.display = res.body.type_detail ? 'flex' : 'none';
            document.getElementById('recapDetail').textContent = res.body.type_detail || '';
            goToStep(3);
        } else {
            showErrorMsg(res.body.message || 'Une erreur est survenue.');
        }
    }).catch(function() { showErrorMsg('Erreur réseau. Réessayez.'); })
    .finally(function() { btn.disabled = false; btn.textContent = 'Continuer'; });
});
</script>
@endpush
