@extends('layouts.public')

@section('title', "Programme d'Affiliation & Apporteurs d'Affaires — PaxEvent")
@section('description', "Découvrez le programme d'affiliation et d'apport d'affaires PaxEvent, gagnez des commissions sur les ventes de billets.")

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4" style="color: var(--violet);">
                            <i class="bi bi-megaphone me-2"></i>Programme d'Affiliation & Apporteurs d'Affaires
                        </h4>
                        <p class="text-muted" style="font-size: 0.85rem;">Dernière mise à jour : Juillet 2026</p>

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">
                            <p>Les présentes Conditions Générales régissent le programme d'affiliation (ci-après le « Programme ») mis en place par Noctam Communication (ci-après « PaxEvent ») sur sa plateforme https://paxevent.com.</p>
                            <p>Le Programme permet à des personnes physiques ou morales (ci-après les « Affiliés » ou « Apporteurs d'Affaires ») de promouvoir des événements majeurs répertoriés sur PaxEvent et de percevoir une commission sur les ventes de billets générées grâce à leurs actions promotionnelles.</p>

                            <h6 class="fw-bold mt-4 mb-2">1. Inscription et éligibilité</h6>
                            <p><strong>Adhésion :</strong> L'inscription au Programme s'effectue via un espace dédié sur le tableau de bord PaxEvent. PaxEvent se réserve le droit d'accepter ou de refuser toute candidature sans obligation de justification.</p>
                            <p><strong>Capacité légale :</strong> L'Affilié certifie être majeur et disposer de la capacité juridique ou des statuts professionnels nécessaires au Bénin pour percevoir des revenus d'apport d'affaires.</p>

                            <h6 class="fw-bold mt-4 mb-2">2. Liens d'affiliation et traçabilité</h6>
                            <p><strong>Attribution :</strong> Après validation de son compte, l'Affilié reçoit un lien URL unique, un code promotionnel ou un accès de suivi pour chaque événement qu'il choisit de promouvoir.</p>
                            <p><strong>Technologie de suivi :</strong> Les ventes sont attribuées à l'Affilié uniquement si l'Acheteur final finalise son achat sur PaxEvent via le lien d'affiliation ou en insérant le code de l'Affilié au moment du paiement. PaxEvent ne pourra être tenu responsable des ventes non comptabilisées si l'Acheteur n'a pas utilisé le protocole de suivi fourni.</p>

                            <h6 class="fw-bold mt-4 mb-2">3. Calcul et taux de commission</h6>
                            <p><strong>Base de calcul :</strong> La commission est calculée exclusivement sur la valeur faciale nette du billet vendu (hors frais de service de la plateforme et taxes).</p>
                            <p><strong>Taux applicable :</strong> Le pourcentage ou le montant fixe de la commission est défini au cas par cas pour chaque événement majeur, en accord avec l'Organisateur de l'événement et PaxEvent. Les taux applicables sont visibles par l'Affilié sur son tableau de bord avant le début de sa promotion.</p>
                            <p><strong>Annulation :</strong> Si un billet fait l'objet d'un remboursement (annulation de l'événement, transaction frauduleuse ou rejet Mobile Money), la commission correspondante est automatiquement annulée et déduite du solde de l'Affilié.</p>

                            <h6 class="fw-bold mt-4 mb-2">4. Modalités de paiement et reversement</h6>
                            <p><strong>Seuil de paiement :</strong> Les commissions cumulées sont payables dès que le solde de l'Affilié atteint un seuil minimum de 5 000 XOF.</p>
                            <p><strong>Canaux de paiement :</strong> Les reversements s'effectuent directement sur le numéro Mobile Money (MTN, Moov, Wave) ou le compte bancaire renseigné par l'Affilié.</p>
                            <p><strong>Frais techniques de reversement :</strong> Conformément à notre infrastructure FedaPay, les frais de transfert de masse (PAYOUT) s'appliquent selon le barème officiel en vigueur de la plateforme.</p>

                            <h6 class="fw-bold mt-4 mb-2">5. Règles de conduite et pratiques interdites</h6>
                            <p>L'Affilié s'engage à préserver l'image de marque de PaxEvent et de l'Organisateur. Sont strictement interdits :</p>
                            <ul>
                                <li><strong>Le Spamming :</strong> l'envoi massif d'e-mails, de SMS ou de messages WhatsApp non sollicités contenant les liens d'affiliation.</li>
                                <li><strong>Le "Self-Affiliation" :</strong> le fait d'utiliser son propre lien d'affiliation pour acheter ses propres billets pour un événement dans le seul but de toucher une commission.</li>
                                <li><strong>Publicité mensongère :</strong> divulguer de fausses informations sur l'événement (tarifs erronés, faux invités, fausses dates) pour forcer la vente.</li>
                                <li><strong>Achat de mots-clés :</strong> l'achat de mots-clés sur Google Ads ou d'autres régies publicitaires contenant la marque "PaxEvent" ou le nom exact de l'événement sans accord écrit.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">6. Responsabilité et indépendance</h6>
                            <p>L'Affilié exerce son activité en totale indépendance. Il n'existe aucun lien de subordination, de contrat de travail ou de mandant de représentation exclusif entre PaxEvent et l'Affilié. L'Affilié est seul responsable de la déclaration fiscale de ses gains auprès des autorités compétentes au Bénin.</p>

                            <h6 class="fw-bold mt-4 mb-2">7. Modification et résiliation</h6>
                            <p><strong>Résiliation par l'Affilié :</strong> L'Affilié peut cesser sa participation au programme à tout moment en cessant d'utiliser ses liens.</p>
                            <p><strong>Bannissement pour fraude :</strong> En cas de non-respect constaté de l'Article 5, PaxEvent annulera immédiatement le compte de l'Affilié sans préavis. Toutes les commissions en attente de paiement seront définitivement confisquées au profit de PaxEvent ou de l'Organisateur pour réparer le préjudice causé.</p>

                            <h6 class="fw-bold mt-4 mb-2">8. Loi applicable</h6>
                            <p>Les présentes conditions d'affiliation sont régies par le droit béninois (Code du numérique). Tout litige relatif à leur exécution ou leur interprétation relève de la compétence des tribunaux de Cotonou.</p>
                        </div>

                        <a href="{{ route('accueil') }}" class="btn btn-violet" style="border-radius: 8px;">
                            <i class="bi bi-arrow-left me-1"></i> Retour à accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
