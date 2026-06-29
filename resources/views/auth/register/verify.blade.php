@extends('layouts.register')

@section('title', 'Vérification — PaxEvent')

@section('progress-bar')
<div class="d-flex justify-content-center align-items-center" style="max-width:480px;margin:0 auto;padding:0 1rem;">
    <div class="progress-step" data-step="1">
        <div class="step-dot done">1</div>
        <div class="step-line done" data-line="1"></div>
    </div>
    <div class="progress-step" data-step="2">
        <div class="step-dot active" id="dot2">2</div>
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
    <strong>Étape 1 – Vérification :</strong> Un code vous a été envoyé par email
</p>
@endsection

@section('content')
<div class="container py-4">
    <div class="register-card mx-auto text-center">
        <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" height="70" class="mb-3">
        <h1 class="h4 fw-bold">Vérification</h1>
        <p class="text-muted small">Un code de vérification a été envoyé à :</p>
        <div style="background:#f8f6f9;border-radius:10px;padding:.6rem 1rem;font-size:.85rem;color:#542680;word-break:break-all;margin-bottom:.5rem;">{{ $email }}</div>
        <p class="text-muted small">Ce code est valable 10 minutes.</p>

        @if(session('success'))
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:.6rem 1rem;font-size:.85rem;margin-bottom:1rem;">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('inscriptions.verify-otp') }}">
            @csrf
            <div class="mb-3 text-start">
                <label class="form-label small fw-semibold text-muted">Code à 4 chiffres</label>
                <input type="text" name="code" class="form-control text-center @error('code') is-invalid @enderror"
                       maxlength="4" inputmode="numeric" pattern="[0-9]*" required autofocus
                       placeholder="••••" style="font-size:1.5rem;letter-spacing:6px;font-weight:700;">
                @error('code') <div class="invalid-feedback text-center">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primary w-100" style="border-radius:10px;">Vérifier mon code</button>
        </form>

        <p class="mt-3" style="font-size:.85rem;color:#6c757d;" id="resend-section">
            <span id="resend-text">Renvoyer dans <strong id="timer">60</strong>s</span>
            <a id="resend-btn" class="disabled" style="color:#542680;font-weight:600;text-decoration:none;cursor:pointer;" onclick="resendCode()">Renvoyer le code</a>
        </p>

        <p class="mt-2" style="font-size:.85rem;">
            <a href="{{ route('inscriptions.create') }}" style="color:#6c757d;text-decoration:none;"><i class="bi bi-arrow-left"></i> Modifier l'email</a>
        </p>
    </div>
</div>

<script>
let seconds = 60;
const timerEl = document.getElementById('timer');
const textEl = document.getElementById('resend-text');
const btnEl = document.getElementById('resend-btn');
const interval = setInterval(() => {
    seconds--;
    timerEl.textContent = seconds;
    if (seconds <= 0) { clearInterval(interval); textEl.style.display = 'none'; btnEl.classList.remove('disabled'); }
}, 1000);
function resendCode() {
    btnEl.classList.add('disabled'); textEl.style.display = 'inline'; seconds = 60; timerEl.textContent = seconds;
    fetch('{{ route("inscriptions.resend-otp") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
    .then(r => r.json()).then(d => { if (d.success) location.reload(); }).catch(() => { location.reload(); });
    const interval2 = setInterval(() => { seconds--; timerEl.textContent = seconds; if (seconds <= 0) { clearInterval(interval2); textEl.style.display = 'none'; btnEl.classList.remove('disabled'); } }, 1000);
}
</script>
@endsection
