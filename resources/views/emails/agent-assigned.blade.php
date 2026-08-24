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
                <img src="{{ asset_v('images/logo_paxevent.png') }}" alt="PaxEvent" style="height:60px;filter:brightness(0)invert(1);">
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

                <div style="background:#ede5f0;border-radius:12px;padding:1.25rem;margin:1.25rem 0;border:1px solid #d9c9e8;">
                    <p style="margin:0 0 0.5rem;font-size:0.85rem;color:#542680;font-weight:600;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#542680" viewBox="0 0 16 16" style="vertical-align:-2px;"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/><path d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415"/></svg> Vos identifiants de connexion
                    </p>
                    <p style="margin:0.25rem 0;font-size:0.85rem;color:#542680;">
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
                    Cet email a été envoyé automatiquement depuis paxevent. Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.<br>
                    PaxEvent — Billetterie en ligne.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
