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
                            Dernière mise à jour : Juillet 2026
                        </p>

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">
                            <p>PaxEvent est une plateforme de billetterie en ligne 100% Béninoise. La protection de vos données personnelles est une priorité. La présente politique de confidentialité vous informe de la manière dont PaxEvent (édité par Noctam Communication) collecte, utilise, partage et protège vos données personnelles lorsque vous utilisez la plateforme accessible sur <strong>www.paxevent.com</strong>.</p>
                            <p>Conformément à la <strong>loi n° 2017-20 du 20 avril 2018</strong> portant protection des données à caractère personnel en République du Bénin, PaxEvent s'engage à respecter la confidentialité et la sécurité de toutes les informations que vous fournissez.</p>

                            <h6 class="fw-bold mt-4 mb-2">1. Responsable du traitement</h6>
                            <p>Le responsable du traitement des données personnelles collectées sur la plateforme PaxEvent est :</p>
                            <ul class="list-unstyled">
                                <li><strong>Raison sociale :</strong> Noctam Communication</li>
                                <li><strong>RCCM :</strong> RB/PNO/20 A 13348</li>
                                <li><strong>Siège social :</strong> C/12 M/MARTIN, Oganla Atakpame, Porto-Novo, Bénin</li>
                                <li><strong>Contact :</strong> <a href="mailto:contact@paxevent.com">contact@paxevent.com</a> / +229 62 83 66 29</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">2. Types de données collectées</h6>
                            <p>Nous collectons uniquement les données strictement nécessaires au fonctionnement de la plateforme et à la billetterie :</p>
                            <ul>
                                <li><strong>Données d'identification :</strong> nom, prénom, adresse email, numéro de téléphone</li>
                                <li><strong>Données de paiement :</strong> Ces données sont collectées et traitées exclusivement par nos partenaires financiers agréés (FedaPay, Kkiapay). PaxEvent ne stocke, ne traite et n'a accès à aucun numéro de carte bancaire, code secret Mobile Money ou mot de passe bancaire</li>
                                <li><strong>Données de navigation :</strong> Adresse IP, type de navigateur, système d'exploitation, pages visitées, durée de la session. Ces données peuvent être collectées par des services tiers (voir section Cookies)</li>
                                <li><strong>Données d'inscription (Organisateurs) :</strong> Justificatifs d'identité ou d'existence légale (Carte d'Identité, IFU, RCCM, Récépissé), numéro de compte Mobile Money pour les reversements</li>
                                <li><strong>Données de communication :</strong> Correspondances échangées avec notre service client (email, WhatsApp, chat, formulaire de contact)</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">3. Base légale et finalités du traitement</h6>
                            <p>Conformément à la loi béninoise, vos données sont traitées sur les bases légales suivantes :</p>
                            <ul>
                                <li><strong>Exécution du contrat :</strong> Traitement des commandes, génération des e-tickets, gestion des paiements et des remboursements</li>
                                <li><strong>Consentement :</strong> Envoi de communications marketing, dépôt de certains cookies non essentiels</li>
                                <li><strong>Obligation légale :</strong> Conservation des factures et documents comptables, réponse aux autorités judiciaires</li>
                                <li><strong>Intérêt légitime :</strong> Amélioration de nos services, prévention des fraudes, sécurisation de la plateforme</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">4. Utilisation des données</h6>
                            <p>Vos données sont utilisées pour :</p>
                            <ul>
                                <li>Générer et envoyer vos billets par email (PDF + QR code)</li>
                                <li>Traiter les paiements via les agrégateurs partenaires (sans stockage des données bancaires)</li>
                                <li>Gérer les accès aux événements (scan QR code)</li>
                                <li>Vous contacter en cas d'annulation ou de modification d'un événement</li>
                                <li>Envoyer des rappels (email, SMS ou WhatsApp) avant un événement</li>
                                <li>Améliorer nos services (analyse anonyme des comportements)</li>
                                <li>Respecter nos obligations légales et réglementaires</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">5. Partage des données</h6>
                            <p>Nous ne vendons jamais vos données personnelles. Elles peuvent être partagées avec :</p>
                            <ul>
                                <li><strong>FedaPay et Kkiapay</strong> : pour le traitement des paiements (uniquement les informations nécessaires à la transaction)</li>
                                <li><strong>Les organisateurs d'événements</strong> : pour les besoins de la billetterie (nom, email, téléphone des participants à leurs événements uniquement)</li>
                                <li><strong>Prestataires techniques</strong> : hébergeur, services d'envoi d'emails, de SMS et WhatsApp Business API – dans le cadre strict de l'exécution du service</li>
                                <li><strong>Autorités légales</strong> : si la loi l'exige, sur réquisition judiciaire officielle des autorités compétentes béninoises</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">6. Gestion des cookies</h6>
                            <p><strong>Cookies essentiels :</strong> PaxEvent utilise des cookies indispensables au fonctionnement du site :</p>
                            <ul>
                                <li>Session utilisateur (maintien de la connexion)</li>
                                <li>Sécurité (token CSRF, protection contre les attaques)</li>
                                <li>Préférences de session (sélection rapide de pays, rappel de thème visuel)</li>
                            </ul>
                            <p><strong>Cookies tiers (performance et marketing) :</strong></p>
                            <p>PaxEvent peut utiliser des services tiers susceptibles de déposer des cookies de performance ou de ciblage sur votre terminal lorsque vous naviguez sur le site :</p>
                            <ul>
                                <li><strong>Google Analytics :</strong> analyse anonyme des visites pour améliorer l'expérience utilisateur</li>
                                <li><strong>Facebook Pixel :</strong> mesure des performances des campagnes publicitaires et ciblage d'audiences pertinentes sur les réseaux sociaux</li>
                            </ul>
                            <p><strong>Gestion des préférences :</strong> Lors de votre première visite, un bandeau vous permet d'accepter ou de refuser les cookies non essentiels. Vous pouvez également configurer vos préférences à tout moment via les paramètres de votre navigateur.</p>
                            <p><strong>Durée de conservation :</strong> Les cookies déposés via des services tiers sont conservés pour une durée maximale de 13 mois conformément aux recommandations de la CNIL et de l'APDP.</p>

                            <h6 class="fw-bold mt-4 mb-2">7. Sécurité</h6>
                            <p>Nous mettons en place des mesures techniques et organisationnelles robustes pour protéger vos données :</p>
                            <ul>
                                <li>Chiffrement des communications (HTTPS / TLS 1.3)</li>
                                <li>Signature QR code anti-falsification (hachage SHA-256 + clé privée)</li>
                                <li>Accès restreint aux données personnelles (contrôle d'accès basé sur les rôles)</li>
                                <li>Sauvegardes régulières des données (chiffrement AES-256 au repos)</li>
                                <li>Journalisation des accès aux données sensibles (logs d'audit horodatés)</li>
                                <li>Pare-feu applicatif (WAF) et détection d'intrusion</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">8. Durée de conservation</h6>
                            <p>Vos données sont conservées pour une durée n'excédant pas celle nécessaire aux finalités pour lesquelles elles sont collectées :</p>
                            <ul>
                                <li><strong>Données de compte organisateur :</strong> pendant toute la durée de vie du compte, et jusqu'à 2 ans après la dernière connexion</li>
                                <li><strong>Données d'achat :</strong> 5 ans après la fin de l'événement (obligation comptable et fiscale)</li>
                                <li><strong>Données de navigation :</strong> 13 mois maximum pour les cookies tiers</li>
                                <li><strong>Données de paiement :</strong> gérées par FedaPay/Kkiapay selon leur propre politique de confidentialité</li>
                            </ul>
                            <p>Passé ces délais, les données sont définitivement supprimées ou anonymisées à des fins statistiques.</p>

                            <h6 class="fw-bold mt-4 mb-2">9. Vos droits</h6>
                            <p>Conformément à la loi n° 2017-20 du 20 avril 2018 portant protection des données à caractère personnel en République du Bénin, vous disposez des droits suivants :</p>
                            <ul>
                                <li><strong>Droit d'information et d'accès :</strong> savoir quelles données nous détenons sur vous et comment elles sont traitées</li>
                                <li><strong>Droit de rectification :</strong> demander la correction de vos données inexactes ou incomplètes</li>
                                <li><strong>Droit à l'effacement (droit à l'oubli) :</strong> demander la suppression de vos données personnelles, sous réserve des obligations légales de conservation</li>
                                <li><strong>Droit à la portabilité :</strong> recevoir vos données dans un format structuré, couramment utilisé et lisible</li>
                                <li><strong>Droit d'opposition :</strong> vous opposer, pour des motifs légitimes, au traitement de vos données</li>
                                <li><strong>Droit de retirer votre consentement :</strong> à tout moment, pour les traitements fondés sur votre consentement</li>
                                <li><strong>Droit de réclamation :</strong> saisir l'Autorité de Protection des Données Personnelles (APDP) du Bénin</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">10. Délégué à la Protection des Données (DPO)</h6>
                            <p>Pour toute question relative à vos données personnelles ou pour exercer vos droits, vous pouvez contacter notre Délégué à la Protection des Données (DPO) :</p>
                            <ul>
                                <li><strong>Email :</strong> <a href="mailto:dpo@paxevent.com">dpo@paxevent.com</a></li>
                                <li><strong>Adresse :</strong> PaxEvent – DPO, C/12 M/MARTIN, Oganla Atakpame, Porto-Novo, Bénin</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">11. Autorité de contrôle compétente (APDP)</h6>
                            <p>Conformément à la loi béninoise, vous avez le droit d'introduire une réclamation auprès de l'Autorité de Protection des Données Personnelles (APDP) si vous estimez que vos droits ne sont pas respectés :</p>
                            <ul>
                                <li><strong>Site web :</strong> <a href="https://apdp.bj" target="_blank" rel="noopener noreferrer">apdp.bj</a></li>
                                <li><strong>Adresse :</strong> Bénin</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">12. Modification de la politique</h6>
                            <p>Nous nous réservons le droit de modifier la présente politique de confidentialité à tout moment pour l'adapter aux évolutions législatives, réglementaires ou techniques. Toute modification substantielle vous sera notifiée par email et publiée sur cette page. La date de la dernière mise à jour est indiquée en haut de cette page.</p>

                            <h6 class="fw-bold mt-4 mb-2">13. Contact</h6>
                            <p>Pour toute question relative à cette politique de confidentialité ou à l'exercice de vos droits :</p>
                            <ul>
                                <li>Email : <a href="mailto:contact@paxevent.com">contact@paxevent.com</a></li>
                                <li>WhatsApp : +229 62 83 66 29</li>
                                <li>Adresse : C/12 M/MARTIN, Oganla Atakpame, Porto-Novo, Bénin</li>
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
