@extends('auth.register.layout')

@section('title', 'Identité — PaxEvent')
@section('step', 1)
@section('page-title', 'Votre identité')
@section('page-subtitle', 'Qui êtes-vous ?')

@section('card-content')
@if($errors->any())
    <div class="alert alert-danger py-2" style="font-size:.85rem;border-radius:10px;">
        @foreach($errors->all() as $e) {{ $e }} @break @endforeach
    </div>
@endif

<form method="POST" action="{{ route('inscriptions.post-identity') }}" enctype="multipart/form-data">
    @csrf

    @if($from_google && !empty(session('registration.google_name')))
    <div class="mb-3">
        <label class="form-label">Nom complet</label>
        <input type="text" name="nom" class="form-control" value="{{ session('registration.google_name') }}" readonly style="background:#f8f6f9;cursor:not-allowed;">
        <div class="form-text">Récupéré de votre compte Google</div>
    </div>
    @else
    <div class="mb-3">
        <label class="form-label">Nom complet</label>
        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
               value="{{ old('nom', $data['nom'] ?? '') }}" required placeholder="Votre nom et prénoms">
        @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    @endif

    <div class="mb-3">
        <label class="form-label">Téléphone</label>
        <input type="tel" name="telephone" class="form-control @error('telephone') is-invalid @enderror"
               value="{{ old('telephone', $data['telephone'] ?? '') }}" required placeholder="+229 XX XX XX XX">
        @error('telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    @if(!$from_google)
    <div class="mb-3">
        <label class="form-label">Mot de passe</label>
        <input type="password" name="mot_de_passe" id="reg_password" class="form-control @error('mot_de_passe') is-invalid @enderror"
               minlength="8" required placeholder="Min. 8 caractères" oninput="checkPasswordStrength(this.value)">
        @error('mot_de_passe') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div id="passwordMeter" class="mt-2" style="display:none;">
            <div style="height:4px;background:#e9ecef;border-radius:2px;overflow:hidden;">
                <div id="passwordBar" style="height:100%;width:0;border-radius:2px;transition:width .3s,background .3s;"></div>
            </div>
            <div id="passwordLabel" class="mt-1" style="font-size:0.78rem;font-weight:600;"></div>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Confirmer le mot de passe</label>
        <input type="password" name="mot_de_passe_confirmation" class="form-control"
               required placeholder="Répétez le mot de passe">
    </div>
    @endif

    <style>
    #passwordMeter { animation: fadeIn .2s ease; }
    @keyframes fadeIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:translateY(0); } }
    </style>
    <script>
    function checkPasswordStrength(pwd) {
        var meter = document.getElementById('passwordMeter');
        var bar = document.getElementById('passwordBar');
        var label = document.getElementById('passwordLabel');
        if (!pwd) { meter.style.display = 'none'; return; }
        meter.style.display = 'block';
        var score = 0;
        if (pwd.length >= 8) score += 25;
        if (pwd.length >= 12) score += 15;
        if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score += 20;
        if (/\d/.test(pwd)) score += 20;
        if (/[^a-zA-Z0-9]/.test(pwd)) score += 20;
        if (pwd.length > 15) score += 10;
        score = Math.min(100, score);
        var color, text, icon;
        if (score < 40) { color = '#dc3545'; text = 'Mot de passe faible'; icon = 'bi-exclamation-triangle-fill'; }
        else if (score < 70) { color = '#f59e0b'; text = 'Mot de passe moyen'; icon = 'bi-shield-exclamation'; }
        else { color = '#10b981'; text = 'Mot de passe fort'; icon = 'bi-shield-fill-check'; }
        bar.style.width = score + '%';
        bar.style.background = color;
        label.innerHTML = '<i class="bi ' + icon + '" style="color:' + color + '"></i> <span style="color:' + color + '">' + text + '</span>';
    }
    </script>

    <div class="mb-3">
        <label class="form-label">Photo de profil <span class="text-muted fw-normal">(optionnel)</span></label>
        <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
        <div class="form-text">Formats acceptés : JPG, PNG. Max 2 Mo.</div>
        @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <button type="submit" class="btn-primary">Suivant</button>
    <a href="{{ route('inscriptions.previous', 0) }}" class="btn-secondary">Précédent</a>
</form>
@endsection