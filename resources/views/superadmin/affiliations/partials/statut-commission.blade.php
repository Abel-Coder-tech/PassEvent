@php
    /** @var \App\Models\AffiliateCommission $commission */
@endphp
@if($commission->statut === 'en_attente')
    <span class="sa-badge sa-badge-warning" title="Disponible le {{ $commission->disponible_le?->format('d/m/Y H:i') }}">En attente</span>
@elseif($commission->statut === 'disponible')
    <span class="sa-badge sa-badge-info">Disponible</span>
@elseif($commission->statut === 'paye')
    <span class="sa-badge sa-badge-success" title="Payée le {{ $commission->paye_le?->format('d/m/Y') }}">Payée</span>
@else
    <span class="sa-badge sa-badge-danger">Annulée</span>
@endif
