<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corrections demandées</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #f0eeec; margin: 0; padding: 24px; }
        .container { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .header { background: linear-gradient(135deg, #b8860b, #8b6914); color: #fff; padding: 28px 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; }
        .body p { font-size: 14px; color: #1d1d1f; margin: 0 0 12px; line-height: 1.6; }
        .reason { background: #fffbe6; border-left: 4px solid #e0a800; padding: 12px 16px; border-radius: 8px; margin: 16px 0; font-size: 14px; color: #8b6914; }
        .btn { display: inline-block; background: #542680; color: #fff !important; padding: 12px 28px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px; margin: 8px 0; }
        .footer { padding: 18px 32px; text-align: center; border-top: 1px solid #eeedeb; font-size: 11px; color: #8a7a8e; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PaxEvent</h1>
        </div>
        <div class="body">
            <p>Bonjour <strong>{{ $user->nom }}</strong>,</p>
            <p>Votre demande de compte organisateur nécessite des corrections avant de pouvoir être validée.</p>
            <div class="reason">{{ $reason }}</div>
            <p>Connectez-vous à votre compte pour apporter les modifications nécessaires, puis soumettez à nouveau votre demande.</p>
            <p style="text-align: center;">
                <a href="{{ route('login') }}" class="btn">Modifier mon profil</a>
            </p>
            <p style="font-size: 13px; color: #6c757d;">L'équipe PaxEvent</p>
        </div>
        <div class="footer">
            <p>PaxEvent &mdash; Billetterie en ligne 100% Bénin</p>
        </div>
    </div>
</body>
</html>
