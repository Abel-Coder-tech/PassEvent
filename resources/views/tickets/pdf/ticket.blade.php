<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $textes['billet'] ?? 'Billet' }} - {{ $ticket->evenement?->titre ?? 'Evenement' }}</title>
<style>
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

        background: #542680;

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
        background: #542680;
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

    .zone-top {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4.16cm;
        background: #ffffff;
        padding: 0.20cm 0.30cm 0.10cm;
        overflow: hidden;
        z-index: 2;
    }
    /* =========================================================
       TITRE : TICKET D'ENTRÉE
    ========================================================= */
    .ticket-title {
        width: 100%;
        text-align: center;
        font-size: 0.25cm;
        font-weight: 700;
        color: #542680;
        letter-spacing: 0.08cm;
        text-transform: uppercase;
        padding: 0.12cm 0;
        background: #eeeeee;
        line-height: 1.1;
    }
    /* =========================================================
       NOM DE L'ÉVÉNEMENT
    ========================================================= */

    .event-name {
        width: 100%;

        font-size: 0.64cm;

        font-weight: 800;

        color: #542680;

        text-transform: uppercase;

        letter-spacing: 0.015cm;

        margin-bottom: 0.12cm;

        line-height: 1.02;

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

        padding: 0.02cm 0;

        vertical-align: top;

        overflow: hidden;
    }


    /* =========================================================
       LABELS
    ========================================================= */

    .info-grid .lbl {
        display: block;

        font-size: 0.17cm;

        font-weight: 600;

        color: #858591;

        text-transform: uppercase;

        letter-spacing: 0.02cm;

        margin-bottom: 0.03cm;

        line-height: 1.1;
    }


    /* =========================================================
       VALEURS
    ========================================================= */

    .info-grid .val {
        display: block;

        font-size: 0.20cm;

        font-weight: 800;

        color: #333333;

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
        font-size: 0.24cm;

        font-weight: 800;

        color: #e53935;

        text-transform: uppercase;

        letter-spacing: 0.035cm;

        margin-top: 0.08cm;

        line-height: 1.1;
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

        background: #542680;

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

        background: #542680;

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

        padding: 0.18cm 0.30cm 0.12cm;

        overflow: hidden;

        z-index: 1;
    }


    /* =========================================================
       CODE UNIQUE / PAX-XXXXXX
    ========================================================= */

    .code-pass-wrap {
        width: 4.89cm;

        background: #eeeeee;

        border-radius: 0.12cm;

        padding: 0.08cm 0;

        text-align: center;

        margin: 0 auto;
    }


    .code-pass-value {
        font-size: 0.58cm;

        font-weight: 800;

        color: #542680;

        letter-spacing: 0.07cm;

        text-transform: uppercase;

        line-height: 1.1;

        white-space: nowrap;

        padding: 0 0.22cm;
    }


    /* =========================================================
       BLOC QR CODE
    ========================================================= */

    .qr-block {
        width: 100%;

        text-align: center;

        margin-top: 0.16cm;

        padding: 0;

        background: #ffffff;
    }


    /* QR CODE */

    .qr-block img {
        display: block;

        width: 4.89cm;
        height: 4.89cm;

        margin: 0 auto;

        padding: 0;
    }


    /* =========================================================
       TEXTE SOUS LE QR CODE
    ========================================================= */

    .qr-label {
        font-size: 0.24cm;

        font-weight: 700;

        color: #7d7d7d;

        letter-spacing: 0.035cm;

        text-transform: uppercase;

        margin-top: 0.12cm;

        line-height: 1.1;
    }
   /* Footer */
    .footer-row {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 2cm;
        margin-top: 0.24cm;
        padding: 0;
    }


    /* =========================================================
       LOGO
    ========================================================= */

    .footer-row img {
        height: 1cm;
        width: auto;
        display: block;
        flex-shrink: 0;
    }
    /* =========================================================
       TEXTE FOOTER
    ========================================================= */
    .footer-merci {
        font-size: 0.24cm;

        font-weight: 700;

        color: #333333;

        text-align: left;

        white-space: nowrap;

        line-height: 1.1;

        flex: 1;
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

            <div class="footer-row">
                <img src="{{ $logoDataUri }}" alt="PaxEvent" class="footer-logo">
                <span class="footer-merci">Merci d'utiliser PaxEvent !</span>
            </div>

        </div>
    </div>
</div>

</body>
</html>