<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Des places sont disponibles</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #f0eeec; margin: 0; padding: 24px; }
        .container { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .header { background: linear-gradient(135deg, #542680, #3d1a5c); color: #fff; padding: 28px 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; }
        .body p { font-size: 14px; color: #1d1d1f; margin: 0 0 12px; line-height: 1.6; }
        .infos { background: #f6f2fa; border-left: 4px solid #542680; padding: 14px 16px; border-radius: 8px; margin: 16px 0; }
        .infos div { font-size: 13px; color: #444; padding: 3px 0; }
        .infos strong { color: #3d1a5c; }
        .btn { display: inline-block; background: #542680; color: #fff !important; padding: 12px 28px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px; margin: 8px 0; }
        .footer { padding: 18px 32px; text-align: center; font-size: 11px; color: #fff; background: linear-gradient(135deg, #542680, #3d1a5c); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>PaxEvent</h1></div>
        <div class="body">
            <p>Bonjour {{ $demande->nom_acheteur ?: 'cher participant' }},</p>
            <p>Bonne nouvelle : de nouvelles places sont disponibles pour le billet que vous attendiez.</p>
            <div class="infos">
                <div><strong>Événement :</strong> {{ $evenement->titre }}</div>
                <div><strong>Billet :</strong> {{ $tarif->nom }}</div>
                <div><strong>Places demandées :</strong> {{ $demande->quantite }}</div>
            </div>
            <p>Les places partent dans l'ordre des achats : premier arrivé, premier servi.</p>
            <p style="text-align:center;"><a href="{{ $lienEvenement }}" class="btn">Acheter mes billets</a></p>
        </div>
        <div class="footer">PaxEvent — Billetterie en ligne</div>
    </div>
</body>
</html>
