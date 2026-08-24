<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion espace équipe - PaxEvent</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #1a1d23;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .carte { width: 100%; max-width: 440px; background: #fff; border-radius: 16px; padding: 2.5rem 2rem; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .brand { text-align: center; margin-bottom: 1.5rem; }
        .brand-icon { width: 56px; height: 56px; background: linear-gradient(135deg, #6B3FA0, #5a35a0); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.5rem; color: #fff; }
        .btn-violet { background: linear-gradient(135deg, #6B3FA0, #5a35a0); color: #fff; border: none; border-radius: 10px; padding: 0.7rem; font-weight: 700; }
        .btn-violet:hover { opacity: 0.9; color: #fff; }
    </style>
</head>
<body>
    <div class="carte">
        <div class="brand">
            <img src="{{ asset_v('images/logo_paxevent.png') }}" alt="PaxEvent" style="height:56px;">
        </div>

        @if(session('info'))
            <div class="alert alert-info py-2" style="font-size: 0.82rem;">{{ session('info') }}</div>
        @endif

        <h1 class="text-center mb-2" style="font-size:1.15rem;font-weight:800;color:#1a1d23;">Bienvenue {{ $user->prenom }} !</h1>
        <p class="text-center text-muted mb-4" style="font-size:0.85rem;">
            Votre compte équipe vient d'être créé avec un mot de passe temporaire.
            Pour votre sécurité, définissez maintenant votre propre mot de passe ou rendez-vous sur votre espace équipe pour le modifier ultérieurement.
        </p>

        @if($errors->any())
            <div class="alert alert-danger py-2" style="font-size:0.8rem;">
                @foreach($errors->all() as $erreur)<div><i class="bi bi-exclamation-circle me-1"></i>{{ $erreur }}</div>@endforeach
            </div>
        @endif

        <form action="{{ route('equipe.premiere-connexion.post') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Pseudo</label>
                <input type="text" class="form-control" value="{{ $user->pseudo }}" disabled
                       style="background:#f5f5f7;">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Nouveau mot de passe <span class="text-danger">*</span></label>
                <input type="password" name="mot_de_passe" class="form-control" minlength="8" required autofocus autocomplete="new-password">
                <small class="text-muted" style="font-size:0.72rem;">Minimum 8 caractères, différent du mot de passe temporaire.</small>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Confirmer le mot de passe <span class="text-danger">*</span></label>
                <input type="password" name="mot_de_passe_confirmation" class="form-control" minlength="8" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-violet w-100">
                <i class="bi bi-shield-lock-fill me-1"></i> Définir mon mot de passe
            </button>
        </form>

        <form action="{{ route('superadmin.logout') }}" method="POST" class="text-center mt-3">
            @csrf
            <button type="submit" class="btn btn-link text-muted" style="font-size:0.78rem;text-decoration:none;">Se déconnecter</button>
        </form>
    </div>
</body>
</html>
