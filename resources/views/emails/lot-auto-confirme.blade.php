<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vos QR codes sont prêts — PaxEvent</title>
    <style>
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;line-height:1.6;color:#1d1d1f;margin:0;padding:0;background:#f5f3f0}
        .container{max-width:560px;margin:24px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.06)}
        .header{background:linear-gradient(135deg,#542680,#3d1a5c);color:#fff;padding:28px 36px;text-align:center;position:relative}
        .header::after{content:'';position:absolute;bottom:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#f39c12,#FED514,#f39c12)}
        .content{padding:28px 36px 20px}
        .greeting{font-size:15px;margin:0 0 4px}
        .greeting strong{color:#542680}
        .intro{font-size:13px;color:#6c757d;margin:0 0 20px}
        .info-box{background:#f0f4f8;border-radius:12px;padding:16px 20px;margin:0 0 20px;border-left:4px solid #7B3FA0}
        .info-box h3{margin:0 0 8px;font-size:14px;color:#7B3FA0}
        .info-box p{margin:0;font-size:13px;color:#6c757d}
        .detail-box{background:#f8f6f9;border-radius:12px;padding:16px 20px;margin:0 0 20px}
        .detail-row{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #ede5f0;font-size:13px}
        .detail-row:last-child{border-bottom:none}
        .detail-label{color:#6c757d}
        .detail-value{font-weight:600;color:#211C31}
        .btn{display:block;text-align:center;background:#542680;color:#fff;text-decoration:none;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:700;margin:0 0 20px}
        .footer{background:#f8f6f9;padding:18px 36px;text-align:center;border-top:1px solid #eeedeb}
        .footer p{margin:0;font-size:11px;color:#8a7a8e}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset_v('images/logo_paxevent.png') }}" alt="PaxEvent" height="60" style="filter:brightness(0) invert(1);-webkit-filter:brightness(0) invert(1);">
        </div>
        <div class="content">
            <p class="greeting">Bonjour,</p>
            <p class="intro">Votre paiement a été confirmé. Vos planches de QR codes sont disponibles dans votre espace organisateur.</p>

            <div class="detail-box">
                <div class="detail-row">
                    <span class="detail-label">Événement</span>
                    <span class="detail-value">{{ $premier?->evenement?->titre ?? '---' }}</span>
                </div>
                @foreach($lots as $lot)
                <div class="detail-row">
                    <span class="detail-label">{{ $lot->tarif?->nom ?? 'Pass' }}</span>
                    <span class="detail-value">{{ $lot->quantite }} ticket(s)</span>
                </div>
                @endforeach
                <div class="detail-row">
                    <span class="detail-label">Commission payée</span>
                    <span class="detail-value">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>

            <a href="{{ route('admin.lots-physiques.index') }}" class="btn">Télécharger mes planches de QR codes</a>

            <div class="info-box">
                <h3>À savoir</h3>
                <p>Chaque billet papier vendu au guichet doit correspondre à un QR code de la planche. Les tickets sont scannables à l'entrée comme les tickets en ligne (3 téléchargements maximum par planche).</p>
            </div>

            <p style="font-size:12px;color:#8a7a8e;text-align:center;margin:0;">Merci pour votre confiance !</p>
        </div>
        <div class="footer">
            <p>PaxEvent — Billetterie en ligne 100% Bénin</p>
            <p style="margin-top:4px;"><a href="mailto:contact@paxevent.com" style="color:#7B3FA0;text-decoration:none;">contact@paxevent.com</a></p>
        </div>
    </div>
</body>
</html>
