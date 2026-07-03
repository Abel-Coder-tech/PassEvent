<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre billet PaxEvent</title>
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
            background: linear-gradient(90deg, #542680, #FED514, #542680);
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
        .event-card {
            background: #f8f6f9;
            border-radius: 14px;
            padding: 18px 20px;
            margin: 0 0 20px;
            border: 1px solid #eee;
        }
        .event-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            padding-bottom: 14px;
            border-bottom: 1px dashed #e0e0e0;
        }
        .event-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, #7B3FA0, #2E7D4F);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .event-icon span {
            color: #fff;
            font-size: 18px;
            font-weight: 700;
        }
        .event-header h2 {
            margin: 0;
            font-size: 16px;
            color: #1d1d1f;
            font-weight: 700;
        }
        .event-card table {
            width: 100%;
            border-collapse: collapse;
        }
        .event-card td {
            padding: 7px 0;
            font-size: 13px;
            border-bottom: 1px solid #f0eeec;
        }
        .event-card tr:last-child td { border-bottom: none; }
        .event-card td:first-child {
            color: #888;
            width: 110px;
        }
        .event-card td:last-child {
            font-weight: 600;
            color: #1d1d1f;
        }
        .event-card .highlight {
            color: #7B3FA0;
            font-weight: 700;
        }
        .event-card .price {
            color: #2E7D4F;
            font-size: 15px;
        }
        .code-block {
            background: #fff;
            border: 1px solid #7B3FA0;
            border-radius: 6px;
            padding: 3px 8px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            letter-spacing: 1px;
            color: #7B3FA0;
            display: inline-block;
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
        .btn:hover {
            background: #6a1b9a;
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
        .help-text .ref {
            display: inline-block;
            margin-top: 6px;
            font-size: 11px;
            color: #aaa;
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
            @php $first = $tickets->first(); $quantite = $tickets->count(); @endphp
            <p class="greeting">Bonjour <strong>{{ $first->nom_acheteur }}</strong>,</p>
            <p class="intro">Votre paiement a &eacute;t&eacute; confirm&eacute;. {{ $quantite > 1 ? "Vos {$quantite} billets sont pr&ecirc;ts !" : 'Votre billet est pr&ecirc;t !' }}</p>

            @foreach($tickets as $ticket)
            <div class="event-card">
                <div class="event-header">
                    <div class="event-icon">
                        <span>&#127915;</span>
                    </div>
                    <h2>{{ $ticket->evenement->titre }}</h2>
                </div>
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td>&#128197; Date et heure</td>
                        <td>{{ $ticket->evenement->date_event->format('d/m/Y \a H:i') }}</td>
                    </tr>
                    <tr>
                        <td>&#128205; Lieu</td>
                        <td>{{ $ticket->evenement->lieu }}</td>
                    </tr>
                    <tr>
                        <td>&#127915; Billet</td>
                        <td>{{ ucfirst($ticket->type === 'normal' ? 'Standard' : 'VIP') }} &middot; {{ ucfirst($ticket->categorie) }}</td>
                    </tr>
                    <tr>
                        <td>&#128196; Code</td>
                        <td><span class="code-block">{{ $ticket->code_unique }}</span></td>
                    </tr>
                    <tr>
                        <td>&#128178; Montant pay&eacute;</td>
                        <td class="price">{{ number_format($ticket->montant, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </table>
            </div>
            @endforeach

            <div class="info-box">
                <h3>&#128230; {{ $quantite > 1 ? "{$quantite} billets PDF joints" : 'Billet PDF joint' }}</h3>
                <p>Imprimez-les ou pr&eacute;sentez-les sur votre t&eacute;l&eacute;phone le jour de l&rsquo;&eacute;v&eacute;nement. Le QR code sera scann&eacute; pour valider votre acc&egrave;s.</p>
            </div>

            <div class="btn-wrap">
                <a href="{{ route('tickets.telecharger', $first->id) }}" class="btn">T&eacute;l&eacute;charger le billet</a>
            </div>

            <p class="help-text">
                En cas de probl&egrave;me, contactez <a href="mailto:contact@paxevent.com">contact@paxevent.com</a><br>
                <span class="ref">R&eacute;f&eacute;rence : {{ $first->transaction_id }}</span>
            </p>
        </div>

        <div class="footer">
            <p>PaxEvent &mdash; Billetterie intelligente pour vos &eacute;v&eacute;nements au B&eacute;nin</p>
            <p style="margin-top: 4px;"><a href="{{ url('/aide') }}">Centre d'aide</a></p>
        </div>
    </div>
</body>
</html>
