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
            margin: 0;
            padding: 0;
        }
        .ticket {
            width: 8cm;
            background: #542680;
            box-sizing: border-box;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .ticket-inner {
            width: calc(100% - 1.17cm);
            height: calc(100% - 1.38cm);
            margin: 0.69cm 0.585cm;
            background: #f2f2f2;
            border-radius: 0.5cm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* ===== ZONE HAUTE ===== */
        .zone-top {
            background: #f2f2f2;
            padding: 0.2cm 0.3cm 0.12cm;
            width: 100%;
            height: 4.16cm;
            box-sizing: border-box;
            position: relative;
        }

        .ticket-title {
            text-align: center;
            font-size: 0.25cm; /* reduced */
            font-weight: 700;
            color: #b7b7b7;
            letter-spacing: 0.08cm;
            text-transform: uppercase;
            padding-bottom: 0.1cm;
            border-bottom: 1px solid #e7e7e7;
            margin-bottom: 0.12cm;
        }

        .event-name {
            font-size: 0.64cm; /* reduced */
            font-weight: 800;
            color: #542680;
            text-transform: uppercase;
            letter-spacing: 0.015cm;
            margin-bottom: 0.12cm;
            line-height: 1.02;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.08cm;
        }
        .info-grid td {
            padding: 0.02cm 0;
            vertical-align: top;
            width: 50%;
        }
        .info-grid .lbl {
            font-size: 0.17cm;
            color: #aaaaaa;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02cm;
            display: block;
            margin-bottom: 0.03cm;
        }
        .info-grid .val {
            font-size: 0.20cm; /* reduced */
            font-weight: 800;
            color: #1a1a1a;
            display: block;
            line-height: 1.15;
        }

        .gratuit {
            font-size: 0.24cm; /* reduced */
            font-weight: 800;
            color: #e53935;
            text-transform: uppercase;
            letter-spacing: 0.035cm;
            margin-top: 0.08cm;
        }

        /* ===== SÉPARATEUR ===== */
        .separator {
            position: absolute;
            left: 0;
            right: 0;
            top: 4.16cm;
            /* ensure separator aligns with adjusted inner height */
            height: 0;
            display: block;
            pointer-events: none;
        }
        .separator-line {
            position: absolute;
            left: 0.22cm;
            right: 0.22cm;
            top: 0;
            border-top: 2px dashed rgba(92, 57, 121, 0.8);
        }
        .sep-circle-left {
            position: absolute;
            left: -0.14cm;
            top: 0;
            transform: translateY(-50%);
            width: 0.28cm;
            height: 0.28cm;
            background: #542680;
            border-radius: 50%;
        }
        .sep-circle-right {
            position: absolute;
            right: -0.14cm;
            top: 0;
            transform: translateY(-50%);
            width: 0.28cm;
            height: 0.28cm;
            background: #542680;
            border-radius: 50%;
        }

        /* ===== ZONE BASSE ===== */
        .zone-bottom {
            background: #f2f2f2;
            padding: 0.18cm 0.3cm 0.12cm;
            width: 100%;
            /* bottom zone height = 11.62 - 4.16 = 7.46 */
            height: 6.86cm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
        }

        .code-pass-wrap {
            background: rgba(0,0,0,0.04);
            border-radius: 0.12cm;
            padding: 0.08cm 0.22cm;
            text-align: center;
            width: 100%;
        }
        .code-pass-value {
            font-size: 0.58cm; /* reduced */
            font-weight: 800;
            color: #542680;
            letter-spacing: 0.07cm;
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
            width: 2.6cm; /* reduced */
            height: 2.6cm; /* reduced */
            display: block;
        }
        .qr-label {
            font-size: 0.15cm; /* reduced */
            font-weight: 700;
            color: #7d7d7d;
            letter-spacing: 0.045cm;
            text-transform: uppercase;
            margin-top: 0.08cm;
        }

        .footer-row {
            width: 100%;
            display: table;
            margin-top: 0.08cm;
        }
        .footer-row td {
            vertical-align: middle;
        }
        .footer-merci {
            font-size: 0.2cm;
            font-weight: 700;
            color: #1a1a1a;
            text-align: right;
        }
    </style>
</head>
<body>

@php $textes = $ticket->evenement?->getTextes() ?? ['billet' => 'Billet']; @endphp

<div class="ticket">
    <div class="ticket-inner">

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
</div>

</body>
</html>