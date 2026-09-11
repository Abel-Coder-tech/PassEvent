@extends('layouts.public')

@section('title', 'Mentions légales — PaxEvent')
@section('description', 'Consultez les mentions légales de PaxEvent, plateforme de billetterie en ligne au Bénin.')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4" style="color: var(--violet);">
                            <i class="bi bi-info-circle me-2"></i>Mentions légales
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
                                    <a href="{{ route('mentions-legales') }}" class="badge text-decoration-none fw-semibold" style="background:var(--violet);color:#fff;">Mentions légales</a>
                                    <a href="{{ route('cgu') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">CGU</a>
                                    <a href="{{ route('politique-remboursement') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">Politique de remboursement</a>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">
                            <p>Conformément aux dispositions de la Loi n° 2017-20 portant Code du numérique en République du Bénin, les utilisateurs de la plateforme PaxEvent sont informés de l'identité des différents intervenants dans le cadre de sa réalisation et de son suivi :</p>

                            <h6 class="fw-bold mt-4 mb-2">1. Éditeur de la plateforme</h6>
                            <ul class="list-unstyled">
                                <li><strong>Marque :</strong> PaxEvent</li>
                                <li><strong>Raison sociale :</strong> Noctam Communication</li>
                                <li><strong>RCCM N° :</strong> RB/PNO 20 A 13348</li>
                                <li><strong>Siège social :</strong> Oganla Atakpamè (Porto-Novo, Bénin)</li>
                                <li><strong>Email :</strong> <a href="mailto:contact@paxevent.com">contact@paxevent.com</a></li>
                                <li><strong>Téléphone :</strong> +229 0162836629</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">2. Hébergeur du site</h6>
                            <p>La plateforme est hébergée sur les serveurs de son prestataire technique, en France.</p>

                            <h6 class="fw-bold mt-4 mb-2">3. Propriété intellectuelle</h6>
                            <p><strong>Droits exclusifs :</strong> Le site www.paxevent.com, l'application et leurs contenus appartiennent exclusivement à PaxEvent et sont protégés par le droit d'auteur au Bénin. Toute modification ou distribution non autorisée de ces éléments est strictement interdite et peut faire l'objet de poursuites judiciaires.</p>
                            <p><strong>Publications :</strong> Les organisateurs d'événement doivent détenir les droits sur les contenus (photos, liens, marque…) qu'ils publient sur la plateforme.</p>

                            <h6 class="fw-bold mt-4 mb-2">4. Protection des données</h6>
                            <p><strong>Vie privée :</strong> Les données personnelles collectées sont chiffrées et exclusivement utilisées pour la génération des e-tickets et la gestion des accès, conformément à la réglementation sur la protection des données au Bénin.</p>
                            <p><strong>Sécurité bancaire :</strong> Aucune donnée bancaire n'est stockée. Les paiements sont sécurisés par des agrégateurs partenaires certifiés PCI DSS (FedaPay, KKiaPay).</p>
                            <p><strong>Cookies :</strong> Le site www.paxevent.com utilise des cookies pour l'authentification (obligatoires), la personnalisation et les statistiques. Pour en savoir plus, consultez notre <a href="{{ route('confidentialite') }}">Politique d'utilisation des Cookies</a>.</p>

                            <h6 class="fw-bold mt-4 mb-2">5. Responsabilité</h6>
                            <ul>
                                <li>PaxEvent est un intermédiaire technique de billetterie entre organisateurs et participants.</li>
                                <li>PaxEvent n'est pas responsable du contenu des événements et décline toute responsabilité concernant le contenu des liens hypertexte et l'utilisation des sites tiers vers lesquels il redirige.</li>
                                <li>PaxEvent n'est pas responsable des retards, annulations ou modifications d'événements.</li>
                                <li>La responsabilité de PaxEvent est limitée aux dysfonctionnements techniques de la plateforme.</li>
                                <li>PaxEvent ne pourra être tenue responsable des dommages découlant d'une utilisation irrégulière ou malveillante de la plateforme ou de litige lié à l'organisation intrinsèque de l'événement.</li>
                                <li>L'utilisateur s'engage à couvrir PaxEvent contre toute plainte, frais ou condamnation résultant de sa violation des présentes conditions.</li>
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