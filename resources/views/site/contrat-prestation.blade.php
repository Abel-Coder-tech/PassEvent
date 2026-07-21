<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrat de prestation — PaxEvent</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #1d1d1f;
            margin: 32px;
        }
        h3 {
            font-size: 14px;
            margin-top: 20px;
            margin-bottom: 8px;
        }
        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 16px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .header h2 {
            font-size: 16px;
            margin: 0;
        }
        .header p {
            font-size: 11px;
            color: #888;
            margin: 4px 0 0;
        }
        .parties {
            margin: 16px 0;
        }
        .parties strong {
            display: inline-block;
            margin-top: 8px;
        }
        ul {
            margin: 4px 0 12px;
            padding-left: 20px;
        }
        li {
            margin-bottom: 2px;
        }
        .signature-block {
            margin-top: 32px;
        }
        .signature-line {
            display: inline-block;
            width: 220px;
            border-bottom: 1px solid #333;
            margin-top: 40px;
        }
        .signature-note {
            font-size: 10px;
            color: #888;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>CONTRAT DE PRESTATION DE SERVICE DE BILLETTERIE</h2>
        <p>Entre PaxEvent (Noctam Communication) et l'Organisateur</p>
        <p>Dernière mise à jour : Juillet 2026</p>
    </div>

    <hr>

    <h3>ENTRE LES SOUSSIGNÉS :</h3>

    <div class="parties">
        <strong>Noctam Communication</strong>, immatriculée au RCCM sous le numéro RB/PNO/20 A 13348, représentée par M. AHOUANVOEKE Amos, en sa qualité de Directeur Général, ci-après dénommée « PaxEvent »,
        <br><br>
        D'une part,
        <br><br>
        <strong>ET :</strong><br>
        <strong>{{ $user->nom ?? $user->name ?? 'L\'Organisateur' }}</strong>
        @if($user->type_organisation && $user->type_organisation !== 'particulier')
            <br>{{ $user->type_organisation }}
        @endif
        @if($user->ifu)
            <br>IFU : {{ $user->ifu }}
        @endif
        @if($user->rccm)
            <br>RCCM : {{ $user->rccm }}
        @endif
        @if($user->adresse)
            <br>{{ $user->adresse }}
        @endif
        <br>Email : {{ $user->email }}
        <br>Téléphone : {{ $user->telephone ?? $user->phone ?? '' }}
        <br><br>
        Ci-après dénommé « l'Organisateur »,
        <br><br>
        D'autre part.
        <br><br>
        <strong>Les deux ensemble dénommés « les Parties ».</strong>
    </div>

    <hr>

    <h3>Article 1 : Objet du contrat</h3>
    <p>Le présent contrat définit les conditions dans lesquelles PaxEvent met à disposition de l'Organisateur sa solution technique de billetterie en ligne pour collecter les paiements des e-tickets par Mobile Money et cartes bancaires via ses agrégateurs de paiement partenaires FedaPay ou KkiaPay.</p>

    <h3>Article 2 : Nature des tickets</h3>
    <p>L'Organisateur accepte expressément que les e-tickets générés par PaxEvent sont au porteur et transférables. Le contrôle s'effectue par scan unique du QR code. La première personne présentant le code est présumée détenir légitimement l'accès.</p>

    <h3>Article 3 : Commissions et frais de reversement</h3>
    <p><strong>Commission de vente :</strong> PaxEvent prélève une commission de 10% par ticket vendu, convenu lors de la configuration en ligne de l'événement.</p>
    <p><strong>Frais de reversement (PAYOUT) :</strong> Les demandes de reversement des avoirs de l'Organisateur vers son compte Mobile Money s'effectuent depuis son interface d'administration. Conformément au barème technique des agrégateurs partenaires, des frais de transaction s'appliquent selon les tranches suivantes :</p>
    <ul>
        <li>Reversement vers mêmes opérateurs mobile (Ex: MTN Benin vers MTN Benin) : 0 FCFA</li>
        <li>Reversement vers opérateur mobile différent</li>
        <ul>
            <li>De 0 à 10 000 XOF : 150 XOF</li>
            <li>De 10 001 à 50 000 XOF : 300 XOF</li>
            <li>De 50 001 à 150 000 XOF : 800 XOF</li>
            <li>De 150 001 à 500 000 XOF : 2 000 XOF</li>
            <li>Plus de 500 001 XOF : 2 500 XOF</li>
        </ul>
    </ul>

    <h3>Article 4 : Annulation et responsabilité financière</h3>
    <p>L'Organisateur est le seul responsable juridique et financier de la tenue de son événement. En cas d'annulation ou de report, l'Organisateur a l'obligation légale de rembourser l'intégralité des 90% de la valeur faciale des billets aux acheteurs. Les commissions initiales perçues par PaxEvent restent acquises à la plateforme pour service informatique rendu.</p>

    <h3>Article 5 : Clause de bannissement</h3>
    <p>En cas de fraude avérée, de publication d'événement fictif, ou de non-respect de la législation béninoise sur le numérique, PaxEvent résiliera immédiatement ce contrat, procédera au bannissement définitif du compte de l'Organisateur et au gel conservatoire de ses fonds pendant 90 jours.</p>

    <h3>Article 6 : Loi applicable et attribution de compétence</h3>
    <p>Le présent contrat est régi par le droit béninois. Tout litige persistant après une tentative de conciliation amiable sera soumis à la compétence exclusive du tribunal de commerce.</p>

    <hr>

    <div class="signature-block">
        <p>Date : {{ now()->format('d/m/Y') }}</p>

        <table style="width:100%; margin-top: 20px;">
            <tr>
                <td style="width:50%;">
                    <strong>Pour PaxEvent</strong><br>
                    <div class="signature-line"></div>
                    <p class="signature-note">M. AHOUANVOEKE Amos<br>Directeur Général</p>
                </td>
                <td style="width:50%;">
                    <strong>Pour l'Organisateur</strong><br>
                    <div class="signature-line"></div>
                    <p class="signature-note">{{ $user->nom ?? $user->name ?? 'L\'Organisateur' }}</p>
                </td>
            </tr>
        </table>

        <p style="font-size: 10px; color: #888; margin-top: 24px;">
            Contrat réputé signé électroniquement par l'Organisateur dès la validation de son compte et de la publication de son premier événement sur PaxEvent.
        </p>
    </div>
</body>
</html>
