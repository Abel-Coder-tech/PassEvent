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
            height: 13cm;
            background: #542680;
            padding: 0.69cm 0.585cm;
            box-sizing: border-box;
        }
        .ticket-inner {
            width: 100%;
            height: 11.62cm;
            background: #f2f2f2;
            border-radius: 0.5cm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* ===== ZONE HAUTE ===== */
        .zone-top {
            background: #f2f2f2;
            padding: 0.2cm 0.3cm 0.12cm;
            flex: 0 0 4.16cm;
            width: 100%;
            min-height: 4.16cm;
        }

        .ticket-title {
            text-align: center;
            font-size: 0.28cm;
            font-weight: 700;
            color: #b7b7b7;
            letter-spacing: 0.09cm;
            text-transform: uppercase;
            padding-bottom: 0.12cm;
            border-bottom: 1px solid #e7e7e7;
            margin-bottom: 0.18cm;
        }

        .event-name {
            font-size: 0.7cm;
            font-weight: 800;
            color: #542680;
            text-transform: uppercase;
            letter-spacing: 0.02cm;
            margin-bottom: 0.18cm;
            line-height: 1.05;
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
            font-size: 0.22cm;
            font-weight: 800;
            color: #1a1a1a;
            display: block;
            line-height: 1.2;
        }

        .gratuit {
            font-size: 0.28cm;
            font-weight: 800;
            color: #e53935;
            text-transform: uppercase;
            letter-spacing: 0.04cm;
            margin-top: 0.12cm;
        }

        /* ===== SÉPARATEUR ===== */
        .separator {
            position: relative;
            height: 0.16cm;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            background: #f2f2f2;
        }
        .separator-line {
            width: 100%;
            border-top: 2px dashed rgba(92, 57, 121, 0.8);
            margin: 0 0.16cm;
        }
        .sep-circle-left {
            position: absolute;
            left: -0.1cm;
            top: 50%;
            transform: translateY(-50%);
            width: 0.28cm;
            height: 0.28cm;
            background: #542680;
            border-radius: 50%;
        }
        .sep-circle-right {
            position: absolute;
            right: -0.1cm;
            top: 50%;
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
            flex: 0 0 7.46cm;
            width: 100%;
            min-height: 7.46cm;
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
            font-size: 0.65cm;
            font-weight: 800;
            color: #542680;
            letter-spacing: 0.08cm;
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
            width: 2.9cm;
            height: 2.9cm;
            display: block;
        }
        .qr-label {
            font-size: 0.17cm;
            font-weight: 700;
            color: #7d7d7d;
            letter-spacing: 0.05cm;
            text-transform: uppercase;
            margin-top: 0.12cm;
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