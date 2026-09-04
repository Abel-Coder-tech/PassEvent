<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modification de tarif refusée</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #f0eeec; margin: 0; padding: 24px; }
        .container { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .header { background: linear-gradient(135deg, #c0392b, #8f241a); color: #fff; padding: 28px 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; }
        .body p { font-size: 14px; color: #1d1d1f; margin: 0 0 12px; line-height: 1.6; }
        .infos { background: #fdf3f1; border-left: 4px solid #c0392b; padding: 14px 16px; border-radius: 8px; margin: 16px 0; }
        .infos div { font-size: 13px; color: #444; padding: 3px 0; }
        .infos strong { color: #8f241a; }
        .motif { background: #fff; border: 1px solid #f0d8d4; padding: 12px 14px; border-radius: 8px; margin-top: 8px; font-size: 13px; color: #6c5b3a; }
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
            <p>Bonjour <strong>{{ $demande->user?->nom ?? 'organisateur' }}</strong>,</p>
            <p>Votre demande de modification du prix d'un tarif a été <strong>refusée</strong> par PaxEvent.</p>
            <div class="infos">
                <div><strong>Événement :</strong> {{ $demande->evenement->titre }}</div>
                <div><strong>Tarif :</strong> {{ $demande->tarif->nom }}</div>
                <div><strong>Prix demandé :</strong> {{ number_format($demande->nouveau_prix, 0, ',', ' ') }} F</div>
            </div>
            <p><strong>Motif du refus :</strong></p>
            <div class="motif">{{ $motif }}</div>
            <p style="font-size: 13px; color: #6c757d;">Vous pouvez soumettre une nouvelle demande avec un prix révisé si besoin.</p>
            <p style="text-align: center;">
                <a href="{{ route('dashboard') }}" class="btn">Voir mon tableau de bord</a>
            </p>
            <p style="font-size: 13px; color: #6c757d;">L'équipe PaxEvent</p>
        </div>
        <div class="footer">
            <p>PaxEvent &mdash; Billetterie en ligne 100% Bénin</p>
        </div>
    </div>
</body>
</html>
