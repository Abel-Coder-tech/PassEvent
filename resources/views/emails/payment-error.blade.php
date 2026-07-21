<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident technique - PaxEvent</title>
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
            background: linear-gradient(90deg, #c0392b, #FED514, #c0392b);
        }
        .header img {
            display: inline-block;
            filter: brightness(0) invert(1);
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
        .alert-box {
            background: #fef5f5;
            border-radius: 12px;
            padding: 16px 20px;
            margin: 0 0 20px;
            border-left: 4px solid #c0392b;
        }
        .alert-box h3 {
            margin: 0 0 4px;
            font-size: 14px;
            color: #c0392b;
        }
        .alert-box p {
            margin: 0;
            font-size: 13px;
            color: #6c757d;
        }
        .info-box {
            background: #f0f4f8;
            border-radius: 12px;
            padding: 16px 20px;
            margin: 0 0 20px;
            border-left: 4px solid #7B3FA0;
        }
        .info-box h3 {
            margin: 0 0 4px;
            font-size: 14px;
            color: #7B3FA0;
        }
        .info-box p {
            margin: 0;
            font-size: 13px;
            color: #6c757d;
        }
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
        .help-text {
            font-size: 13px;
            color: #6c757d;
            margin: 20px 0 0;
            text-align: center;
        }
        .help-text a {
            color: #7B3FA0;
            font-weight: 600;
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
            <p class="greeting">Bonjour <strong>{{ $nomAcheteur }}</strong>,</p>
            <p class="intro">Lors de votre tentative d'achat pour l'événement <strong>{{ $titreEvenement }}</strong>, une perturbation sur le réseau de l'opérateur télécom a interrompu la finalisation de votre commande.</p>

            <div class="alert-box">
                <h3>&#128683; Ne tentez pas un nouveau paiement imm&eacute;diatement</h3>
                <p>Si votre compte Mobile Money a &eacute;t&eacute; d&eacute;bit&eacute;, pas d'inqui&eacute;tude. Notre &eacute;quipe technique v&eacute;rifie actuellement la transaction avec notre partenaire FedaPay.</p>
            </div>

            <div class="info-box">
                <h3>&#128260; Ce que nous allons faire</h3>
                <p>Si des places sont encore disponibles : nous allons forcer la g&eacute;n&eacute;ration et l'envoi de votre e-ticket manuellement dans les plus brefs d&eacute;lais.</p>
                <p style="margin-top: 8px;">Si l'&eacute;v&eacute;nement est complet : la transaction sera rejet&eacute;e et vous serez int&eacute;gralement rembours&eacute; sur votre compte Mobile Money.</p>
            </div>

            @if($transactionId)
            <p class="help-text">
                R&eacute;f&eacute;rence de la transaction : <strong>{{ $transactionId }}</strong>
            </p>
            @endif

            <p class="help-text">
                Pour acc&eacute;l&eacute;rer le traitement, r&eacute;pondez &agrave; cet e-mail en fournissant l'ID de la transaction re&ccedil;u par SMS de votre op&eacute;rateur et le num&eacute;ro de t&eacute;l&eacute;phone utilis&eacute; pour le d&eacute;bit.<br><br>
                Nous nous excusons pour ce d&eacute;sagr&eacute;ment ind&eacute;pendant de notre volont&eacute;.<br><br>
                <strong>Le support technique PaxEvent</strong>
            </p>
        </div>

        <div class="footer">
            <p>PaxEvent &mdash; Billetterie en ligne 100% B&eacute;nin</p>
            <p style="margin-top: 4px;"><a href="mailto:contact@paxevent.com">contact@paxevent.com</a></p>
        </div>
    </div>
</body>
</html>
