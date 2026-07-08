<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle demande organisateur</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #f0eeec; margin: 0; padding: 24px; }
        .container { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .header { background: linear-gradient(135deg, #542680, #3d1a5c); color: #fff; padding: 28px 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; }
        .body p { font-size: 14px; color: #1d1d1f; margin: 0 0 8px; line-height: 1.6; }
        .info { background: #f8f6f9; border-radius: 10px; padding: 16px; margin: 16px 0; }
        .info p { margin: 4px 0; font-size: 13px; }
        .info strong { color: #542680; }
        .btn { display: inline-block; background: #542680; color: #fff !important; padding: 12px 28px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px; margin: 8px 0; }
        .footer { padding: 18px 32px; text-align: center; border-top: 1px solid #eeedeb; font-size: 11px; color: #8a7a8e; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PaxEvent — Administration</h1>
        </div>
        <div class="body">
            <p>Une nouvelle demande de compte organisateur a été soumise.</p>
            <div class="info">
                <p><strong>Type :</strong> {{ $user->type === 'universitaire' ? 'Universitaire' : ($user->type === 'particulier' ? 'Particulier' : 'Organisation') }}</p>
                <p><strong>Nom :</strong> {{ $user->nom }}</p>
                @if($user->organisation)
                    <p><strong>{{ $user->type === 'universitaire' ? 'Université' : 'Organisation' }} :</strong> {{ $user->organisation }}</p>
                @endif
                <p><strong>Email :</strong> {{ $user->email }}</p>
                <p><strong>Téléphone :</strong> {{ $user->telephone }}</p>
            </div>
            <p style="text-align: center;">
                <a href="{{ route('superadmin.organisateurs') }}" class="btn">Voir dans le panel</a>
            </p>
        </div>
        <div class="footer">
            <p>PaxEvent &mdash; Billetterie en ligne 100% Bénin</p>
        </div>
    </div>
</body>
</html>
