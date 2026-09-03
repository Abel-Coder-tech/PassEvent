<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Contrat de prestation — PaxEvent</title>
    <style>
        @page {
            margin: 16mm 14mm 24mm 14mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.55;
            color: #1d1d1f;
        }
        h3 {
            font-size: 12px;
            margin: 12px 0 5px;
        }
        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 12px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 14px;
        }
        .header h2 {
            font-size: 15px;
            margin: 0;
            letter-spacing: 1px;
        }
        .header p {
            font-size: 10.5px;
            color: #888;
            margin: 3px 0 0;
        }
        .parties {
            margin: 10px 0;
        }
        .parties p {
            margin: 0 0 8px;
        }
        ul {
            margin: 4px 0 10px;
            padding-left: 18px;
        }
        li {
            margin-bottom: 2px;
        }
        .bloc-def {
            margin: 4px 0 10px;
        }
        .bloc-def li {
            margin-bottom: 4px;
        }
        .signature-block {
            margin-top: 24px;
        }
        table.signatures {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table.signatures td {
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }
        .signature-line {
            display: inline-block;
            width: 200px;
            border-bottom: 1px solid #333;
            margin-top: 30px;
        }
        .signature-note {
            font-size: 9.5px;
            color: #666;
            margin-top: 3px;
        }
        .footer {
            position: fixed;
            bottom: -18mm;
            left: 0;
            right: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #bbb;
            padding-top: 4px;
            line-height: 1.4;
        }
        .footer strong {
            color: #444;
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
        $pieceNumero = $user->numero_rc ?: ($user->numero_cip ?: null);
        $partiePiece = '';
        if ($estAssociation && $pieceNumero) {
            $pieceLibelle = 'récépissé d\'immatriculation';
            $partiePiece = ', immatriculée sous le numéro du <strong>' . $pieceLibelle . '</strong> : <strong>' . $pieceNumero . '</strong>';
        } elseif ($estPersonneMorale && $pieceNumero) {
            $pieceLibelle = 'Registre de Commerce (RC)';
            $partiePiece = ', immatriculé(e) sous le numéro du <strong>' . $pieceLibelle . '</strong> : <strong>' . $pieceNumero . '</strong>';
        } elseif (($user->type ?? '') === 'universitaire' && $pieceNumero) {
            $pieceLibelle = 'carte d\'étudiant';
            $partiePiece = ', détenteur(trice) de la <strong>' . $pieceLibelle . '</strong> n° <strong>' . $pieceNumero . '</strong>';
        } elseif (($user->type ?? '') === 'particulier' && $pieceNumero) {
            $pieceLibelle = 'carte CIP';
            $partiePiece = ', détenteur(trice) de la <strong>' . $pieceLibelle . '</strong> n° <strong>' . $pieceNumero . '</strong>';
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
                $partieRepresentant .= ', détenteur(trice) de la carte CIP n° ' . $cipRepresentant;
            }
        }
    @endphp

    <div class="header">
        <h2>CONTRAT DE PRESTATION DE SERVICE</h2>
        <p>Ce document officiel est téléchargeable par l'Organisateur depuis son tableau de bord</p>
    </div>

    <hr>

    <div class="parties">
        <h3>ENTRE</h3>
        <p>
            <strong>{{ $denomination }}</strong>{{ $partiePiece }}{{ $partieRepresentant }}, agissant en sa qualité de {{ $fonctionRepresentant ?: 'représentant dûment habilité' }} à l'effet des présentes.
        </p>
        <p>Ci-après dénommé le « <strong>Organisateur ou Client</strong> » d'une part.</p>

        <p><strong>ET</strong></p>

        <p>
            <strong>NOCTAM COMMUNICATION</strong> (éditeur de PAXEVENT), sise à Oganla Atakpamé C/12, M/MARTIN, Porto-Novo Bénin, immatriculée auprès du Registre du Commerce et du Crédit Mobilier sous le N° <strong>RB/PNO/20 A 13348</strong> et représentée par <strong>Amos AHOUANVOEKE</strong>, agissant en sa qualité de Directeur Général, dûment habilité à l'effet des présentes.
        </p>
        <p>Ci-après désignée le « <strong>PaxEvent ou Prestataire</strong> », d'autre part.</p>

        <p>Les deux étant collectivement désignées « les <strong>Parties</strong> » et individuellement « une <strong>Partie</strong> ».</p>
    </div>

    <h3>PRÉAMBULE :</h3>
    <ul>
        <li>Etant entendu que le client exerce régulièrement une activité génératrice de revenu,</li>
        <li>Etant entendu que le Prestataire dispose des compétences et aptitudes à fournir un service de billetterie fiable et sécurisé,</li>
    </ul>
    <p>Les parties ont donc convenu et arrêté ce qui suit :</p>

    <h3>DÉFINITIONS :</h3>
    <div class="bloc-def">
        <ul>
            <li>« <strong>Prestataire</strong> » désigne celui qui doit fournir une prestation.</li>
            <li>« <strong>Client</strong> » désigne la personne physique ou morale qui reçoit d'une entreprise des produits ou services moyennant une contrepartie.</li>
            <li>« <strong>Service</strong> » désigne un ensemble d'activités ou de prestations fournies par le prestataire au client.</li>
            <li>« <strong>Utilisateur</strong> » désigne la personne qui exploite la solution logicielle du client.</li>
            <li>« <strong>NOCTAM COMMUNICATION</strong> » désigne l'entreprise qui gère la billetterie en ligne.</li>
            <li>« <strong>PAXEVENT</strong> » désigne la billetterie en ligne dans toute son entièreté.</li>
        </ul>
    </div>

    <h3>ARTICLE 1 : OBJET DU CONTRAT</h3>
    <p>Le présent contrat définit les conditions dans lesquelles PaxEvent met à disposition de l'Organisateur son service de billetterie en ligne fiable et sécurisé dans le cadre de ses activités.</p>

    <h3>ARTICLE 2 : FONCTIONNEMENT DU SERVICE</h3>
    <p>Le mode de fonctionnement de PaxEvent est consigné dans la documentation qui est à consulter sur : <strong>https://paxevent.com/aide</strong></p>

    <h3>ARTICLE 3 : MOYEN DE PAIEMENT DISPONIBLE</h3>
    <p>Le paiement des tickets sur PaxEvent est traité de façon sécurisée :</p>
    <ul>
        <li>Via Mobile Money / carte bancaire grâce à nos agrégateurs de paiement partenaires (FedaPay et/ou KkiaPay).</li>
        <li>Et en espèces grâce aux ventes manuelles de tickets effectuées par l'organisateur et ses agents via la plateforme.</li>
    </ul>

    <h3>ARTICLE 4 : DESCRIPTION DES PRESTATIONS</h3>
    <p>PaxEvent met à disposition de l'Organisateur son service de billetterie en ligne comprenant :</p>
    <ul>
        <li>La création de compte organisateur et la publication des événements, gratuitement.</li>
        <li>L'achat rapide et sécurisé des e-tickets pour les participants.</li>
        <li>La génération et l'envoi instantané des e-tickets (PDF) dans l'email des acheteurs/participants.</li>
        <li>La génération de tickets physiques destinés à l'impression. Il s'agit des planches de QR codes bruts ou des QR codes apposés sur des templates de tickets fournis par l'organisateur.</li>
        <li>Une interface complète de suivi des opérations en temps réel pour l'organisateur.</li>
        <li>L'attribution des rôles « Agent de vente » et « Agents de scan » avec espace de gestion dédié.</li>
        <li>La vente manuelle de tickets et le contrôle des accès par scanning le jour de l'événement.</li>
    </ul>

    <h3>ARTICLE 5 : NATURE DES TICKETS</h3>
    <p>Tous les tickets générés par PaxEvent sont électroniques mais aussi physiques (destinés à l'impression). Ils sont dotés d'un QR Code avec un Code Pass, infalsifiables, unique au porteur et transférable. C'est-à-dire que la première personne à présenter un ticket valide à l'entrée en est le propriétaire légitimement. Le contrôle des tickets s'effectue par scan du QR Code ou par saisie manuelle du Code Pass via des interfaces dédiées.</p>

    <h3>ARTICLE 6 : COMMISSIONS</h3>
    <ul>
        <li>Pour chaque e-ticket vendu, PaxEvent prélève des frais de commission d'un taux de <strong>10%</strong> de la valeur nominale du ticket ;</li>
        <li>Pour chaque ticket physique généré, PaxEvent prélève des frais de commission d'un taux de <strong>5%</strong> de la valeur nominale du ticket.</li>
    </ul>

    <h3>ARTICLE 7 : RETRAIT DES AVOIRS</h3>
    <ul>
        <li><strong>Retrait standard :</strong> Les recettes nettes issues des ventes de tickets (déduction faite des commissions de PaxEvent) sont versées à l'Organisateur dans un délai de 24h à 72h après la tenue de l'événement.</li>
        <li><strong>Retrait anticipé :</strong> L'organisateur peut demander un retrait des fonds disponibles avant la tenue de l'événement, avec un délai de traitement maximal de 3 jours ouvrés. Le solde minimum disponible autorisé est de 1 000 FCFA.</li>
        <li><strong>Frais de retrait (PAYOUT) :</strong> Les demandes de reversement des avoirs de l'Organisateur vers son compte Mobile Money s'effectuent depuis son interface d'administration. Conformément au barème technique des agrégateurs partenaires, des frais de transaction s'appliquent selon les tranches et/ou conditions suivantes :
            <ul>
                <li>Tout retrait de fonds d'une balance mobile money vers un compte mobile money du même opérateur n'est assujetti à aucun frais (Ex : Balance MTN Benin vers N° MTN Benin) : <strong>0 FCFA</strong>.</li>
                <li>Tout retrait de fonds d'une balance mobile money vers un compte mobile money d'opérateurs différents est assujetti aux frais de transferts locaux et internationaux des opérateurs (Ex : Balance MTN Benin vers N° Moov Benin) : <strong>FRAIS HABITUELS</strong>.</li>
            </ul>
        </li>
    </ul>

    <h3>ARTICLE 8 : ENGAGEMENT DE PAXEVENT</h3>
    <p>PaxEvent s'engage à assister l'Organisateur dans l'utilisation du service en lui fournissant des appuis et conseils techniques nécessaires. Il s'engage à l'accompagner pour une offre sur mesure dans la mesure du possible.</p>

    <h3>ARTICLE 9 : ENGAGEMENT DE L'ORGANISATEUR</h3>
    <p>L'Organisateur s'engage à :</p>
    <ul>
        <li>Donner libre accès à toutes informations pouvant contribuer à la bonne marche et à l'utilisation du service.</li>
        <li>Respecter les termes des documents intitulés « Conditions Générales d'Utilisation », « Conditions Générales de vente » et la « Politique de Confidentialité » disponibles sur le site internet de PaxEvent.</li>
    </ul>

    <h3>ARTICLE 10 : DROITS ET LIMITATIONS</h3>
    <p>PaxEvent conserve l'exclusivité de tous les droits, titres et profits liés à sa solution.</p>
    <p>Il est strictement interdit à l'organisateur (ainsi qu'à ses employés, clients ou tiers) de :</p>
    <ul>
        <li>Copier, traduire, modifier, paramétrer, décompiler ou rétro-concevoir PaxEvent pour créer des œuvres dérivées.</li>
        <li>Sous-traiter, transférer ou mettre la solution (ou ses copies) à disposition d'un tiers.</li>
    </ul>
    <p>Pour accorder un accès à une entité liée (filiale ou groupe), l'organisateur doit adresser une demande écrite à PaxEvent, qui proposera un contrat direct à cette structure.</p>
    <p>L'organisateur désigne un référent technique unique, chargé de la gestion informatique du compte PaxEvent, de ses supports et de sa documentation.</p>

    <h3>ARTICLE 11 : OBLIGATION DE CONFIDENTIALITÉ</h3>
    <p>PaxEvent garantit la stricte confidentialité des données liées au contrat, y compris vis-à-vis de ses salariés. Seules font exception les informations déjà publiques, préalablement connues ou légalement transmises par un tiers.</p>

    <h3>ARTICLE 12 : CERTIFICATIONS ET ENREGISTREMENT</h3>
    <p>Le site www.paxevent.com répond aux exigences de l'Autorité de Protection des Données Personnelles du Bénin pour le traitement des données personnelles de ses utilisateurs.</p>
    <p>Il dispose également d'une connexion chiffrée via un certificat SSL à jour. La plateforme de billetterie dispose de plusieurs instances hébergées sur des serveurs en Europe avec redondance et backup automatiques des données pour éviter les interruptions de service.</p>
    <p>Toutes les transactions s'effectuent sous authentification sécurisée par nos agrégateurs de paiement (FedaPay / KkiaPay) qui sont conformes à la certification PCI DSS niveau II.</p>

    <h3>ARTICLE 13 : SÉCURITÉ ET PROTECTION DES DONNÉES</h3>
    <p>PaxEvent privilégie des interventions sans interruption de service. En cas de maintenance critique programmée, un préavis de deux (2) semaines est transmis au Client. PaxEvent se réserve le droit de suspendre temporairement l'accès au service pour des raisons techniques ou de sécurité, sans indemnité et dans la stricte mesure du nécessaire.</p>
    <p>Les Parties s'engagent à traiter l'ensemble des données à caractère personnel conformément aux réglementations applicables, notamment la loi n° 2017-20 du 20 avril 2018 portant Code du numérique en République du Bénin.</p>
    <p>PaxEvent applique toutes les mesures nécessaires pour garantir la sécurité, l'intégrité et la confidentialité des données collectées. L'accès à ces données est strictement limité aux seuls collaborateurs dont les fonctions l'exigent.</p>

    <h3>ARTICLE 14 : MODIFICATION, ANNULATION DE L'ÉVÉNEMENT ET RESPONSABILITÉ FINANCIÈRE</h3>
    <p><strong>Notification :</strong> En cas d'annulation, de report ou de modification majeure de l'événement, l'Organisateur s'engage à en informer PaxEvent par écrit sans délai. Dès réception de cette notification, PaxEvent procédera à la suspension immédiate des ventes de billets.</p>
    <p><strong>Modalités de remboursement :</strong> Les remboursements sont exécutés automatiquement par PaxEvent pour le compte de l'Organisateur, à la hauteur du solde disponible pour l'événement.</p>
    <ul>
        <li>Si le solde disponible pour l'événement est suffisant, PaxEvent rembourse directement les acheteurs sur leur moyen de paiement d'origine.</li>
        <li>Si les recettes ou une partie ont déjà été versées à l'Organisateur, ce dernier dispose de 5 jours ouvrés après notification pour créditer le compte des acheteurs en attente de remboursement à la hauteur des montants retirés.</li>
    </ul>
    <p><strong>Transmission des coordonnées en cas de litige ou de défaillance :</strong> En cas de remboursement automatique impossible suite à l'annulation d'un événement du fait de l'Organisateur (manque de fonds, défaillance, non-coopération) :</p>
    <ul>
        <li>PaxEvent informe les acheteurs par e-mail.</li>
        <li>PaxEvent transmet les coordonnées officielles de l'Organisateur (nom, adresse, e-mail, téléphone) aux acheteurs pour faciliter leurs recours directs.</li>
    </ul>
    <p><strong>Frais de commissions :</strong> Les frais de commission issus des tickets vendus et générés par PaxEvent restent intégralement acquis et ne peuvent, en aucun cas, faire l'objet de remboursement.</p>

    <h3>ARTICLE 15 : CLAUSE DE BANNISSEMENT</h3>
    <p>En cas de fraude avérée, de publication d'événement fictif, ou de non-respect de la législation béninoise sur le numérique, PaxEvent résiliera immédiatement ce contrat, procédera au bannissement définitif du compte de l'Organisateur et au gel conservatoire de ses fonds pendant 90 jours.</p>

    <h3>ARTICLE 16 : RESPONSABILITÉS ET GARANTIES</h3>
    <p>Le Prestataire garantit la continuité du service et la sécurité des fonds du Client jusqu'à leur versement sur son compte bancaire.</p>
    <p>PaxEvent ne traitant pas directement les transactions, les délais d'exécution des agrégateurs partenaires peuvent s'allonger de sept (7) jours maximum.</p>
    <p>La responsabilité du Prestataire en cas de piratage ne couvre ni les attaques par phishing, ni la négligence du Client quant à la sécurité de ses identifiants et terminaux.</p>

    <h3>ARTICLE 17 : PUBLICITÉS</h3>
    <p>Par le présent, le Client accorde au Prestataire les autorisations d'utiliser son nom et son logo dans les documents de marketing, y compris sur le site Web de PaxEvent, dans les listes des organisateurs, dans les interviews et dans les communiqués de presse, sans s'y limiter.</p>

    <h3>ARTICLE 18 : FORCE MAJEURE</h3>
    <p>Les Parties ne seront pas tenues pour responsables, ou considérées comme ayant failli au titre des présentes, en cas de retard ou inexécution, lorsque leur cause est liée à un cas de force majeure tel que défini par le droit positif béninois.</p>

    <h3>ARTICLE 19 : DURÉE</h3>
    <p>Ce contrat est à durée indéterminée. L'Organisateur peut suspendre son utilisation de PaxEvent ou y mettre fin définitivement à tout moment. PaxEvent ne peut résilier le contrat sans restituer les avoirs de l'Organisateur, sauf en cas de mesure judiciaire (acte d'huissier, réquisition ou ordonnance) ordonnant leur blocage.</p>

    <h3>ARTICLE 20 : RÈGLEMENT DES LITIGES</h3>
    <p>Le présent contrat est régi par le Droit Béninois. Tout litige persistant après une tentative de conciliation amiable sera soumis à la compétence exclusive du tribunal de commerce.</p>

    <h3>ARTICLE 21 : ENTRÉE EN VIGUEUR</h3>
    <p>Le présent contrat est réputé signé électroniquement par l'Organisateur dès la validation de son compte sur PaxEvent.</p>

    <hr>

    <div class="signature-block">
        <p>Fait à Porto-Novo le {{ now()->format('d/m/Y') }}</p>

        <table class="signatures">
            <tr>
                <td>
                    <strong>Pour PaxEvent</strong><br>
                    <div class="signature-line"></div>
                    <p class="signature-note">Amos AHOUANVOEKE<br>Directeur Général</p>
                </td>
                <td>
                    <strong>L'Organisateur</strong><br>
                    <div class="signature-line"></div>
                    <p class="signature-note">{{ $denomination }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        NOCTAM COMMUNICATION &nbsp;–&nbsp; N°RCCM : RB/PNO/20 A 13348 &nbsp;|&nbsp; N°IFU : 0202011274722 &nbsp;|&nbsp; Tél : +229 0162836629 &nbsp;|&nbsp; Email : contact@paxevent.com &nbsp;|&nbsp; Site web : www.paxevent.com
    </div>
</body>
</html>
