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
                            Dernière mise à jour : {{ $derniereMiseAJour }}
                        </p>

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">

                            <h6 class="fw-bold mt-4 mb-2">1. Annulation par l'organisateur</h6>
                            <p>Lorsqu'un organisateur annule son événement :</p>
                            <ul>
                                <li>Tous les acheteurs sont automatiquement notifiés par email (adresse utilisée lors de l'achat).</li>
                                <li>Les billets sont immédiatement marqués comme annulés dans le système.</li>
                                <li>Le remboursement est effectué sur le moyen de paiement d'origine (Mobile Money) dans un délai de 3 à 5 jours ouvrés.</li>
                                <li>PaxEvent n'est pas responsable du traitement des remboursements ; ceux-ci sont gérés directement par l'organisateur via sa passerelle KKiaPay.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">2. Annulation par l'acheteur (participant)</h6>
                            <p>Sauf indication contraire de l'organisateur au moment de l'achat, les billets ne sont ni échangeables ni remboursables à l'initiative de l'acheteur.</p>
                            <p>Nous vous conseillons de vérifier attentivement les détails de l'événement (date, lieu, conditions) avant de finaliser votre achat.</p>

                            <h6 class="fw-bold mt-4 mb-2">3. Annulation avant l'événement (optionnel)</h6>
                            <p>L'organisateur peut définir une date limite d'annulation (ex : 48h avant l'événement) pendant laquelle l'acheteur peut demander un remboursement partiel (ex : 80% du prix).</p>
                            <ul>
                                <li>Dans ce cas, le remboursement est effectué directement par l'organisateur, après validation par PaxEvent (si la fonctionnalité est activée).</li>
                                <li>Les frais de transaction KKiaPay (environ 1,5%) ne sont pas remboursés.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">4. Billets gratuits</h6>
                            <p>En cas d'annulation d'un événement avec billets gratuits, les billets sont automatiquement annulés.</p>
                            <p>Aucun remboursement n'est nécessaire puisqu'aucun paiement n'a été effectué.</p>

                            <h6 class="fw-bold mt-4 mb-2">5. Frais de transaction (KKiaPay)</h6>
                            <p>Les frais de transaction préalablement prélevés par le prestataire de paiement (KKiaPay) lors de l'achat initial ne sont pas remboursés en cas de remboursement.</p>
                            <p>Ces frais représentent environ 1,9% du montant total de la transaction au Bénin (selon l'opérateur Mobile Money utilisé).</p>

                            <h6 class="fw-bold mt-4 mb-2">6. Billets payés (remboursement partiel)</h6>
                            <p>En cas de remboursement partiel (ex : annulation avant l'événement), seuls les frais de transaction KKiaPay ne sont pas remboursés.</p>
                            <p>Le montant restant (prix du ticket – frais KKiaPay) est reversé à l'acheteur.</p>

                            <h6 class="fw-bold mt-4 mb-2">7. Modification de l'événement (report, changement de lieu, etc.)</h6>
                            <p>En cas de report ou de modification substantielle de l'événement, l'organisateur doit en informer les acheteurs.</p>
                            <p>Les acheteurs peuvent alors :</p>
                            <ul>
                                <li>Accepter la modification (le billet reste valable).</li>
                                <li>Refuser la modification et demander un remboursement (dans ce cas, les frais de transaction KKiaPay ne sont pas remboursés).</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">8. Cas particuliers</h6>
                            <p>Pour toute situation non couverte par cette politique (annulation partielle, changement de programmation, cas de force majeure, etc.), l'organisateur est invité à contacter PaxEvent pour une prise en charge individuelle.</p>
                            <p>Chaque cas sera étudié au cas par cas.</p>

                            <h6 class="fw-bold mt-4 mb-2">9. Responsabilité de PaxEvent</h6>
                            <p>PaxEvent agit uniquement en tant que plateforme de mise en relation entre les organisateurs et les acheteurs.</p>
                            <p>En cas de litige, PaxEvent s'engage à faciliter la communication et à fournir les preuves de transaction (si disponibles), mais n'est pas responsable des remboursements effectués par les organisateurs.</p>

                            <h6 class="fw-bold mt-4 mb-2">10. Contact</h6>
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
