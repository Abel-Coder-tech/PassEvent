<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $textes['billet'] ?? 'Billet' }} - {{ $ticket->evenement?->titre ?? 'Evenement' }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
            size: 8cm 13cm;
        }
        html, body {
            width: 8cm;
            height: 13cm;
            margin: 0;
            padding: 0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            background: #542680;
            width: 8cm;
            height: 13cm;
            overflow: hidden;
        }
        .ticket {
            width: 8cm;
            height: 13cm;
            background: #542680;
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        /* ===== ZONE HAUTE ===== */
        .zone-top {
            background: #ffffff;
            border-radius: 14px;
            padding: 10px 12px 10px;
            flex-shrink: 0;
        }

        .ticket-title {
            text-align: center;
            font-size: 8px;
            font-weight: 700;
            color: #aaaaaa;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            padding-bottom: 7px;
            border-bottom: 1px solid #eeeeee;
            margin-bottom: 8px;
        }

        .event-name {
            font-size: 22px;
            font-weight: 800;
            color: #542680;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .info-grid td {
            padding: 2px 0;
            vertical-align: top;
            width: 50%;
        }
        .info-grid .lbl {
            font-size: 7px;
            color: #aaaaaa;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            display: block;
            margin-bottom: 1px;
        }
        .info-grid .val {
            font-size: 9px;
            font-weight: 800;
            color: #1a1a1a;
            display: block;
        }

        .gratuit {
            font-size: 10px;
            font-weight: 800;
            color: #e53935;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 4px;
        }

        /* ===== SÉPARATEUR ===== */
        .separator {
            position: relative;
            height: 18px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }
        .separator-line {
            width: 100%;
            border-top: 2px dashed rgba(255,255,255,0.5);
            margin: 0 14px;
        }
        .sep-circle-left {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            background: #542680;
            border-radius: 50%;
        }
        .sep-circle-right {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            background: #542680;
            border-radius: 50%;
        }

        /* ===== ZONE BASSE ===== */
        .zone-bottom {
            background: #ffffff;
            border-radius: 14px;
            padding: 10px 12px 10px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
        }

        .code-pass-wrap {
            background: #f0f0f0;
            border-radius: 8px;
            padding: 5px 14px;
            text-align: center;
            width: 100%;
        }
        .code-pass-value {
            font-size: 20px;
            font-weight: 800;
            color: #542680;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .qr-block {
            text-align: center;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .qr-block img {
            width: 120px;
            height: 120px;
            display: block;
        }
        .qr-label {
            font-size: 7px;
            font-weight: 700;
            color: #aaaaaa;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .footer-row {
            width: 100%;
            display: table;
            margin-top: 4px;
        }
        .footer-row td {
            vertical-align: middle;
        }
        .footer-merci {
            font-size: 8px;
            font-weight: 700;
            color: #1a1a1a;
            text-align: right;
        }
    </style>
</head>
<body>

@php $textes = $ticket->evenement?->getTextes() ?? ['billet' => 'Billet']; @endphp

<div class="ticket">

    {{-- ZONE HAUTE --}}
    <div class="zone-top">
        <div class="ticket-title">{{ strtoupper($textes['pdf_titre'] ?? 'Ticket d\'entrée') }}</div>

        <div class="event-name">{{ $ticket->evenement?->titre ?? 'Événement' }}</div>

        <table class="info-grid" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <span class="lbl">Tarif</span>
                    <span class="val">{{ strtoupper($ticket->nom_tarif ?? '—') }}</span>
                </td>
                <td>
                    <span class="lbl">Date et heure</span>
                    <span class="val">
                        {{ $ticket->evenement?->date_event?->isoFormat('D MMM YYYY') ?? '---' }}
                        @if($ticket->evenement?->date_event)
                            - {{ $ticket->evenement->date_event->format('H\hi') }}
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="lbl">ID</span>
                    <span class="val" style="font-size:8px;">{{ $ticket->transaction_id ?? '---' }}</span>
                </td>
                <td>
                    <span class="lbl">Lieu</span>
                    <span class="val">{{ $ticket->evenement?->lieu ?? '---' }}</span>
                </td>
            </tr>
        </table>

        @if($ticket->montant > 0)
            <div style="margin-top:4px;">
                <span class="lbl" style="font-size:7px;color:#aaaaaa;text-transform:uppercase;letter-spacing:0.8px;">Montant</span>
                <span style="font-size:10px;font-weight:800;color:#1a1a1a;"> {{ number_format($ticket->montant, 0, ',', ' ') }} FCFA</span>
                @if($ticket->montant_reduction > 0)
                    <span style="font-size:8px;color:#2E7D4F;font-weight:700;"> (-{{ number_format($ticket->montant_reduction, 0, ',', ' ') }} FCFA)</span>
                @endif
            </div>
        @else
            <div class="gratuit">Entrée gratuite</div>
        @endif
    </div>

    {{-- SÉPARATEUR --}}
    <div class="separator">
        <div class="sep-circle-left"></div>
        <div class="separator-line"></div>
        <div class="sep-circle-right"></div>
    </div>

    {{-- ZONE BASSE --}}
    <div class="zone-bottom">

        @if($ticket->statut_paiement === 'payé' || $ticket->statut_paiement === 'physique')
        <div class="code-pass-wrap">
            <div class="code-pass-value">{{ $ticket->code_unique }}</div>
        </div>
        @endif

        <div class="qr-block">
            <img src="{{ $qrCodeDataUri }}" alt="QR Code">
            <div class="qr-label">Scannez à l'entrée</div>
        </div>

        <table class="footer-row" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="width:36px;">
                    <img src="{{ $logoDataUri }}" alt="PaxEvent" style="height:22px;display:block;">
                </td>
                <td class="footer-merci">Merci d'utiliser PaxEvent !</td>
            </tr>
        </table>

    </div>

</div>

</body>
</html>