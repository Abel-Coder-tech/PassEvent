@extends('layouts.public')

@section('title', 'Connexion affilié — PaxEvent')

@section('styles')
<style>
    .aff-auth-shell {
        min-height: calc(100vh - 64px);
        display: flex; align-items: center; justify-content: center;
        padding: 2.5rem 1.5rem;
    }
    .card-auth {
        background: #fff; border-radius: 20px; padding: 2rem 1.5rem;
        max-width: 400px; width: 100%; box-shadow: 0 8px 40px rgba(0,0,0,0.06);
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
</style>
@endsection

@section('content')
<div class="aff-auth-shell">
    <div class="card-auth">
        <h1>Espace affilié</h1>
        <p class="subtitle">Connectez-vous à votre tableau de bord</p>

        <form method="POST" action="{{ route('affiliate.login.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus placeholder="vous@exemple.com">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Mot de passe</label>
                <div class="position-relative">
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Mot de passe" style="padding-right: 2.6rem;">
                    <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password" style="right: 8px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5;">
                        <i class="bi bi-eye" style="color: #9a9a9a;"></i>
                    </button>
                </div>
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>

        <p class="text-center small text-muted mt-3 mb-0">
            Pas encore de compte affilié ?
            <a href="{{ route('affiliate.register') }}" style="color: var(--violet); font-weight:600;">Créer un compte</a>
        </p>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input');
        if (!input) return;
        const icon = this.querySelector('i');
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });
});
</script>
@endsection
