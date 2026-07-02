@extends('layouts.public')

@section('title', 'Redirection paiement — PaxEvent')
@section('description', 'Redirection vers la plateforme de paiement')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm text-center" style="border-radius: 16px;">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <div class="spinner-border" style="color: #198754; width: 3rem; height: 3rem;" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-2">Redirection vers Sebpay</h5>
                        <p class="text-muted mb-4" style="font-size: 0.9rem;">
                            Vous allez etre redirige pour valider le paiement.
                        </p>
                        <p class="mb-0" style="font-size: 0.82rem;">
                            <a href="{{ $url }}" class="btn btn-success btn-lg px-5" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-box-arrow-up-right me-2"></i> Ouvrir Sebpay
                            </a>
                        </p>
                        <p class="mt-3" style="font-size: 0.78rem; color: #999;">
                            Apres validation, revenez sur cette page pour voir la confirmation.
                        </p>
                        <a href="{{ route('paiement.sebpay.callback', ['ticket' => $ticket->id, 'transaction_id' => $transactionId]) }}" class="btn btn-outline-secondary btn-sm mt-3">
                            Deja paye ? Verifier le statut
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
