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
                <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" style="height:60px;filter:brightness(0)invert(1);">
                <h1 style="color:#fff;font-size:1.3rem;margin:1rem 0 0;">Vous êtes chargé du scan</h1>
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

                <div style="background:#e8f4e8;border-radius:12px;padding:1.25rem;margin:1.25rem 0;border:1px solid #c3e6c3;">
                    <p style="margin:0 0 0.5rem;font-size:0.85rem;color:#155724;font-weight:600;">
                        <i style="font-style:normal;">&#128274;</i> Vos identifiants de connexion
                    </p>
                    <p style="margin:0.25rem 0;font-size:0.85rem;color:#155724;">
                        <strong>Email :</strong> {{ $agent->email }}<br>
                        <strong>Mot de passe :</strong> <code style="background:#fff;padding:0.2rem 0.5rem;border-radius:4px;">{{ $motDePasse }}</code><br>
                        <strong>Code d'accès scan :</strong> <code style="background:#fff;padding:0.2rem 0.5rem;border-radius:4px;font-size:1.1rem;letter-spacing:2px;">{{ $agent->code_acces }}</code>
                    </p>
                </div>

                <p style="font-size:0.85rem;color:#555;">
                    <strong>Comment procéder ?</strong>
                </p>
                <ol style="font-size:0.85rem;color:#555;padding-left:1.25rem;">
                    <li>Connectez-vous sur votre espace agent via le lien ci-dessous</li>
                    <li>Sur votre tableau de bord, cliquez sur "Accéder au scan"</li>
                    <li>Saisissez votre code d'accès à 6 chiffres pour débloquer le scan</li>
                    <li>Scannez les QR codes des tickets des participants</li>
                </ol>

                <div style="text-align:center;margin:2rem 0;">
                    <a href="{{ route('agent.login') }}" style="display:inline-block;background:#542680;color:#fff;text-decoration:none;padding:0.85rem 2.5rem;border-radius:12px;font-weight:700;font-size:0.95rem;">
                        Accéder à mon espace
                    </a>
                </div>

                <p style="font-size:0.8rem;color:#999;border-top:1px solid #eee;padding-top:1rem;margin-top:1rem;">
                    Cet email a été envoyé automatiquement. Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.<br>
                    PaxEvent — Billetterie en ligne.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
