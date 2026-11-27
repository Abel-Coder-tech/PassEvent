<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Agent de scan - {{ $agent->evenement->titre }}</title>
</head>
<body style="margin:0;padding:0;font-family:'Segoe UI',sans-serif;background:#f5f5f7;">
    <div style="max-width:600px;margin:0 auto;padding:20px;">
        <div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.06);">
            <div style="background:linear-gradient(135deg,#542680,#3d1a5c);padding:2rem;text-align:center;">
                <img src="{{ asset_v('images/logo_paxevent_blanc.png') }}" alt="PaxEvent" style="height:60px;">
                <h1 style="color:#fff;font-size:1.3rem;margin:1rem 0 0;">Vous êtes chargé du scan de l'événement {{ $agent->evenement->titre }}</h1>
            </div>
            <div style="padding:2rem;">
                <p style="font-size:0.95rem;color:#333;">Bonjour <strong>{{ $agent->nom }}</strong>,</p>
                <p style="font-size:0.9rem;color:#555;">
                    Vous avez été désigné comme agent de scan pour l'événement :
                </p>

                <div style="background:#f8f6f9;border-radius:12px;padding:1.25rem;margin:1.25rem 0;">
                    <h3 style="margin:0 0 0.5rem;color:#211C31;">{{ $agent->evenement->titre }}</h3>
                    <p style="margin:0.25rem 0;font-size:0.85rem;color:#6c757d;">
                        <strong>Date :</strong> {{ $agent->evenement->date_event ? $agent->evenement->date_event->isoFormat('D MMMM YYYY') : 'Non définie' }}<br>
                        <strong>Lieu :</strong> {{ $agent->evenement->lieu ?? 'Non défini' }}
                    </p>
                </div>

                @include('emails.partials.agent-etapes', [
                    'type' => 'scan',
                    'email' => $agent->email,
                    'motDePasse' => $motDePasse,
                    'codeAcces' => $codeAcces,
                    'lienApp' => $lienApp,
                    'lienExpireLe' => $lienExpireLe,
                    'lienWeb' => route('agent.login'),
                ])

            </div>
            <div style="background:linear-gradient(135deg,#542680,#3d1a5c);padding:1.25rem;text-align:center;">
                <p style="margin:0;font-size:0.8rem;color:#fff;">
                    PaxEvent — Billetterie Intelligente 100% Bénin
                </p>
            </div>
        </div>
    </div>
</body>
</html>
