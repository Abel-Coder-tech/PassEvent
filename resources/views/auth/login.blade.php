<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_paxevent.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — PaxEvent</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f7;
            padding: 1rem;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border-radius: 18px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            background: #fff;
            padding: 2.5rem;
        }
        .login-form { width: 100%; }
        .login-form h2 {
            font-size: 1.4rem; font-weight: 800;
            color: #211C31; margin-bottom: 0.2rem;
        }
        .login-form .subtitle {
            color: #6c757d; font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        .form-control {
            border: 1px solid #e0e0e0; border-radius: 8px;
            padding: 0.75rem 1rem 0.75rem 2.5rem; font-size: 0.9rem;
        }
        .form-control:focus {
            border-color: #542680; box-shadow: 0 0 0 3px rgba(84,38,128,0.12);
        }
        .input-group-custom { position: relative; }
        .input-group-custom .icon {
            position: absolute; left: 0.8rem; top: 50%;
            transform: translateY(-50%);
            color: #9a9a9a; font-size: 1rem; z-index: 5;
        }
        .btn-connexion {
            background: #542680; border: none; border-radius: 8px;
            color: #fff; font-weight: 700; font-size: 0.95rem;
            padding: 0.75rem; width: 100%;
            transition: background 0.2s;
        }
        .btn-connexion:hover { background: #3d1a5c; }
        .forgot-link { color: #542680; font-size: 0.85rem; text-decoration: underline; }
        .forgot-link:hover { color: #542680; }
        .success-card {
            text-align: center;
        }
        .success-card .check-icon {
            width: 64px; height: 64px;
            background: #e8f5e9; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.8rem; color: #2e7d32;
        }
        .success-card h2 {
            font-size: 1.3rem; font-weight: 800;
            color: #211C31; margin-bottom: 0.5rem;
        }
        .success-card p {
            color: #6c757d; font-size: 0.9rem;
            margin-bottom: 0.5rem; line-height: 1.5;
        }
        .success-links {
            margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.6rem;
        }
        .success-links .btn-success-link {
            display: block; padding: 0.7rem; border-radius: 8px;
            font-weight: 600; font-size: 0.9rem; text-decoration: none;
            transition: background 0.2s;
        }
        .success-links .btn-success-link.primary {
            background: #542680; color: #fff;
        }
        .success-links .btn-success-link.primary:hover { background: #3d1a5c; }
        .success-links .btn-success-link.outline {
            border: 1px solid #542680; color: #542680;
        }
        .success-links .btn-success-link.outline:hover { background: #f5f0fa; }
    </style>
</head>
<body>
    <div class="login-card">
        @if (session('success'))
            <div class="success-card">
                <div class="check-icon"><i class="bi bi-check-lg"></i></div>
                <h2>Inscription réussie !</h2>
                <p>Votre compte a été créé avec succès.<br>Vous recevrez sous peu un email de confirmation une fois votre compte validé par l'administrateur.</p>
                <div class="success-links">
                    <a href="{{ route('evenements.public') }}" class="btn-success-link primary">
                        <i class="bi bi-calendar-event me-1"></i> Explorer les événements
                    </a>
                    <a href="{{ route('login') }}" class="btn-success-link outline">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
                    </a>
                </div>
            </div>
        @else
            <div class="login-form" style="text-align:center;">
                <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" height="100" class="mb-3">
                <h2>Connexion</h2>
                <p class="subtitle">Accédez a votre tableau de bord</p>
                @if(config('services.google.client_id'))
                    <a href="{{ route('google.redirect') }}" class="btn btn-google w-100 mb-3" style="display:flex;align-items:center;justify-content:center;gap:.5rem;border:1.5px solid #e0dde3;border-radius:8px;padding:.7rem 1rem;font-weight:600;font-size:.9rem;color:#1d1d1f;text-decoration:none;transition:.2s;background:#fff;">
                        <i class="bi bi-google" style="color:#4285F4;font-size:1.1rem;"></i> Se connecter avec Google
                    </a>
                    <div style="display:flex;align-items:center;color:#ccc;font-size:.85rem;margin-bottom:1rem;">
                        <span style="flex:1;border-bottom:1px solid #e0dde3;"></span>
                        <span style="padding:0 12px;background:#fff;color:#6c757d;">ou</span>
                        <span style="flex:1;border-bottom:1px solid #e0dde3;"></span>
                    </div>
                @endif
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 0.82rem; font-weight: 600;">Email</label>
                        <div class="input-group-custom">
                            <i class="bi bi-envelope icon"></i>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="Entrez votre email" value="{{ old('email') }}" required autofocus>
                        </div>
                        @error('email')<div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 0.82rem; font-weight: 600;">Mot de passe</label>
                        <div class="input-group-custom">
                            <i class="bi bi-lock icon"></i>
                            <input type="password" class="form-control @error('mot_de_passe') is-invalid @enderror" id="mot_de_passe" name="mot_de_passe" placeholder="Entrez votre mot de passe" required>
                                <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password" style="right: 4px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5;">
                                    <i class="bi bi-eye" style="color: #9a9a9a;"></i>
                                </button>
                        </div>
                        @error('mot_de_passe')<div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember" style="font-size: 0.85rem; color: #6c757d;">Se souvenir</label>
                        </div>
                        <a href="{{ route('password.request') }}" class="forgot-link">Mot de passe oublié ?</a>
                    </div>
                    <button type="submit" class="btn-connexion">Se connecter</button>
                </form>
                <p class="text-center mt-3" style="font-size:0.85rem; color:#6c757d;">
                    Pas encore de compte ? <a href="{{ route('inscriptions.organisateur') }}" style="color:#542680; font-weight:600; text-decoration:underline;">Créer un compte</a>
                </p>
            </div>
        @endif
    </div>
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
</body>
</html>
