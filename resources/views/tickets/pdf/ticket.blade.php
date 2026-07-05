<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Billet - {{ $ticket->evenement?->titre ?? 'Evenement' }}</title>
    <style>
        @page { margin: 0; padding: 0; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0; padding: 0;
            background: #f5f3f0;
            color: #1d1d1f;
            font-size: 10px;
            line-height: 1.4;
        }
        * { margin: 0; padding: 0; font-family: 'DejaVu Sans', sans-serif; }
        .ticket {
            width: 380px;
            margin: 20px auto;
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .header { background: #542680; padding: 20px 24px; }
        .header-title { color: #fff; }
        .header-title .pass { font-size: 9px; font-weight: 400; text-transform: uppercase; letter-spacing: 2px; opacity: 0.8; }
        .header-title .event-name { font-size: 20px; font-weight: 800; margin-top: 2px; }

        .body { padding: 20px 24px 16px; }

        .event-meta {
            font-size: 11px; color: #888; margin-bottom: 14px;
            padding-bottom: 12px; border-bottom: 1px solid #eee;
        }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info-table td { padding: 5px 0; vertical-align: middle; }
        .info-table .il { font-size: 9px; color: #888; font-weight: 600; width: 80px; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-table .iv { font-size: 12px; font-weight: 700; color: #1d1d1f; }
        .info-table .iv-mono { font-size: 10px; font-weight: 700; color: #1d1d1f; }
        .info-table .iv-green { color: #2E7D4F; }

        .code-pass {
            text-align: center;
            margin: 12px 0 14px;
            padding: 10px;
            background: #f8f6f9;
            border-radius: 8px;
        }
        .code-pass .label { font-size: 8px; color: #888; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px; }
        .code-pass .value {
            font-size: 18px; font-weight: 800;
            color: #542680;
            letter-spacing: 2px;
        }

        .qr-block { text-align: center; margin: 16px 0 10px; }
        .qr-box {
            display: inline-block; padding: 8px;
            border: 3px solid #542680; border-radius: 12px;
        }
        .qr-box img { width: 200px; height: 200px; display: block; }
        .qr-label { font-size: 7px; font-weight: 700; color: #542680; letter-spacing: 2px; text-transform: uppercase; margin-top: 6px; }

        .note {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 8px;
            padding: 10px 14px;
            margin: 12px 0 0;
        }
        .note p { font-size: 9px; color: #666; margin: 0; line-height: 1.5; }

        .footer { background: #542680; padding: 14px 24px; }
        .footer-text { color: #fff; font-size: 9px; }
        .footer-logo img { height: 28px; display: block; }

        hr.dashed { border: none; border-top: 1px dashed #ddd; margin: 8px 0; }
    </style>
</head>
<body>

<div class="ticket">

    {{-- HEADER violet (no logo) --}}
    <div class="header">
        <div class="header-title">
            <div class="pass">Pass</div>
            <div class="event-name">{{ $ticket->evenement?->titre ?? 'Événement' }}</div>
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
                <td class="il">Billet</td>
                <td class="iv">{{ $ticket->type === 'normal' ? 'Standard' : 'VIP' }} &middot; {{ ucfirst($ticket->categorie) }}</td>
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
            @if($ticket->montant_reduction > 0)
            <tr>
                <td class="il">Remise</td>
                <td class="iv iv-green">&minus;{{ number_format($ticket->montant_reduction, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            @endif
        </table>

        @if($ticket->montant <= 0)
        <div style="text-align:center;padding:8px 0;color:#542680;font-weight:700;font-size:12px;letter-spacing:1px;text-transform:uppercase;">
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
            <div class="qr-label">Scannez ce code pour valider votre entrée</div>
        </div>

        <div class="note">
            <p><span style="font-size:12px;">&#9888;&#65039;</span> Ce billet est personnel et non transférable. Présentez ce QR code (imprimé ou sur écran) à l'entrée de l'événement. Tout billet scanné une première fois ne pourra plus être réutilisé.</p>
        </div>

    </div>

    {{-- FOOTER violet --}}
    <table class="footer" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="text-align:left;vertical-align:middle;width:36px;">
                <div class="footer-logo">
                    <img src="{{ $logoDataUri ?? '' }}" alt="PaxEvent">
                </div>
            </td>
            <td style="text-align:left;vertical-align:middle;padding-left:10px;">
                <div class="footer-text">Billetterie simple et rapide pour vos événements</div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>
