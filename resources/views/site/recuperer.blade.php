@extends('layouts.public')

@section('title', 'Recuperer mon ticket - PaxEvent')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Récupérer mon ticket</li>
@endsection

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4 text-center">
                        <div class="mb-4">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px; background: rgba(135,66,139,0.08);">
                                <i class="bi bi-ticket-perforated" style="font-size: 1.8rem; color: var(--violet);"></i>
                            </div>
                            <h4 class="fw-bold mb-2">Récupérer mon ticket</h4>
                            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                Entrez les informations utilisées lors de l'achat
                            </p>
                        </div>

                        <form action="{{ route('tickets.rechercher') }}" method="POST">
                            @csrf

                            <div class="mb-3 text-start">
                                <label for="nom" class="form-label fw-semibold">Nom et prénoms</label>
                                <input type="text" class="form-control" id="nom" name="nom" placeholder="Kofi Adebayo" value="{{ old('nom') }}" required>
                            </div>

                            <div class="mb-3 text-start">
                                <label for="email" class="form-label fw-semibold">Email de l'acheteur</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="votre@email.com" value="{{ old('email') }}" required>
                            </div>

                            <div class="mb-4 text-start">
                                <label for="telephone" class="form-label fw-semibold">Numéro de téléphone</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" placeholder="+229 62 83 66 29" value="{{ old('telephone') }}" required>
                            </div>

                            <button type="submit" class="btn btn-violet w-100 py-3" style="border-radius: 8px;">
                                <i class="bi bi-search me-2"></i> Rechercher mon ticket
                            </button>
                        </form>

                        <div class="mt-4">
                            <a href="{{ route('accueil') }}" class="text-decoration-underline" style="color: var(--violet); font-size: 0.88rem;">
                                <i class="bi bi-arrow-left me-1"></i> Retour a l'accueil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
