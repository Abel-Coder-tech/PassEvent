@extends('auth.register.layout')

@section('title', 'Vérification — PaxEvent')
@section('step', 0)
@section('page-title', 'Vérification')
@section('page-subtitle')
Un code de vérification a été envoyé à&nbsp;:
@endsection

@section('card-content')
<div class="info-email">{{ $email }}</div>
<p style="font-size:.8rem;margin-top:-.75rem;margin-bottom:1.5rem;color:#6c757d;text-align:center;">Ce code est valable 10 minutes.</p>

@if($errors->any())
    <div class="alert-danger">
        @foreach($errors->all() as $e) {{ $e }} @break @endforeach
    </div>
@endif

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('inscriptions.verify-otp') }}">
    @csrf
    <div class="mb-3 text-start">
        <label class="form-label small fw-semibold text-muted">Code à 4 chiffres</label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
               maxlength="4" inputmode="numeric" pattern="[0-9]*" required autofocus
               placeholder="••••" style="text-align:center;font-size:1.5rem;letter-spacing:6px;font-weight:700;">
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <button id="verify-code-submit" type="submit" class="btn btn-primary" disabled>Vérifier mon code</button>
</form>

<p class="resend-link" id="resend-section">
    <span id="resend-text">Renvoyer dans <strong id="timer">10:00</strong></span>
    <a id="resend-btn" role="button" class="disabled" aria-disabled="true" onclick="resendCode()">Renvoyer le code</a>
</p>

<p class="back-link">
    <a href="{{ route('inscriptions.organisateur') }}"><i class="bi bi-arrow-left"></i> Modifier l'email</a>
</p>
@endsection

@section('scripts')
<script>
    const timerEl = document.getElementById('timer');
    const textEl = document.getElementById('resend-text');
    const btnEl = document.getElementById('resend-btn');
    const sentAt = {{ (int) $otpSentAt }};

    function updateCountdown() {
        const remaining = Math.max(0, sentAt + 600 - Math.floor(Date.now() / 1000));
        timerEl.textContent = `${String(Math.floor(remaining / 60)).padStart(2, '0')}:${String(remaining % 60).padStart(2, '0')}`;
        if (remaining === 0) {
            textEl.style.display = 'none';
            btnEl.classList.remove('disabled');
            btnEl.setAttribute('aria-disabled', 'false');
        }
    }

    updateCountdown();
    const countdownInterval = setInterval(() => {
        updateCountdown();
        if (Date.now() / 1000 >= sentAt + 600) clearInterval(countdownInterval);
    }, 1000);

    const codeInput = document.querySelector('input[name="code"]');
    const verifyButton = document.getElementById('verify-code-submit');
    const updateVerifyButton = () => {
        verifyButton.disabled = !/^\d{4}$/.test(codeInput.value);
    };
    codeInput.addEventListener('input', updateVerifyButton);
    updateVerifyButton();

    function afficherErreurResend(message) {
        const box = document.createElement('div');
        box.id = 'resend-error';
        box.className = 'alert-danger';
        box.textContent = message;
        document.querySelector('.card-register').prepend(box);
    }

    function resendCode() {
        if (btnEl.classList.contains('disabled')) return;
        btnEl.classList.add('disabled');
        btnEl.setAttribute('aria-disabled', 'true');

        const alertBox = document.getElementById('resend-error');
        if (alertBox) alertBox.remove();

        fetch('{{ route("inscriptions.resend-otp") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(r => r.json()).then(d => {
            if (d.success) {
                location.reload();
                return;
            }
            afficherErreurResend(d.message || "Le renvoi du code a échoué.");
            btnEl.classList.remove('disabled');
            btnEl.setAttribute('aria-disabled', 'false');
        }).catch(() => {
            afficherErreurResend("Le renvoi du code a échoué : aucune réponse du serveur.");
            btnEl.classList.remove('disabled');
            btnEl.setAttribute('aria-disabled', 'false');
        });
    }
</script>
@endsection