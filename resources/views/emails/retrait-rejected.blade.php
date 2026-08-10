<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retrait rejeté — PaxEvent</title>
    <style>
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;line-height:1.6;color:#1d1d1f;margin:0;padding:0;background:#f5f3f0}
        .container{max-width:560px;margin:24px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.06)}
        .header{background:linear-gradient(135deg,#542680,#3d1a5c);color:#fff;padding:28px 36px;text-align:center;position:relative}
        .header::after{content:'';position:absolute;bottom:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#c0392b,#FED514,#c0392b)}
        .content{padding:28px 36px 20px}
        .greeting{font-size:15px;margin:0 0 4px}
        .greeting strong{color:#542680}
        .intro{font-size:13px;color:#6c757d;margin:0 0 20px}
        .alert-box{background:#fef5f5;border-radius:12px;padding:16px 20px;margin:0 0 20px;border-left:4px solid #c0392b}
        .alert-box h3{margin:0 0 8px;font-size:14px;color:#c0392b}
        .alert-box p{margin:0;font-size:13px;color:#6c757d}
        .detail-box{background:#f8f6f9;border-radius:12px;padding:16px 20px;margin:0 0 20px}
        .detail-row{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #ede5f0;font-size:13px}
        .detail-row:last-child{border-bottom:none}
        .detail-label{color:#6c757d}
        .detail-value{font-weight:600;color:#211C31}
        .footer{background:#f8f6f9;padding:18px 36px;text-align:center;border-top:1px solid #eeedeb}
        .footer p{margin:0;font-size:11px;color:#8a7a8e}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset_v('images/logo_paxevent.png') }}" alt="PaxEvent" height="60" style="filter:brightness(0) invert(1);">
        </div>
        <div class="content">
            <p class="greeting">Bonjour <strong>{{ $nomOrganisateur }}</strong>,</p>
            <p class="intro">Votre demande de retrait a été examinée par l'équipe PaxEvent. Malheureusement, elle ne peut pas être traitée pour le moment.</p>

            <div class="detail-box">
                <div class="detail-row">
                    <span class="detail-label">Montant</span>
                    <span class="detail-value">{{ number_format($montant, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Réseau</span>
                    <span class="detail-value">{{ $reseau }}</span>
                </div>
            </div>

            <div class="alert-box">
                <h3>Raison(s) du rejet</h3>
                <p>{!! nl2br(e($raisons)) !!}</p>
            </div>

            <p style="font-size:13px;color:#6c757d;text-align:center;margin:0 0 8px;">
                Vous pouvez corriger les éléments signalés et soumettre une nouvelle demande depuis votre espace organisateur.
            </p>
        </div>
        <div class="footer">
            <p>PaxEvent — Billetterie en ligne 100% Bénin</p>
            <p style="margin-top:4px;"><a href="mailto:contact@paxevent.com" style="color:#7B3FA0;text-decoration:none;">contact@paxevent.com</a></p>
        </div>
    </div>
</body>
</html>
