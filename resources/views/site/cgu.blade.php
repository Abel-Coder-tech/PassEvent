@extends('layouts.public')

@section('title', 'Conditions Generales d\'Utilisation - PassEvent')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4" style="color: var(--violet);">
                            <i class="bi bi-file-earmark-text me-2"></i>Conditions Generales d'Utilisation
                        </h4>
                        <p class="text-muted" style="font-size: 0.85rem;">Derniere mise a jour : Mai 2026</p>

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">
                            <h6 class="fw-bold mt-4 mb-2">1. Objet</h6>
                            <p>Les presentes conditions generales d'utilisation (CGU) regissent l'utilisation de la plateforme PassEvent, une billetterie en ligne basee au Benin.</p>

                            <h6 class="fw-bold mt-4 mb-2">2. Definitions</h6>
                            <ul>
                                <li><strong>Plateforme</strong> : le site web PassEvent accessible a l'adresse indiquee</li>
                                <li><strong>Organisateur</strong> : la personne physique ou morale qui cree et gere un evenement</li>
                                <li><strong>Participant</strong> : la personne qui achete un billet pour un evenement</li>
                                <li><strong>Billet</strong> : le titre d'acces numerique genere apres paiement</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">3. Inscription et compte</h6>
                            <p>Pour creer et gerer des evenements, l'organisateur doit creer un compte en fournissant des informations exactes. Le compte est personnel et non transférable.</p>

                            <h6 class="fw-bold mt-4 mb-2">4. Achat de billets</h6>
                            <ul>
                                <li>Les billets sont achetes en ligne via la plateforme</li>
                                <li>Le paiement est effectue via KKiaPay (mobile money)</li>
                                <li>Un billet est emis uniquement apres confirmation du paiement</li>
                                <li>Les billets sont personnels et non transférables</li>
                                <li>Un billet ne peut etre utilise qu'une seule fois</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">5. Prix et paiement</h6>
                            <ul>
                                <li>Les prix sont fixes par les organisateurs et exprimes en FCFA</li>
                                <li>Les frais de service KKiaPay sont inclus dans le prix affiche</li>
                                <li>Le paiement est securise via les operateurs de mobile money partenaires</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">6. Annulation et remboursement</h6>
                            <ul>
                                <li>Un billet peut etre annule par l'organisateur avant l'evenement</li>
                                <li>En cas d'annulation par l'organisateur, le remboursement est effectue selon les modalites definies par l'organisateur</li>
                                <li>PassEvent n'est pas responsable des annulations decidees par les organisateurs</li>
                                <li>Aucun remboursement n'est effectue en cas de non-presentation a l'evenement</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">7. QR code et acces</h6>
                            <ul>
                                <li>Chaque billet contient un QR code unique et personnalise</li>
                                <li>Le QR code est scanne a l'entree pour valider l'acces</li>
                                <li>Un QR code deja utilise ne permet plus l'acces</li>
                                <li>Le participant doit presenter le billet (impression ou ecran) avec un QR code lisible</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">8. Responsabilite</h6>
                            <ul>
                                <li>PassEvent est un intermediaire technique entre organisateurs et participants</li>
                                <li>PassEvent n'est pas responsable du contenu des evenements</li>
                                <li>PassEvent n'est pas responsable des retards, annulations ou modifications d'evenements</li>
                                <li>La responsabilite de PassEvent est limitee aux dysfonctionnements techniques de la plateforme</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">9. Propriete intellectuelle</h6>
                            <p>L'ensemble des elements de la plateforme (logo, design, code, contenu) sont la propriete de PassEvent et sont proteges par le droit d'auteur.</p>

                            <h6 class="fw-bold mt-4 mb-2">10. Modification des CGU</h6>
                            <p>PassEvent se reserve le droit de modifier les presentes CGU a tout moment. Les modifications entrent en vigueur des leur publication sur la plateforme.</p>

                            <h6 class="fw-bold mt-4 mb-2">11. Loi applicable et juridiction</h6>
                            <p>Les presentes CGU sont soumises au droit beninois. En cas de litige, les tribunaux competents sont ceux du Benin.</p>

                            <h6 class="fw-bold mt-4 mb-2">12. Contact</h6>
                            <ul>
                                <li>Email : <a href="mailto:passevent2026@gmail.com">passevent2026@gmail.com</a></li>
                                <li>WhatsApp : +229 43 70 45 13</li>
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
