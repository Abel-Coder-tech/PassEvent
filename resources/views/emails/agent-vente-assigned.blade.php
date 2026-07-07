<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #7c3aed; color: #fff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 1.5rem; }
        .content { padding: 20px; background: #f9fafb; }
        .info { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin: 16px 0; }
        .info p { margin: 8px 0; }
        .label { font-weight: bold; color: #6b7280; }
        .btn { display: inline-block; padding: 12px 24px; background: #7c3aed; color: #fff; text-decoration: none; border-radius: 6px; margin-top: 16px; }
        .footer { text-align: center; padding: 16px; color: #9ca3af; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue dans l'équipe de vente</h1>
        </div>
        <div class="content">
            <p>Bonjour <strong>{{ $agent->nom }}</strong>,</p>
            <p>Vous avez été ajouté comme agent de vente pour l'événement <strong>{{ $agent->evenement->titre }}</strong>.</p>

            <div class="info">
                <p><span class="label">Événement :</span> {{ $agent->evenement->titre }}</p>
                <p><span class="label">Date :</span> {{ $agent->evenement->date_event->format('d/m/Y H:i') }}</p>
                <p><span class="label">Lieu :</span> {{ $agent->evenement->lieu }}</p>
                <p><span class="label">Email de connexion :</span> {{ $agent->email }}</p>
                <p><span class="label">Mot de passe :</span> {{ $motDePasse }}</p>
            </div>

            <p style="text-align: center;">
                <a href="{{ route('agent-vente.login') }}" class="btn">Accéder à mon espace de vente</a>
            </p>

            <p><strong>Important :</strong></p>
            <ul>
                <li>Ne partagez jamais votre mot de passe</li>
                <li>Vous pouvez générer des tickets PDF à remettre aux acheteurs</li>
            </ul>
        </div>
        <div class="footer">
            <p>PassEvent &mdash; Billetterie en ligne 100% Bénin</p>
        </div>
    </div>
</body>
</html>
