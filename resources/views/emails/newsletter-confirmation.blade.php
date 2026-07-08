<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation newsletter PaxEvent</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f0eeec;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 480px;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .header {
            background: linear-gradient(135deg, #5a3d5e, #3d2a40);
            color: #fff;
            padding: 28px 32px;
            text-align: center;
        }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; }
        .body p { font-size: 14px; color: #1d1d1f; margin: 0 0 12px; line-height: 1.6; }
        .body ul { font-size: 14px; color: #1d1d1f; margin: 0 0 12px; line-height: 1.6; padding-left: 20px; }
        .btn {
            display: inline-block;
            background: #5a3d5e;
            color: #fff !important;
            padding: 12px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin: 8px 0;
        }
        .footer {
            padding: 18px 32px;
            text-align: center;
            border-top: 1px solid #eeedeb;
            font-size: 11px;
            color: #8a7a8e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PaxEvent</h1>
        </div>
        <div class="body">
            <p>Merci pour votre abonnement !</p>
            <p>Vous êtes désormais abonné à la newsletter <strong>PaxEvent</strong>. Vous recevrez les actualités, les offres exclusives et les événements à ne pas manquer directement dans votre boîte mail.</p>
            <p><strong>Ce que vous allez recevoir :</strong></p>
            <ul>
                <li>Les prochains événements populaires</li>
                <li>Offres exclusives et réductions</li>
                <li>Rappels des événements à venir</li>
                <li>Les nouveautés de la plateforme</li>
            </ul>
            <p>Vous pouvez vous désabonner à tout moment en cliquant sur le lien de désabonnement présent dans chaque email.</p>
            <p style="text-align: center;">
                <a href="{{ route('accueil') }}" class="btn">Découvrir les événements</a>
            </p>
            <p style="font-size:13px; color:#6c757d;">Si vous n'êtes pas à l'origine de cette inscription, vous pouvez ignorer cet email.</p>
        </div>
        <div class="footer">
            <p>PaxEvent — Billetterie en ligne 100% Bénin</p>
        </div>
    </div>
</body>
</html>
