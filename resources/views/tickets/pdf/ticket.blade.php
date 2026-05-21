<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Billet - {{ $ticket->evenement?->titre ?? 'Evenement' }}</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Helvetica', 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f3f0;
            color: #1d1d1f;
            font-size: 10px;
        }
        .ticket {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        /* ===== Hero Image ===== */
        .hero {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
        .hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(123,63,160,0.7) 0%, rgba(123,63,160,0.2) 60%, transparent 100%);
        }
        .hero-content {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            padding: 28px 28px 20px;
        }
        .hero-content .category {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(4px);
            color: #fff;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 3px 12px;
            border-radius: 20px;
            margin-bottom: 8px;
        }
        .hero-content h1 {
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            margin: 0;
            line-height: 1.15;
            text-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .hero-content .hero-meta {
            color: rgba(255,255,255,0.9);
            font-size: 10px;
            margin-top: 6px;
        }
        .hero-content .hero-meta span {
            margin-right: 14px;
        }
        /* ===== Corps du billet ===== */
        .body {
            padding: 20px 28px 0;
        }
        /* Nom participant */
        .participant {
            text-align: center;
            padding: 0 0 16px;
        }
        .participant .label {
            font-size: 7.5px;
            font-weight: 700;
            color: #7B3FA0;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0 0 4px;
        }
        .participant .name {
            font-size: 22px;
            font-weight: 800;
            color: #1d1d1f;
            margin: 0;
        }
        .participant .email {
            font-size: 10px;
            color: #888;
            margin: 2px 0 0;
        }
        /* Info cards */
        .info-grid {
            display: flex;
            gap: 10px;
            margin: 16px 0;
        }
        .info-card {
            flex: 1;
            background: #f8f6f9;
            border-radius: 12px;
            padding: 12px 10px;
            text-align: center;
        }
        .info-card .ico {
            font-size: 16px;
            margin-bottom: 4px;
            display: block;
        }
        .info-card .lbl {
            font-size: 7px;
            font-weight: 700;
            color: #7B3FA0;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 0 0 3px;
        }
        .info-card .val {
            font-size: 14px;
            font-weight: 700;
            color: #1d1d1f;
            margin: 0;
        }
        .info-card .val small {
            font-size: 9px;
            font-weight: 500;
            color: #888;
        }
        .info-card .val.green { color: #2E7D4F; }
        /* ===== Séparateur décoratif ===== */
        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 6px 0 0;
            padding: 0 28px;
        }
        .divider .line {
            flex: 1;
            height: 1px;
            background: repeating-linear-gradient(to right, #ddd 0, #ddd 6px, transparent 6px, transparent 10px);
        }
        .divider .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #7B3FA0;
            flex-shrink: 0;
        }
        /* ===== Bas du billet (QR + infos) ===== */
        .bottom {
            display: flex;
            align-items: stretch;
            gap: 20px;
            padding: 16px 28px 20px;
        }
        .qr-section {
            flex-shrink: 0;
            text-align: center;
            width: 140px;
        }
        .qr-box {
            background: #fff;
            padding: 10px;
            border: 2.5px solid #7B3FA0;
            border-radius: 14px;
            display: inline-block;
        }
        .qr-box img {
            width: 110px;
            height: 110px;
            display: block;
        }
        .qr-label {
            font-size: 7px;
            font-weight: 700;
            color: #7B3FA0;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 6px;
        }
        .qr-code-text {
            display: inline-block;
            background: #7B3FA0;
            padding: 4px 14px;
            border-radius: 20px;
            margin-top: 4px;
        }
        .qr-code-text span {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 1px;
        }
        .details-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 10px;
            background: #f8f6f9;
            border-radius: 8px;
        }
        .detail-row .dl {
            font-size: 8px;
            color: #888;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .detail-row .dv {
            font-size: 10px;
            font-weight: 700;
            color: #1d1d1f;
        }
        /* ===== Footer ===== */
        .footer {
            background: #7B3FA0;
            padding: 12px 28px;
            text-align: center;
        }
        .footer p {
            font-size: 7.5px;
            color: rgba(255,255,255,0.85);
            margin: 0;
            line-height: 1.5;
        }
        .footer strong {
            color: #fff;
            font-weight: 700;
        }
        .footer-stripe {
            height: 4px;
            background: linear-gradient(90deg, #7B3FA0, #2E7D4F, #7B3FA0);
        }
        /* ===== Badge promo ===== */
        .promo-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(46,125,79,0.1);
            color: #2E7D4F;
            font-size: 8px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            margin-top: 6px;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .ticket { box-shadow: none; border: 1px solid #eee; }
        }
    </style>
</head>
<body>
    @php
        $bgImg = $ticket->evenement?->image
            ? Storage::url($ticket->evenement->image)
            : asset('images/image_hero.jpg');
    @endphp

    <div class="ticket">
        {{-- Hero --}}
        <div class="hero">
            <img src="{{ $bgImg }}" alt="">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                @if($ticket->evenement?->categorie)
                    <div class="category">{{ $ticket->evenement->categorie }}</div>
                @endif
                <h1>{{ $ticket->evenement?->titre ?? 'Evenement' }}</h1>
                <div class="hero-meta">
                    <span>&#128197; {{ $ticket->evenement?->date_event?->format('d M Y') ?? '—' }}</span>
                    <span>&#128340; {{ $ticket->evenement?->date_event?->format('H:i') ?? '' }}</span>
                    <span>&#128205; {{ $ticket->evenement?->lieu ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="body">
            {{-- Participant --}}
            <div class="participant">
                <p class="label">Passager / Participant</p>
                <p class="name">{{ $ticket->nom_acheteur ?? '—' }}</p>
                <p class="email">{{ $ticket->email_acheteur ?? '' }}</p>
                @if($ticket->code_promo_utilise)
                    <div class="promo-badge">&#127991; Code promo: {{ $ticket->code_promo_utilise }}</div>
                @endif
            </div>

            {{-- Infos --}}
            <div class="info-grid">
                <div class="info-card">
                    <span class="ico">&#127942;</span>
                    <p class="lbl">Catégorie</p>
                    <p class="val">{{ ucfirst($ticket->categorie ?? '—') }}</p>
                </div>
                <div class="info-card">
                    <span class="ico">&#127915;</span>
                    <p class="lbl">Type</p>
                    <p class="val">{{ $ticket->type === 'normal' ? 'Standard' : 'VIP' }}</p>
                </div>
                <div class="info-card">
                    <span class="ico">&#128178;</span>
                    <p class="lbl">Montant</p>
                    <p class="val">{{ number_format($ticket->montant, 0, ',', ' ') }} <small>FCFA</small></p>
                </div>
                <div class="info-card">
                    <span class="ico">&#9989;</span>
                    <p class="lbl">Statut</p>
                    <p class="val green">Payé</p>
                </div>
            </div>
        </div>

        {{-- Separator --}}
        <div class="divider">
            <span class="line"></span>
            <span class="dot"></span>
            <span class="line"></span>
        </div>

        {{-- Bottom --}}
        <div class="bottom">
            <div class="qr-section">
                <div class="qr-box">
                    <img src="{{ $qrCodeDataUri }}" alt="QR Code">
                </div>
                <p class="qr-label">Scannez ce code</p>
                <div class="qr-code-text">
                    <span>{{ $ticket->code_unique }}</span>
                </div>
            </div>
            <div class="details-section">
                <div class="detail-row">
                    <span class="dl">Transaction</span>
                    <span class="dv">{{ $ticket->transaction_id ? Str::limit($ticket->transaction_id, 20) : '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="dl">Paiement</span>
                    <span class="dv">{{ ucfirst($ticket->methode_paiement ?? '—') }}</span>
                </div>
                <div class="detail-row">
                    <span class="dl">Date d'achat</span>
                    <span class="dv">{{ $ticket->date_achat?->format('d/m/Y H:i') ?? '—' }}</span>
                </div>
                @if($ticket->montant_reduction > 0)
                    <div class="detail-row">
                        <span class="dl">Réduction</span>
                        <span class="dv" style="color: #2E7D4F;">-{{ number_format($ticket->montant_reduction, 0, ',', ' ') }} F</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Footer stripe --}}
        <div class="footer-stripe"></div>

        {{-- Footer --}}
        <div class="footer">
            <p>
                Présentez ce QR code à l'entrée pour scanner votre billet. &bull;
                <strong>Billet personnel et non transférable</strong> &bull;
                PassEvent &copy; UPAO
            </p>
        </div>
    </div>
</body>
</html>
