<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre ticket PaxEvent</title>
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
            background: linear-gradient(135deg, #542680, #3d1a5c);
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
            color: #542680;
            font-size: 15px;
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
            <img src="{{ asset_v('images/logo-ticket.png') }}" alt="PaxEvent" height="60" style="display:inline-block;filter:brightness(0) invert(1);-webkit-filter:brightness(0) invert(1);">
        </div>

        <div class="content">
            @php $first = $tickets->first(); $quantite = $tickets->count(); @endphp
            <p class="greeting">Bonjour <strong>{{ $first->nom_acheteur }}</strong>,</p>
            @php $textes = $first->evenement->getTextes(); @endphp
            <p class="intro">Votre paiement a été confirmé. {{ $quantite > 1 ? "Vos {$quantite} tickets sont prêts !" : 'Votre ticket est prêt !' }}</p>

            @foreach($tickets as $ticket)
            <div class="event-card">
                <div class="event-header">
                    <h2>{{ $ticket->evenement->titre }}</h2>
                </div>
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
                        <td>{{ $textes['billet'] }}</td>
                        <td>{{ $ticket->nom_tarif }}</td>
                    </tr>
                    <tr>
                        <td><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#888" viewBox="0 0 16 16" style="vertical-align:-2px;"><path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73z"/></svg> Montant pay&eacute;</td>
                        <td class="price">{{ number_format($ticket->montant, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </table>
            </div>
            @endforeach

            <div class="info-box">
                <h3><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#7B3FA0" viewBox="0 0 16 16" style="vertical-align:-2px;"><path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 5.961 14.154 3.5zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464z"/></svg> {{ $quantite > 1 ? "{$quantite} " . mb_strtolower($textes['billet_pluriel']) . ' PDF joints' : $textes['billet'] . ' PDF joint' }}</h3>
                <p>Imprimez-les ou pr&eacute;sentez-les sur votre t&eacute;l&eacute;phone le jour de l&rsquo;&eacute;v&eacute;nement. Le QR code sera scann&eacute; pour valider votre acc&egrave;s.</p>
            </div>

            @if($quantite > 1)
            <div class="info-box" style="background:#fff;border:1px solid #e3d5ec;">
                <h3 style="color:#542680;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#7B3FA0" viewBox="0 0 16 16" style="vertical-align:-2px;"><path d="M4.5 1h7A1.5 1.5 0 0 1 13 2.5v11a1.5 1.5 0 0 1-1.5 1.5h-7A1.5 1.5 0 0 1 3 13.5v-11A1.5 1.5 0 0 1 4.5 1zM3 6v1h4V6H3zm0 3v1h4V9H3zm5-3v1h3V6H8zm0 3v1h3V9H8z"/></svg> Partager chaque billet</h3>
                <p style="font-size:13px;color:#5a5a5a;margin-bottom:10px;">Transf&eacute;rez &agrave; chaque personne le lien de <strong>son</strong> billet ci-dessous. Chaque lien ne t&eacute;l&eacute;charge que le billet correspondant.</p>
                @foreach($tickets as $gt)
                    <p style="margin:6px 0;font-size:13px;line-height:1.5;">
                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} — {{ $textes['billet'] }} <strong>{{ $gt->code_unique }}</strong><br>
                        <a href="{{ \Illuminate\Support\Facades\URL::signedRoute('tickets.telecharger', ['ticket' => $gt->id]) }}" style="color:#542680;font-weight:600;word-break:break-all;">{{ \Illuminate\Support\Facades\URL::signedRoute('tickets.telecharger', ['ticket' => $gt->id]) }}</a>
                    </p>
                @endforeach
            </div>
            @endif

            <div class="btn-wrap">
                @if($quantite > 1)
                    <a href="{{ \Illuminate\Support\Facades\URL::signedRoute('tickets.telecharger-tous', ['ticket' => $first->id]) }}" class="btn">Télécharger tous mes tickets (ZIP)</a>
                    <p style="text-align:center;margin:6px 0 0;"><a href="{{ \Illuminate\Support\Facades\URL::signedRoute('tickets.telecharger', ['ticket' => $first->id]) }}" style="font-size:12px;color:#7B3FA0;">Télécharger un seul ticket</a></p>
                @else
                    <a href="{{ \Illuminate\Support\Facades\URL::signedRoute('tickets.telecharger', ['ticket' => $first->id]) }}" class="btn">Télécharger ticket</a>
                @endif
            </div>

            <p class="help-text">
                En cas de probl&egrave;me, contactez <a href="mailto:contact@paxevent.com">contact@paxevent.com</a><br>
                <span class="ref">R&eacute;f&eacute;rence : {{ $first->transaction_id }}</span>
            </p>
        </div>

        <div class="footer">
            <p>PaxEvent &mdash; Billetterie en ligne 100% B&eacute;nin</p>
            <p style="margin-top: 4px;"><a href="{{ url('/aide') }}">Centre d'aide</a></p>
        </div>
    </div>
</body>
</html>
