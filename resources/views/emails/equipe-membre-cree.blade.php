<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Votre compte équipe PaxEvent</title>
</head>
<body style="margin:0;padding:0;font-family:'Segoe UI',sans-serif;background:#f5f5f7;">
    <div style="max-width:600px;margin:0 auto;padding:20px;">
        <div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.06);">
            <div style="background:linear-gradient(135deg,#542680,#3d1a5c);padding:2rem;text-align:center;">
                <img src="{{ asset_v('images/logo_paxevent.png') }}" alt="PaxEvent" style="height:60px;filter:brightness(0)invert(1);">
                <h1 style="color:#fff;font-size:1.3rem;margin:1rem 0 0;">
                    {{ $reinitialisation ? 'Votre mot de passe a été réinitialisé' : 'Bienvenue dans l\'équipe PaxEvent !' }}
                </h1>
            </div>
            <div style="padding:2rem;">
                <p style="font-size:0.95rem;color:#333;">Bonjour <strong>{{ $membre->prenom }} {{ $membre->nom }}</strong>,</p>

                @if($reinitialisation)
                <p style="font-size:0.9rem;color:#555;">
                    Un nouveau mot de passe temporaire vient d'être généré pour votre compte équipe PaxEvent.
                    Veuillez l'utiliser lors de votre prochaine connexion.
                </p>
                @else
                <p style="font-size:0.9rem;color:#555;">
                    Un compte vient d'être créé pour vous afin de rejoindre l'équipe interne de la plateforme PaxEvent.
                </p>
                @endif

                <div style="background:#ede5f0;border-radius:12px;padding:1.25rem;margin:1.25rem 0;border:1px solid #d9c9e8;">
                    <p style="margin:0 0 0.5rem;font-size:0.85rem;color:#542680;font-weight:600;">Vos identifiants de connexion</p>
                    <p style="margin:0.25rem 0;font-size:0.85rem;color:#542680;">
                        <strong>Espace :</strong> Espace Super Admin (superadmin)<br>
                        <strong>Pseudo :</strong> <code style="background:#fff;padding:0.2rem 0.5rem;border-radius:4px;">{{ $membre->pseudo }}</code><br>
                        <strong>Mot de passe temporaire :</strong> <code style="background:#fff;padding:0.2rem 0.5rem;border-radius:4px;font-size:1.05rem;">{{ $motDePasse }}</code>
                    </p>
                </div>

                <div style="background:#fdf8e9;border-radius:12px;padding:1rem 1.25rem;margin:1.25rem 0;border:1px solid #f2e2ad;">
                    <p style="margin:0;font-size:0.82rem;color:#8a6d1a;">
                        ⚠️ Pour votre sécurité, ce mot de passe est temporaire : vous serez invité à définir
                        votre propre mot de passe dès votre première connexion.
                    </p>
                </div>

                <div style="text-align:center;margin:2rem 0;">
                    <a href="{{ route('superadmin.login') }}" style="display:inline-block;background:#542680;color:#fff;text-decoration:none;padding:0.85rem 2.5rem;border-radius:12px;font-weight:700;font-size:0.95rem;">
                        Me connecter
                    </a>
                </div>

                <p style="font-size:0.8rem;color:#999;border-top:1px solid #eee;padding-top:1rem;margin-top:1rem;">
                    Cet email a été envoyé par l'équipe de PaxEvent. Si vous ne reconnaissez pas cette action, <a href="mailto:contact@paxevent.fr" style="color:#542680;text-decoration:underline;">contactez l'administrateur</a>.<br>
                    PaxEvent – Billetterie en ligne.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
