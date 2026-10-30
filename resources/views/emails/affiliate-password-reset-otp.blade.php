<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe affilié</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #f0eeec; margin: 0; padding: 24px; }
        .container { max-width: 440px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; }
        .header, .footer { background: linear-gradient(135deg, #542680, #3d1a5c); color: #fff; text-align: center; }
        .header { padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; text-align: center; }
        .body p { color: #1d1d1f; font-size: 14px; line-height: 1.6; }
        .code { display: inline-block; margin: 8px 0 16px; padding: 16px; border-radius: 12px; background: #f5f2f7; color: #542680; font-size: 36px; font-weight: 800; letter-spacing: 8px; }
        .footer { padding: 18px 32px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>PaxEvent</h1></div>
        <div class="body">
            <p>Utilisez ce code pour réinitialiser le mot de passe de votre compte affilié :</p>
            <div class="code">{{ $code }}</div>
            <p>Ce code est valable <strong>10 minutes</strong> et ne peut être utilisé qu'une seule fois.</p>
            <p>Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</p>
        </div>
        <div class="footer">PaxEvent &mdash; Billetterie en ligne 100% Bénin</div>
    </div>
</body>
</html>
