<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #542680, #3d1a5c); color: #fff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 1.5rem; }
        .content { padding: 20px; background: #f9fafb; }
        .info { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin: 16px 0; }
        .info p { margin: 8px 0; }
        .label { font-weight: bold; color: #6b7280; }
        .btn { display: inline-block; padding: 12px 24px; background: #522680; color: white; text-decoration: none; border-radius: 6px; margin-top: 16px; }
        .footer { text-align: center; padding: 16px; color: #fff; font-size: 0.875rem; background: linear-gradient(135deg, #542680, #3d1a5c); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue dans l'équipe de vente</h1>
        </div>
        <div class="content">
            <p>Bonjour <strong>{{ $agent->nom }}</strong>,</p>
            <p>Vous avez été designé comme agent de vente pour l'événement <strong>{{ $agent->evenement->titre }}</strong>.</p>

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

            @if($lienApp)
            <div style="background:#f6f2f9;border-radius:12px;padding:1.25rem;margin:1.25rem 0;text-align:center;">
                <p style="margin:0 0 0.75rem;font-size:0.9rem;color:#542680;font-weight:700;">Installez l'application PaxAgent (Android)</p>
                <a href="{{ $lienApp }}" style="display:inline-block;background:#542680;color:#fff;text-decoration:none;padding:0.8rem 2rem;border-radius:12px;font-weight:700;font-size:0.9rem;">
                    Télécharger PaxAgent
                </a>
                <p style="margin:0.75rem 0 0;font-size:0.75rem;color:#777;">
                    Lien valable 7 jours. À l'ouverture du fichier, autorisez l'installation depuis cette source si Android le demande.
                </p>
            </div>
            @endif

            <p><strong>Important :</strong></p>
            <ul>
                <li>Ne partagez jamais votre mot de passe</li>
                <li>Vous pouvez générer des tickets PDF à remettre aux acheteurs</li>
            </ul>
        </div>
        <div class="footer">
            <p>PaxEvent &mdash; Billetterie en ligne 100% Bénin</p>
        </div>
    </div>
</body>
</html>
