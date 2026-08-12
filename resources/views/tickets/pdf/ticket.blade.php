<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $textes['billet'] ?? 'Billet' }} - {{ $ticket->evenement?->titre ?? 'Evenement' }}</title>
    <style>
        /* =========================================================
           CONFIGURATION PDF
        ========================================================= */

        @page {
            margin: 0;
            padding: 0;
            size: 8cm 13cm;
        }

        html,
        body {
            width: 8cm;
            height: 13cm;
            margin: 0;
            padding: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            width: 8cm;
            height: 13cm;
            margin: 0;
            padding: 0;
            background: #602183;
            overflow: hidden;
        }

        /* =========================================================
            TICKET GLOBAL
        ========================================================= */

        .ticket {
            position: relative;
            width: 8cm;
            height: 13cm;
            margin: 0;
            padding: 0;
            background: #602183;
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
            page-break-after: avoid;
        }

        /* =========================================================
           CONTENEUR BLANC
        ========================================================= */

        .ticket-inner {
            position: absolute;
            top: 0.69cm;
            left: 0.585cm;
            width: 6.83cm;
            height: 11.62cm;
            background: #ffffff;
            border-radius: 0.5cm;
            overflow: hidden;
            margin: 0;
            padding: 0;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* =========================================================
           ZONE HAUTE - CORRIGÉE
        ========================================================= */

        .zone-top {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4.16cm;
            background: #ffffff;
            padding: 0 0.30cm 0.10cm 0.30cm;
            overflow: hidden;
            z-index: 2;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        /* =========================================================
           TITRE : TICKET D'ENTRÉE - CORRIGÉ (collé sur les bords)
        ========================================================= */

        .ticket-title {
            width: calc(100% + 0.60cm);
            margin-left: -0.30cm;
            margin-right: -0.30cm;
            padding: 0.20cm 0.30cm 0.10cm 0.30cm;
            text-align: center;
            justify-content: center;
            font-size: 10pt;
            font-weight: 700;
            color: rgba(57, 59, 61, 0.56);
            letter-spacing: 0.02cm;
            text-transform: uppercase;
            background: rgba(96, 33, 131, 0.05);
            line-height: 1.2;
            box-sizing: border-box;
        }

        /* =========================================================
           NOM DE L'ÉVÉNEMENT
        ========================================================= */

        .event-name {
            width: 100%;
            font-size: 11pt;
            font-weight: 700;
            color: #602183;
            text-transform: uppercase;
            letter-spacing: 0.015cm;
            margin: 0.15cm 0 0.15cm 0;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* =========================================================
           GRILLE DES INFORMATIONS
        ========================================================= */

        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
            table-layout: fixed;
        }

        .info-grid td {
            width: 50%;
            padding: 0;
            vertical-align: top;
            overflow: hidden;
        }

        /* =========================================================
           LABELS
        ========================================================= */

        .info-grid .lbl {
            display: block;
            font-size: 5.6pt;
            font-weight: 500;
            color: #767683;
            text-transform: uppercase;
            letter-spacing: 0.02cm;
            margin-bottom: 0.12cm;
            line-height: 1.1;
        }

        /* =========================================================
           VALEURS
        ========================================================= */

        .info-grid .val {
            display: block;
            font-size: 7pt;
            font-weight: 600;
            color: #393B3D;
            line-height: 1.15;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* =========================================================
           ENTRÉE GRATUITE
        ========================================================= */

        .gratuit {
            font-size: 8pt;
            font-weight: 700;
            color: #e53935;
            text-transform: uppercase;
            letter-spacing: 0.035cm;
            margin: 0.015cm 0;
            line-height: 1.1;
        }

        /* =========================================================
           MONTANT
        ========================================================= */

        .montant-block {
            margin-top: 0.08cm;
        }

        .montant-label {
            display: inline-block;
            font-size: 0.17cm;
            font-weight: 600;
            color: #aaaaaa;
            text-transform: uppercase;
            letter-spacing: 0.02cm;
        }

        .montant-value {
            font-size: 0.22cm;
            font-weight: 800;
            color: #1a1a1a;
        }

        .montant-reduction {
            font-size: 0.17cm;
            color: #2E7D4F;
            font-weight: 700;
        }

        /* =========================================================
           SÉPARATEUR
        ========================================================= */

        .separator {
            position: absolute;
            top: 4.16cm;
            left: 0;
            width: 100%;
            height: 0;
            z-index: 20;
            pointer-events: none;
        }

        /* Ligne en pointillés */
        .separator-line {
            position: absolute;
            top: 0;
            left: 0.22cm;
            right: 0.22cm;
            height: 0;
            border-top: 2px dashed rgba(92, 57, 121, 0.8);
        }

        /* Cercle gauche */
        .sep-circle-left {
            position: absolute;
            left: -0.14cm;
            top: 0;
            width: 0.28cm;
            height: 0.28cm;
            transform: translateY(-50%);
            background: #602183;
            border-radius: 50%;
        }

        /* Cercle droit */
        .sep-circle-right {
            position: absolute;
            right: -0.14cm;
            top: 0;
            width: 0.28cm;
            height: 0.28cm;
            transform: translateY(-50%);
            background: #602183;
            border-radius: 50%;
        }

        /* =========================================================
           ZONE INFÉRIEURE
        ========================================================= */

        .zone-bottom {
            position: absolute;
            top: 4.16cm;
            left: 0;
            width: 100%;
            height: 7.46cm;
            background: #ffffff;
            padding: 0.18cm 0 0.12cm;
            overflow: hidden;
            z-index: 1;
        }

        /* =========================================================
            CONTENEUR CENTRE : CODE PASS + QR
        ========================================================= */

        .zone-bottom-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.12cm;
        }

        /* =========================================================
            CODE UNIQUE / PAX-XXXXXX - CORRIGÉ
        ========================================================= */

        .code-pass-wrap {
            width: 4.14cm;
            height: 0.90cm;
            background: rgba(96, 33, 131, 0.05);
            border-radius: 0.18cm;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .code-pass-value {
            font-size: 17pt;
            font-weight: 600;
            color: #552680;
            letter-spacing: 0.05cm;
            text-transform: uppercase;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
        }

        /* =========================================================
           BLOC QR CODE
        ========================================================= */

        .qr-block {
            width: 100%;
            text-align: center;
            margin-top: 0;
            padding: 0;
            background: #ffffff;
        }

        .qr-block img {
            display: block;
            width: 4.89cm;
            height: 4.89cm;
            margin: 0 auto;
            padding: 0;
        }

        .qr-label {
            font-size: 7pt;
            font-weight: 500;
            color: #767683;
            letter-spacing: 0.02cm;
            text-transform: uppercase;
            margin-top: 0.06cm;
            line-height: 1.1;
        }

        /* =========================================================
           FOOTER
        ========================================================= */

        .footer-row {
            position: relative;
            width: 6.23cm;
            height: 0.82cm;
            margin: 0.12cm 0 0 0.30cm;
            padding: 0;
        }

        .footer-row .footer-logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 2.07cm;
            height: 0.82cm;
            display: block;
        }

        .footer-merci {
            position: absolute;
            right: 0;
            top: 0.30cm;
            font-size: 6pt;
            font-weight: 600;
            color: #393B3D;
            text-align: right;
            white-space: nowrap;
            line-height: 1.1;
        }

        /* =========================================================
           COMPATIBILITÉ IMPRESSION / PDF
        ========================================================= */

        .ticket,
        .ticket-inner,
        .zone-top,
        .zone-bottom,
        .separator {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* =========================================================
           IMPRESSION
        ========================================================= */

        @media print {
            html,
            body {
                width: 8cm;
                height: 13cm;
                margin: 0;
                padding: 0;
            }

            body {
                overflow: hidden;
            }

            .ticket {
                width: 8cm;
                height: 13cm;
                page-break-after: avoid;
                page-break-before: avoid;
                page-break-inside: avoid;
            }
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
                    <td style="text-align: left;">
                        <span class="lbl">Tarif</span>
                        <span class="val">{{ strtoupper($ticket->nom_tarif ?? '—') }}</span>
                    </td>
                    <td style="text-align: right;">
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
                    <td style="text-align: left;">
                        <span class="lbl">ID</span>
                        <span class="val" style="font-size:8px;">{{ $ticket->transaction_id ?? '---' }}</span>
                    </td>
                    <td style="text-align: right;">
                        <span class="lbl">Lieu</span>
                        <span class="val">{{ $ticket->evenement?->lieu ?? '---' }}</span>
                    </td>
                </tr>
            </table>

            @if($ticket->montant > 0)
                <div style="margin-top:2px;">
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

            <div class="zone-bottom-center">

                @if($ticket->statut_paiement === 'payé' || $ticket->statut_paiement === 'physique')
                <div class="code-pass-wrap">
                    <div class="code-pass-value">{{ $ticket->code_unique }}</div>
                </div>
                @endif

                <div class="qr-block">
                    <img src="{{ $qrCodeDataUri }}" alt="QR Code">
                    <div class="qr-label">Scannez à l'entrée</div>
                </div>

            </div>

            <div class="footer-row">
                <img src="{{ $logoDataUri }}" alt="PaxEvent" class="footer-logo">
                <span class="footer-merci">Merci d'utiliser PaxEvent !</span>
            </div>

        </div>
    </div>
</div>

</body>
</html>