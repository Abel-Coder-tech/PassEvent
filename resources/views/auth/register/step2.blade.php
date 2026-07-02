@extends('auth.register.layout')

@section('title', 'Organisation — PaxEvent')
@section('step', 2)
@section('page-title', "Type d'organisation")
@section('page-subtitle', 'Choisissez votre type et fournissez les justificatifs')

@section('card-content')
<img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo" style="max-width:150px;height:auto;display:block;margin:0 auto 1rem;">

@if($errors->any())
    <div class="alert alert-danger py-2" style="font-size:.85rem;border-radius:10px;">
        @foreach($errors->all() as $e) {{ $e }} @break @endforeach
    </div>
@endif

<form method="POST" action="{{ route('inscriptions.post-org') }}" enctype="multipart/form-data">
    @csrf

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="type-card @if(old('type', $type) === 'universitaire') selected @endif" onclick="selectType(this, 'universitaire')">
                <div class="icon"><i class="bi bi-building-columns"></i></div>
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
        <label class="form-label">Description <span class="text-muted fw-normal">(optionnelle)</span></label>
        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                  rows="3" placeholder="Parlez-nous de votre activité..." style="resize:vertical;">{{ old('description', $data['description'] ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" id="doc-label">Pièce justificative</label>
        <div id="doc-helper" class="doc-info mb-2"><i class="bi bi-file-earmark-text"></i> <span id="doc-text">Carte étudiante ou lettre de l'université</span></div>
        <input type="file" name="document_justificatif" class="form-control @error('document_justificatif') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
        <div class="form-text">Format PDF, JPG ou PNG. Max 5 Mo.</div>
        @error('document_justificatif') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <button type="submit" class="btn-primary">Continuer</button>
    <a href="{{ route('inscriptions.identity') }}" class="btn-secondary">Précédent</a>
</form>
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