@extends('layouts.public')

@section('title', 'Politique de confidentialité — PaxEvent')
@section('description', 'Découvrez comment PaxEvent protège vos données personnelles et respecte votre vie privée.')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4" style="color: var(--violet);">
                            <i class="bi bi-shield-lock me-2"></i>Politique de confidentialité
                        </h4>

                        <div class="card border-0 bg-light shadow-none mb-4">
                            <div class="card-body py-3">
                                <div class="fw-semibold mb-2" style="font-size: 0.85rem; color: var(--violet);">
                                    <i class="bi bi-journal-text me-1"></i>Pages légales
                                </div>
                                <div class="d-flex flex-wrap gap-2" style="font-size: 0.8rem;">
                                    <a href="{{ route('cgv') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">CGV</a>
                                    <a href="{{ route('confidentialite') }}" class="badge text-decoration-none fw-semibold" style="background:var(--violet);color:#fff;">Politique de confidentialité</a>
                                    <a href="{{ route('mentions-legales') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">Mentions légales</a>
                                    <a href="{{ route('cgu') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">CGU</a>
                                    <a href="{{ route('politique-remboursement') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">Politique de remboursement</a>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">
                            <h6 class="fw-bold mt-4 mb-2">Gestion des données</h6>

                            <h6 class="fw-bold mt-4 mb-2">1. Collecte des données</h6>
                            <p>Dans le cadre de l'utilisation de la plateforme, PaxEvent collecte deux types de données personnelles :</p>
                            <ul>
                                <li>L'adresse e-mail et le nom de l'Acheteur pour l'envoi du e-ticket.</li>
                                <li>Le numéro de téléphone Mobile Money lors de l'initiation de la transaction financière via FedaPay ou Kkiapay.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">2. Traitement spécifique des numéros Mobile Money</h6>
                            <p>La sécurité de vos données financières est notre priorité absolue :</p>
                            <ul>
                                <li><strong>Non-stockage des données bancaires :</strong> Les numéros Mobile Money saisis lors du paiement sont traités directement par les serveurs sécurisés de notre agrégateur partenaire FedaPay ou Kkiapay. PaxEvent ne stocke jamais vos codes secrets de validation (codes PIN).</li>
                                <li><strong>Finalité stricte :</strong> Le numéro de téléphone Mobile Money est utilisé exclusivement pour émettre la demande de débit auprès de votre opérateur (MTN, Moov, Wave) et, le cas échéant, pour procéder au remboursement automatique des fonds en cas d'annulation de l'événement par l'Organisateur.</li>
                                <li><strong>Interdiction de revente :</strong> Conformément aux règles de l'Autorité de Protection des Données Personnelles (APDP) du Bénin, ces numéros ne sont jamais vendus, loués, ni cédés à des régies publicitaires ou à des tiers non autorisés.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">3. Droits des utilisateurs</h6>
                            <p>Conformément au Code du numérique, tout utilisateur dispose d'un droit d'accès, de rectification, d'opposition et de suppression des données personnelles le concernant. Ces droits peuvent être exercés en écrivant directement à l'adresse suivante : <a href="mailto:contact@paxevent.com">contact@paxevent.com</a>.</p>

                            <hr class="my-4">

                            <h6 class="fw-bold mt-4 mb-2">Gestion des cookies</h6>

                            <h6 class="fw-bold mt-4 mb-2">1. Qu'est-ce qu'un cookie ?</h6>
                            <p>Un cookie est un petit fichier texte stocké par votre navigateur sur votre ordinateur ou smartphone lors de votre visite sur notre site. Il nous permet de faire fonctionner la billetterie et d'analyser l'audience du site.</p>

                            <h6 class="fw-bold mt-4 mb-2">2. Quels types de cookies utilisons-nous et pourquoi ?</h6>
                            <p>Nous classons nos cookies en trois catégories distinctes :</p>
                            <ul>
                                <li><strong>Les Cookies Techniques (Strictement nécessaires) :</strong><br>
                                    <strong>Utilité :</strong> Ils sont indispensables pour réserver vos places, maintenir votre session connectée et mémoriser les billets dans votre panier pendant le processus d'achat.<br>
                                    <strong>Consentement :</strong> Non soumis à votre accord, car le site ne peut pas fonctionner sans eux.
                                </li>
                                <li><strong>Les Cookies Analytiques (Mesure d'audience) :</strong><br>
                                    <strong>Utilité :</strong> Ils nous aident à comprendre comment les utilisateurs naviguent sur PaxEvent (pages les plus visitées, temps passé) afin d'améliorer l'expérience d'achat et de fournir des statistiques anonymes aux organisateurs d'événements.<br>
                                    <strong>Consentement :</strong> Soumis à votre accord préalable.
                                </li>
                                <li><strong>Les Cookies Marketing et Réseaux Sociaux (Pixels Meta, TikTok, Google) :</strong><br>
                                    <strong>Utilité :</strong> Ils permettent à nos partenaires organisateurs d'événements de vous proposer des publicités ciblées et pertinentes sur les réseaux sociaux (Facebook, Instagram, TikTok) basées sur les événements que vous avez consultés sur PaxEvent.<br>
                                    <strong>Consentement :</strong> Strictement soumis à votre accord préalable. Aucun suivi n'est activé sans votre action positive.
                                </li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">3. Comment gérer vos consentements ?</h6>
                            <p>Conformément aux directives de l'Autorité de Protection des Données Personnelles (APDP) du Bénin :</p>
                            <ul>
                                <li>Lors de votre première visite sur PaxEvent, notre gestionnaire de cookies vous demande explicitement vos préférences.</li>
                                <li><strong>Rien n'est installé par défaut :</strong> Tant que vous n'avez pas cliqué sur « Tout accepter » ou personnalisé vos choix, aucun cookie marketing ou analytique n'est déposé.</li>
                                <li>Vous pouvez modifier vos choix ou retirer votre consentement à tout moment en cliquant sur le petit icône de cookie flottant situé en bas à droite de votre écran.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">4. Durée de conservation</h6>
                            <p>Les cookies publicitaires et de mesure d'audience ont une durée de vie limitée sur votre terminal et expirent automatiquement après un délai maximum de 13 mois.</p>

                            <h6 class="fw-bold mt-4 mb-2">5. Contact</h6>
                            <p>Pour toute question relative à notre gestion des cookies et à la protection de vos données personnelles sur PaxEvent, vous pouvez contacter notre équipe à l'adresse e-mail dédiée : <a href="mailto:contact@paxevent.com">contact@paxevent.com</a>.</p>
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