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
            background: #ffffff;
            overflow: hidden;
        }

        /* =========================================================
           TICKET GLOBAL — remplit toute la surface
        ========================================================= */

        .ticket {
            position: relative;
            width: 8cm;
            height: 13cm;
            background: #ffffff;
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
            page-break-after: avoid;
        }

        .ticket-body {
            position: absolute;
            top: 0;
            left: 0;
            width: 8cm;
            height: 13cm;
            padding: 0.30cm;
            box-sizing: border-box;
        }

        /* =========================================================
           CARTE D'INFOS — fond blanc, bordure pointillée, coins arrondis
        ========================================================= */

        .info-card {
            width: 7.40cm;
            height: 4.16cm;
            background: #ffffff;
            border: 1px dashed #C6B7DA;
            border-radius: 0.38cm;
            overflow: hidden;
            box-sizing: border-box;
        }

        /* Bandeau "TICKET D'ENTRÉE" — violet PaxEvent, texte blanc */
        .info-header {
            width: 100%;
            background-color: #5C2D91;
            color: #ffffff;
            text-align: center;
            font-size: 9.5pt;
            font-weight: 700;
            letter-spacing: 0.03cm;
            text-transform: uppercase;
            padding: 0.20cm 0;
            line-height: 1.2;
        }

        .event-name {
            padding: 0.16cm 0.28cm 0.04cm;
            font-size: 10.5pt;
            font-weight: 700;
            color: #5C2D91;
            text-transform: uppercase;
            letter-spacing: 0.01cm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* =========================================================
           GRILLE D'INFOS (tableau — compatible dompdf)
        ========================================================= */

        .info-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            padding: 0;
            margin: 0;
        }

        .info-grid td {
            width: 50%;
            padding: 0.05cm 0.28cm 0.05cm;
            vertical-align: top;
            overflow: hidden;
            text-align: left;
        }

        .info-grid td:first-child {
            width: 34%;
            padding-right: 0.10cm;
        }

        .info-grid td:last-child {
            width: 66%;
            padding-left: 0.10cm;
            text-align: right;
        }

        .info-grid td:last-child .info-val {
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
        }

        .info-label {
            display: block;
            font-size: 6pt;
            font-weight: 500;
            color: #767683;
            text-transform: uppercase;
            letter-spacing: 0.02cm;
            margin-bottom: 0.03cm;
            line-height: 1.1;
        }

        .info-val {
            display: block;
            font-size: 7.5pt;
            font-weight: 600;
            color: #393B3D;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* =========================================================
           MONTANT / GRATUIT
        ========================================================= */

        .amount-row {
            text-align: center;
            padding: 0.06cm 0.28cm 0.16cm;
        }

        .amount-label {
            font-size: 6pt;
            font-weight: 500;
            color: #767683;
            text-transform: uppercase;
            letter-spacing: 0.02cm;
        }

        .amount-value {
            font-size: 9pt;
            font-weight: 800;
            color: #1a1a1a;
        }

        .amount-reduction {
            font-size: 7.5pt;
            font-weight: 700;
            color: #2E7D4F;
        }

        .gratuit-badge {
            text-align: left;
            padding: 0.06cm 0.28cm 0.16cm;
            font-size: 8.5pt;
            font-weight: 700;
            color: #E53935;
            text-transform: uppercase;
            letter-spacing: 0.02cm;
        }

        /* =========================================================
           ZONE QR — noire, coins arrondis
        ========================================================= */

        .qr-zone {
            position: relative;
            margin-top: 0.26cm;
            width: 7.40cm;
            height: 7.98cm;
            border-radius: 0.42cm;
            overflow: hidden;
            background: #000000;
        }

        /* Image de l'événement — recouvre toute la zone sans déformation */
        .qr-zone-bg {
            position: absolute;
            top: 0;
            left: 0;
            background: #000000;
        }

        /* Voile noir opacité 80–90% */
        .qr-zone-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 7.40cm;
            height: 7.98cm;
            background: rgba(0, 0, 0, 0.85);
            z-index: 1;
        }

        /* =========================================================
           CARTE QR / CODE UNIQUE
        ========================================================= */

        .qr-card {
            position: absolute;
            left: 50%;
            top: 50%;
            margin-left: -2.10cm;
            margin-top: -2.10cm;
            z-index: 3;
            width: 4.20cm;
            height: 4.20cm;
            background: #ffffff;
            border-radius: 0.32cm;
            padding: 0.08cm;
            text-align: center;
            box-sizing: border-box;
        }

        .qr-wrap {
            position: relative;
            width: 3.61cm;
            height: 3.61cm;
            margin: 0 auto;
        }

        .qr-wrap .qr-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 3.61cm;
            height: 3.61cm;
            background: #ffffff;
        }

        .qr-favicon {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0.72cm;
            height: 0.72cm;
            margin-top: -0.36cm;
            margin-left: -0.36cm;
            border-radius: 50%;
            background: #ffffff;
            padding: 0.05cm;
            box-sizing: border-box;
        }

        .code-pass-value {
            display: block;
            margin-top: 0.08cm;
            font-size: 10pt;
            font-weight: 700;
            color: #000000;
            letter-spacing: 0.02cm;
            text-transform: uppercase;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
        }

        /* =========================================================
           FOOTER — juste © www.paxevent.com
        ========================================================= */

        .qr-footer {
            font-family: 'Montserrat', sans-serif;
            position: absolute;
            left: 0;
            width: 100%;
            bottom: 0.26cm;
            z-index: 3;
            text-align: center;
            font-size: 12pt;
            font-weight: 300;
            letter-spacing: 0.015cm;
            color: rgba(255, 255, 255, 0.9);
        }

        /* =========================================================
           COMPATIBILITÉ IMPRESSION / PDF
        ========================================================= */

        .ticket,
        .info-card,
        .qr-zone {
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
    <div class="ticket-body">

        {{-- CARTE D'INFOS --}}
        <div class="info-card">

            <div class="info-header">{{ strtoupper($textes['pdf_titre'] ?? 'Ticket d\'entrée') }}</div>

            <div class="event-name">{{ $ticket->evenement?->titre ?? 'Événement' }}</div>

            <table class="info-grid" cellpadding="0" cellspacing="0">

                <tr>
                    {{-- TARIF --}}
                    <td>
                        <span class="info-label">Tarif</span>
                        <span class="info-val">{{ strtoupper($ticket->nom_tarif ?? '—') }}</span>
                    </td>

                    {{-- DATE / HEURE --}}
                    <td>
                        <span class="info-label">Date et heure</span>
                        <span class="info-val">
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
                    </td>
                </tr>

                <tr>
                    {{-- ID TRANSACTION --}}
                    <td>
                        <span class="info-label">ID</span>
                        <span class="info-val" style="font-size:6.5pt;">{{ $ticket->transaction_id ?? '---' }}</span>
                    </td>

                    {{-- LIEU --}}
                    <td>
                        <span class="info-label">Lieu</span>
                        <span class="info-val">{{ $ticket->evenement?->lieu ?? '---' }}</span>
                    </td>
                </tr>

            </table>

            {{-- MONTANT / GRATUIT --}}
            @if($ticket->montant > 0)
                <div class="amount-row">
                    <span class="amount-label">Montant&nbsp;&nbsp;</span>
                    <span class="amount-value">{{ number_format($ticket->montant, 0, ',', ' ') }} FCFA</span>
                    @if($ticket->montant_reduction > 0)
                        <span class="amount-reduction">&nbsp;(-{{ number_format($ticket->montant_reduction, 0, ',', ' ') }} FCFA)</span>
                    @endif
                </div>
            @else
                <div class="gratuit-badge">Entrée gratuite</div>
            @endif

        </div>

        {{-- ZONE QR (image événement + voile noir + QR) --}}
        @php
            $zoneW = 7.40;
            $zoneH = 7.98;
            $bgStyle = 'width:auto; height:auto;';
            if ($eventImageDataUri && $eventImageW && $eventImageH) {
                $scale = max($zoneW / $eventImageW, $zoneH / $eventImageH);
                $dw = round($eventImageW * $scale, 3);
                $dh = round($eventImageH * $scale, 3);
                $dx = round(($zoneW - $dw) / 2, 3);
                $dy = round(($zoneH - $dh) / 2, 3);
                $bgStyle = "width:{$dw}cm; height:{$dh}cm; left:{$dx}cm; top:{$dy}cm;";
            }
        @endphp
        <div class="qr-zone">

            @if($eventImageDataUri)
                <img src="{{ $eventImageDataUri }}" alt="" class="qr-zone-bg" style="{{ $bgStyle }}">
            @endif
            <div class="qr-zone-overlay"></div>

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

            <div class="qr-footer">© www.paxevent.com</div>

        </div>

    </div>
</div>

</body>
</html>