@extends('layouts.public')

@section('title', 'Inscription affilié — PaxEvent')

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
        <h1>Créer un compte affilié</h1>
        <p class="subtitle">Gratuit et sans engagement — réservé aux non-organisateurs</p>

        <form method="POST" action="{{ route('affiliate.register.post') }}">
            @csrf
            <div class="row g-2">
                <div class="col-12 col-sm-6">
                    <label class="form-label small fw-semibold text-muted">Prénom</label>
                    <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror" value="{{ old('prenom') }}" required>
                    @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label small fw-semibold text-muted">Nom</label>
                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}" required>
                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mb-3 mt-3">
                <label class="form-label small fw-semibold text-muted">Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="vous@exemple.com">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Téléphone</label>
                <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}" placeholder="Optionnel">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Mot de passe</label>
                <div class="position-relative">
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="8 caractères minimum" style="padding-right: 2.6rem;">
                    <button type="button" class="btn position-absolute border-0 bg-transparent password-toggle" aria-label="Afficher le mot de passe" aria-pressed="false">
                        <i class="bi bi-eye" aria-hidden="true" style="color: #9a9a9a;"></i>
                    </button>
                </div>
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Confirmer le mot de passe</label>
                <div class="position-relative">
                    <input type="password" name="password_confirmation" class="form-control" required style="padding-right: 2.6rem;">
                    <button type="button" class="btn position-absolute border-0 bg-transparent password-toggle" aria-label="Afficher la confirmation du mot de passe" aria-pressed="false">
                        <i class="bi bi-eye" aria-hidden="true" style="color: #9a9a9a;"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Créer mon compte affilié</button>
        </form>

        <p class="text-center small text-muted mt-3 mb-0">
            Déjà inscrit ?
            <a href="{{ route('affiliate.login') }}" style="color: var(--violet); font-weight:600;">Se connecter</a>
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
        const fieldName = input.name === 'password_confirmation' ? 'la confirmation du mot de passe' : 'le mot de passe';
        this.setAttribute('aria-label', `${isVisible ? 'Masquer' : 'Afficher'} ${fieldName}`);
        this.setAttribute('aria-pressed', String(isVisible));
        icon.classList.toggle('bi-eye', !isVisible);
        icon.classList.toggle('bi-eye-slash', isVisible);
    });
});
</script>
@endsection
