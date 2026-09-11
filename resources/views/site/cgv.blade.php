@extends('layouts.public')

@section('title', 'Conditions Générales de Vente (CGV) — PaxEvent')
@section('description', 'Consultez les conditions générales de vente de PaxEvent pour l\'achat de billets d\'événements.')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4" style="color: var(--violet);">
                            <i class="bi bi-file-earmark-text me-2"></i>Conditions Générales de Vente (CGV)
                        </h4>
                        <p class="text-muted" style="font-size: 0.85rem;">Dernière mise à jour : Juillet 2026</p>

                        <div class="card border-0 bg-light shadow-none mb-4">
                            <div class="card-body py-3">
                                <div class="fw-semibold mb-2" style="font-size: 0.85rem; color: var(--violet);">
                                    <i class="bi bi-journal-text me-1"></i>Pages légales
                                </div>
                                <div class="d-flex flex-wrap gap-2" style="font-size: 0.8rem;">
                                    <a href="{{ route('cgv') }}" class="badge text-decoration-none fw-semibold" style="background:var(--violet);color:#fff;">CGV</a>
                                    <a href="{{ route('confidentialite') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">Politique de confidentialité</a>
                                    <a href="{{ route('mentions-legales') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">Mentions légales</a>
                                    <a href="{{ route('cgu') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">CGU</a>
                                    <a href="{{ route('politique-remboursement') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">Politique de remboursement</a>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">
                            <h6 class="fw-bold mt-4 mb-2">1. Objet</h6>
                            <p>Les présentes Conditions Générales de Vente (CGV) régissent l'ensemble des transactions effectuées sur la plateforme PaxEvent accessible via https://paxevent.com. Elles s'appliquent entre l'entreprise Noctam Communication (ci-après « PaxEvent ») et toute personne physique ou morale effectuant un achat ou une vente sur le site (ci-après « l'Acheteur » et « l'Organisateur »).</p>

                            <h6 class="fw-bold mt-4 mb-2">2. Rôle de la plateforme</h6>
                            <p>PaxEvent agit exclusivement en tant qu'intermédiaire technique de billetterie entre les Organisateurs d'événements et les Acheteurs. En conséquence, PaxEvent ne saurait être tenu responsable du contenu, de l'organisation, de la modification ou de l'annulation d'un événement, qui relèvent de la responsabilité unique de l'Organisateur.</p>

                            <h6 class="fw-bold mt-4 mb-2">3. Processus de commande et réception</h6>
                            <p>Pour acquérir un billet électronique (e-ticket), l'Acheteur doit :</p>
                            <ul>
                                <li>Choisir l'événement de son choix sur la plateforme.</li>
                                <li>Remplir les informations demandées (nom, prénom, e-mail) et sélectionner le tarif.</li>
                                <li>Payer de manière sécurisée en ligne via les agrégateurs de paiement partenaires (FedaPay, Kkiapay — Mobile Money et carte Visa ou MasterCard) ou en espèces via les organisateurs ou leurs agents de ventes accrédités.</li>
                                <li>Recevoir son ticket sous format PDF avec un QR code et code Pass unique, envoyé instantanément à l'adresse e-mail renseignée lors de l'achat.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">4. Nature et validité des e-tickets</h6>
                            <p>Conformément aux modalités d'utilisation de la plateforme, les e-tickets vendus sur PaxEvent présentent les caractéristiques juridiques suivantes :</p>
                            <ul>
                                <li><strong>Au porteur :</strong> Le ticket n'est pas lié à l'identité de l'acheteur initial. L'entrée de l'événement est accordée à toute personne présentant le QR code unique le jour J. L'Organisateur procède à un contrôle unique du QR code par scan ; la première personne à le présenter est considérée comme le porteur légitime.</li>
                                <li><strong>Transférable / Cessible :</strong> L'Acheteur est libre d'acheter un e-ticket dans le but de l'offrir ou de le céder à un tiers. PaxEvent décline toute responsabilité en cas de perte, de vol ou de duplication malveillante du QR code après sa livraison.</li>
                                <li><strong>Inchangeable :</strong> Sauf mention contraire de l'Organisateur, un billet acheté pour un événement spécifique, à une date et une heure données, ne peut pas être modifié pour un autre événement ou une autre session.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">5. Prix et modalités de paiement</h6>
                            <p>Les prix affichés sur PaxEvent sont indiqués en Francs CFA (XOF), toutes taxes comprises (TTC). Les frais de transaction ne sont pas inclus et sont à la charge de l'acheteur. Les transactions sont traitées et sécurisées via les passerelles de paiement partenaires (FedaPay, Kkiapay). Le débit est immédiat dès la confirmation de la transaction.</p>
                            <p class="mb-1 fw-semibold">5.1. Commissions et frais de services</p>
                            <p>PaxEvent prélève aux organisateurs une commission de 10% seulement sur chaque ticket vendu.</p>
                            <p>A la demande, PaxEvent facture également aux organisateurs des frais de fourniture de services additionnels (Marketing, APK, Terminaux, membres). Ces frais sont clairement indiqués au moment de la validation de la commande, avant le paiement final.</p>
                            <p>En cas d'annulation de l'événement ou de remboursement du billet, les frais de commission ou de service de la Plateforme restent définitivement acquis à PaxEvent et ne feront l'objet d'aucun remboursement, car ils rémunèrent un service de traitement de commande déjà intégralement exécuté.</p>
                            <p class="mb-1 fw-semibold">5.2. Incidents de réseau et bugs liés au mobile money</p>
                            <p>Les paiements par Mobile Money dépendent de la stabilité des réseaux des opérateurs de télécommunication locaux (MTN, Moov, Celtiis etc.) et des agrégateurs (FedaPay, Kkiapay…).</p>
                            <p><strong>Débit sans génération de ticket (Bug de synchronisation) :</strong> En cas d'incident technique réseau où le compte de l'Acheteur est débité mais que la plateforme PaxEvent ne génère pas le e-ticket (le paiement n'ayant pas pu être validé à temps par l'opérateur), l'Acheteur ne doit pas tenter un second paiement immédiat.</p>
                            <p><strong>Procédure de signalement :</strong> L'Acheteur doit immédiatement contacter le support technique de PaxEvent (via la page Contact ou par e-mail) en fournissant l'ID de la transaction Mobile Money, l'heure exacte et le numéro de téléphone utilisé.</p>
                            <p><strong>Résolution :</strong> Après vérification auprès du partenaire de paiement (FedaPay, Kkiapay…), PaxEvent s'engage soit à forcer la génération et l'envoi manuel du e-ticket à l'Acheteur si des places sont encore disponibles, soit à rembourser intégralement l'acheteur, ou encore à rejeter la transaction pour que l'opérateur Mobile Money restitue les fonds sur le compte de l'Acheteur (hors frais d'opérateur).</p>
                            <p>PaxEvent ne peut être tenu responsable des lenteurs de remboursement causées par des pannes générales des réseaux télécoms.</p>

                            <h6 class="fw-bold mt-4 mb-2">6. Annulation et remboursement</h6>
                            <p><strong>Principes de non-remboursement :</strong> les billets achetés ne sont ni remboursables ni échangeables sur PaxEvent ni auprès des organisateurs.</p>
                            <p><strong>Principes de remboursement :</strong></p>
                            <ul>
                                <li>En cas d'annulation de l'événement, les tickets achetés ne sont remboursés aux acheteurs qu'à hauteur de 90% du montant, les frais de service de la plateforme PaxEvent (10% sur les tickets vendus) restent non remboursables.</li>
                                <li>En cas d'erreurs techniques avérées imputables à PaxEvent (double facturation, non émission de tickets) : les tickets non émis sont régénérés, les doublons de facture sont annulés et les sommes rétrocédées automatiquement et intégralement aux acheteurs.</li>
                            </ul>
                            <p class="mb-1">Les remboursements sont gérés par les acteurs suivants :</p>
                            <ul>
                                <li>Par PaxEvent, entièrement ou en partie à hauteur des fonds disponibles à son niveau.</li>
                                <li>Par les organisateurs, intégralement ou en partie selon les circonstances de l'annulation de l'événement et à hauteur des fonds déjà retirés.</li>
                                <li>Conjointement par PaxEvent et l'organisateur à hauteur de 90% du prix de chaque ticket et selon les fonds disponibles auprès de chacun d'eux.</li>
                            </ul>
                            <p class="mb-1 fw-semibold">6.1. Politique de remboursement spécifique (à l'attention des organisateurs et des acheteurs)</p>
                            <p>PaxEvent agit uniquement comme intermédiaire de vente. La décision d'annuler un événement relève de la responsabilité exclusive de l'organisateur de l'événement.</p>
                            <p><strong>Annulation de l'événement :</strong> Si l'Organisateur annule son événement, il est légalement tenu de rembourser 90% du prix facial des billets aux Acheteurs. L'Organisateur doit alors procéder directement au remboursement ou ordonner à PaxEvent l'exécution technique des remboursements.</p>
                            <p><strong>Frais non remboursables :</strong> Conformément à l'article 5.1., les commissions et frais de service prélevés par PaxEvent restent définitivement acquis à la plateforme. Seule 90% de la valeur nette du billet fixée par l'Organisateur sera reversée à l'Acheteur.</p>
                            <p><strong>Processus technique :</strong> Les remboursements approuvés par l'Organisateur sont re-crédités directement sur le compte Mobile Money (MTN, Moov, Celtiis, etc.) ayant servi à l'achat initial. Les délais de traitement dépendent des agrégateurs de paiement et des opérateurs télécoms (généralement entre 24 heures et 5 jours ouvrés).</p>

                            <h6 class="fw-bold mt-4 mb-2">7. Récupération d'un ticket perdu</h6>
                            <p>En cas de perte du mail contenant le e-ticket, PaxEvent met à disposition une interface dédiée permettant à l'Acheteur de récupérer son ticket en fournissant les informations d'achat initiales depuis la page de récupération du site.</p>

                            <h6 class="fw-bold mt-4 mb-2">8. Protection des données personnelles</h6>
                            <p>Les données collectées lors de la commande (notamment le nom et l'adresse e-mail) sont nécessaires au traitement de la commande et à l'envoi du e-ticket. Elles peuvent être transmises à l'Organisateur de l'événement à des fins de gestion des accès. PaxEvent s'engage à respecter la réglementation béninoise en vigueur sur la protection des données personnelles.</p>

                            <h6 class="fw-bold mt-4 mb-2">9. Droit applicable et litiges</h6>
                            <p>Les présentes CGV sont soumises au droit béninois. En cas de litige ou de contestation, et à défaut d'accord amiable préalable, compétence exclusive est attribuée aux tribunaux compétents de Cotonou/Porto-Novo.</p>

                            <h6 class="fw-bold mt-4 mb-2">10. Revente et marché noir (revente non officielle)</h6>
                            <p>Bien que les e-tickets soient transférables et « au porteur » (permettant de les offrir légitimement à des tiers), PaxEvent applique une politique stricte concernant la revente commerciale non autorisée.</p>
                            <ul>
                                <li><strong>Interdiction de spéculation :</strong> La revente de billets achetés sur PaxEvent à un prix supérieur à leur valeur faciale initiale (marché noir ou surévaluation) est formellement interdite.</li>
                                <li><strong>Responsabilité en cas de fraude :</strong> PaxEvent décline toute responsabilité en cas d'achat de billets en dehors de sa plateforme officielle (réseaux sociaux, sites tiers, revente de gré à gré). L'Acheteur final assume seul le risque d'acquérir un billet falsifié, dupliqué ou déjà scanné par un autre utilisateur à l'entrée de l'événement.</li>
                                <li><strong>Sanctions :</strong> L'Organisateur et PaxEvent se réservent le droit d'annuler sans préavis ni remboursement tout billet suspecté d'avoir fait l'objet d'une transaction frauduleuse ou spéculative, et de bloquer l'accès à l'événement au porteur de ce billet.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">11. Force majeure et sécurité des réseaux</h6>
                            <p>PaxEvent ne pourra être tenu responsable de la non-exécution ou du retard dans l'exécution de l'une de ses obligations si cet échec découle d'un cas de force majeure, tel que défini par la jurisprudence et la législation béninoise.</p>
                            <p><strong>Événements déclencheurs :</strong> Sont expressément considérés comme cas de force majeure : les catastrophes naturelles, les incendies, les grèves totales ou partielles, les décisions ou interdictions préfectorales et gouvernementales, les insurrections, ainsi que les pannes générales d'électricité (coupures nationales/délestages prolongés), les interruptions majeures des réseaux de télécommunication (coupures de câbles sous-marins de fibre optique ou blocage des serveurs des opérateurs MTN/Moov/Celtiis).</p>
                            <p><strong>Suspension des obligations :</strong> La survenance d'un tel événement suspend immédiatement les obligations de PaxEvent pour toute la durée de la perturbation. PaxEvent mettra en œuvre tous les efforts raisonnables pour rétablir l'accès à la plateforme dès la cessation de l'événement de force majeure.</p>
                        </div>

                        <a href="{{ route('accueil') }}" class="btn btn-violet" style="border-radius: 8px;">
                            <i class="bi bi-arrow-left me-1"></i> Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection