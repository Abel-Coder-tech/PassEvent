@extends('layouts.public')

@section('title', 'Connexion agent — PaxEvent')

@section('styles')
<style>
    body { background: linear-gradient(135deg, #f5f5f7 0%, #e8e0ec 100%); padding-top: 0; }
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card-login {
        background: #fff;
        border-radius: 20px;
        padding: 2rem 1.5rem;
        max-width: 400px;
        width: 100%;
        box-shadow: 0 8px 40px rgba(0,0,0,0.06);
    }
    .card-login .logo { max-width: 120px; display: block; margin: 0 auto 1rem; }
    .card-login h1 { font-size: 1.3rem; font-weight: 700; text-align: center; color: var(--sombre); margin-bottom: 0.25rem; }
    .card-login .subtitle { font-size: 0.85rem; color: #6c757d; text-align: center; margin-bottom: 1.5rem; }
    .card-login .form-control { border-radius: 10px; padding: 0.65rem 1rem; border: 1.5px solid #e0dde3; }
    .card-login .form-control:focus { border-color: var(--violet); box-shadow: 0 0 0 3px rgba(84,38,128,0.12); }
    .card-login .btn-primary {
        background: var(--violet); border: none; border-radius: 10px; padding: 0.7rem 1rem;
        font-weight: 600; width: 100%; transition: 0.2s;
    }
    .card-login .btn-primary:hover { background: #3d1a5c; transform: translateY(-1px); }
</style>
@endsection

@section('content')
<div class="login-container">
    <div class="card-login">
        <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo">
        <h1>Espace Agent</h1>
        <p class="subtitle">Connectez-vous pour scanner les tickets</p>

        @if($errors->any())
            <div class="alert alert-danger py-2" style="font-size:0.85rem;border-radius:10px;">
                @foreach($errors->all() as $e) {{ $e }} @break @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('agent.login.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="agent@exemple.com">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Mot de passe</label>
                <input type="password" name="password" class="form-control" required placeholder="Mot de passe">
            </div>
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>
    </div>
</div>
@endsection
