@extends('auth.register.layout')

@section('title', 'Vérification — PaxEvent')

@section('steps')
    @include('auth.register.steps', ['current' => 0])
@endsection

@section('page-title', 'Vérification')
@section('page-subtitle', 'Un code de vérification a été envoyé à votre adresse email')

@section('card-content')
    <div style="background:#f8f6f9;border-radius:10px;padding:.6rem 1rem;font-size:.85rem;color:#542680;text-align:center;word-break:break-all;margin-bottom:1rem;">
        <i class="bi bi-envelope me-1"></i> {{ $email }}
    </div>
    <p style="font-size:.8rem;color:#6c757d;text-align:center;margin-bottom:1rem;">Ce code est valable 10 minutes.</p>

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
            <label class="form-label text-muted">Code à 4 chiffres</label>
            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                   maxlength="4" inputmode="numeric" pattern="[0-9]*" required autofocus
                   placeholder="• • • •" style="text-align:center;font-size:1.5rem;letter-spacing:8px;font-weight:700;">
            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn-primary">Vérifier mon code</button>
    </form>

    <p style="margin-top:1rem;text-align:center;font-size:.85rem;color:#6c757d;" id="resend-section">
        <span id="resend-text">Renvoyer dans <strong id="timer">60</strong>s</span>
        <a id="resend-btn" class="disabled" onclick="resendCode()" style="color:#542680;font-weight:600;cursor:pointer;text-decoration:none;">Renvoyer le code</a>
    </p>

    <p style="margin-top:.5rem;text-align:center;font-size:.82rem;">
        <a href="{{ route('inscriptions.create') }}" style="color:#6c757d;text-decoration:none;">
            <i class="bi bi-arrow-left"></i> Modifier l'email
        </a>
    </p>
@endsection

@push('scripts')
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
        }).then(r => r.json()).then(d => { if (d.success) location.reload(); }).catch(() => { location.reload(); });
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
@endpush
