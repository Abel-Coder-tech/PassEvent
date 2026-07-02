@extends('auth.register.layout')

@section('title', 'Connexion — PaxEvent')
@section('page-title', 'Connexion')
@section('page-subtitle', 'Accédez à votre tableau de bord')

@section('card-content')
@if (session('success'))
    <div class="success-icon text-center"><i class="bi bi-check-circle-fill"></i></div>
    <h2 style="font-size:1.3rem;font-weight:800;color:#211C31;text-align:center;margin-bottom:0.5rem;">Inscription réussie !</h2>
    <p style="text-align:center;color:#6c757d;font-size:.9rem;line-height:1.5;">Votre compte a été créé avec succès.<br>Vous recevrez sous peu un email de confirmation une fois votre compte validé par l'administrateur.</p>
    <div style="margin-top:1.5rem;display:flex;flex-direction:column;gap:0.6rem;">
        <a href="{{ route('evenements.public') }}" class="btn-primary" style="text-align:center;text-decoration:none;display:block;">
            <i class="bi bi-calendar-event me-1"></i> Explorer les événements
        </a>
        <a href="{{ route('login') }}" class="btn-secondary" style="text-align:center;margin-top:0;">
            <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
        </a>
    </div>
@else
    @if(config('services.google.client_id'))
        <a href="{{ route('google.redirect') }}" class="btn-google w-100 mb-3">
            <i class="bi bi-google" style="color:#4285F4;font-size:1.1rem;"></i> Se connecter avec Google
        </a>
        <div class="divider mb-3">
            <span style="background:#fff;padding:0 12px;color:#6c757d;font-size:.85rem;">ou</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="mb-3 text-start">
            <label class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                   placeholder="Entrez votre email" value="{{ old('email') }}" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-2 text-start">
            <label class="form-label">Mot de passe</label>
            <div style="position:relative;">
                <input type="password" class="form-control @error('mot_de_passe') is-invalid @enderror"
                       id="mot_de_passe" name="mot_de_passe" placeholder="Entrez votre mot de passe" required>
                <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password"
                        style="right:4px;top:50%;transform:translateY(-50%);padding:4px;z-index:5;">
                    <i class="bi bi-eye" style="color:#9a9a9a;"></i>
                </button>
            </div>
            @error('mot_de_passe')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember" style="font-size:0.85rem;color:#6c757d;">Se souvenir de moi</label>
            </div>
            <a href="{{ route('password.request') }}" style="color:#542680;font-size:0.82rem;font-weight:600;">Mot de passe oublié ?</a>
        </div>
        <button type="submit" class="btn-primary" style="margin-top:0;">Se connecter</button>
    </form>
    <p class="text-center mt-3" style="font-size:0.85rem;color:#6c757d;">
        Pas encore de compte ? <a href="{{ route('inscriptions.organisateur') }}" style="color:#542680;font-weight:600;text-decoration:underline;">Créer un compte</a>
    </p>
@endif
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