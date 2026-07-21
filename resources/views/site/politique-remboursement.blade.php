@extends('layouts.public')

@section('title', 'Politique de remboursement — PaxEvent')
@section('description', 'Consultez la politique de remboursement de PaxEvent pour les billets d\'événements.')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4" style="color: var(--violet);">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Politique de remboursement
                        </h4>
                        <p class="text-muted" style="font-size: 0.85rem;">
                            Dernière mise à jour : Juillet 2026
                        </p>

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">
                            <p>La présente politique de remboursement fait partie intégrante des Conditions Générales de Vente (CGV) et d'Utilisation (CGU) de la plateforme PaxEvent.</p>

                            <h6 class="fw-bold mt-4 mb-2">1. Principe général</h6>
                            <p>Sauf disposition contraire prévue dans les sections ci-dessous, les billets achetés sur PaxEvent ne sont ni échangeables ni remboursables, y compris en cas de non-présentation à l'événement ou d'erreur de saisie lors de l'achat.</p>

                            <h6 class="fw-bold mt-4 mb-2">2. Annulation par l'organisateur</h6>
                            <p>Lorsqu'un organisateur annule son événement via la plateforme :</p>
                            <ul>
                                <li>Tous les acheteurs sont automatiquement notifiés par email.</li>
                                <li>Les billets sont immédiatement marqués comme annulés dans le système.</li>
                                <li><strong>Remboursement aux acheteurs : 90% du montant du billet</strong> (hors frais de transaction des agrégateurs de paiement, qui restent acquis à ces derniers).</li>
                                <li><strong>Frais de service PaxEvent (10%) :</strong> Non remboursables. La commission de 10% perçue par PaxEvent sur chaque ticket vendu est définitivement acquise à PaxEvent, même en cas d'annulation.</li>
                                <li>Le remboursement est effectué sur le moyen de paiement d'origine (Mobile Money ou compte bancaire selon le cas) dans un délai de 3 à 5 jours ouvrés suivant la confirmation de l'annulation.</li>
                                <li>Le remboursement est assuré soit par PaxEvent directement, soit par l'organisateur selon les modalités convenues au moment de l'annulation.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">3. Annulation par l'acheteur (participant)</h6>
                            <p>Sauf indication contraire de l'organisateur au moment de l'achat, les billets ne sont ni échangeables ni remboursables à l'initiative de l'acheteur. Nous vous conseillons de vérifier attentivement les détails de l'événement (date, lieu, conditions) avant de finaliser votre achat.</p>
                            <p>PaxEvent décline toute responsabilité en cas d'erreur de frappe commise par l'acheteur, l'organisateur ou un agent de vente lors de l'achat.</p>

                            <h6 class="fw-bold mt-4 mb-2">4. Annulation avant l'événement (optionnel)</h6>
                            <p>L'organisateur peut définir une date limite d'annulation (ex : 48h avant l'événement) pendant laquelle l'acheteur peut demander un remboursement partiel selon les conditions définies par l'organisateur.</p>
                            <ul>
                                <li>Dans ce cas, le remboursement est effectué directement par l'organisateur, après validation par PaxEvent (si la fonctionnalité est activée).</li>
                                <li>Les frais de transaction des agrégateurs de paiement ne sont pas remboursés.</li>
                                <li>La commission PaxEvent de 10% reste acquise à PaxEvent.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">5. Erreur technique imputable à PaxEvent</h6>
                            <p>En cas de double facturation avérée imputable à un dysfonctionnement technique de la plateforme, PaxEvent annule le doublon et procède au remboursement intégral du montant indu à l'acheteur.</p>

                            <h6 class="fw-bold mt-4 mb-2">6. Billets gratuits</h6>
                            <p>En cas d'annulation d'un événement avec billets gratuits, les billets sont automatiquement annulés. Aucun remboursement n'est nécessaire puisqu'aucun paiement n'a été effectué.</p>

                            <h6 class="fw-bold mt-4 mb-2">7. Frais de transaction des agrégateurs de paiement</h6>
                            <p>Les frais de transaction préalablement prélevés par les prestataires de paiement (FedaPay, KkiaPay) et les opérateurs Mobile Money lors de l'achat initial ne sont pas remboursés en cas de remboursement. Ces frais restent acquis auxdits prestataires.</p>

                            <h6 class="fw-bold mt-4 mb-2">8. Modification de l'événement (report, changement de lieu, etc.)</h6>
                            <p>En cas de report ou de modification substantielle de l'événement, l'organisateur doit en informer les acheteurs. Les acheteurs peuvent alors :</p>
                            <ul>
                                <li>Accepter la modification (le billet reste valable pour la nouvelle date/lieu).</li>
                                <li>Refuser la modification et demander un remboursement selon les modalités prévues à l'article 2 (90% du montant, hors frais de transaction).</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">9. Cas particuliers</h6>
                            <p>Pour toute situation non couverte par cette politique (annulation partielle, changement de programmation, cas de force majeure, etc.), l'organisateur est invité à contacter PaxEvent pour une prise en charge individuelle. Chaque cas sera étudié au cas par cas.</p>

                            <h6 class="fw-bold mt-4 mb-2">10. Responsabilité de PaxEvent</h6>
                            <p>PaxEvent agit uniquement en tant que plateforme de mise en relation entre les organisateurs et les acheteurs. En cas de litige, PaxEvent s'engage à faciliter la communication et à fournir les preuves de transaction (si disponibles), mais n'est pas responsable des remboursements effectués par les organisateurs.</p>
                            <p>En cas de retrait anticipé effectué par l'organisateur, ce dernier assume seul la responsabilité juridique et financière du remboursement intégral des acheteeurs en cas d'annulation de l'événement.</p>

                            <h6 class="fw-bold mt-4 mb-2">11. Contact</h6>
                            <p>Pour toute question ou demande liée aux remboursements :</p>
                            <ul>
                                <li>Email : <a href="mailto:contact@paxevent.com">contact@paxevent.com</a></li>
                                <li>WhatsApp : +229 62 83 66 29</li>
                            </ul>
                            <p>Vous pouvez également contacter directement l'organisateur de l'événement (ses coordonnées sont disponibles sur la page de l'événement).</p>

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
