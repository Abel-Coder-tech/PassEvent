<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de retrait — PaxEvent</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1d1d1f;
            margin: 0;
            padding: 0;
            background-color: #f5f3f0;
        }
        .container {
            max-width: 560px;
            margin: 24px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }
        .header {
            background: linear-gradient(135deg, #542680, #3d1a5c);
            color: white;
            padding: 28px 36px;
            text-align: center;
            position: relative;
        }
        .header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f39c12, #FED514, #f39c12);
        }
        .content {
            padding: 28px 36px 20px;
        }
        .greeting {
            font-size: 15px;
            margin: 0 0 4px;
        }
        .greeting strong { color: #542680; }
        .intro {
            font-size: 13px;
            color: #6c757d;
            margin: 0 0 24px;
        }
        .detail-box {
            background: #f8f6f9;
            border-radius: 12px;
            padding: 20px;
            margin: 0 0 20px;
            border-left: 4px solid #7B3FA0;
        }
        .detail-box h3 {
            margin: 0 0 12px;
            font-size: 14px;
            color: #7B3FA0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #ede5f0;
            font-size: 13px;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #6c757d; }
        .detail-value { font-weight: 600; color: #211C31; }
        .btn-wrap {
            text-align: center;
            margin: 22px 0;
        }
        .btn {
            display: inline-block;
            background: #7B3FA0;
            color: #ffffff !important;
            padding: 14px 36px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 14px rgba(123,63,160,0.25);
        }
        .footer {
            background: #f8f6f9;
            padding: 18px 36px;
            text-align: center;
            border-top: 1px solid #eeedeb;
        }
        .footer p {
            margin: 0;
            font-size: 11px;
            color: #8a7a8e;
        }
        .footer a {
            color: #7B3FA0;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="text-align:center;">
            <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" height="60" style="display:inline-block;filter:brightness(0) invert(1);-webkit-filter:brightness(0) invert(1);">
        </div>

        <div class="content">
            <p class="greeting">Bonjour <strong>PaxEvent</strong>,</p>
            <p class="intro">Un organisateur vient de soumettre une nouvelle demande de retrait. Voici les détails :</p>

            <div class="detail-box">
                <h3>Demande de retrait</h3>
                <div class="detail-row">
                    <span class="detail-label">Organisateur</span>
                    <span class="detail-value">{{ $nomOrganisateur }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">{{ $emailOrganisateur }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Réseau</span>
                    <span class="detail-value">{{ $reseau }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Montant</span>
                    <span class="detail-value">{{ number_format($montant, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Bénéficiaire</span>
                    <span class="detail-value">{{ $nomBeneficiaire }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Numéro</span>
                    <span class="detail-value">{{ $mobile }}</span>
                </div>
            </div>

            <div class="btn-wrap">
                <a href="{{ route('superadmin.retraits') }}" class="btn">Traiter la demande</a>
            </div>

            <p style="font-size:12px; color:#8a7a8e; text-align:center; margin:0;">
                Rappel : Les retraits sont effectués sous un délai minimum de 72 heures.
            </p>
        </div>

        <div class="footer">
            <p>PaxEvent &mdash; Billetterie en ligne 100% Bénin</p>
            <p style="margin-top: 4px;"><a href="mailto:contact@paxevent.com">contact@paxevent.com</a></p>
        </div>
    </div>
</body>
</html>
