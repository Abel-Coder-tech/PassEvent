@extends('layouts.public')

@section('title', 'Conditions Generales d\'Utilisation - PaxEvent')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4" style="color: var(--violet);">
                            <i class="bi bi-file-earmark-text me-2"></i>Conditions Générales d'Utilisation
                        </h4>
                        <p class="text-muted" style="font-size: 0.85rem;">Dernière mise à jour : {{ $derniereMiseAJour }}</p>

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">
                            <h6 class="fw-bold mt-4 mb-2">1. Objet</h6>
                            <p>Les présentes conditions générales d'utilisation (CGU) régissent l'utilisation de la plateforme PaxEvent, une billetterie en ligne basée au Bénin.</p>

                            <h6 class="fw-bold mt-4 mb-2">2. Definitions</h6>
                            <ul>
                                <li><strong>Plateforme</strong> : le site web PaxEvent accessible a l'adresse indiquée</li>
                                <li><strong>Organisateur</strong> : la personne physique ou morale qui crée et gère un événement</li>
                                <li><strong>Participant</strong> : la personne qui achète un ticket pour un événement</li>
                                <li><strong>Ticket</strong> : le titre d'accès numérique généré après paiement</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">3. Inscription et compte</h6>
                            <p>Pour créer et gerer des événements, l'organisateur doit créer un compte en fournissant des informations exactes. Le compte est personnel et non transférable.</p>

                            <h6 class="fw-bold mt-4 mb-2">4. Achat de tickets</h6>
                            <ul>
                                <li>Les tickets sont achetés en ligne via la plateforme</li>
                                <li>Le paiement est effectué via KKiaPay (mobile money)</li>
                                <li>Un ticketet est émis uniquement après confirmation du paiement</li>
                                <li>Les tickets sont personnels et non transférables</li>
                                <li>Un ticket ne peut être utilise qu'une seule fois</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">5. Prix et paiement</h6>
                            <ul>
                                <li>Les prix sont fixés par les organisateurs et exprimés en FCFA</li>
                                <li>Les frais de service KKiaPay sont inclus dans le prix affiché</li>
                                <li>Le paiement est sécurisé via les opérateurs de mobile money partenaires</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">6. Annulation et remboursement</h6>
                            <ul>
                                <li>Un ticket peut être annulé par l'organisateur avant l'événement</li>
                                <li>En cas d'annulation par l'organisateur, le remboursement est effectué selon les modalités définies par l'organisateur</li>
                                <li>PaxEvent n'est pas responsable des annulations décidées par les organisateurs</li>
                                <li>Aucun remboursement n'est effectué en cas de non-présentation à l'événement</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">7. QR code et accès</h6>
                            <ul>
                                <li>Chaque ticket contient un QR code unique et personnalisé</li>
                                <li>Le QR code est scanné a l'entrée pour valider l'accès</li>
                                <li>Un QR code déjà utilisé ne permet plus l'accès</li>
                                <li>Le participant doit presenter le ticket (impression ou écran) avec un QR code lisible</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">8. Responsabilité</h6>
                            <ul>
                                <li>PaxEvent est un intermediaire technique entre organisateurs et participants</li>
                                <li>PaxEvent n'est pas responsable du contenu des événements</li>
                                <li>PaxEvent n'est pas responsable des retards, annulations ou modifications d'événements</li>
                                <li>La responsabilité de PaxEvent est limitée aux dysfonctionnements techniques de la plateforme</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">9. Propriété intellectuelle</h6>
                            <p>L'ensemble des éléments de la plateforme (logo, design, code, contenu) sont la propriété de PaxEvent et sont protégés par le droit d'auteur.</p>

                            <h6 class="fw-bold mt-4 mb-2">10. Modification des CGU</h6>
                            <p>PaxEvent se reserve le droit de modifier les présentes CGU a tout moment. Les modifications entrent en vigueur des leur publication sur la plateforme.</p>

                            <h6 class="fw-bold mt-4 mb-2">11. Loi applicable et juridiction</h6>
                            <p>Les présentes CGU sont soumises au droit beninois. En cas de litige, les tribunaux competents sont ceux du Benin.</p>

                            <h6 class="fw-bold mt-4 mb-2">12. Contact</h6>
                            <ul>
                                <li>Email : <a href="mailto:paxevent09@gmail.com">paxevent09@gmail.com</a></li>
                                <li>WhatsApp : +229 62 83 66 29</li>
                            </ul>
                        </div>

                        <a href="{{ route('accueil') }}" class="btn btn-violet" style="border-radius: 8px;">
                            <i class="bi bi-arrow-left me-1"></i> Retour a l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
