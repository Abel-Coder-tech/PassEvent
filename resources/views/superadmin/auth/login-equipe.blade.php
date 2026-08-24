<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Équipe - PaxEvent</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 2.5rem 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.35);
        }

        .login-brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-brand-icon {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: #fff;
        }
        .login-brand h1 {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1a1d23;
            margin-bottom: 0.25rem;
        }
        .login-brand p {
            font-size: 0.8rem;
            color: #8898aa;
            font-weight: 500;
        }

        .login-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.4rem;
            display: block;
        }

        .login-input {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 1.5px solid #e9ecef;
            border-radius: 10px;
            font-size: 0.88rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #f8f9fa;
        }
        .login-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
            outline: none;
            background: #fff;
        }

        .login-btn {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s;
        }
        .login-btn:hover { opacity: 0.92; transform: translateY(-1px); }
        .login-btn:active { transform: translateY(0); }

        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: #8898aa;
        }
        .login-footer a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }
        .login-footer a:hover { text-decoration: underline; }

        .login-alert {
            background: rgba(231,76,60,0.08);
            color: #e74c3c;
            border-radius: 10px;
            padding: 0.7rem 1rem;
            font-size: 0.82rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .login-info {
            background: rgba(37,99,235,0.08);
            color: #1d4ed8;
            border-radius: 10px;
            padding: 0.7rem 1rem;
            font-size: 0.82rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .password-hint {
            font-size: 0.7rem;
            color: #8898aa;
            margin-top: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .password-hint i { font-size: 0.6rem; }

        @media (max-width: 480px) {
            .login-card { padding: 1.75rem 1.25rem; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-brand">
                <img src="{{ asset_v('images/logo_paxevent.png') }}" alt="PaxEvent" height="56" class="mb-3">
                <div class="login-brand-icon"><i class="bi bi-people-fill"></i></div>
                <h1>Espace Équipe</h1>
                <p>Connexion réservée aux membres de l'équipe PaxEvent</p>
            </div>

            @if($errors->any())
                <div class="login-alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    {{ $errors->first() }}
                </div>
            @elseif(session('info'))
                <div class="login-info">
                    <i class="bi bi-info-circle-fill"></i>
                    {{ session('info') }}
                </div>
            @endif

            <form action="{{ route('equipe.login.post') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="login-label"><i class="bi bi-person me-1"></i>Pseudo</label>
                    <input type="text" name="pseudo" class="login-input @error('pseudo') is-invalid @enderror" placeholder="Entrez votre pseudo" value="{{ old('pseudo') }}" required autofocus autocomplete="username">
                    @error('pseudo')<div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="login-label"><i class="bi bi-lock me-1"></i>Mot de passe</label>
                    <div class="position-relative">
                        <input type="password" name="mot_de_passe" id="eq_password" class="login-input @error('mot_de_passe') is-invalid @enderror" placeholder="Mot de passe" required minlength="8" autocomplete="current-password">
                        <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password" style="right: 4px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5;">
                            <i class="bi bi-eye" style="color: #9a9a9a;"></i>
                        </button>
                    </div>
                    @error('mot_de_passe')<div class="text-danger mt-1" style="font-size:0.78rem;">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="login-btn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Accéder à mon espace
                </button>
            </form>

            <div class="login-footer">
                <span>Vous êtes le propriétaire ? <a href="{{ route('superadmin.login') }}">Connexion administration</a></span>
            </div>
        </div>
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
