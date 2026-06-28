<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Billet - {{ $ticket->evenement?->titre ?? 'Evenement' }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #1d1d1f;
            font-size: 11px;
            line-height: 1.3;
        }
        * { margin: 0; padding: 0; }
        .ticket {
            width: 350px;
            margin: 20px auto;
            background: #fff;
            border: 1px solid #e0dde3;
            border-radius: 14px;
            overflow: hidden;
        }
        .inner { padding: 14px 16px 12px; }

        /* Header */
        .header-top { font-size: 9px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
        .header-top .left { text-align: left; }
        .header-top .right { text-align: right; }
        .event-title { font-size: 18px; font-weight: 800; color: #1d1d1f; margin: 6px 0 3px; }
        .event-meta { font-size: 10px; color: #888; margin-bottom: 2px; }

        /* Participant */
        .participant-block {
            margin: 12px 0 10px;
            padding: 10px 0 8px;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
        .participant-label {
            font-size: 8px;
            font-weight: 700;
            color: #888;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .participant-name {
            font-size: 16px;
            font-weight: 800;
            color: #1d1d1f;
        }
        .participant-email {
            font-size: 10px;
            color: #888;
            margin-top: 1px;
        }
        /* Details grid 2x2 */
        .detail-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .detail-grid td {
            width: 50%;
            padding: 6px 8px;
            text-align: center;
        }
        .detail-grid .dl {
            font-size: 8px;
            font-weight: 700;
            color: #888;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .detail-grid .dv {
            font-size: 13px;
            font-weight: 700;
            color: #1d1d1f;
        }
        .detail-grid .dv small {
            font-size: 9px;
            font-weight: 500;
            color: #888;
        }
        .detail-grid .dv-violet { color: #7B3FA0; }
        .detail-grid .dv-green { color: #2E7D4F; }

        /* Transaction section */
        .section-title {
            font-size: 8px;
            font-weight: 700;
            color: #7B3FA0;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-align: center;
            margin: 8px 0 6px;
        }
        .tx-table {
            width: 100%;
            border-collapse: collapse;
        }
        .tx-table td {
            padding: 3px 0;
            vertical-align: middle;
        }
        .tx-table .tl {
            font-size: 9px;
            color: #888;
            font-weight: 600;
            width: 90px;
        }
        .tx-table .tv {
            font-size: 10px;
            font-weight: 700;
            color: #1d1d1f;
        }
        .tx-table .tv-mono {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            font-weight: 700;
            color: #1d1d1f;
        }
        /* HR */
        hr.dashed {
            border: none;
            border-top: 1px dashed #ddd;
            margin: 8px 0;
        }
        hr.solid {
            border: none;
            border-top: 1px solid #eee;
            margin: 6px 0;
        }

        /* QR code */
        .qr-block {
            text-align: center;
            margin: 10px 0 4px;
        }
        .qr-box {
            display: inline-block;
            padding: 6px;
            border: 2px solid #7B3FA0;
            border-radius: 10px;
        }
        .qr-box img {
            width: 140px;
            height: 140px;
            display: block;
        }
        .qr-label {
            font-size: 7px;
            font-weight: 700;
            color: #7B3FA0;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .qr-code-display {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            font-weight: 800;
            color: #1d1d1f;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 8px 16px 12px;
        }
        .footer p {
            font-size: 7px;
            color: #aaa;
            margin: 0;
            line-height: 1.6;
        }
        .footer strong { color: #888; font-weight: 700; }
    </style>
</head>
<body>

<table class="ticket" cellpadding="0" cellspacing="0">
    <tr><td class="inner">

        {{-- 1. HEADER --}}
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="text-align:left;vertical-align:middle;">
                    <div class="event-title">{{ $ticket->evenement?->titre ?? 'Événement' }}</div>
                    <div class="event-meta">
                        {{ $ticket->evenement?->date_event?->format('d M Y') ?? '---' }}
                        &ndash; {{ $ticket->evenement?->date_event?->format('H\hi') ?? '' }}
                        @if($ticket->evenement?->lieu)
                            &ndash; {{ $ticket->evenement->lieu }}
                        @endif
                    </div>
                </td>
                <td style="text-align:right;vertical-align:middle;width:80px;">
                    <img src="{{ $logoDataUri ?? '' }}" alt="PaxEvent" style="height:40px; display:inline-block;">
                </td>
            </tr>
        </table>

        {{-- 2. PARTICIPANT --}}
        <div class="participant-block">
            <div class="participant-label">Participant</div>
            <div class="participant-name">{{ $ticket->nom_acheteur ?? '---' }}</div>
            <div class="participant-email">{{ $ticket->email_acheteur ?? '' }}</div>
        </div>

        {{-- 3. DETAILS GRID 2 COLUMNS --}}
        <table class="detail-grid" cellpadding="0" cellspacing="4">
            <tr>
                <td>
                    <div class="dl">Catégorie</div>
                    <div class="dv">{{ ucfirst($ticket->categorie ?? '---') }}</div>
                </td>
                <td>
                    <div class="dl">Type</div>
                    <div class="dv">{{ $ticket->type === 'normal' ? 'Standard' : 'VIP' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="dl">Montant</div>
                    <div class="dv">{{ number_format($ticket->montant, 0, ',', ' ') }} <small>FCFA</small></div>
                </td>
                <td>
                    <div class="dl">Paiement</div>
                    <div class="dv">{{ ucfirst($ticket->methode_paiement ?? '---') }}</div>
                </td>
            </tr>
        </table>

        {{-- 4. TRANSACTION --}}
        <hr class="dashed">
        <div class="section-title">Détails de la transaction</div>

        <table class="tx-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="tl">Transaction</td>
                <td class="tv-mono">{{ $ticket->transaction_id ?? '---' }}</td>
            </tr>
            <tr>
                <td class="tl">Paiement</td>
                <td class="tv">{{ $ticket->methode_paiement ?? '---' }}</td>
            </tr>
            <tr>
                <td class="tl">Date d'achat</td>
                <td class="tv">{{ $ticket->date_achat?->format('d/m/Y \\· H:i') ?? '---' }}</td>
            </tr>
        </table>

        @if($ticket->montant_reduction > 0)
            <table class="tx-table" cellpadding="0" cellspacing="0" style="margin-top:4px">
                <tr>
                    <td class="tl">Réduction</td>
                    <td class="tv" style="color:#2E7D4F">&minus;{{ number_format($ticket->montant_reduction, 0, ',', ' ') }} FCFA</td>
                </tr>
            </table>
        @endif

        <hr class="dashed">

        {{-- 5. QR CODE --}}
        <div class="qr-block">
            <div class="qr-box">
                <img src="{{ $qrCodeDataUri }}" alt="QR Code">
            </div>
            <div class="qr-label">Scannez ce code</div>
            <div class="qr-code-display">{{ $ticket->code_unique }}</div>
        </div>

        {{-- 6. FOOTER --}}
        <div class="footer">
            <hr class="solid">
            <p>
                Présentez ce QR code à l'entrée pour scanner votre billet<br>
                <strong>Billet personnel et non transférable</strong> &middot; PaxEvent UPAO
            </p>
        </div>

    </td></tr>
</table>

</body>
</html>
