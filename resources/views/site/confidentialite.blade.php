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
                        <p class="text-muted" style="font-size: 0.85rem;">
                            Dernière mise à jour : {{ $derniereMiseAJour }}
                        </p>

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">

                            <h6 class="fw-bold mt-4 mb-2">1. Collecte des données</h6>
                            <p>PaxEvent collecte les informations suivantes lors de l'achat d'un billet ou de l'inscription d'un organisateur :</p>
                            <ul>
                                <li><strong>Nom et prénom</strong> (pour l'identification et l'envoi des billets)</li>
                                <li><strong>Adresse email</strong> (pour l'envoi des billets et les notifications)</li>
                                <li><strong>Numéro de téléphone</strong> (optionnel, utilisé pour le paiement via KKiaPay et les rappels)</li>
                                <li><strong>Informations de paiement</strong> (traitées exclusivement par KKiaPay – PaxEvent ne stocke aucune donnée bancaire ou Mobile Money)</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">2. Utilisation des données</h6>
                            <p>Vos données sont utilisées pour :</p>
                            <ul>
                                <li>Générer et envoyer vos billets par email (PDF + QR code)</li>
                                <li>Traiter les paiements via KKiaPay (sans stockage des données bancaires)</li>
                                <li>Gérer les accès aux événements (scan QR code)</li>
                                <li>Vous contacter en cas d'annulation ou de modification d'un événement</li>
                                <li>Envoyer des rappels (email) avant un événement</li>
                                <li>Améliorer nos services (analyse anonyme des comportements)</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">3. Partage des données</h6>
                            <p>Nous ne vendons jamais vos données personnelles. Elles peuvent être partagées avec :</p>
                            <ul>
                                <li><strong>KKiaPay</strong> : pour le traitement des paiements (uniquement les informations nécessaires à la transaction)</li>
                                <li><strong>Les organisateurs d'événements</strong> : pour les besoins de la billetterie (nom, email, téléphone des participants à leurs événements uniquement)</li>
                                <li><strong>Prestataires techniques</strong> : hébergeur, service d'envoi d'emails – dans le cadre strict de l'exécution du service</li>
                                <li><strong>Autorités légales</strong> : si la loi l'exige (sur demande officielle)</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">4. Sécurité</h6>
                            <p>Nous mettons en place des mesures pour protéger vos données :</p>
                            <ul>
                                <li>Chiffrement des communications (HTTPS / TLS)</li>
                                <li>Signature QR code anti-falsification</li>
                                <li>Accès restreint aux données personnelles (seuls les administrateurs autorisés y ont accès)</li>
                                <li>Sauvegardes régulières des données (chiffrées)</li>
                                <li>Journalisation des accès aux données sensibles (logs d'audit)</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">5. Durée de conservation</h6>
                            <p>Vos données sont conservées pendant 2 ans après votre dernière interaction avec la plateforme (achat, connexion, etc.).</p>
                            <p>Passé ce délai, elles sont anonymisées ou supprimées, sauf obligation légale de conservation (ex: facturation).</p>
                            <p>Les données de paiement (via KKiaPay) sont conservées par KKiaPay selon leur propre politique.</p>

                            <h6 class="fw-bold mt-4 mb-2">6. Vos droits</h6>
                            <p>Conformément à la législation en vigueur (loi béninoise sur la protection des données), vous disposez des droits suivants :</p>
                            <ul>
                                <li><strong>Droit d'accès</strong> : savoir quelles données nous détenons sur vous</li>
                                <li><strong>Droit de rectification</strong> : corriger des informations inexactes</li>
                                <li><strong>Droit à l'effacement</strong> : demander la suppression de vos données</li>
                                <li><strong>Droit à la portabilité</strong> : recevoir vos données dans un format structuré</li>
                                <li><strong>Droit d'opposition</strong> : vous opposer à l'utilisation de vos données pour le marketing</li>
                            </ul>
                            <p>Pour exercer ces droits, contactez-nous à <a href="mailto:contact@paxevent.com">contact@paxevent.com</a>.</p>

                            <h6 class="fw-bold mt-4 mb-2">7. Cookies</h6>
                            <p>PaxEvent utilise uniquement des cookies essentiels pour le fonctionnement du site :</p>
                            <ul>
                                <li>Session utilisateur (maintien de la connexion)</li>
                                <li>Sécurité (token CSRF, protection contre les attaques)</li>
                            </ul>
                            <p>Aucun cookie de traçage publicitaire (Google Analytics, Facebook Pixel, etc.) n'est utilisé.</p>

                            <h6 class="fw-bold mt-4 mb-2">8. Modification de la politique</h6>
                            <p>Nous nous réservons le droit de modifier cette politique de confidentialité.</p>
                            <p>Toute modification sera notifiée par email aux utilisateurs enregistrés et publiée sur cette page.</p>
                            <p>La date de la dernière mise à jour est indiquée en haut de cette page.</p>

                            <h6 class="fw-bold mt-4 mb-2">9. Contact</h6>
                            <p>Pour toute question relative à cette politique de confidentialité :</p>
                            <ul>
                                <li>Email : <a href="mailto:contact@paxevent.com">contact@paxevent.com</a></li>
                                <li>WhatsApp : +229 62 83 66 29</li>
                                <li>Adresse : Bénin</li>
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
