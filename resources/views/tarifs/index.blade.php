@extends('layouts.app')

@section('title', 'Tarifs — ' . $evenement->titre)

@section('page-title', 'Tarifs — ' . $evenement->titre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.evenements.index') }}">Événements</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.evenements.show', $evenement->id) }}">{{ $evenement->titre }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Tarifs</li>
@endsection

@section('topbar-actions')
<a href="{{ route('admin.tarifs.create', $evenement->id) }}" class="btn btn-vert btn-sm me-2">
    <i class="bi bi-plus-lg me-1"></i> <span class="btn-text">Ajouter un tarif</span>
</a>
<a href="{{ route('admin.evenements.show', $evenement->id) }}" class="btn btn-secondary-custom">
    <i class="bi bi-arrow-left me-1"></i> <span class="btn-text">Retour</span>
</a>
@endsection

@section('content')
<div class="page-content">
    @php
        $evenementAVendu = $evenement->tarifs()->sum('quantite_vendue') > 0;
        $multiJour = $jours->count() > 1;
        $groupesTarifs = collect();
        if ($multiJour) {
            $packs = $tarifs->whereNull('evenement_date_id');
            if ($packs->isNotEmpty()) {
                $groupesTarifs->push([
                    'titre' => 'Tous les jours (pack)',
                    'sous_titre' => 'Valable à chaque date de l\'événement',
                    'tarifs' => $packs,
                ]);
            }
            foreach ($jours as $jour) {
                $duJour = $tarifs->where('evenement_date_id', $jour->id);
                if ($duJour->isNotEmpty()) {
                    $groupesTarifs->push([
                        'titre' => $jour->libelle(),
                        'sous_titre' => $jour->date_debut->isoFormat('dddd D MMMM YYYY · HH:mm').' — '.$jour->capaciteEffective().' places',
                        'tarifs' => $duJour,
                    ]);
                }
            }
            $orphelins = $tarifs->filter(fn ($tarif) => $tarif->evenement_date_id && ! $jours->contains('id', $tarif->evenement_date_id));
            if ($orphelins->isNotEmpty()) {
                $groupesTarifs->push(['titre' => 'Jours supprimés', 'sous_titre' => null, 'tarifs' => $orphelins]);
            }
        } else {
            $groupesTarifs->push(['titre' => null, 'sous_titre' => null, 'tarifs' => $tarifs]);
        }
    @endphp
    @if($evenementAVendu)
        <div class="alert alert-warning">
            <i class="bi bi-info-circle me-2"></i>
            Des billets ont déjà été vendus pour cet événement : le prix d'un tarif ne peut plus être modifié directement. Cliquez sur « Modifier » puis utilisez le bouton « Demander une modification » pour soumettre un nouveau prix à validation par PaxEvent.
        </div>
    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Aucun billet vendu pour l'instant : vous pouvez créer et modifier librement les prix des tarifs.
        </div>
    @endif
    <div class="panel-card">
        <div class="card-body p-0">
        @if($tarifs->count() > 0)
            <div class="table-responsive">
                <table class="table custom-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prix</th>
                            <th>Quantité disponible</th>
                            <th>Vendus</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groupesTarifs as $groupe)
                            @if($groupe['titre'])
                                <tr class="table-light">
                                    <td colspan="6">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-1">
                                            <span class="fw-bold" style="color:var(--violet);">
                                                <i class="bi bi-calendar3 me-1"></i>{{ $groupe['titre'] }}
                                            </span>
                                            @if($groupe['sous_titre'])
                                                <small class="text-muted">{{ $groupe['sous_titre'] }}</small>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                            @foreach($groupe['tarifs'] as $tarif)
                                @php
                                    $badgeClass = match($tarif->statut) {
                                        'actif' => 'badge-publie',
                                        'épuisé' => 'badge-annule',
                                        'désactivé' => 'badge-brouillon',
                                    };
                                @endphp
                                <tr>
                                    <td class="fw-semibold">
                                        {{ $tarif->nom }}
                                        @if($tarif->jour && !$groupe['titre'])
                                            <small class="text-muted fw-normal d-block">{{ $tarif->jour->libelle() }} — {{ $tarif->jour->date_debut->isoFormat('ddd D MMM · HH:mm') }}</small>
                                        @endif
                                    </td>
                                    <td class="fw-semibold">{{ number_format($tarif->prix, 0, ',', ' ') }} F</td>
                                    <td>{{ $tarif->quantite_disponible ?? 'Illimité' }}</td>
                                    <td>
                                        {{ $tarif->quantite_vendue }}
                                        @if($attente = $listeAttente->get($tarif->id))
                                            <div class="small fw-semibold mt-1" style="color:#9a5b00;">
                                                <i class="bi bi-hourglass-split me-1"></i>{{ (int) $attente->places }} place(s) demandée(s) · {{ $attente->personnes }} personne(s)
                                            </div>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $badgeClass }}">{{ ucfirst($tarif->statut) }}</span></td>
                                    <td class="text-end">
                                        @if($attente = $listeAttente->get($tarif->id))
                                            <form action="{{ route('admin.tarifs.liste-attente.notifier', [$evenement->id, $tarif->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Prévenir les {{ $attente->personnes }} personne(s) inscrite(s) que des places sont disponibles ?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm me-1" style="background:#FED514;color:#211C31;font-weight:600;" title="Avant d'envoyer, ajoutez des places avec Modifier">
                                                    <i class="bi bi-send me-1"></i> Envoyer la notification ({{ $attente->personnes }})
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.tarifs.edit', [$evenement->id, $tarif->id]) }}" class="btn btn-sm btn-outline-secondary me-1" title="Modifier ce tarif (prix, quantité, ou demande de modification)">
                                            <i class="bi bi-pencil me-1"></i> Modifier
                                        </a>
                                        <form action="{{ route('admin.tarifs.destroy', [$evenement->id, $tarif->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce tarif ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-tag d-block mb-3" style="font-size: 3rem; color: var(--gris);"></i>
                <h5>Aucun tarif configuré</h5>
                <p>Commencez par ajouter des tarifs pour cet événement.</p>
            </div>
        @endif
    </div>
    </div>
</div>
@endsection