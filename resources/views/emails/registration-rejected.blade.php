<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande non retenue</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #f0eeec; margin: 0; padding: 24px; }
        .container { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .header { background: linear-gradient(135deg, #991b1b, #7f1d1d); color: #fff; padding: 28px 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; }
        .body p { font-size: 14px; color: #1d1d1f; margin: 0 0 12px; line-height: 1.6; }
        .reason { background: #fef2f2; border-left: 4px solid #e74c3c; padding: 12px 16px; border-radius: 8px; margin: 16px 0; font-size: 14px; color: #991b1b; }
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
            <p>Nous avons examiné votre demande de création de compte organisateur.</p>
            <p>Malheureusement, votre demande n'a pas été retenue pour la raison suivante :</p>
            <div class="reason">{{ $reason }}</div>
            <p>Si vous souhaitez soumettre une nouvelle demande ou obtenir plus d'informations, contactez notre support à <a href="mailto:contact@paxevent.com">contact@paxevent.com</a>.</p>
            <p style="font-size: 13px; color: #6c757d;">L'équipe PaxEvent</p>
        </div>
        <div class="footer">
            <p>PaxEvent &mdash; Billetterie en ligne 100% Bénin</p>
        </div>
    </div>
</body>
</html>
