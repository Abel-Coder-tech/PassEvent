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
        <input type="password" name="mot_de_passe" class="form-control @error('mot_de_passe') is-invalid @enderror"
               minlength="8" required placeholder="Min. 8 caractères">
        @error('mot_de_passe') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label class="form-label">Confirmer le mot de passe</label>
        <input type="password" name="mot_de_passe_confirmation" class="form-control"
               required placeholder="Répétez le mot de passe">
    </div>
    @endif

    <div class="mb-3">
        <label class="form-label">Photo de profil <span class="text-muted fw-normal">(optionnel)</span></label>
        <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
        <div class="form-text">Formats acceptés : JPG, PNG. Max 2 Mo.</div>
        @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <button type="submit" class="btn-primary">Continuer</button>
    <a href="{{ route('inscriptions.organisateur') }}" class="btn-secondary">Recommencer</a>
</form>
@endsection