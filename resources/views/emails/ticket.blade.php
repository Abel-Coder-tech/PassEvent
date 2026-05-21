<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre billet PassEvent</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1d1d1f;
            margin: 0;
            padding: 0;
            background-color: #f0eeec;
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
            background: linear-gradient(135deg, #5a3d5e, #3d2a40);
            color: white;
            padding: 32px 36px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .header .badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 10px;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 28px 36px 20px;
        }
        .greeting {
            font-size: 15px;
            margin: 0 0 6px;
        }
        .greeting strong { color: #3d2a40; }
        .intro {
            font-size: 13px;
            color: #6c757d;
            margin: 0 0 20px;
        }
        .event-card {
            background: #f8f7f5;
            border-radius: 12px;
            padding: 18px 20px;
            margin: 0 0 20px;
        }
        .event-card h2 {
            margin: 0 0 12px;
            font-size: 17px;
            color: #3d2a40;
        }
        .event-card table {
            width: 100%;
            border-collapse: collapse;
        }
        .event-card td {
            padding: 6px 0;
            font-size: 13px;
            border-bottom: 1px solid #eeedeb;
        }
        .event-card tr:last-child td { border-bottom: none; }
        .event-card td:first-child {
            color: #6c757d;
            width: 110px;
        }
        .event-card td:last-child {
            font-weight: 600;
            color: #1d1d1f;
        }
        .event-card .price { color: #12976e; }
        .info-box {
            background: #f0f4f8;
            border-radius: 12px;
            padding: 16px 20px;
            margin: 0 0 20px;
            border-left: 4px solid #5a3d5e;
        }
        .info-box h3 {
            margin: 0 0 4px;
            font-size: 14px;
            color: #3d2a40;
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
            background: #5a3d5e;
            color: #ffffff !important;
            padding: 13px 32px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .help-text {
            font-size: 13px;
            color: #6c757d;
            margin: 20px 0 0;
            text-align: center;
        }
        .help-text a {
            color: #5a3d5e;
        }
        .footer {
            background: #f8f7f5;
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
            color: #5a3d5e;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PassEvent</h1>
            <span class="badge">&bull; UPAO &bull;</span>
        </div>

        <div class="content">
            <p class="greeting">Bonjour <strong>{{ $ticket->nom_acheteur }}</strong>,</p>
            <p class="intro">Votre paiement a &eacute;t&eacute; confirm&eacute;. Votre billet est pr&ecirc;t !</p>

            <div class="event-card">
                <h2>{{ $ticket->evenement->titre }}</h2>
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td>Date et heure</td>
                        <td>{{ $ticket->evenement->date_event->format('d/m/Y \a H:i') }}</td>
                    </tr>
                    <tr>
                        <td>Lieu</td>
                        <td>{{ $ticket->evenement->lieu }}</td>
                    </tr>
                    <tr>
                        <td>Billet</td>
                        <td>{{ ucfirst($ticket->type) }} &middot; {{ $ticket->categorie }}</td>
                    </tr>
                    <tr>
                        <td>Code</td>
                        <td style="font-family: monospace;">{{ $ticket->code_unique }}</td>
                    </tr>
                    <tr>
                        <td>Montant pay&eacute;</td>
                        <td class="price">{{ number_format($ticket->montant, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </table>
            </div>

            <div class="info-box">
                <h3>Billet PDF joint</h3>
                <p>Imprimez-le ou pr&eacute;sentez-le sur votre t&eacute;l&eacute;phone le jour de l&rsquo;&eacute;v&eacute;nement. Le QR code sera scann&eacute; pour valider votre acc&egrave;s.</p>
            </div>

            <div class="btn-wrap">
                <a href="{{ route('tickets.telecharger', $ticket->id) }}" class="btn">T&eacute;l&eacute;charger mon billet</a>
            </div>

            <p class="help-text">
                En cas de probl&egrave;me, contactez <a href="mailto:passevent2026@gmail.com">passevent2026@gmail.com</a><br>
                R&eacute;f&eacute;rence : <strong>{{ $ticket->transaction_id }}</strong>
            </p>
        </div>

        <div class="footer">
            <p>PassEvent &mdash; Billetterie intelligente pour vos &eacute;v&eacute;nements au B&eacute;nin</p>
            <p style="margin-top: 4px;"><a href="{{ url('/aide') }}">Centre d'aide</a></p>
        </div>
    </div>
</body>
</html>