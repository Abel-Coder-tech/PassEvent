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

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">
                            <h6 class="fw-bold mt-4 mb-2">Éditeur de la plateforme</h6>
                            <p>La plateforme PaxEvent (accessible à l'adresse www.paxevent.com) est éditée par :</p>
                            <ul class="list-unstyled">
                                <li><strong>Raison sociale :</strong> Noctam Communication</li>
                                <li><strong>Marque commerciale :</strong> PaxEvent</li>
                                <li><strong>RCCM :</strong> RB/PNO/20 A 13348</li>
                                <li><strong>Siège social :</strong> C/12 M/MARTIN, Oganla Atakpame, Porto-Novo, Bénin</li>
                                <li><strong>Email :</strong> <a href="mailto:contact@paxevent.com">contact@paxevent.com</a></li>
                                <li><strong>WhatsApp :</strong> +229 62 83 66 29</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">Directeur de la publication</h6>
                            <p>Le Directeur de la publication est le représentant légal de Noctam Communication.</p>

                            <h6 class="fw-bold mt-4 mb-2">Hébergement</h6>
                            <p>La plateforme PaxEvent est hébergée par un prestataire technique spécialisé dans l'hébergement de sites web et d'applications sécurisées. L'hébergement est assuré par les serveurs sécurisés situés en Europe, garantissant une haute disponibilité et une protection avancée contre les cyberattaques.</p>

                            <h6 class="fw-bold mt-4 mb-2">Propriété intellectuelle</h6>
                            <p>L'ensemble du contenu de la plateforme PaxEvent, incluant sans limitation les textes, graphismes, logos, icônes, images, vidéos, code source, base de données et architecture technique, est la propriété exclusive de PaxEvent (Noctam Communication).</p>
                            <p>Toute reproduction, représentation, modification, distribution ou exploitation non autorisée de tout ou partie de ces éléments, par quelque procédé que ce soit, est strictement interdite et constitue une contrefaçon susceptible d'entraîner des poursuites judiciaires conformément au Code béninois de la propriété intellectuelle et au Code du numérique en République du Bénin.</p>

                            <h6 class="fw-bold mt-4 mb-2">Protection des données</h6>
                            <p>PaxEvent collecte et traite les données personnelles conformément à sa <a href="{{ route('confidentialite') }}">Politique de confidentialité</a> et à la loi n° 2017-20 du 20 avril 2018 portant protection des données à caractère personnel en République du Bénin.</p>
                            <p>Conformément à cette réglementation, vous disposez d'un droit d'accès, de rectification, d'effacement et de portabilité de vos données, ainsi que d'un droit d'opposition et de retrait de votre consentement. Pour exercer ces droits, contactez-nous à <a href="mailto:contact@paxevent.com">contact@paxevent.com</a>.</p>

                            <h6 class="fw-bold mt-4 mb-2">Responsabilité</h6>
                            <p>PaxEvent s'efforce d'assurer l'exactitude et la mise à jour des informations publiées sur sa plateforme. Toutefois, PaxEvent ne saurait garantir l'absence d'erreurs, d'omissions ou d'interruptions de service.</p>
                            <p>PaxEvent intervient en qualité de simple intermédiaire technique de mise en relation entre organisateurs et participants. En conséquence, PaxEvent décline toute responsabilité en cas de litige entre un organisateur et un participant, notamment en ce qui concerne la qualité de l'événement, la non-tenue de celui-ci, ou tout dommage survenant lors de son déroulement.</p>

                            <h6 class="fw-bold mt-4 mb-2">Contact</h6>
                            <p>Pour toute question relative aux présentes mentions légales :</p>
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
