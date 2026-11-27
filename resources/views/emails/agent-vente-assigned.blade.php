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
            <p>Vous avez été désigné comme agent de vente pour l'événement <strong>{{ $agent->evenement->titre }}</strong>.</p>

            <div class="info">
                <p><span class="label">Événement :</span> {{ $agent->evenement->titre }}</p>
                <p><span class="label">Date :</span> {{ $agent->evenement->date_event->format('d/m/Y H:i') }}</p>
                <p><span class="label">Lieu :</span> {{ $agent->evenement->lieu }}</p>
            </div>

            @include('emails.partials.agent-etapes', [
                'type' => 'vente',
                'email' => $agent->email,
                'motDePasse' => $motDePasse,
                'lienApp' => $lienApp,
                'lienExpireLe' => $lienExpireLe,
                'lienWeb' => route('agent-vente.login'),
            ])
        </div>
        <div class="footer">
            <p>PaxEvent &mdash; Billetterie Intelligente 100% Bénin</p>
        </div>
    </div>
</body>
</html>
