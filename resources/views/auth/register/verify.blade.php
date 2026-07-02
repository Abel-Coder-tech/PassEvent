@extends('auth.register.layout')

@section('title', 'Vérification — PaxEvent')
@section('step', 0)
@section('page-title', 'Vérification')
@section('page-subtitle')
Un code de vérification a été envoyé à&nbsp;:
@endsection

@section('card-content')
<img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo" style="max-width:150px;height:auto;display:block;margin:0 auto 1rem;">

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
    <button type="submit" class="btn btn-primary">Vérifier mon code</button>
</form>

<p class="resend-link" id="resend-section">
    <span id="resend-text">Renvoyer dans <strong id="timer">60</strong>s</span>
    <a id="resend-btn" class="disabled" onclick="resendCode()">Renvoyer le code</a>
</p>

<p class="back-link">
    <a href="{{ route('inscriptions.organisateur') }}"><i class="bi bi-arrow-left"></i> Modifier l'email</a>
</p>
@endsection

@section('scripts')
<script>
    let seconds = 60;
    const timerEl = document.getElementById('timer');
    const textEl = document.getElementById('resend-text');
    const btnEl = document.getElementById('resend-btn');

    const interval = setInterval(() => {
        seconds--;
        timerEl.textContent = seconds;
        if (seconds <= 0) {
            clearInterval(interval);
            textEl.style.display = 'none';
            btnEl.classList.remove('disabled');
        }
    }, 1000);

    function resendCode() {
        btnEl.classList.add('disabled');
        textEl.style.display = 'inline';
        seconds = 60;
        timerEl.textContent = seconds;

        fetch('{{ route("inscriptions.resend-otp") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(r => r.json()).then(d => {
            if (d.success) location.reload();
        }).catch(() => {
            location.reload();
        });

        const interval2 = setInterval(() => {
            seconds--;
            timerEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(interval2);
                textEl.style.display = 'none';
                btnEl.classList.remove('disabled');
            }
        }, 1000);
    }
</script>
@endsection