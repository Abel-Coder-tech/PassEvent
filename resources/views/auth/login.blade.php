<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — PassEvent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
            display: flex;
            width: 100%;
            max-width: 800px;
            min-height: 480px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            background: #fff;
        }
        .login-card-left {
            width: 45%;
            position: relative;
            overflow: hidden;
        }
        .login-card-left::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(123,63,160,0.55), rgba(46,125,79,0.35));
            z-index: 0;
        }
        .login-card-left img {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .login-card-left .content {
            position: relative; z-index: 1;
            color: #fff; text-align: center;
            padding: 2rem;
            text-shadow: 0 1px 8px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: inherit;
            height: 100%;
        }
        .login-card-left .content h2 {
            font-size: 1.6rem; font-weight: 800;
            margin-bottom: 0.75rem;
        }
        .login-card-left .content p {
            font-size: 0.9rem; opacity: 0.9;
            line-height: 1.5;
        }
        .login-card-right {
            width: 55%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem;
        }
        .login-form { width: 100%; max-width: 340px; }
        .login-form h2 {
            font-size: 1.4rem; font-weight: 800;
            color: #1a1a2e; margin-bottom: 0.2rem;
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
            border-color: #87428b; box-shadow: 0 0 0 3px rgba(135,66,139,0.12);
        }
        .input-group-custom { position: relative; }
        .input-group-custom .icon {
            position: absolute; left: 0.8rem; top: 50%;
            transform: translateY(-50%);
            color: #9a9a9a; font-size: 1rem; z-index: 5;
        }
        .btn-connexion {
            background: #6d3570; border: none; border-radius: 8px;
            color: #fff; font-weight: 700; font-size: 0.95rem;
            padding: 0.75rem; width: 100%;
            transition: background 0.2s;
        }
        .btn-connexion:hover { background: #5a2d5d; }
        .forgot-link { color: #87428b; font-size: 0.85rem; text-decoration: underline; }
        .forgot-link:hover { color: #6d3570; }
        @media (max-width: 640px) {
            .login-card-left { display: none; }
            .login-card-right { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <!-- Left: Image -->
        <div class="login-card-left">
            <img src="{{ asset('images/image_connexion.jpg') }}" alt="">
            <div class="content">
                <h2>Bienvenue</h2>
                <p>Connectez-vous a votre espace organisateur pour créer et gérer vos événements.</p>
            </div>
        </div>

        <!-- Right: Form -->
        <div class="login-card-right">
            <div class="login-form">
                <h2>Connexion</h2>
                <p class="subtitle">Accédez a votre tableau de bord</p>
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
                            <button type="button" class="btn position-absolute border-0 bg-transparent" id="togglePassword" style="right: 4px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5;">
                                <i class="bi bi-eye" id="toggleIcon" style="color: #9a9a9a;"></i>
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
                    Pas encore de compte ? <a href="{{ route('inscriptions.create') }}" style="color:#87428b; font-weight:600; text-decoration:underline;">Créer un compte</a>
                </p>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const input = document.getElementById('mot_de_passe');
        const icon = document.getElementById('toggleIcon');
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });
    </script>
</body>
</html>
