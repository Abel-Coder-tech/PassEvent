@extends('layouts.public')

@section('title', 'Mot de passe oublié — Espace affilié PaxEvent')

@section('styles')
<style>
    .aff-auth-shell {
        min-height: calc(100vh - 64px);
        display: flex; align-items: center; justify-content: center;
        padding: 2.5rem 1.5rem;
    }
    .card-auth {
        background: #fff; border-radius: 20px; padding: 2rem 1.5rem;
        max-width: 460px; width: 100%; box-shadow: 0 8px 40px rgba(0,0,0,0.06);
    }
    .card-auth h1 { font-size: 1.3rem; font-weight: 700; color: var(--sombre); margin-bottom: 0.25rem; }
    .card-auth .subtitle { font-size: 0.85rem; color: #6c757d; margin-bottom: 1.5rem; }
    .card-auth .form-control { border-radius: 10px; padding: 0.65rem 1rem; border: 1.5px solid #e0dde3; }
    .card-auth .form-control:focus { border-color: var(--violet); box-shadow: 0 0 0 3px rgba(84,38,128,0.12); }
    .card-auth .btn-primary {
        background: var(--violet); border: none; border-radius: 10px; padding: 0.7rem 1rem;
        font-weight: 600; width: 100%; transition: 0.2s;
    }
    .card-auth .btn-primary:hover { background: #3d1a5c; transform: translateY(-1px); }
    .password-toggle { right: 8px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5; }
</style>
@endsection

@section('content')
<div class="aff-auth-shell">
    <div class="card-auth">
        <h1>Mot de passe oublié ?</h1>
        <p class="subtitle">Recevez un code par email pour choisir un nouveau mot de passe.</p>

        @if (session('success'))
            <div class="alert alert-success small">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger small">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('affiliate.password.email') }}">
            @csrf
            <div class="mb-3">
                <label for="reset-email" class="form-label small fw-semibold text-muted">Email du compte affilié</label>
                <input id="reset-email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', session('affiliate_password_reset_email')) }}" required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-primary">Envoyer un code</button>
        </form>

        @if (session('affiliate_password_reset_email'))
            <hr class="my-4">
            <h2 class="h6 fw-bold">Choisir un nouveau mot de passe</h2>
            <p class="small text-muted">Saisissez le code à 6 chiffres reçu à l'adresse indiquée, puis votre nouveau mot de passe.</p>

            <form method="POST" action="{{ route('affiliate.password.update') }}">
                @csrf
                <div class="mb-3">
                    <label for="reset-code" class="form-label small fw-semibold text-muted">Code reçu par email</label>
                    <input id="reset-code" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="new-password" class="form-label small fw-semibold text-muted">Nouveau mot de passe</label>
                    <div class="position-relative">
                        <input id="new-password" type="password" name="password" autocomplete="new-password" class="form-control @error('password') is-invalid @enderror" style="padding-right: 2.6rem;" required>
                        <button type="button" class="btn position-absolute border-0 bg-transparent password-toggle" aria-label="Afficher le nouveau mot de passe" aria-pressed="false">
                            <i class="bi bi-eye" aria-hidden="true" style="color: #9a9a9a;"></i>
                        </button>
                    </div>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="new-password-confirmation" class="form-label small fw-semibold text-muted">Confirmer le nouveau mot de passe</label>
                    <div class="position-relative">
                        <input id="new-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="form-control" style="padding-right: 2.6rem;" required>
                        <button type="button" class="btn position-absolute border-0 bg-transparent password-toggle" aria-label="Afficher la confirmation du nouveau mot de passe" aria-pressed="false">
                            <i class="bi bi-eye" aria-hidden="true" style="color: #9a9a9a;"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Réinitialiser le mot de passe</button>
            </form>
        @endif

        <p class="text-center small text-muted mt-3 mb-0">
            <a href="{{ route('affiliate.login') }}" style="color: var(--violet); font-weight:600;">Retour à la connexion</a>
        </p>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.password-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input');
        if (!input) return;
        const icon = this.querySelector('i');
        const isVisible = input.type === 'password';
        input.type = isVisible ? 'text' : 'password';
        const fieldName = input.name === 'password_confirmation' ? 'la confirmation du nouveau mot de passe' : 'le nouveau mot de passe';
        this.setAttribute('aria-label', `${isVisible ? 'Masquer' : 'Afficher'} ${fieldName}`);
        this.setAttribute('aria-pressed', String(isVisible));
        icon.classList.toggle('bi-eye', !isVisible);
        icon.classList.toggle('bi-eye-slash', isVisible);
    });
});
</script>
@endsection
