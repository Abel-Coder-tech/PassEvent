<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; size: auto; }
        body {
            font-family: 'Helvetica', 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f0f0f0;
            color: #1a1a2e;
        }

        .ticket-container {
            width: 340px;
            margin: 0 auto;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            background: #fff;
        }

        .ticket-header {
            background: linear-gradient(135deg, #87428b, #6d3570);
            color: #fff;
            padding: 16px 18px;
            text-align: center;
        }

        .ticket-header .brand {
            font-size: 1.3rem;
            font-weight: 800;
            margin: 0 0 4px;
            letter-spacing: 0.5px;
        }

        .ticket-header .event-name {
            font-size: 1rem;
            font-weight: 700;
            margin: 0 0 4px;
            line-height: 1.2;
        }

        .ticket-header .event-info {
            font-size: 0.7rem;
            opacity: 0.85;
            margin: 0;
        }

        .ticket-body {
            padding: 14px 18px 18px;
            background: #fff;
        }

        .participant-block {
            margin-bottom: 14px;
        }

        .participant-block .label {
            font-size: 0.7rem;
            color: #98919b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 0 0 2px;
        }

        .participant-block .name {
            font-size: 1rem;
            font-weight: 700;
            margin: 0 2px 0;
        }

        .participant-block .contact {
            font-size: 0.75rem;
            color: #6c757d;
            margin: 0;
        }

        .details-grid {
            border-top: 1px dashed #e0e0e0;
            border-bottom: 1px dashed #e0e0e0;
            padding: 14px 0;
        }

        .detail-item {
            display: inline-block;
            width: 24%;
            vertical-align: top;
            text-align: center;
        }

        .detail-item .detail-label {
            font-size: 0.65rem;
            color: #98919b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 0 0 3px;
            font-weight: 600;
        }

        .detail-item .detail-value {
            font-size: 0.9rem;
            font-weight: 700;
            margin: 0;
        }

        .status-paid {
            color: #12976e;
        }

        .qr-section {
            text-align: center;
            padding-top: 16px;
        }

        .qr-box {
            display: inline-block;
            background: #fff;
            padding: 8px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .qr-box img {
            width: 130px;
            height: 130px;
            display: block;
        }

        .qr-code-text {
            font-family: 'Courier New', monospace;
            font-size: 0.7rem;
            color: #3d4345;
            text-align: center;
            margin-top: 6px;
        }

        .legal-notice {
            border-top: 1px solid #e0e0e0;
            margin-top: 8px;
            padding-top: 10px;
            text-align: center;
        }

        .legal-notice p {
            font-size: 0.65rem;
            color: #98919b;
            margin: 0;
            line-height: 1.4;
        }

        .ticket-footer {
            background: #f8fafc;
            padding: 14px 18px;
            text-align: center;
        }

        .ticket-footer p {
            font-size: 0.6rem;
            color: #6c757d;
            margin: 0;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <!-- Header -->
        <div class="ticket-header">
            <div class="brand">PassEvent</div>
            <div class="event-name">{{ $ticket->evenement?->titre ?? 'Evenement' }}</div>
            <div class="event-info">
                {{ $ticket->evenement?->date_event?->format('d M Y \a H:i') ?? '—' }} · {{ $ticket->evenement?->lieu ?? '—' }}
            </div>
        </div>

        <!-- Body -->
        <div class="ticket-body">
            <!-- Participant -->
            <div class="participant-block">
                <div class="label">Participant</div>
                <div class="name">{{ $ticket->nom_acheteur ?? '—' }}</div>
                <div class="contact">
                    {{ $ticket->email_acheteur ?? '—' }} · {{ $ticket->telephone_acheteur ?? '—' }}
                </div>
            </div>

            <!-- Details Grid -->
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Tarif</div>
                    <div class="detail-value" style="font-size: 0.75rem;">
                        {{ ucfirst($ticket->categorie ?? '—') }}
                        <br>{{ ucfirst($ticket->type ?? '—') }}
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Montant</div>
                    <div class="detail-value">{{ number_format($ticket->montant, 0, ',', ' ') }} F</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Statut</div>
                    <div class="detail-value status-paid">Payé</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Methode</div>
                    <div class="detail-value" style="font-size: 0.75rem;">{{ ucfirst($ticket->methode_paiement ?? '—') }}</div>
                </div>
            </div>

            <!-- QR Code -->
            <div class="qr-section">
                <div class="qr-box">
                    <img src="{{ $qrCodeDataUri }}" alt="QR Code">
                </div>
                <div class="qr-code-text">CODE : {{ $ticket->code_unique }}</div>
            </div>

            <!-- Legal -->
            <div class="legal-notice">
                <p>Scannez ce QR code a l'entree. Billet personnel et non transférable.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="ticket-footer">
            <p>PassEvent · Billetterie intelligente · Benin</p>
            <p>passevent2026@gmail.com | WhatsApp +229 43 70 45 13</p>
            <p>Paiement securise par KKiaPay</p>
        </div>
    </div>
</body>
</html>
