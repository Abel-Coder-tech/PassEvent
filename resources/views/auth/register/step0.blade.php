@extends('layouts.register')

@section('title', 'Inscription — PaxEvent')

@section('progress-bar')
<div class="d-flex justify-content-center align-items-center" style="max-width:480px;margin:0 auto;padding:0 1rem;">
    <div class="progress-step" data-step="1">
        <div class="step-dot active">1</div>
        <div class="step-line" data-line="1"></div>
    </div>
    <div class="progress-step" data-step="2">
        <div class="step-dot" id="dot2">2</div>
        <div class="step-line" data-line="2"></div>
    </div>
    <div class="progress-step" data-step="3">
        <div class="step-dot" id="dot3">3</div>
        <div class="step-line" data-line="3"></div>
    </div>
    <div class="progress-step" data-step="4">
        <div class="step-dot" id="dot4">4</div>
    </div>
</div>
<p class="step-label active text-center mt-2 mb-0" style="font-size:0.78rem;color:var(--gris);">
    <strong>Étape 1 – Email :</strong> Veuillez entrer votre adresse email
</p>
@endsection

@section('content')
<div class="container py-4">
    <div class="register-card mx-auto text-center">
        <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" height="70" class="mb-3">
        <h1 class="h4 fw-bold">Créer un compte organisateur</h1>
        <p class="text-muted small mb-3">Entrez votre adresse email pour commencer</p>

        @if(config('services.google.client_id'))
        <a href="{{ route('google.redirect') }}" class="btn btn-google w-100 mb-3">
            <i class="bi bi-google me-2"></i> S'inscrire avec Google
        </a>
        <div class="d-flex align-items-center mb-3" style="color:#ccc;font-size:.85rem;">
            <span style="flex:1;border-bottom:1px solid #e0dde3;"></span>
            <span style="padding:0 12px;">ou</span>
            <span style="flex:1;border-bottom:1px solid #e0dde3;"></span>
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
            <button type="submit" class="btn btn-primary w-100" style="border-radius:10px;">Recevoir mon code</button>
        </form>

        <p class="mt-3 mb-0" style="font-size:.85rem;color:#6c757d;">
            Déjà un compte ? <a href="{{ route('login') }}" style="color:#542680;font-weight:600;text-decoration:none;">Connectez-vous</a>
        </p>
    </div>
</div>

<style>
.btn-google {
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    background: #fff; border: 1.5px solid #e0dde3; border-radius: 10px; padding: .65rem 1rem;
    font-weight: 600; font-size: .9rem; color: #1d1d1f; text-decoration: none; transition: .2s;
}
.btn-google:hover { background: #f8f6f9; border-color: #9972B0; color: #1d1d1f; }
.btn-google i { color: #4285F4; font-size: 1.1rem; }
</style>
@endsection
