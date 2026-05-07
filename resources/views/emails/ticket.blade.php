<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre billet PassEvent</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1d1d1f;
            margin: 0;
            padding: 0;
            background-color: #f5f5f7;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #6d4e72, #34495e);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .header p {
            margin: 8px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 40px;
        }
        .event-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .event-details h2 {
            margin: 0 0 15px;
            font-size: 18px;
            color: #6d4e72;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #6c757d;
            font-weight: 500;
        }
        .detail-value {
            font-weight: 600;
            color: #1d1d1f;
        }
        .download-info {
            background: #e8f5e9;
            border-left: 4px solid #2e7d32;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        .download-info h3 {
            margin: 0 0 8px;
            color: #2e7d32;
            font-size: 16px;
        }
        .download-info p {
            margin: 0;
            color: #555;
            font-size: 14px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px 40px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
        .footer a {
            color: #6d4e72;
            text-decoration: none;
        }
        .btn {
            display: inline-block;
            background: #6d4e72;
            color: white !important;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 15px 0;
            text-align: center;
        }
        .qr-info {
            text-align: center;
            padding: 20px;
            background: #fafafa;
            border-radius: 8px;
            margin: 20px 0;
        }
        .qr-info i {
            font-size: 48px;
            color: #6d4e72;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PassEvent</h1>
            <p>Votre billet est pret!</p>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $ticket->nom_acheteur }}</strong>,</p>

            <p>Votre paiement a ete confirme avec succes. Voici les details de votre evenement :</p>

            <div class="event-details">
                <h2>{{ $ticket->evenement->titre }}</h2>
                <div class="detail-row">
                    <span class="detail-label">Date et heure</span>
                    <span class="detail-value">{{ $ticket->evenement->date_event->format('d/m/Y \a H:i') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Lieu</span>
                    <span class="detail-value">{{ $ticket->evenement->lieu }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Type de billet</span>
                    <span class="detail-value">{{ ucfirst($ticket->type) }} - {{ $ticket->categorie }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Code unique</span>
                    <span class="detail-value">{{ $ticket->code_unique }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Montant paye</span>
                    <span class="detail-value" style="color: #2e7d32;">{{ number_format($ticket->montant, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>

            <div class="download-info">
                <h3><i class="bi bi-envelope-paper"></i> Billet PDF attache</h3>
                <p>Votre billet PDF est attache a cet email. Imprimez-le ou presentez-le sur votre telephone le jour de l'evenement. Le QR code sera scanne pour valider votre acces.</p>
            </div>

            <div class="qr-info">
                <p><strong>Presentez ce billet a l'entree</strong></p>
                <p style="font-size: 14px; color: #6c757d;">Le QR code contenu dans le PDF est unique et personnel. Ne le partagez pas.</p>
            </div>

            <div style="text-align: center;">
                <a href="{{ route('tickets.telecharger', $ticket->id) }}" class="btn">Telecharger mon billet</a>
            </div>

            <p style="font-size: 14px; color: #6c757d; margin-top: 25px;">
                En cas de probleme, contactez-nous a <a href="mailto:passevent2026@gmail.com">passevent2026@gmail.com</a> avec votre reference <strong>{{ $ticket->transaction_id }}</strong>.
            </p>
        </div>

        <div class="footer">
            <p>PassEvent - Billetterie intelligente pour vos evenements au Benin</p>
            <p><a href="{{ url('/aide') }}">Centre d'aide</a> | <a href="{{ url('/contact') }}">Contact</a></p>
        </div>
    </div>
</body>
</html>
