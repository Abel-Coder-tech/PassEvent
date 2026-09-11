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
                        <p class="text-muted" style="font-size: 0.85rem;">Dernière mise à jour : Juillet 2026</p>

                        <div class="card border-0 bg-light shadow-none mb-4">
                            <div class="card-body py-3">
                                <div class="fw-semibold mb-2" style="font-size: 0.85rem; color: var(--violet);">
                                    <i class="bi bi-journal-text me-1"></i>Pages légales
                                </div>
                                <div class="d-flex flex-wrap gap-2" style="font-size: 0.8rem;">
                                    <a href="{{ route('cgv') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">CGV</a>
                                    <a href="{{ route('confidentialite') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">Politique de confidentialité</a>
                                    <a href="{{ route('mentions-legales') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">Mentions légales</a>
                                    <a href="{{ route('cgu') }}" class="badge text-decoration-none" style="background:rgba(84,38,128,.08);color:#542680;border:1px solid rgba(84,38,128,.2);">CGU</a>
                                    <a href="{{ route('politique-remboursement') }}" class="badge text-decoration-none fw-semibold" style="background:var(--violet);color:#fff;">Politique de remboursement</a>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">
                            <h6 class="fw-bold mt-4 mb-2">1. Annulation par l'Organisateur</h6>
                            <p>En cas d'annulation définitive d'un événement par l'organisateur :</p>
                            <ul>
                                <li>Les participants reçoivent une notification automatique par e-mail.</li>
                                <li>Les tickets correspondants sont immédiatement invalidés.</li>
                                <li>Le remboursement est crédité sur le moyen de paiement d'origine (Mobile Money ou Carte bancaire) dans un délai de 24h minimum.</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">2. Annulation par le Participant</h6>
                            <p>Conformément à nos conditions de vente, les tickets achetés ne sont ni échangeables, ni remboursables, ni annulables à l'initiative de l'acheteur ou en cas de non-présentation.</p>
                            <div class="alert" style="background:#fff5f5;border:1px solid #f5c6cb;color:#842029;border-radius:.5rem;font-size:.85rem;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                <strong>Important :</strong> PaxEvent décline toute responsabilité en cas d'erreur de saisie ou de frappe lors de l'achat. Veuillez vérifier scrupuleusement les détails de l'événement avant de valider votre paiement.
                            </div>

                            <h6 class="fw-bold mt-4 mb-2">3. Tickets Gratuits</h6>
                            <p>Si un événement gratuit est annulé, les tickets associés sont automatiquement annulés. Aucun dédommagement n'est applicable puisqu'aucune transaction financière n'a eu lieu.</p>

                            <h6 class="fw-bold mt-4 mb-2">4. Frais de Transaction Non Remboursables</h6>
                            <p>En cas de remboursement d'un ticket, les frais de service initialement prélevés par nos opérateurs et agrégateurs partenaires (FedaPay et KkiaPay) restent à la charge de l'acheteur et ne sont pas restitués. Ces frais techniques correspondent aux barèmes en vigueur au Bénin (environ 1,5% selon la solution de paiement).</p>

                            <h6 class="fw-bold mt-4 mb-2">5. Cas Particuliers</h6>
                            <p>Pour toute situation exceptionnelle non mentionnée dans cette politique (report de l'événement, modification majeure du programme, etc.), le traitement se fera au cas par cas en coordination avec l'organisateur.</p>

                            <h6 class="fw-bold mt-4 mb-2">Contact</h6>
                            <p>Pour toute réclamation ou question relative à vos tickets, contactez notre support :</p>
                            <ul>
                                <li><strong>E-mail :</strong> <a href="mailto:contact@paxevent.com">contact@paxevent.com</a></li>
                                <li><strong>Site web :</strong> <a href="https://paxevent.com" target="_blank" rel="noopener noreferrer">https://paxevent.com</a></li>
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