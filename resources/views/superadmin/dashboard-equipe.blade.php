@extends('superadmin.layouts.master')

@section('title', 'Tableau de bord - Équipe')
@section('page-title', 'Mon tableau de bord')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="border-radius: 10px; font-size: 0.85rem;">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h4 class="mb-1 fw-bold">Bonjour {{ auth('superadmin')->user()->prenom }} {{ auth('superadmin')->user()->nom }} </h4>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">Voici votre périmètre. Toute section hors périmètre est bloquée.</p>
    </div>
    <div>
        @foreach($roles as $slug)
            @php $roleDef = \App\Models\User::ROLES_EQUIPE[$slug]; @endphp
            <span class="sa-badge sa-badge-secondary me-1"><i class="bi {{ $roleDef['icone'] }} me-1"></i>{{ $roleDef['libelle'] }}</span>
        @endforeach
        @if(empty($roles))
            <span class="sa-badge sa-badge-danger">Aucun rôle attribué</span>
        @endif
    </div>
</div>

@if(empty($roles))
    <div class="sa-card">
        <div class="sa-card-body text-center py-5">
            <i class="bi bi-person-x-fill" style="font-size: 2.5rem; color: #adb5bd;"></i>
            <p class="text-muted mt-3 mb-0" style="font-size: 0.9rem;">
                Aucun rôle ne vous a encore été attribué.<br>
                Contactez l'administrateur pour définir votre périmètre.
            </p>
        </div>
    </div>
@else
<div class="row g-3">

    @if($nbOrganisateursEnAttente > 0)
    <div class="col-lg-6">
        <div class="sa-card h-100">
            <div class="sa-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person-check-fill me-2" style="color: var(--sa-primary);"></i>Comptes organisateurs à valider</span>
                <a href="{{ route('equipe.organisateurs') }}" class="sa-badge sa-badge-amber text-decoration-none">
                    {{ $nbOrganisateursEnAttente }} en attente
                </a>
            </div>
            <div class="sa-card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($organisateursEnAttente as $org)
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size: 0.83rem; border-color: rgba(255,255,255,0.06);">
                            <div>
                                <strong>{{ $org->organisation ?: ($org->prenom ? $org->prenom.' '.$org->nom : $org->nom) }}</strong><br>
                                <small class="text-muted">{{ $org->email }}</small>
                            </div>
                            <small class="text-muted">{{ $org->created_at->diffForHumans() }}</small>
                        </li>
                    @endforeach
                </ul>
                <div class="text-center py-2">
                    <a href="{{ route('equipe.organisateurs') }}" style="font-size: 0.78rem; color: var(--sa-primary); text-decoration: none;">Voir tous les organisateurs →</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($nbRetraitsEnAttente > 0)
    <div class="col-lg-6">
        <div class="sa-card h-100">
            <div class="sa-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cash-coin me-2" style="color: var(--sa-primary);"></i>Demandes de retrait à traiter</span>
                <a href="{{ route('equipe.retraits') }}" class="sa-badge sa-badge-amber text-decoration-none">
                    {{ $nbRetraitsEnAttente }} en attente
                </a>
            </div>
            <div class="sa-card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($retraitsEnAttente as $retrait)
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size: 0.83rem; border-color: rgba(255,255,255,0.06);">
                            <div>
                                <strong>{{ $retrait->nom ?? optional($retrait->user)->nom }}</strong><br>
                                <small class="text-muted">{{ $retrait->reseau ?? '' }} · {{ $retrait->mobile ?? '' }}</small>
                            </div>
                            <strong>{{ number_format((float) $retrait->montant, 0, ',', ' ') }} F</strong>
                        </li>
                    @endforeach
                </ul>
                <div class="text-center py-2">
                    <a href="{{ route('equipe.retraits') }}" style="font-size: 0.78rem; color: var(--sa-primary); text-decoration: none;">Toutes les demandes →</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($nbMessagesNonLus > 0)
    <div class="col-lg-6">
        <div class="sa-card h-100">
            <div class="sa-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-headset me-2" style="color: var(--sa-primary);"></i>Messages à traiter (Support)</span>
                <a href="{{ route('equipe.notifications') }}" class="sa-badge sa-badge-secondary text-decoration-none">
                    {{ $nbMessagesNonLus }} non lus
                </a>
            </div>
            <div class="sa-card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($messagesNonLus as $message)
                        <li class="list-group-item" style="font-size: 0.83rem; border-color: rgba(255,255,255,0.06);">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $message->objet ?? 'Sans objet' }}</strong>
                                <small class="text-muted">{{ $message->created_at->diffForHumans() }}</small>
                            </div>
                            <small class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($message->message), 90) }}</small>
                        </li>
                    @endforeach
                </ul>
                <div class="text-center py-2">
                    <a href="{{ route('equipe.notifications') }}" style="font-size: 0.78rem; color: var(--sa-primary); text-decoration: none;">Tous les messages →</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($nbIncidentsTechniques > 0)
    <div class="col-lg-6">
        <div class="sa-card h-100">
            <div class="sa-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-tools me-2" style="color: #e74c3c;"></i>Incidents techniques ouverts</span>
                <a href="{{ route('equipe.support') }}" class="sa-badge sa-badge-danger text-decoration-none">
                    {{ $nbIncidentsTechniques }}
                </a>
            </div>
            <div class="sa-card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($incidentsTechniques as $ticket)
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size: 0.83rem; border-color: rgba(255,255,255,0.06);">
                            <div>
                                <strong>{{ $ticket->code_unique }}</strong><br>
                                <small class="text-muted">{{ $ticket->email_achat ?: $ticket->email }}</small>
                            </div>
                            <small class="text-muted">{{ $ticket->created_at->diffForHumans() }}</small>
                        </li>
                    @endforeach
                </ul>
                <div class="text-center py-2">
                    <a href="{{ route('equipe.support') }}" style="font-size: 0.78rem; color: var(--sa-primary); text-decoration: none;">Tous les incidents →</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="col-12">
        <div class="sa-card">
            <div class="sa-card-body text-center py-3" style="font-size: 0.8rem;">
                <i class="bi bi-shield-lock-fill me-1" style="color: var(--sa-primary);"></i>
                Votre accès est limité à votre périmètre. Les statistiques financières globales sont réservées à l'administrateur.
            </div>
        </div>
    </div>

</div>
@endif
@endsection
