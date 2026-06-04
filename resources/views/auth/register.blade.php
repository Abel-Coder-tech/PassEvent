<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — PassEvent</title>
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
        .register-card {
            display: flex;
            width: 100%;
            max-width: 800px;
            min-height: 520px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            background: #fff;
        }
        .register-card-left {
            width: 45%;
            position: relative;
            overflow: hidden;
        }
        .register-card-left img {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .register-card-left .content {
            position: relative;
            z-index: 1;
            color: #fff;
            text-align: center;
            padding: 2rem;
            text-shadow: 0 1px 8px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        .register-card-left .content h2 {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
        }
        .register-card-left .content p {
            font-size: 0.85rem;
            opacity: 0.9;
            line-height: 1.5;
        }
        .register-card-right {
            width: 55%;
            padding: 2.5rem;
        }
        .register-form { width: 100%; max-width: 360px; }
        .register-form h2 {
            font-size: 1.3rem;
            font-weight: 800;
            color: #1a1a2e;
            margin-bottom: 0.2rem;
        }
        .register-form .subtitle {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 1.2rem;
        }
        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.65rem 1rem 0.65rem 2.3rem;
            font-size: 0.85rem;
        }
        .form-control:focus {
            border-color: var(--violet, #87428b);
            box-shadow: 0 0 0 3px rgba(135,66,139,0.12);
        }
        .input-group-custom { position: relative; }
        .input-group-custom .icon {
            position: absolute;
            left: 0.7rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9a9a9a;
            font-size: 0.9rem;
            z-index: 5;
        }
        .btn-inscription {
            background: #6d3570;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 0.7rem;
            width: 100%;
            transition: background 0.2s;
        }
        .btn-inscription:hover { background: #5a2d5d; }
        .login-link { color: #87428b; font-size: 0.85rem; text-decoration: underline; }
        .login-link:hover { color: #6d3570; }
        .step-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        .step-badge .num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #b2e0d6;
            color: #3d4345;
            font-weight: 700;
            font-size: 0.7rem;
        }
        @media (max-width: 640px) {
            .register-card-left { display: none; }
            .register-card-right { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-card-left">
            <img src="{{ asset('images/image_connexion.jpeg') }}" alt="">
        </div>
        <div class="register-card-right">
            <div class="register-form">
                <div class="step-badge"><span class="num">1</span> Créer votre compte</div>
                <h2>Inscription</h2>
                <p class="subtitle">Rejoignez PassEvent en quelques clics</p>

                @if ($errors->any())
                    <div class="alert alert-danger py-2" style="font-size:0.82rem;">
                        @foreach ($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('inscriptions.store') }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label" style="font-size:0.8rem; font-weight:600;">Nom complet</label>
                        <div class="input-group-custom">
                            <i class="bi bi-person icon"></i>
                            <input type="text" class="form-control @error('nom') is-invalid @enderror" name="nom" placeholder="Votre nom" value="{{ old('nom') }}" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:0.8rem; font-weight:600;">Email</label>
                        <div class="input-group-custom">
                            <i class="bi bi-envelope icon"></i>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="exemple@email.com" value="{{ old('email') }}" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:0.8rem; font-weight:600;">Telephone</label>
                        <div class="input-group-custom">
                            <i class="bi bi-telephone icon"></i>
                            <input type="tel" class="form-control @error('telephone') is-invalid @enderror" name="telephone" placeholder="Votre numero" value="{{ old('telephone') }}" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:0.8rem; font-weight:600;">Organisation</label>
                        <div class="input-group-custom">
                            <i class="bi bi-building icon"></i>
                            <input type="text" class="form-control @error('organisation') is-invalid @enderror" name="organisation" placeholder="Nom de votre organisation" value="{{ old('organisation') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.8rem; font-weight:600;">Mot de passe</label>
                        <div class="input-group-custom">
                            <i class="bi bi-lock icon"></i>
                            <input type="password" class="form-control @error('mot_de_passe') is-invalid @enderror" name="mot_de_passe" placeholder="Minimum 8 caracteres" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-inscription">Créer mon compte</button>
                </form>
                <p class="text-center mt-3" style="font-size:0.82rem; color:#6c757d;">
                    Déja un compte ? <a href="{{ route('login') }}" class="login-link">Connectez-vous</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>