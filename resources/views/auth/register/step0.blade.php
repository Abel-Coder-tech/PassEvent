@extends('auth.register.layout')

@section('title', 'Inscription — PaxEvent')
@section('step', 0)
@section('page-title', 'Créer un compte organisateur')
@section('page-subtitle', 'Entrez votre adresse email pour commencer')

@section('card-content')
<img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo" style="max-width:150px;height:auto;display:block;margin:0 auto 1rem;">

@if($errors->any())
    <div class="alert-danger">
        @foreach($errors->all() as $e) {{ $e }} @break @endforeach
    </div>
@endif

@if(config('services.google.client_id'))
<a href="{{ route('google.redirect') }}" class="btn-google w-100 mb-3">
    <i class="bi bi-google me-2"></i> S'inscrire avec Google
</a>
<div class="divider mb-3">
    <span style="background:#fff;padding:0 12px;color:#6c757d;font-size:.85rem;">ou</span>
</div>
@endif

<form method="POST" action="{{ route('inscriptions.send-otp') }}">
    @csrf
    <div class="mb-3 text-start">
        <label class="form-label small fw-semibold text-muted">Adresse email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email') }}" required autofocus placeholder="exemple@email.com">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="btn btn-primary">Recevoir mon code</button>
</form>

<p class="login-link">
    Déjà un compte ? <a href="{{ route('login') }}">Connectez-vous</a>
</p>
@endsection