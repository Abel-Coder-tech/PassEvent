<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande en cours</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #f0eeec; margin: 0; padding: 24px; }
        .container { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .header { background: linear-gradient(135deg, #542680, #3d1a5c); color: #fff; padding: 28px 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; }
        .body p { font-size: 14px; color: #1d1d1f; margin: 0 0 12px; line-height: 1.6; }
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
            <p>Nous avons bien reçu votre demande de création de compte organisateur sur PaxEvent.</p>
            <p>Votre compte est en cours de validation par notre équipe. Vous recevrez un email de confirmation sous <strong>24 heures</strong>.</p>
            <p>En attendant, si vous avez la moindre question, n'hésitez pas à contacter notre support à <a href="mailto:contact@paxevent.com">contact@paxevent.com</a>.</p>
            <p style="margin-top: 20px; font-size: 13px; color: #6c757d;">L'équipe PaxEvent</p>
        </div>
        <div class="footer">
            <p>PaxEvent &mdash; Billetterie intelligente pour vos événements au Bénin</p>
        </div>
    </div>
</body>
</html>
