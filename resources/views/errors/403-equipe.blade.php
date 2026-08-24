<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès non autorisé - PaxEvent</title>
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
        .carte { width: 100%; max-width: 440px; background: #fff; border-radius: 16px; padding: 2.5rem 2rem; box-shadow: 0 20px 60px rgba(0,0,0,0.3); text-align: center; }
        .icone { width: 72px; height: 72px; background: #ede5f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; font-size: 2rem; color: #542680; }
    </style>
</head>
<body>
    <div class="carte">
        <img src="{{ asset_v('images/logo_paxevent.png') }}" alt="PaxEvent" style="height:48px;" class="mb-3">
        <div class="icone"><i class="bi bi-lock-fill"></i></div>
        <h1 style="font-size:1.15rem;font-weight:800;color:#1a1d23;margin-bottom:0.5rem;">Accès non autorisé</h1>
        <p class="text-muted mb-4" style="font-size:0.85rem;">
            Cette section ne fait pas partie de votre périmètre.<br>
            Si vous pensez qu'il s'agit d'une erreur, contactez l'administrateur.
        </p>
        <a href="{{ route('superadmin.dashboard') }}" class="btn w-100" style="background:linear-gradient(135deg,#6B3FA0,#5a35a0);color:#fff;border:none;border-radius:10px;padding:0.7rem;font-weight:700;">
            <i class="bi bi-grid-1x2-fill me-1"></i> Retour à mon tableau de bord
        </a>
    </div>
</body>
</html>
