@extends('layouts.public')

@section('title', 'Conditions Générales de Vente — PaxEvent')
@section('description', 'Consultez les conditions générales de vente de PaxEvent pour l\'achat de billets d\'événements.')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4" style="color: var(--violet);">
                            <i class="bi bi-file-earmark-text me-2"></i>Conditions Générales de Vente
                        </h4>
                        <p class="text-muted" style="font-size: 0.85rem;">Dernière mise à jour : Juillet 2026</p>

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">
                            <p>Les présentes Conditions Générales de Vente (CGV) régissent l'achat de billets pour des événements organisés via la plateforme PaxEvent (www.paxevent.com), éditée par Noctam Communication (RCCM RB/PNO 20 A 13348, Oganla Atakpamè, Porto-Novo, Bénin).</p>

                            <h6 class="fw-bold mt-4 mb-2">1. Objet</h6>
                            <p>Les présentes CGV définissent les droits et obligations des parties dans le cadre de la vente de billets d'événements via la plateforme PaxEvent.</p>

                            <h6 class="fw-bold mt-4 mb-2">2. Produits</h6>
                            <p>Les billets sont émis sous forme électronique (e-ticket) contenant un QR code unique. Chaque billet est valable pour une seule entrée à l'événement concerné. Les billets sont au porteur : la première personne présentant le QR code à l'entrée est acceptée, sans obligation de vérifier la concordance avec le nom inscrit.</p>
                            <p>Les codes QR pour ceux ayant fait la demande sont émis après paiement sous forme de document dans le tableau de bord ou par mail de l'organisateur. Chaque QR code étant unique et valable pour une seule entrée à l'événement concerné.</p>

                            <h6 class="fw-bold mt-4 mb-2">3. Prix</h6>
                            <p>Les prix des billets sont fixés librement par les organisateurs et sont exprimés en Francs CFA (FCFA). Les frais de service des agrégateurs de paiement (FedaPay, Kkiapay) et des opérateurs Mobile Money partenaires ne sont pas inclus dans le prix affiché et sont entièrement à la charge de l'acheteur.</p>

                            <h6 class="fw-bold mt-4 mb-2">4. Commandes</h6>
                            <p>La commande est validée après paiement réussi. Un e-ticket PDF contenant un QR code unique et un code Pax est immédiatement envoyé par email à l'acheteur. L'acheteur peut également télécharger son ticket depuis la page de confirmation. Un SMS ou WhatsApp peut également être envoyé contenant le code Pax.</p>

                            <h6 class="fw-bold mt-4 mb-2">5. Paiement</h6>
                            <p>Les paiements sont sécurisés via les agrégateurs partenaires FedaPay et Kkiapay. Les moyens de paiement acceptés sont : Mobile Money (MTN MoMo, Moov Money, Celtiis Cash) et cartes bancaires (Visa, Mastercard). Le paiement en espèces auprès d'un organisateur ou d'un agent de vente est également possible.</p>

                            <h6 class="fw-bold mt-4 mb-2">6. Livraison des billets</h6>
                            <p>Les billets sont livrés immédiatement après validation du paiement par email à l'adresse fournie par l'acheteur. En cas de non-réception, l'acheteur peut récupérer son billet via l'onglet "Récupérer mon ticket" en saisissant son email et l'ID de transaction (10 chiffres).</p>

                            <h6 class="fw-bold mt-4 mb-2">7. Nature et validité des e-tickets</h6>
                            <p>Conformément aux modalités d'utilisation de la plateforme, les e-tickets vendus sur PaxEvent présentent les caractéristiques juridiques suivantes :</p>
                            <ul>
                                <li><strong>Au porteur :</strong> Le ticket n'est pas lié à l'identité de l'acheteur initial. L'entrée de l'événement est accordée à toute personne présentant le QR code unique le jour J. L'Organisateur procède à un contrôle unique du QR code par scan ; la première personne à le présenter est considérée comme le porteur légitime.</li>
                                <li><strong>Transférable / Cessible :</strong> L'Acheteur est libre d'acheter un e-ticket dans le but de l'offrir ou de le céder à un tiers. PaxEvent décline toute responsabilité en cas de perte, de vol ou de duplication malveillante du QR code après sa livraison.</li>
                                <li><strong>Inchangeable :</strong> Sauf mention contraire de l'Organisateur, un billet acheté pour un événement spécifique, à une date et une heure donnée, ne peut pas être modifié pour un autre événement ou une autre session.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">8. Droit de rétractation</h6>
                            <p>Conformément à la législation béninoise et aux exceptions applicables aux prestations de services de loisirs, les billets achetés sur PaxEvent ne sont ni échangeables ni remboursables, sauf dans les cas prévus par la politique de remboursement accessible sur la plateforme.</p>

                            <h6 class="fw-bold mt-4 mb-2">9. Responsabilité</h6>
                            <p>PaxEvent agit en qualité d'intermédiaire technique entre l'organisateur et l'acheteur. PaxEvent ne saurait être tenu responsable de la qualité de l'événement, de son annulation (hors défaut technique de la plateforme), ou des litiges entre l'organisateur et les participants.</p>

                            <h6 class="fw-bold mt-4 mb-2">10. Revente et marché noir (revente non officielle)</h6>
                            <p>Bien que les e-tickets soient transférables et « au porteur » (permettant de les offrir légitimement à des tiers), PaxEvent applique une politique stricte concernant la revente commerciale non autorisée.</p>
                            <ul>
                                <li><strong>Interdiction de spéculation :</strong> La revente de billets achetés sur PaxEvent à un prix supérieur à leur valeur faciale initiale (marché noir ou surévaluation) est formellement interdite.</li>
                                <li><strong>Responsabilité en cas de fraude :</strong> PaxEvent décline toute responsabilité en cas d'achat de billets en dehors de sa plateforme officielle (réseaux sociaux, sites tiers, revente de gré à gré). L'Acheteur final assume seul le risque d'acquérir un billet falsifié, dupliqué ou déjà scanné par un autre utilisateur à l'entrée de l'événement.</li>
                                <li><strong>Sanctions :</strong> L'Organisateur et PaxEvent se réservent le droit d'annuler sans préavis ni remboursement tout billet suspecté d'avoir fait l'objet d'une transaction frauduleuse ou spéculative, et de bloquer l'accès à l'événement au porteur de ce billet.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">11. Données personnelles</h6>
                            <p>Les données personnelles collectées lors de l'achat sont traitées conformément à la politique de confidentialité de PaxEvent, en accord avec la loi n° 2017-20 du 20 avril 2018 portant protection des données à caractère personnel en République du Bénin.</p>

                            <h6 class="fw-bold mt-4 mb-2">12. Loi applicable et juridiction</h6>
                            <p>Les présentes CGV sont régies par le droit béninois. En cas de litige, les tribunaux compétents sont ceux du Bénin.</p>
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