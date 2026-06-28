<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de vérification</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #f0eeec; margin: 0; padding: 24px; }
        .container { max-width: 400px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .header { background: linear-gradient(135deg, #542680, #3d1a5c); color: #fff; padding: 28px 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; text-align: center; }
        .body p { font-size: 14px; color: #1d1d1f; margin: 0 0 16px; line-height: 1.6; }
        .code { font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #542680; background: #f5f2f7; padding: 16px; border-radius: 12px; display: inline-block; margin: 8px 0 16px; }
        .expire { font-size: 13px; color: #6c757d; margin-top: 8px; }
        .footer { padding: 18px 32px; text-align: center; border-top: 1px solid #eeedeb; font-size: 11px; color: #8a7a8e; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PaxEvent</h1>
        </div>
        <div class="body">
            <p>Voici votre code de vérification :</p>
            <div class="code">{{ $code }}</div>
            <p>Ce code est valable <strong>10 minutes</strong>.</p>
            <p class="expire">Si vous n'avez pas demandé ce code, ignorez cet email.</p>
        </div>
        <div class="footer">
            <p>PaxEvent &mdash; Billetterie intelligente pour vos événements au Bénin</p>
        </div>
    </div>
</body>
</html>
