@extends('layouts.public')

@section('title', 'Vérification — PaxEvent')

@section('styles')
<style>
    body { background: linear-gradient(135deg, #f5f5f7 0%, #e8e0ec 100%); }
    .card-register {
        background: #fff;
        border-radius: 20px;
        padding: 1.5rem;
        max-width: 420px;
        margin: 0 auto;
        box-shadow: 0 8px 40px rgba(0,0,0,0.06);
        text-align: center;
    }
    .card-register .logo { max-width: 150px; height: auto; display: block; margin: 0 auto 1rem; }
    .card-register h1 { font-size: 1.5rem; font-weight: 700; color: #1d1d1f; margin-bottom: .35rem; }
    .card-register .subtitle { font-size: .9rem; color: #6c757d; margin-bottom: 1.5rem; }
    .card-register .form-control { border-radius: 10px; padding: .7rem 1rem; border: 1.5px solid #e0dde3; text-align: center; font-size: 1.5rem; letter-spacing: 6px; font-weight: 700; }
    .card-register .form-control:focus { border-color: #542680; box-shadow: 0 0 0 3px rgba(84,38,128,.12); }
    .card-register .btn-primary {
        background: #542680; border: none; border-radius: 10px; padding: .7rem 1rem;
        font-weight: 600; width: 100%; transition: .2s;
    }
    .card-register .btn-primary:hover { background: #451e68; transform: translateY(-1px); }
    .card-register .btn-primary:disabled { opacity: .6; }
    .card-register .invalid-feedback { text-align: center; font-size: .8rem; }
    .card-register .is-invalid { border-color: #dc3545 !important; }
    .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 10px; padding: .6rem 1rem; font-size: .85rem; margin-bottom: 1rem; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 10px; padding: .6rem 1rem; font-size: .85rem; margin-bottom: 1rem; }
    .info-email { background: #f8f6f9; border-radius: 10px; padding: .6rem 1rem; font-size: .85rem; color: #542680; margin-bottom: 1rem; word-break: break-all; }
    .resend-link { font-size: .85rem; color: #6c757d; margin-top: 1rem; }
    .resend-link a { color: #542680; font-weight: 600; text-decoration: none; cursor: pointer; }
    .resend-link a.disabled { color: #aaa; cursor: not-allowed; pointer-events: none; }
    .back-link { font-size: .85rem; color: #6c757d; margin-top: .75rem; }
    .back-link a { color: #6c757d; text-decoration: none; }
    .back-link a:hover { text-decoration: underline; }
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-xl-5">
            <div class="card-register">
                <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo">
                <h1>Vérification</h1>
                <p class="subtitle">Un code de vérification a été envoyé à&nbsp;:</p>
                <div class="info-email">{{ $email }}</div>
                <p class="subtitle" style="font-size:.8rem;margin-top:-.75rem;">Ce code est valable 10 minutes.</p>

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
                               placeholder="••••">
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Vérifier mon code</button>
                </form>

                <p class="resend-link" id="resend-section">
                    <span id="resend-text">Renvoyer dans <strong id="timer">60</strong>s</span>
                    <a id="resend-btn" class="disabled" onclick="resendCode()">Renvoyer le code</a>
                </p>

                <p class="back-link">
                    <a href="{{ route('inscriptions.create') }}"><i class="bi bi-arrow-left"></i> Modifier l'email</a>
                </p>
            </div>
        </div>
    </div>
</div>
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
