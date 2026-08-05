<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $textes['billet'] ?? 'Billet' }} - {{ $ticket->evenement?->titre ?? 'Evenement' }}</title>
    <style>
        @page { margin: 0; padding: 0; }
        html, body { width: 100%; height: 100%; margin: 0; padding: 0; font-family: 'DejaVu Sans', sans-serif; }
        body {
            margin: 0; padding: 0;
            color: #1d1d1f;
            font-size: 10px;
            line-height: 1.4;
        }
        * { margin: 0; padding: 0; }
        .ticket, .ticket * {
            font-family: 'DejaVu Sans', sans-serif;
        }
        .ticket {
            width: 100%;
            margin: 0;
            background: #fff;
        }

        .header { 
            background: #542680; 
            padding: 14px 20px; 
        }

        .header-title { 
            color: #fff; 
        }

        .header-title .pass { 
            font-size: 18px; 
            font-weight: 400; 
            letter-spacing: 2px; 
            opacity: 0.8; 
        }

        .header-title .event-name { 
            font-size: 14px; 
            font-weight: 800; 
            margin-top: 2px; 
        }

        .body { 
            padding: 14px 20px 10px; 
        }

        .event-meta {
            font-size: 11px; 
            color: #888; 
            border-bottom: 1px solid #eee;
            margin-bottom: 5px;
            padding-bottom: 6px;
        }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info-table td { padding: 5px 0; vertical-align: middle; }
        .info-table .il { font-size: 9px; color: #888; font-weight: 600; width: 100px; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-table .iv { font-size: 12px; font-weight: 700; color: #1d1d1f; }
        .info-table .iv-mono { font-size: 9px; font-weight: 700; color: #1d1d1f; white-space: nowrap; }
        .info-table .iv-green { color: #2E7D4F; }

        .code-pass {
            text-align: center;
            margin: 8px 0 10px;
            padding: 8px;
            background: #f8f6f9;
            border-radius: 8px;
        }
        .code-pass .label { font-size: 8px; color: #888; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 3px; }
        .code-pass .value {
            font-size: 24px; 
            font-weight: 800;
            color: #542680;
            letter-spacing: 2px;
        }

        .qr-block { text-align: center; margin: 10px 0 6px; }
        .qr-box {
            display: inline-block; padding: 8px;
            border: 3px solid #542680; border-radius: 12px;
        }
        .qr-box img { width: 170px; height: 170px; display: block; }
        .qr-label { font-size: 7px; font-weight: 700; color: #542680; letter-spacing: 2px; text-transform: uppercase; margin-top: 6px; }

        .note {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 8px;
            padding: 8px 12px;
            margin: 8px 0 0;
        }
        .note p { font-size: 8px; color: #666; margin: 0; line-height: 1.4; text-align: center; }

        .footer { background: #542680; padding: 14px 20px; width: 100%; }
        .footer-text { color: #fff; font-size: 9px; }
        .footer-logo img { display: block; }

        hr.dashed { border: none; border-top: 1px dashed #ddd; margin: 8px 0; }
    </style>
</head>
<body>

@php $textes = $ticket->evenement?->getTextes() ?? ['billet' => 'Billet']; @endphp
<div class="ticket">

    {{-- HEADER violet (no logo) --}}
    <div class="header" style="text-align: center; justify-content: center;">
        <div class="header-title">
            <div class="pass">{{ $ticket->evenement?->titre ?? 'Événement' }}</div>
            
        </div>
    </div>

    {{-- BODY --}}
    <div class="body">

        <div class="event-meta">
            {{ $ticket->evenement?->date_event?->isoFormat('D MMM YYYY') ?? '---' }}
            @if($ticket->evenement?->date_event)
                &ndash; {{ $ticket->evenement->date_event->format('H\hi') }}
            @endif
            @if($ticket->evenement?->lieu)
                &ndash; {{ $ticket->evenement->lieu }}
            @endif
        </div>

        <table class="info-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="il">{{ $textes['billet'] }}</td>
                <td class="iv">{{ $ticket->nom_tarif }}</td>
            </tr>
            <tr>
                <td class="il">ID transaction</td>
                <td class="iv-mono">{{ $ticket->transaction_id ?? '---' }}</td>
            </tr>
            @if($ticket->montant > 0)
            <tr>
                <td class="il">Montant</td>
                <td class="iv">{{ number_format($ticket->montant, 0, ',', ' ') }} FCFA</td>
            </tr>
            @if($ticket->code_promo_utilise)
            <tr>
                <td class="il">Code promo</td>
                <td class="iv iv-green">{{ $ticket->code_promo_utilise }}</td>
            </tr>
            @endif
            @endif
        </table>

        @if($ticket->montant <= 0)
        <div style="text-align:center;padding:3px 0;color:#542680;font-weight:700;font-size:12px;letter-spacing:1px;text-transform:uppercase;">
            Entrée gratuite
        </div>
        @endif

        @if($ticket->statut_paiement === 'payé')
        <div class="code-pass">
            <div class="label">Code Pass</div>
            <div class="value">{{ $ticket->code_unique }}</div>
        </div>
        @endif

        <hr class="dashed">

        <div class="qr-block">
            <div class="qr-box">
                <img src="{{ $qrCodeDataUri }}" alt="QR Code">
            </div>
            <div class="qr-label">Scannez ce code à l'entrée</div>
        </div>

        <div class="note">
            <p><span style="font-size:12px;">&#9888;&#65039;</span> Ce ticket comporte un QR Code unique au porteur. Gardez-le et ne le partagez jamais.</p>
        </div>

    </div>

    {{-- FOOTER violet --}}
    <table class="footer" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="text-align:left;vertical-align:middle;width:36px;">
                <div class="footer-logo">
                    <img src="{{ $logoDataUri }}" alt="PaxEvent" style="height: 28px; display: block;">
                </div>
            </td>
            <td style="text-align:right;vertical-align:middle;">
                <div class="footer-text">Billetterie simple et rapide pour vos événements</div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>
