@extends('layouts.public')

@section('title', 'Mentions légales - PaxEvent')

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
                        <p class="text-muted" style="font-size: 0.85rem;">Dernière mise à jour : {{ $derniereMiseAJour }}</p>

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">
                            <h6 class="fw-bold mt-4 mb-2">Éditeur de la plateforme</h6>
                            <ul class="list-unstyled">
                                <li><strong>Nom :</strong> PaxEvent</li>
                                <li><strong>Email :</strong> <a href="mailto:paxevent09@gmail.com">paxevent09@gmail.com</a></li>
                                <li><strong>WhatsApp :</strong> +229 62 83 66 29</li>
                                <li><strong>Pays :</strong> Bénin</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">Hébergement</h6>
                            <p>La plateforme est hébergée par les serveurs de son prestataire technique.</p>

                            <h6 class="fw-bold mt-4 mb-2">Propriété intellectuelle</h6>
                            <p>L'ensemble du contenu de la plateforme (textes, graphismes, logo, code source) est la propriété exclusive de PaxEvent. Toute reproduction, distribution ou utilisation non autorisée est interdite.</p>

                            <h6 class="fw-bold mt-4 mb-2">Protection des données</h6>
                            <p>PaxEvent collecte et traite les données personnelles conformément à sa <a href="{{ route('confidentialite') }}">Politique de confidentialité</a>. Conformément à la réglementation béninoise, vous disposez d'un droit d'accès, de rectification et de suppression de vos données.</p>

                            <h6 class="fw-bold mt-4 mb-2">Responsabilité</h6>
                            <p>PaxEvent s'efforce d'assurer l'exactitude et la disponibilité des informations publiées. Toutefois, PaxEvent ne saurait garantir l'absence d'erreurs ou d'interruptions de service.</p>

                            <h6 class="fw-bold mt-4 mb-2">Contact</h6>
                            <p>Pour toute question relative aux présentes mentions légales :</p>
                            <ul>
                                <li>Email : <a href="mailto:paxevent09@gmail.com">paxevent09@gmail.com</a></li>
                                <li>WhatsApp : +229 62 83 66 29</li>
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
