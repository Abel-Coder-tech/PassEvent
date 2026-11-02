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
        @if ($errors->any())
            <div class="alert alert-danger small">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('affiliate.password.email') }}">
            @csrf
            <div class="mb-3">
                <label for="reset-email" class="form-label small fw-semibold text-muted">Email du compte affilié</label>
                <input id="reset-email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', session('affiliate_password_reset_email')) }}" @if(session('affiliate_password_reset_sent_at')) readonly @endif required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @if(session('affiliate_password_reset_sent_at'))
                <button type="button" class="btn btn-primary" disabled>Code envoyé</button>
                <p class="text-center small text-muted mt-2 mb-0" id="affiliate-countdown">
                    Vous pourrez renvoyer un code dans <strong id="affiliate-timer">10:00</strong>
                </p>
            @else
                <button type="submit" class="btn btn-primary">Envoyer un code</button>
            @endif
        </form>

        @if(session('affiliate_password_reset_sent_at'))
            <form method="POST" action="{{ route('affiliate.password.email') }}" id="affiliate-resend-form" class="text-center mt-2" hidden>
                @csrf
                <input type="hidden" name="email" value="{{ session('affiliate_password_reset_email') }}">
                <a href="#" class="small" onclick="this.closest('form').requestSubmit(); return false;" style="color: var(--violet); font-weight:600; text-decoration:underline;">
                    Renvoyer le code
                </a>
            </form>
        @endif

        @if (session('affiliate_password_reset_sent_at'))
            <hr class="my-4">
            <h2 class="h6 fw-bold">Vérifier le code</h2>
            <p class="small text-muted">Saisissez le code à 4 chiffres reçu à l'adresse indiquée.</p>

            <form method="POST" action="{{ route('affiliate.password.verify') }}">
                @csrf
                <div class="mb-3">
                    <label for="reset-code" class="form-label small fw-semibold text-muted">Code reçu par email</label>
                    <input id="reset-code" type="text" name="code" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="one-time-code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button id="verify-code-submit" type="submit" class="btn btn-primary" disabled>Vérifier le code</button>
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
const resetCode = document.getElementById('reset-code');
const verifySubmit = document.getElementById('verify-code-submit');
if (resetCode && verifySubmit) {
    const updateVerifyButton = () => {
        verifySubmit.disabled = !/^\d{4}$/.test(resetCode.value);
    };
    resetCode.addEventListener('input', updateVerifyButton);
    updateVerifyButton();
}

const sentAt = {{ (int) session('affiliate_password_reset_sent_at', 0) }};
const timerEl = document.getElementById('affiliate-timer');
const resendForm = document.getElementById('affiliate-resend-form');
if (sentAt && timerEl && resendForm) {
    const updateTimer = () => {
        const remaining = Math.max(0, sentAt + 600 - Math.floor(Date.now() / 1000));
        timerEl.textContent = `${String(Math.floor(remaining / 60)).padStart(2, '0')}:${String(remaining % 60).padStart(2, '0')}`;
        if (remaining === 0) {
            document.getElementById('affiliate-countdown').hidden = true;
            resendForm.hidden = false;
        }
    };
    updateTimer();
    setInterval(updateTimer, 1000);
}
</script>
@endsection
