@extends('layouts.public')

@section('title', 'Inscription — PaxEvent')

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
    .card-register .form-control { border-radius: 10px; padding: .7rem 1rem; border: 1.5px solid #e0dde3; }
    .card-register .form-control:focus { border-color: #542680; box-shadow: 0 0 0 3px rgba(84,38,128,.12); }
    .card-register .btn-primary {
        background: #542680; border: none; border-radius: 10px; padding: .7rem 1rem;
        font-weight: 600; width: 100%; transition: .2s;
    }
    .card-register .btn-primary:hover { background: #451e68; transform: translateY(-1px); }
    .card-register .btn-primary:disabled { opacity: .6; }
    .card-register .login-link { margin-top: 1.5rem; font-size: .85rem; color: #6c757d; }
    .card-register .login-link a { color: #542680; font-weight: 600; text-decoration: none; }
    .card-register .login-link a:hover { text-decoration: underline; }
    .card-register .invalid-feedback { text-align: left; font-size: .8rem; }
    .card-register .is-invalid { border-color: #dc3545 !important; }
    .card-register .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 10px; padding: .6rem 1rem; font-size: .85rem; margin-bottom: 1rem; }
    .btn-google {
        display: flex; align-items: center; justify-content: center; gap: .5rem;
        background: #fff; border: 1.5px solid #e0dde3; border-radius: 10px; padding: .7rem 1rem;
        font-weight: 600; font-size: .9rem; color: #1d1d1f; text-decoration: none; transition: .2s;
    }
    .btn-google:hover { background: #f8f6f9; border-color: #9972B0; color: #1d1d1f; }
    .btn-google i { color: #4285F4; font-size: 1.1rem; }
    .divider { display: flex; align-items: center; color: #ccc; font-size: .85rem; }
    .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #e0dde3; }
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-xl-5">
            <div class="card-register">
                <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo">
                <h1>Créer un compte organisateur</h1>
                <p class="subtitle">Entrez votre adresse email pour commencer</p>

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
            </div>
        </div>
    </div>
</div>
@endsection
