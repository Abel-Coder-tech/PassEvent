<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $textes['billet'] ?? 'Billet' }} - {{ $ticket->evenement?->titre ?? 'Evenement' }}</title>
    <style>
        @page { margin: 0; padding: 0; }
        html, body { width: 100%; height: 100%; margin: 0; padding: 0; font-family: 'DejaVu Sans', sans-serif; }
        body {
            margin: 5px; padding: 0;
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
            page-break-inside: avoid;
        }

        .header { 
            color: #542680; 
            padding: 6px 20px 4px; 
        }

        .header-title { 
            color: #542680; 
            text-transform: uppercase;
        }

        .header-title .pass { 
            font-size: 20px; 
            font-weight: 1000; 
            letter-spacing: 1.5px; 
            opacity: 0.8; 
            text-transform: uppercase;
        }

        .header-title .event-name { 
            font-size: 14px; 
            font-weight: 800; 
            margin-top: 2px; 
        }

        .body { 
            padding: 10px 20px 58px; 
        }

        .event-meta {
            font-size: 9px; 
            color: #888; 
            border-bottom: 1px solid #eee;
            margin-bottom: 3px;
            padding-bottom: 3px;
            text-align: center;
        }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .info-table td { padding: 3px 0; vertical-align: middle; }
        .row-solid td { border-bottom: 1px solid #eee; }
        .row-dash td { border-bottom: 1px dashed #e8e8e8; }
        .info-table .il { font-size: 9px; color: #888; font-weight: 600; width: 100px; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-table .iv { font-size: 12px; font-weight: 700; color: #1d1d1f; }
        .info-table .iv-mono { font-size: 9px; font-weight: 700; color: #1d1d1f; white-space: nowrap; }
        .info-table .iv-green { color: #2E7D4F; }
        .info-table .il-sm { font-size: 8px; color: #888; font-weight: 600; width: 46px; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-table .iv-sm { font-size: 11px; font-weight: 700; color: #1d1d1f; white-space: nowrap; }

        .code-pass {
            text-align: center;
            margin: 5px 0 6px;
            padding: 5px;
            background: #f8f6f9;
            border-radius: 8px;
            text-transform: uppercase;
        }
        .code-pass .label { font-size: 8px; color: #888; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 2px; }
        .code-pass .value {
            font-size: 20px; 
            font-weight: 800;
            color: #542680;
            letter-spacing: 2px;
        }

        .qr-block { text-align: center; margin: 6px 0 4px; }
        .qr-box {
            display: inline-block; padding: 6px;
            border: 2px solid #542680; border-radius: 12px;
        }
        .qr-box img { width: 150px; height: 150px; display: block; }
        .qr-label { font-size: 10px; font-weight: 700; color: #542680; letter-spacing: 2px; text-transform: uppercase; margin-top: 4px; }

        .note {
            color: #ffe082;
            border-radius: 8px;
            padding: 6px 10px;
            margin: 6px 0 0;
        }
        .note p { font-size: 8px; color: #9269b9; margin: 0; line-height: 1.4; text-align: center; }

        .footer { position: absolute; bottom: 0; left: 0; right: 0; width: 100%; background: #542680; padding: 10px 20px; page-break-inside: avoid; }
        .footer-text { color: #fff; font-size: 9px; }
        .footer-logo img { display: block; }

        hr.dashed { border: none; border-top: 1px dashed #ddd; margin: 5px 0; }
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
    <hr class="dashed">

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

        @php
            $idRow = $ticket->montant > 0;
            $promoRow = (bool) $ticket->code_promo_utilise;
        @endphp
        <table class="info-table" cellpadding="0" cellspacing="0">
            <tr class="{{ ($idRow || $promoRow) ? 'row-solid' : '' }}">
                <td class="il-sm">{{ $textes['billet'] }}</td>
                <td class="iv-sm">{{ $ticket->nom_tarif }}</td>
                <td class="il-sm">{{ $ticket->montant > 0 ? 'Montant' : 'ID' }}</td>
                <td class="iv-sm">
                    @if($ticket->montant > 0)
                        {{ number_format($ticket->montant, 0, ',', ' ') }} FCFA
                    @else
                        {{ $ticket->transaction_id ?? '---' }}
                    @endif
                </td>
            </tr>
            @if($idRow)
            <tr class="{{ $promoRow ? 'row-dash' : '' }}">
                <td colspan="4" class="il">ID transaction : <span class="iv-mono">{{ $ticket->transaction_id ?? '---' }}</span></td>
            </tr>
            @endif
            @if($promoRow)
            <tr>
                <td colspan="4" class="il">Code promo : <span class="iv iv-green">{{ $ticket->code_promo_utilise }}</span></td>
            </tr>
            @endif
        </table>

        @if($ticket->montant <= 0)
        <div style="text-align:left;padding:3px 0;color:#542680;font-weight:700;font-size:12px;letter-spacing:1px;text-transform:uppercase;">
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
            <p><span style="font-size:12px;">&#9888;&#65039;</span> Ce ticket comporte un QR Code unique au porteur. Ne le partagez jamais.</p>
        </div>

    </div>

    {{-- FOOTER violet --}}
    <table class="footer" width="100%" cellpadding="0" cellspacing="0" style="border-radius: 20px 20px 0 0;">
        <tr>
            <td style="text-align:left;vertical-align:middle;width:36px;">
                <div class="footer-logo">
                    <img src="{{ $logoDataUri }}" alt="PaxEvent" style="height: 28px; display: block;">
                </div>
            </td>
            <td style="text-align:right;vertical-align:middle;">
                <div class="footer-text">Billetterie en ligne 100% Bénin</div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>
