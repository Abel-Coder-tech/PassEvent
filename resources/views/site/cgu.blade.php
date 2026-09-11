@extends('layouts.public')

@section('title', 'Conditions Générales d\'Utilisation (CGU) — PaxEvent')
@section('description', 'Consultez les conditions générales d\'utilisation de PaxEvent, la plateforme de billetterie en ligne au Bénin.')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4" style="color: var(--violet);">
                            <i class="bi bi-file-earmark-text me-2"></i>Conditions Générales d'Utilisation (CGU)
                        </h4>
                        <p class="text-muted" style="font-size: 0.85rem;">Dernière mise à jour : Juillet 2026</p>

                        <div class="card border-0 bg-light shadow-none mb-4">
                            <div class="card-body py-3">
                                <div class="fw-semibold mb-2" style="font-size: 0.85rem; color: var(--violet);">
                                    <i class="bi bi-journal-text me-1"></i>Pages légales
                                </div>
                                <div class="d-flex flex-wrap gap-2" style="font-size: 0.8rem;">
                                    <a href="{{ route('cgv') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">CGV</a>
                                    <a href="{{ route('confidentialite') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">Politique de confidentialité</a>
                                    <a href="{{ route('mentions-legales') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">Mentions légales</a>
                                    <a href="{{ route('cgu') }}" class="badge text-decoration-none fw-semibold" style="background:var(--violet);color:#fff;">CGU</a>
                                    <a href="{{ route('politique-remboursement') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">Politique de remboursement</a>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">
                            <p>Les présentes Conditions Générales d'Utilisation (CGU) (ci-après « CGU ») ont pour objet de déterminer l'accès et l'utilisation de la plateforme PaxEvent accessible sur www.paxevent.com. Ces CGU encadrent également les relations entre PaxEvent, ses clients et ses utilisateurs.</p>
                            <p>L'accès ou l'utilisation de la plateforme implique l'acceptation totale et sans réserve de ces conditions par tout visiteur, acheteur ou organisateur d'événement.</p>

                            <h6 class="fw-bold mt-4 mb-2">1. Présentation</h6>
                            <p>PaxEvent est une plateforme de billetterie en ligne 100% Béninoise qui connecte organisateurs d'événements et participants. Elle centralise la vente, l'achat rapide des tickets sans compte requis, et le contrôle des accès par scan de QR code. Les paiements s'effectuent par Mobile Money localement, et par Visa/MasterCard à l'international.</p>
                            <ul class="list-unstyled">
                                <li><strong>Éditeur du site :</strong> PaxEvent</li>
                                <li><strong>Nom de l'entreprise :</strong> Noctam Communication</li>
                                <li><strong>RCCM :</strong> RB/PNO/20 A 13348</li>
                                <li><strong>Siège social :</strong> C/12 M/MARTIN, Oganla Atakpame, Porto-Novo (Bénin)</li>
                                <li><strong>Téléphone :</strong> +229 01 62 83 66 29</li>
                                <li><strong>Email :</strong> <a href="mailto:contact@paxevent.com">contact@paxevent.com</a></li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">2. Clarification conceptuelle</h6>
                            <ul>
                                <li><strong>Plateforme :</strong> le site web PaxEvent, accessible à l'adresse https://paxevent.com/</li>
                                <li><strong>Organisateur :</strong> la personne physique ou morale qui crée et gère un événement</li>
                                <li><strong>Participant :</strong> la personne qui achète un ticket pour un événement</li>
                                <li><strong>Ticket :</strong> le titre d'accès numérique généré après paiement</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">3. Achat de tickets</h6>
                            <ul>
                                <li>Les tickets sont achetés en ligne et en physique exclusivement via la plateforme.</li>
                                <li>Le paiement est effectué principalement en ligne via mobile money ou carte bancaire ou en espèces auprès d'organisateur ou d'agent de vente accrédité par l'organisateur.</li>
                                <li>Un ticket est émis uniquement après confirmation du paiement.</li>
                                <li>Les tickets contiennent un QR code unique au porteur à garder jalousement et à ne jamais partager avec un tiers.</li>
                                <li>Un ticket ne peut être utilisé qu'une seule fois.</li>
                                <li>La vente ou la revente des tickets hors les outils proposés directement sur la plateforme est proscrite.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">4. Prix et paiement</h6>
                            <ul>
                                <li>Les prix des tickets sont fixés par les organisateurs et exprimés en FCFA.</li>
                                <li>Les frais de service des agrégateurs (FedaPay & KkiaPay) et des opérateurs Mobiles Money partenaires ne sont pas inclus dans le prix affiché. Ces frais de transaction sont entièrement à la charge des participants (acheteurs).</li>
                                <li>Les paiements sont sécurisés via des agrégateurs partenaires certifiés PCI DSS.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">5. Annulation des tickets</h6>
                            <ul>
                                <li>Les tickets ne sont ni échangeables, ni remboursables, y compris en cas de non-présentation ou d'erreur de frappe.</li>
                                <li>PaxEvent décline toute responsabilité en cas d'erreur de frappe commise lors de l'achat, par l'acheteur, l'organisateur ou un agent de vente. Veuillez vérifier scrupuleusement les détails de l'événement avant de valider tout paiement.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">6. QR code et accès</h6>
                            <ul>
                                <li>Le QR code est scanné à l'entrée pour valider l'accès.</li>
                                <li>Un QR code déjà utilisé ne permet plus l'accès.</li>
                                <li>Le participant doit présenter le ticket (impression ou écran) avec un QR code lisible.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">7. Utilisation acceptable</h6>
                            <ul>
                                <li><strong>Accès au service :</strong> La plateforme PaxEvent permet de consulter les événements disponibles, de s'inscrire ou d'acheter des billets électroniques (e-tickets). L'accès à la plateforme est gratuit, les frais d'équipement et de connexion internet étant à la charge exclusive de l'utilisateur.</li>
                                <li><strong>Inscription et compte :</strong> Pour créer et gérer des événements, l'organisateur doit créer un compte en fournissant des informations exactes. Le compte est personnel et non transférable.</li>
                                <li><strong>Éligibilité :</strong> L'utilisation est soumise à la capacité juridique de contracter en ligne, conformément aux lois en vigueur au Bénin.</li>
                                <li><strong>Sécurité :</strong> Vous êtes seul responsable de la confidentialité de vos identifiants et de l'exactitude des informations fournies.</li>
                                <li><strong>Interdictions :</strong> Sont strictement interdits le harcèlement, la fraude, le piratage, la violation de propriété intellectuelle, la création d'événements fictifs ou non autorisés, et la publication de contenus inappropriés.</li>
                                <li><strong>Sanctions :</strong> En cas de non-respect, PaxEvent se réserve le droit de restreindre, suspendre ou supprimer votre accès, sans préjudice des poursuites judiciaires possibles.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">8. Conditions applicables aux acheteurs</h6>
                            <p class="mb-1 fw-semibold">8.1. Processus d'achat et limites</p>
                            <p>Étapes : L'acheteur sélectionne l'événement, choisit son tarif (Gratuit, Standard, VIP), saisit ses coordonnées (avec option code promo unique), et procède au paiement sécurisé par Mobile Money (MTN MoMo, Moov Money, Celtiis Cash) ou Carte Bancaire (Visa/Mastercard).</p>
                            <ul>
                                <li><strong>Codes Promos :</strong> Les codes de réduction fournis par les organisateurs sont uniques, à usage unique et valables sur un seul tarif spécifié.</li>
                                <li><strong>Quota :</strong> L'achat est limité à un maximum de 05 tickets par personne pour un même événement.</li>
                                <li><strong>Livraison :</strong> Après validation, le e-ticket contenant un QR code unique accompagné d'un code Pax est délivré en pdf instantanément par email et également téléchargeable directement sur la page de confirmation d'achat. Vous pourriez aussi recevoir le code Pax via SMS ou WhatsApp.</li>
                            </ul>
                            <p class="mb-1 fw-semibold">8.2. Récupération des tickets</p>
                            <p>En cas de perte ou de non-réception, l'acheteur peut récupérer son e-ticket au format PDF via l'onglet « Récupérer mon ticket » en saisissant son email et l'ID de transaction (10 chiffres).</p>
                            <p class="mb-1 fw-semibold">8.3. Politique de remboursement</p>
                            <ul>
                                <li><strong>Principe :</strong> Les tickets ne sont ni échangeables ni remboursables.</li>
                                <li><strong>Cas d'annulation par l'organisateur :</strong> Les tickets sont remboursés aux acheteurs à hauteur de 90% du montant pour tout paiement effectué par mobile money ou espèces (remboursement assuré selon le cas par PaxEvent ou par l'organisateur).</li>
                                <li><strong>Cas d'erreur technique imputable à PaxEvent :</strong> En cas de double facturation avérée, PaxEvent annule le doublon et procède au remboursement du montant à l'acheteur.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">9. Conditions applicables aux organisateurs</h6>
                            <p class="mb-1 fw-semibold">9.1. Inscription, profil et validation</p>
                            <ul>
                                <li>La création de compte organisateur se fait via email ou compte Google.</li>
                                <li>L'organisateur doit soumettre son profil (Particulier, Université, Entreprise, ONG) accompagné des justificatifs requis (CIP, IFU, RCCM ou Récépissé). Un délai de 24 heures est requis pour l'approbation du profil par l'équipe PaxEvent avant de pouvoir publier un événement.</li>
                            </ul>
                            <p class="mb-1 fw-semibold">9.2. Tarifs et commissions de PaxEvent</p>
                            <ul>
                                <li><strong>Événements gratuits :</strong> L'accès à la plateforme et la billetterie à 0 FCFA sont 100% gratuits (sans commission, sous réserve de quotas).</li>
                                <li><strong>Événements payants :</strong> PaxEvent prélève une commission automatique de 10% sur chaque ticket vendu.</li>
                                <li>Les services additionnels (campagnes marketing SMS/WhatsApp/Mail, terminaux physiques, agents supplémentaires) font l'objet d'une facturation à la demande.</li>
                            </ul>
                            <p class="mb-1 fw-semibold">9.3. Gestion du personnel et des ventes sur place</p>
                            <ul>
                                <li><strong>Gestion des agents :</strong> L'organisateur dispose de 02 comptes agents gratuits non cumulables (rôle unique par événement : Agent de Scan pour le contrôle ou Agent de Vente pour la billetterie physique). L'ajout d'agents supplémentaires est payant.</li>
                                <li><strong>Ventes manuelles :</strong> Possibilité d'émettre des tickets depuis le tableau de bord (organisateur ou agent de vente) et d'encaisser par mobile money ou en espèces. Néanmoins, le règlement en espèces n'est autorisé que si au moins 15% des places totales de l'événement ont préalablement été vendues en ligne (via Mobile Money).</li>
                                <li><strong>Contrôle d'accès :</strong> L'organisateur et ses agents de scan s'engagent à n'utiliser la caméra de l'application que pour contrôler les tickets de leurs propres événements.</li>
                            </ul>
                            <p class="mb-1 fw-semibold">9.4. Reversement des revenus</p>
                            <ul>
                                <li><strong>Retrait standard :</strong> Les revenus des ventes (nets de commission) sont versés à l'organisateur dans un délai de 24h à 72h après la tenue de l'événement.</li>
                                <li><strong>Retrait anticipé :</strong> L'organisateur peut demander un retrait des fonds disponibles avant l'événement (traitement sous 4 jours ouvrés), sous réserve d'un solde minimum disponible de 1 000 FCFA.</li>
                                <li><strong>Blocage de sécurité :</strong> Tout retrait anticipé bloque la possibilité d'annuler l'événement via la plateforme. Dès qu'un retrait par anticipation est validé, l'organisateur ne peut plus annuler son événement. En cas de force majeure l'obligeant à annuler, l'organisateur devra assumer seul la responsabilité juridique et financière du remboursement intégral des acheteurs. Autrement dit, l'organisateur assume lui-même et à ses frais, les remboursements à hauteur des montants retirés.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">10. Acceptation de la nature des billets</h6>
                            <p>L'Organisateur accepte expressément le fonctionnement technique de PaxEvent :</p>
                            <ul>
                                <li>Les e-tickets émis sont au porteur et transférables.</li>
                                <li>Le jour de l'événement, l'Organisateur est tenu de valider l'entrée de la première personne présentant le QR code unique, sans obligation de vérifier la correspondance entre le nom inscrit sur le billet et la pièce d'identité du spectateur.</li>
                            </ul>
                            <p class="mb-1 fw-semibold">10.1. Modalités de contrôle des QR codes à l'entrée</p>
                            <p>Afin d'assurer la fluidité et la sécurité des accès le jour de l'événement, l'Organisateur s'engage à respecter scrupuleusement le protocole de vérification de la plateforme :</p>
                            <ul>
                                <li>L'Organisateur doit utiliser exclusivement l'application ou l'interface de scan officielle fournie ou agréée par PaxEvent pour valider les e-tickets.</li>
                                <li>Le système de PaxEvent invalide automatiquement chaque QR code dès son premier scan réussi. L'Organisateur reconnaît que seul le premier passage informatique valide l'entrée. Si un e-ticket avec un QR code identique est présenté une seconde fois, l'écran de contrôle affichera une alerte « Ticket déjà scanné ». L'Organisateur est alors en droit et en obligation de refuser l'accès au porteur de cette copie, PaxEvent ne pouvant être tenu responsable de la duplication frauduleuse du billet par l'Acheteur initial.</li>
                                <li>Il relève de la responsabilité unique de l'Organisateur de s'assurer, sur le lieu de l'événement, de la disponibilité d'appareils mobiles fonctionnels (smartphones ou douchettes) dotés d'une autonomie de batterie suffisante et d'une connexion internet (3G/4G/5G ou Wi-Fi) stable pour permettre la synchronisation en temps réel de la base de données des tickets.</li>
                                <li>L'organisateur peut, à sa discrétion, remettre un bracelet, un badge ou tout autre moyen d'identification permettant l'accès ou la circulation dans l'événement après validation du ticket.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">11. Modification, report ou annulation de l'événement</h6>
                            <p><strong>Information immédiate :</strong> En cas de modification majeure (changement de lieu, de date ou d'horaire) ou d'annulation de l'événement, l'Organisateur doit immédiatement en informer PaxEvent ainsi que tous les Acheteurs ayant déjà réservé une place.</p>
                            <p><strong>Responsabilité financière :</strong> En cas d'annulation définitive, l'Organisateur assume la responsabilité légale et financière du remboursement intégral ou partiel à hauteur de 90% de la valeur faciale des billets auprès des Acheteurs, conformément aux CGV.</p>

                            <h6 class="fw-bold mt-4 mb-2">12. Reversement des recettes et commissions</h6>
                            <p><strong>Commissions de PaxEvent :</strong> En contrepartie du service de billetterie, de gestion des transactions et de maintenance, PaxEvent perçoit une commission sur chaque billet vendu. Les modalités de cette commission sont fixées par contrat de partenariat individuel avec l'Organisateur.</p>
                            <p><strong>Délais de reversement :</strong> Les recettes nettes issues des ventes de billets (déduction faite des commissions de PaxEvent) sont reversées à l'Organisateur selon les modalités convenues (retraits standards ou anticipés). PaxEvent se réserve le droit de geler les fonds en cas de suspicion légitime de fraude ou de réclamation massive des Acheteurs.</p>
                            <p class="mb-1 fw-semibold">12.1. Clause d'exclusion automatique et de bannissement</p>
                            <p>PaxEvent se réserve le droit exclusif de suspendre temporairement ou de bannir définitivement, de manière automatique et sans préavis, le compte de tout Organisateur qui ne respecterait pas ses engagements contractuels et légaux.</p>
                            <p><strong>Motifs de bannissement :</strong> L'exclusion automatique sera prononcée notamment en cas de :</p>
                            <ul>
                                <li>Annulation répétée d'événements sans motif de force majeure ou refus répété de procéder au remboursement des Acheteurs.</li>
                                <li>Publication d'événements fictifs, frauduleux, mensongers ou contraires aux lois en vigueur au Bénin (escroquerie, infractions au Code du numérique).</li>
                                <li>Utilisation de fausses informations d'identité ou de coordonnées financières lors de l'inscription.</li>
                                <li>Comportement abusif, diffamatoire ou préjudiciable à l'image de marque de la plateforme PaxEvent.</li>
                            </ul>
                            <p><strong>Conséquences du bannissement :</strong> L'exclusion entraîne la clôture immédiate du compte de l'Organisateur et le retrait de tous ses événements en cours de vente.</p>
                            <p><strong>Rétention conservatoire des fonds :</strong> En cas de suspicion de fraude ou d'annulation non gérée, PaxEvent bloquera de plein droit l'ensemble des recettes en cours sur le compte de l'Organisateur à titre conservatoire. Ces fonds seront gelés pendant une période minimale de 90 jours afin de couvrir les éventuelles demandes de remboursement des Acheteurs lésés ou les frais de procédure.</p>

                            <h6 class="fw-bold mt-4 mb-2">13. Exclusion de responsabilité de la plateforme</h6>
                            <p>PaxEvent intervient en qualité de simple intermédiaire technique de mise en relation. En conséquence, PaxEvent ne saurait être tenu pour responsable :</p>
                            <ul>
                                <li>Des litiges survenant entre un Acheteur et un Organisateur (ex: mauvaise qualité du spectacle, refus d'accès injustifié par l'Organisateur, etc.).</li>
                                <li>Des dommages corporels ou matériels survenant lors du déroulement de l'événement.</li>
                                <li>Du manque à gagner de l'Organisateur en cas de dysfonctionnement temporaire de la plateforme indépendant de sa volonté.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">14. Modification des CGU</h6>
                            <p>PaxEvent se réserve le droit de modifier les présentes CGU à tout moment afin de les adapter aux évolutions techniques ou réglementaires du droit béninois. Les modifications entrent en vigueur dès leur publication en ligne sur le site.</p>

                            <h6 class="fw-bold mt-4 mb-2">15. Litiges</h6>
                            <p>Les présentes CGU sont régies par le droit béninois. En cas de contestation sur l'interprétation ou l'exécution des présentes, et à défaut de résolution amiable, le litige sera porté devant les tribunaux compétents de Cotonou.</p>

                            <h6 class="fw-bold mt-4 mb-2">16. Loi applicable et juridiction</h6>
                            <p>Les présentes CGU sont soumises au droit béninois. En cas de litige, les tribunaux compétents sont ceux du Bénin.</p>

                            <h6 class="fw-bold mt-4 mb-2">17. Contact</h6>
                            <p>Pour toute question relative à l'utilisation de la plateforme, à l'achat de tickets d'événements, ou à l'application des présentes conditions générales d'utilisation et de vente, vous pouvez nous contacter :</p>
                            <ul>
                                <li><strong>Email :</strong> <a href="mailto:contact@paxevent.com">contact@paxevent.com</a></li>
                                <li><strong>WhatsApp :</strong> +229 62 83 66 29</li>
                            </ul>
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