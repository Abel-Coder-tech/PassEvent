@extends('layouts.public')

@section('title', 'Identité — PaxEvent')

@section('styles')
<style>
    body { background: linear-gradient(135deg, #f5f5f7 0%, #e8e0ec 100%); }
    .card-register {
        background: #fff;
        border-radius: 20px;
        padding: 1.5rem;
        max-width: 480px;
        margin: 0 auto;
        box-shadow: 0 8px 40px rgba(0,0,0,0.06);
    }
    .card-register .logo { max-width: 150px; height: auto; display: block; margin: 0 auto 1rem; }
    .card-register h1 { font-size: 1.5rem; font-weight: 700; color: #1d1d1f; margin-bottom: .25rem; text-align: center; }
    .card-register .subtitle { font-size: .9rem; color: #6c757d; margin-bottom: 1.5rem; text-align: center; }
    .card-register .form-control { border-radius: 10px; padding: .65rem 1rem; border: 1.5px solid #e0dde3; }
    .card-register .form-control:focus { border-color: #542680; box-shadow: 0 0 0 3px rgba(84,38,128,.12); }
    .card-register .is-invalid { border-color: #dc3545 !important; }
    .card-register .invalid-feedback { font-size: .8rem; }
    .card-register .form-label { font-size: .82rem; font-weight: 600; color: #495057; margin-bottom: .25rem; }
    .card-register .btn-primary {
        background: #542680; border: none; border-radius: 10px; padding: .7rem 1rem;
        font-weight: 600; width: 100%; transition: .2s; margin-top: .5rem;
    }
    .card-register .btn-primary:hover { background: #451e68; transform: translateY(-1px); }
    .card-register .btn-secondary {
        background: transparent; border: 1.5px solid #e0dde3; border-radius: 10px; padding: .6rem 1rem;
        font-weight: 600; width: 100%; color: #6c757d; transition: .2s; margin-top: .5rem;
        text-decoration: none; display: block; text-align: center;
    }
    .card-register .btn-secondary:hover { background: #f8f6f9; }
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card-register">
                <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo">
                <h1>Votre identité</h1>
                <p class="subtitle">Qui êtes-vous ?</p>

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
                    <a href="{{ route('inscriptions.create') }}" class="btn-secondary">Recommencer</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
