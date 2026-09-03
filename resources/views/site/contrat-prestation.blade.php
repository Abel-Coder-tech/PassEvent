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
            margin: 28px;
        }
        h3 {
            font-size: 13px;
            margin-top: 16px;
            margin-bottom: 6px;
        }
        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 14px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            font-size: 15px;
            margin: 0;
        }
        .header p {
            font-size: 11px;
            color: #888;
            margin: 4px 0 0;
        }
        .parties {
            margin: 12px 0;
        }
        .parties p {
            margin: 0 0 8px;
        }
        .parties strong {
            /* inline to keep "Noctam Communication" and "ET :" on the same line as the sentence */
        }
        ul {
            margin: 4px 0 12px;
            padding-left: 20px;
        }
        li {
            margin-bottom: 2px;
        }
        .signature-block {
            margin-top: 28px;
        }
        .signature-line {
            display: inline-block;
            width: 220px;
            border-bottom: 1px solid #333;
            margin-top: 36px;
        }
        .signature-note {
            font-size: 10px;
            color: #888;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    @php
        $estPersonneMorale = ($user->type ?? '') === 'organisation';
        $estAssociation = $estPersonneMorale && ($user->type_detail ?? '') === 'association';

        $nomComplet = trim(($user->prenom ?? '') . ' ' . ($user->nom ?? ''));
        if (trim($nomComplet) === '') { $nomComplet = $user->nom ?? $user->email ?? 'L\'Organisateur'; }

        // Dénomination / identité principale selon le type
        $denomination = $estPersonneMorale
            ? ($user->organisation ?: $nomComplet)
            : $nomComplet;

        // Libellé et numéro d'immatriculation
        if ($estAssociation) {
            $pieceLibelle = 'récépissé d\'immatriculation';
            $pieceNumero = $user->numero_rc ?: null;
        } elseif ($estPersonneMorale) {
            $pieceLibelle = 'Registre de Commerce (RC)';
            $pieceNumero = $user->numero_rc ?: null;
        } elseif (($user->type ?? '') === 'universitaire') {
            $pieceLibelle = 'carte d\'étudiant';
            $pieceNumero = $user->numero_cip ?: null;
        } else { // particulier
            $pieceLibelle = 'carte CIP';
            $pieceNumero = $user->numero_cip ?: null;
        }

        $nomRepresentant = ($estPersonneMorale || ($user->type ?? '') === 'universitaire')
            ? ($user->nom_representant ?: $nomComplet)
            : null;
        $fonctionRepresentant = $user->fonction ?? null;
        $cipRepresentant = $estPersonneMorale
            ? ($user->numero_cip ?: null)
            : null;

        $partieRepresentant = '';
        if ($nomRepresentant && ($estPersonneMorale || ($user->type ?? '') === 'universitaire')) {
            $partieRepresentant = ', représenté(e) par ' . $nomRepresentant;
            if ($fonctionRepresentant) {
                $partieRepresentant .= ', en qualité de ' . $fonctionRepresentant;
            }
            if ($cipRepresentant) {
                $partieRepresentant .= ' (CIP n° ' . $cipRepresentant . ')';
            }
        }
    @endphp

    <div class="header">
        <h2>CONTRAT DE PRESTATION</h2>
        <p>Entre PaxEvent (Noctam Communication) et l'Organisateur</p>
        <p>(Document téléchargeable par l'Organisateur depuis son tableau de bord)</p>
    </div>

    <hr>

    <h3>ENTRE LES SOUSSIGNÉS :</h3>

    <div class="parties">
        <p>
            <strong>Noctam Communication</strong>, immatriculée au RCCM sous le numéro <strong>RB/PNO/20 A 13348</strong>, dont le siège social est situé à Porto-Novo, Oganla Attakpamè, M/MARTIN, représentée par <strong>M. AHOUANVOEKE Amos </strong>, en sa qualité de Directeur Général, ci-après dénommée « <strong>PaxEvent</strong> », d'une part.
        </p>
        <p>
            <strong>ET :</strong> {{ $denomination }}@if($pieceNumero), immatriculé(e) sous le numéro de <strong>{{ $pieceLibelle }}</strong> : <strong>{{ $pieceNumero }}</strong>@endif{{ $partieRepresentant }}, Email : {{ $user->email }}, Téléphone : {{ $user->telephone ?? $user->phone ?? '' }}, ci-après désigné « <strong>L'Organisateur</strong> », d'autre part.
        </p>
        <p><strong>Les deux ensemble dénommés « les Parties » conviennent de ce qui suit :</strong></p>
    </div>

    <hr>

    <h3>Article 1 : Objet du contrat</h3>
    <p>Le présent contrat définit les conditions dans lesquelles PaxEvent met à disposition de l'Organisateur sa solution technique de billetterie en ligne pour vendre et collecter les paiements des e-tickets dans le cadre de ses événements et contrôler les accès par génération de codes QR uniques.</p>

    <h3>Article 2 : Description des prestations</h3>
    <p>PaxEvent met à disposition de l'Organisateur sa solution technologique comprenant :</p>
    <ul>
        <li>La création et le paramétrage des événements gratuitement sur la plateforme.</li>
        <li>Le traitement sécurisé des paiements par Mobile Money / carte bancaire via des agrégateurs de paiement partenaires FedaPay et/ou KkiaPay.</li>
        <li>La génération et l'envoi automatisé des billets électroniques (e-tickets) aux acheteurs.</li>
        <li>La mise à disposition d'une interface de suivi des ventes en temps réel.</li>
        <li>L'outil nécessaire pour la vente de tickets manuels et de contrôle d'accès le jour de l'événement.</li>
    </ul>

    <h3>Article 3 : Nature des tickets</h3>
    <p>L'Organisateur accepte expressément que les e-tickets générés par PaxEvent comportent un Code Pass et un QR Code unique au porteur, et sont transférables. Le contrôle s'effectue par saisie manuelle du code pass ou par scan du QR code depuis les interfaces dédiées. La première personne présentant le code est présumée détenir légitimement l'accès.</p>

    <h3>Article 4 : Commissions et Reversement des recettes</h3>
    <p><strong>Commission de vente :</strong></p>
    <ul>
        <li>PaxEvent prélève une commission de 10% par ticket vendu, convenue lors de la configuration en ligne de l'événement.</li>
        <li>PaxEvent prélève une commission de 5% par génération de QR codes à apposer sur les billets imprimés.</li>
    </ul>
    <p><strong>Reversement des recettes :</strong></p>
    <ul>
        <li><strong>Retrait standard :</strong> Les recettes nettes issues des ventes de billets (déduction faite des commissions de PaxEvent) sont versées à l'Organisateur dans un délai de 24h à 72h après la tenue de l'événement.</li>
        <li><strong>Retrait anticipé :</strong> L'organisateur peut demander un retrait des fonds disponibles avant l'événement (traitement sous 4 jours ouvrés), sous réserve d'un solde minimum disponible de 1 000 FCFA.</li>
    </ul>
    <p><strong>Frais de reversement (PAYOUT) :</strong> Les demandes de reversement des avoirs de l'Organisateur vers son compte Mobile Money s'effectuent depuis son interface d'administration. Conformément au barème technique des agrégateurs partenaires, des frais de transaction s'appliquent selon les tranches suivantes :</p>
    <ul>
        <li>Reversement vers mêmes opérateurs mobiles (Ex : MTN Benin vers MTN Benin) : 0 FCFA</li>
        <li>Reversement vers opérateurs mobiles différents :
            <ul>
                <li>De 0 à 10 000 XOF : 150 XOF</li>
                <li>De 10 001 à 50 000 XOF : 300 XOF</li>
                <li>De 50 001 à 150 000 XOF : 800 XOF</li>
                <li>De 150 001 à 500 000 XOF : 2 000 XOF</li>
                <li>Plus de 500 001 XOF : 2 500 XOF</li>
            </ul>
        </li>
    </ul>

    <h3>Article 5 : Engagement de PaxEvent</h3>
    <p>PaxEvent s'engage à assister l'Organisateur dans l'utilisation du service en lui fournissant les appuis et conseils techniques nécessaires. Il s'engage à l'accompagner pour une offre sur mesure dans la mesure du possible.</p>

    <h3>Article 6 : Engagement de l'Organisateur</h3>
    <p>L'Organisateur s'engage à :</p>
    <ul>
        <li>Détenir l'ensemble des autorisations administratives, droits d'auteur et assurances nécessaires à la tenue de l'événement.</li>
        <li>Fournir à PaxEvent des informations exactes concernant la programmation, les tarifs, les jauges et les conditions d'accès.</li>
        <li>Donner accès libre à toutes les informations pouvant contribuer à la bonne marche et à l'utilisation du service.</li>
        <li>Respecter le contrat intitulé « Conditions Générales d'Utilisation ».</li>
    </ul>

    <h3>Article 7 : Modification, Annulation de l'événement et Responsabilité financière</h3>
    <p><strong>7.1. Notification</strong> — En cas d'annulation, de report ou de modification majeure de l'événement, l'Organisateur s'engage à en informer PaxEvent par écrit sans délai. Dès réception de cette notification, PaxEvent procédera à la suspension immédiate des ventes de billets.</p>
    <p><strong>7.2. Modalités de remboursement</strong> — Les remboursements sont exécutés par PaxEvent pour le compte de l'Organisateur, en utilisant le montant disponible issu de la billetterie de l'événement.</p>
    <ul>
        <li>Si le montant total des ventes conservé par PaxEvent est suffisant, elle recréditera directement les acheteurs sur leur moyen de paiement d'origine.</li>
        <li>Si tout ou partie des recettes a déjà été versé à l'Organisateur, ce dernier s'engage à recréditer le compte de PaxEvent du montant nécessaire sous un délai de cinq (5) jours ouvrés à compter de la notification.</li>
    </ul>
    <p><strong>7.3. Transmission des coordonnées en cas de litige ou de défaillance</strong> — En cas d'annulation de l'événement suivie d'un refus de coopération, d'un défaut d'approvisionnement des fonds ou d'une défaillance de l'Organisateur empêchant PaxEvent d'exécuter le remboursement automatique :</p>
    <ul>
        <li>PaxEvent informera les Acheteurs par courrier électronique du statut du dossier.</li>
        <li>Conformément aux dispositions légales et aux règles de protection des données, PaxEvent sera pleinement autorisée à transmettre aux Acheteurs lésés (ou à leurs représentants légaux) les coordonnées officielles et complètes de l'Organisateur (Nom/Raison Sociale, adresse du siège ou domicile, e-mail et contact téléphonique) afin de leur permettre d'exercer directement leurs recours à son encontre.</li>
    </ul>
    <p><strong>7.4. Frais de service et commissions</strong> — Les commissions et frais de gestion dus à PaxEvent au titre de la vente initiale restent intégralement acquis à cette dernière, la prestation technique d'émission et de gestion de la transaction ayant été exécutée.</p>

    <h3>Article 8 : Clause de bannissement</h3>
    <p>En cas de fraude avérée, de publication d'événement fictif, ou de non-respect de la législation béninoise sur le numérique, PaxEvent résiliera immédiatement ce contrat, procédera au bannissement définitif du compte de l'Organisateur et au gel conservatoire de ses fonds pendant 90 jours.</p>

    <h3>Article 9 : Données à caractère personnel</h3>
    <p>Conformément à la réglementation en vigueur (RGPD), PaxEvent traite les données des acheteurs pour le compte de l'Organisateur. Les Parties s'engagent à préserver la confidentialité des données collectées.</p>

    <h3>Article 10 : Durée</h3>
    <p>Le présent contrat est conclu pour une durée déterminée à compter de sa signature et prendra fin après la clôture de l'événement et le solde complet des comptes. En cas de manquement grave de l'une des Parties à ses obligations, le contrat pourra être résilié de plein droit 48 heures après une mise en demeure restée sans effet.</p>

    <h3>Article 11 : Règlement des litiges</h3>
    <p>Le présent contrat est régi par le Droit Béninois. Tout litige persistant après une tentative de conciliation amiable sera soumis à la compétence exclusive du tribunal de commerce.</p>

    <h3>Article 12 : Entrée en vigueur</h3>
    <p>Le présent contrat est réputé signé électroniquement par l'Organisateur dès la validation de son compte et de la publication de son premier événement sur PaxEvent.</p>

    <hr>

    <div class="signature-block">
        <p>Date : {{ now()->format('d/m/Y') }}</p>

        <table style="width:100%; margin-top: 20px;">
            <tr>
                <td style="width:50%;">
                    <strong>Pour PaxEvent</strong><br>
                    <div class="signature-line"></div>
                    <p class="signature-note">Amos AHOUANVOEKE<br>Directeur Général</p>
                </td>
                <td style="width:50%;">
                    <strong>L'Organisateur</strong><br>
                    <div class="signature-line"></div>
                    <p class="signature-note">{{ $denomination }}</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>