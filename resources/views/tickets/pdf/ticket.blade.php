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
            background: #f5f5f5;
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
            background: #ffffff;
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
           ZONE HAUTE
        ========================================================= */

        .zone-top {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4.16cm;
            background: #f5f5f5;
            padding: 0 0.30cm 0.10cm 0.30cm;
            overflow: hidden;
            z-index: 2;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        /* =========================================================
           TITRE : TICKET D'ENTRÉE - CORRIGÉ (centré horizontalement)
        ========================================================= */

        .ticket-title {
            width: 6.83cm;
            margin-left: -0.30cm;
            margin-right: -0.30cm;
            padding: 0.15cm 0 0.10cm 0;
            text-align: center;
            font-size: 10pt;
            font-weight: 700;
            color: #333333;
            letter-spacing: 0;
            text-transform: uppercase;
            background: #e0e0e0;
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
            margin: 0.12cm 0 0.10cm 0;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* =========================================================
           GRILLE DES INFORMATIONS - CORRIGÉE
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
            padding: 0.03cm 0;
            vertical-align: top;
            overflow: hidden;
        }

        /* Colonne de gauche : alignée à gauche */
        .info-grid td:first-child {
            text-align: left;
            padding-right: 0.10cm;
        }

        /* Colonne de droite : alignée à droite avec marge de 0.30cm */
        .info-grid td:last-child {
            text-align: right;
            padding-right: 0.8cm;  /* AJOUTÉ : marge à droite de 0.30cm */
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
            margin-bottom: 0.05cm;
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
            margin: 0.02cm 0;
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

        .separator-line {
            position: absolute;
            top: 0;
            left: 0.22cm;
            right: 0.22cm;
            height: 0;
            border-top: 2px dashed rgba(92, 57, 121, 0.8);
        }

        .sep-circle-left {
            position: absolute;
            left: -0.14cm;
            top: 0;
            width: 0.28cm;
            height: 0.28cm;
            transform: translateY(-50%);
            background: #ffffff;
            border-radius: 50%;
        }

        .sep-circle-right {
            position: absolute;
            right: -0.14cm;
            top: 0;
            width: 0.28cm;
            height: 0.28cm;
            transform: translateY(-50%);
            background: #ffffff;
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
            overflow: hidden;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Image de l'événement au fond de la zone basse + voile noir 80% */
        .zone-bottom-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .zone-bottom-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1;
        }

        /* =========================================================
           CODE UNIQUE / PAX-XXXXXX
        ========================================================= */

        .qr-card {
            position: relative;
            z-index: 3;
            width: 5.60cm;
            height: 5.60cm;
            background: #ffffff;
            border-radius: 0.35cm;
            margin: 1.5cm auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.1cm 0.1cm 0.2cm 0.1cm;
            box-sizing: border-box;
        }

        .qr-wrap {
            position: relative;
            width: 4.00cm;
            height: 4.00cm;
            margin: 0 auto;
            flex-shrink: 0;
        }

        .qr-wrap .qr-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 4.00cm;
            height: 4.00cm;
            background: #ffffff;
        }

        .qr-favicon {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0.85cm;
            height: 0.85cm;
            margin-top: -0.425cm;
            margin-left: -0.425cm;
            border-radius: 50%;
            background: #ffffff;
            padding: 0.08cm;
            box-sizing: border-box;
        }

        .code-pass-value {
            display: block;
            margin-top: 0.15cm;
            font-size: 11pt;
            font-weight: 700;
            color: #000000;
            letter-spacing: 0.04cm;
            text-transform: uppercase;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
        }

        /* =========================================================
           FOOTER
        ========================================================= */

        .ticket-footer {
            position: absolute;
            bottom: 0.3cm;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 10pt;
            font-weight: 700;
            color: #333333;
            text-transform: uppercase;
            letter-spacing: 0;
            background: #e0e0e0;
            padding: 0.15cm 0;
            border-radius: 0 0 0.35cm 0.35cm;
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
                    <td>
                        <span class="lbl">Tarif</span>
                        <span class="val">{{ strtoupper($ticket->nom_tarif ?? '—') }}</span>
                        <span class="lbl" style="margin-top:0.05cm;">ID</span>
                        <span class="val" style="font-size:8px;">{{ $ticket->transaction_id ?? '---' }}</span>
                    </td>
                    <td>
                        <span class="lbl">Date et heure</span>
                        <span class="val">
                            @php
                                $datesTicket = $ticket->evenement?->dates ?? collect();
                            @endphp
                            @if($datesTicket->count() > 1)
                                @foreach($datesTicket as $d)
                                    {{ $d->date_debut->isoFormat('D MMM') }} - {{ $d->date_debut->format('H\hi') }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            @else
                                {{ $ticket->evenement?->date_event?->isoFormat('D MMM YYYY') ?? '---' }}
                                @if($ticket->evenement?->date_event)
                                    - {{ $ticket->evenement->date_event->format('H\hi') }}
                                @endif
                            @endif
                        </span>
                        <span class="lbl" style="margin-top:0.05cm;">Lieu</span>
                        <span class="val">{{ $ticket->evenement?->lieu ?? '---' }}</span>
                    </td>
                </tr>
            </table>

            @if($ticket->montant > 0)
                <div style="margin-top:0.05cm;">
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

            @if($eventImageDataUri)
                <img src="{{ $eventImageDataUri }}" alt="" class="zone-bottom-bg">
                <div class="zone-bottom-overlay"></div>
            @endif

            <div class="qr-card">

                <div class="qr-wrap">
                    <img src="{{ $qrCodeDataUri }}" alt="QR Code" class="qr-img">
                    @if($faviconDataUri)
                        <img src="{{ $faviconDataUri }}" alt="" class="qr-favicon">
                    @endif
                </div>

                @if($ticket->statut_paiement === 'payé' || $ticket->statut_paiement === 'physique')
                <div class="code-pass-value">{{ $ticket->code_unique }}</div>
                @endif

            </div>

        </div>
    </div>

    <div class="ticket-footer">Merci d'utiliser PaxEvent !</div>
</div>

</body>
</html>