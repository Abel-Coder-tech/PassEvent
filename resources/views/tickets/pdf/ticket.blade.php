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
            inset: 0;
            display: flex;
            flex-direction: column;
            padding: 0.30cm;
            gap: 0.28cm;
            box-sizing: border-box;
        }

        /* =========================================================
           CARTE D'INFOS — fond blanc, bordure pointillée, coins arrondis
        ========================================================= */

        .info-card {
            flex: 0 0 auto;
            width: 100%;
            background: #ffffff;
            border: 1.4px dashed #C6B7DA;
            border-radius: 0.38cm;
            overflow: hidden;
            box-sizing: border-box;
        }

        /* Bandeau "TICKET D'ENTRÉE" — violet/bleu PaxEvent, texte blanc */
        .info-header {
            width: 100%;
            background: linear-gradient(135deg, #5C2D91 0%, #3949AB 100%);
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
           GRILLE D'INFOS AVEC ICÔNES
        ========================================================= */

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.14cm 0.16cm;
            padding: 0.06cm 0.28cm 0.14cm;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.14cm;
            overflow: hidden;
        }

        .info-icon {
            flex: 0 0 auto;
            width: 0.40cm;
            height: 0.40cm;
            color: #5C2D91;
        }

        .info-val {
            font-size: 7pt;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.16cm;
            padding: 0.06cm 0.28cm 0.16cm;
        }

        .amount-icon {
            width: 0.42cm;
            height: 0.42cm;
            color: #5C2D91;
            flex: 0 0 auto;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.14cm;
            font-size: 8pt;
            font-weight: 700;
            color: #E53935;
            text-transform: uppercase;
            letter-spacing: 0.02cm;
            padding: 0.06cm 0.28cm 0.16cm;
        }

        .gratuit-badge .amount-icon {
            color: #E53935;
        }

        /* =========================================================
           ZONE QR — coins arrondis, sans contour visible
        ========================================================= */

        .qr-zone {
            flex: 1 1 auto;
            position: relative;
            width: 100%;
            border-radius: 0.42cm;
            overflow: hidden;
            background: #000000;
        }

        /* Image de l'événement — recouvre toute la zone sans déformation */
        .qr-zone-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        /* Voile noir opacité 80–90% */
        .qr-zone-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
        }

        .qr-zone-content {
            position: absolute;
            inset: 0;
            z-index: 3;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* =========================================================
           CARTE QR / CODE UNIQUE
        ========================================================= */

        .qr-card {
            background: #ffffff;
            border-radius: 0.30cm;
            padding: 0.20cm 0.14cm 0.14cm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 3.60cm;
        }

        .qr-wrap {
            position: relative;
            width: 3.00cm;
            height: 3.00cm;
        }

        .qr-wrap .qr-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
        }

        .qr-favicon {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0.60cm;
            height: 0.60cm;
            margin-top: -0.30cm;
            margin-left: -0.30cm;
            border-radius: 50%;
            background: #ffffff;
            padding: 0.05cm;
            box-sizing: border-box;
        }

        .code-pass-value {
            display: block;
            margin-top: 0.10cm;
            font-size: 9.5pt;
            font-weight: 700;
            color: #000000;
            letter-spacing: 0.02cm;
            text-transform: uppercase;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
        }

        /* =========================================================
           FOOTER — juste © www.paxevent.com, police légère
        ========================================================= */

        .qr-footer {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0.26cm;
            z-index: 3;
            text-align: center;
            font-size: 7pt;
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

            <div class="info-grid">

                {{-- TARIF --}}
                <div class="info-item">
                    <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41 12 22l-9-9V4a2 2 0 0 1 2-2h9l9 9a2 2 0 0 1 0 2.41z"/>
                        <circle cx="8" cy="8" r="1.4" fill="currentColor" stroke="none"/>
                    </svg>
                    <span class="info-val">{{ strtoupper($ticket->nom_tarif ?? '—') }}</span>
                </div>

                {{-- DATE / HEURE --}}
                <div class="info-item">
                    <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
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
                </div>

                {{-- ID TRANSACTION --}}
                <div class="info-item">
                    <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="9" x2="20" y2="9"/>
                        <line x1="4" y1="15" x2="20" y2="15"/>
                        <line x1="10" y1="3" x2="8" y2="21"/>
                        <line x1="16" y1="3" x2="14" y2="21"/>
                    </svg>
                    <span class="info-val" style="font-size:6.5pt;">{{ $ticket->transaction_id ?? '---' }}</span>
                </div>

                {{-- LIEU --}}
                <div class="info-item">
                    <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span class="info-val">{{ $ticket->evenement?->lieu ?? '---' }}</span>
                </div>

            </div>

            {{-- MONTANT / GRATUIT --}}
            @if($ticket->montant > 0)
                <div class="amount-row">
                    <svg class="amount-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="4" width="22" height="16" rx="2"/>
                        <line x1="1" y1="10" x2="23" y2="10"/>
                    </svg>
                    <span class="amount-value">{{ number_format($ticket->montant, 0, ',', ' ') }} FCFA</span>
                    @if($ticket->montant_reduction > 0)
                        <span class="amount-reduction">(-{{ number_format($ticket->montant_reduction, 0, ',', ' ') }} FCFA)</span>
                    @endif
                </div>
            @else
                <div class="gratuit-badge">
                    <svg class="amount-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="4" width="22" height="16" rx="2"/>
                        <line x1="1" y1="10" x2="23" y2="10"/>
                    </svg>
                    Entrée gratuite
                </div>
            @endif

        </div>

        {{-- ZONE QR (image événement + voile noir + QR) --}}
        <div class="qr-zone">

            @if($eventImageDataUri)
                <img src="{{ $eventImageDataUri }}" alt="" class="qr-zone-bg">
            @endif
            <div class="qr-zone-overlay"></div>

            <div class="qr-zone-content">
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

            <div class="qr-footer">© www.paxevent.com</div>

        </div>

    </div>
</div>

</body>
</html>