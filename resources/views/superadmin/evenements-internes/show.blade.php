@extends('superadmin.layouts.master')

@section('title', $evenement->titre.' - Événement interne')
@section('page-title', $evenement->titre)

@section('content')
@if (session('success'))
<div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="alert alert-danger py-2 small">{{ session('error') }}</div>
@endif
@if ($errors->any())
<div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
@endif

<div class="d-flex gap-2 flex-wrap mb-3">
    <a href="{{ route('superadmin.evenements-internes') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Retour</a>
    <span class="badge bg-light text-muted border align-self-center"><i class="bi bi-eye-slash me-1"></i>Masqué du public</span>
    <a href="{{ route('superadmin.qr-externes', ['evenement' => $evenement->id]) }}" class="btn btn-sm btn-primary ms-auto" style="background:var(--sa-primary);border-color:var(--sa-primary);">
        <i class="bi bi-qr-code me-1"></i>Générer des QR codes
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="sa-card mb-3">
            <div class="sa-card-header"><span><i class="bi bi-info-circle me-2" style="color:var(--sa-primary);"></i>Informations</span></div>
            <div class="sa-card-body">
                @if ($evenement->image)
                    <img src="{{ asset('storage/'.$evenement->image) }}" alt="" class="img-fluid rounded mb-3">
                @endif
                <dl class="ei-infos">
                    <dt>Auteur</dt><dd>{{ $evenement->auteurComplet() ?? '—' }}</dd>
                    <dt>Lieu</dt><dd>{{ $evenement->lieu }}</dd>
                    <dt>Capacité</dt><dd>{{ number_format($evenement->capacite, 0, ',', ' ') }}</dd>
                    <dt>Date{{ $evenement->dates->count() > 1 ? 's' : '' }}</dt>
                    <dd>
                        @foreach ($evenement->dates as $date)
                            <div>{{ $date->date_debut->isoFormat('dddd D MMMM YYYY à HH:mm') }}</div>
                        @endforeach
                    </dd>
                    @if ($evenement->description)
                        <dt>Description</dt><dd>{{ $evenement->description }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        <div class="sa-card mb-3">
            <div class="sa-card-header"><span><i class="bi bi-tags me-2" style="color:var(--sa-primary);"></i>Tarifs</span></div>
            <div class="sa-card-body p-0">
                @foreach ($evenement->tarifs as $tarif)
                    <div class="ei-rangee">
                        <span class="fw-semibold">{{ $tarif->nom }}</span>
                        <span class="text-muted">{{ (float) $tarif->prix > 0 ? number_format((float) $tarif->prix, 0, ',', ' ').' F' : 'Sans prix' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="sa-card mb-3">
            <div class="sa-card-header"><span><i class="bi bi-person-badge me-2" style="color:var(--sa-primary);"></i>Agents de scan</span></div>
            <div class="sa-card-body">
                <form action="{{ route('superadmin.evenements-internes.agents', $evenement) }}" method="POST" class="row g-2 align-items-end mb-3">
                    @csrf
                    <div class="col-sm-5">
                        <label class="ei-label">Nom</label>
                        <input type="text" name="nom" class="sa-form-control" maxlength="255" value="{{ old('nom') }}" required>
                    </div>
                    <div class="col-sm-7">
                        <label class="ei-label">Email</label>
                        <input type="email" name="email" class="sa-form-control" maxlength="255" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-sm btn-primary" style="background:var(--sa-primary);border-color:var(--sa-primary);">
                            <i class="bi bi-person-plus me-1"></i>Créer l'agent et lui envoyer ses identifiants
                        </button>
                    </div>
                </form>

                @forelse ($evenement->agents as $agent)
                    @php
                        $st = $statsAgents[$agent->id] ?? ['total' => 0, 'valides' => 0, 'dernier' => null];
                    @endphp
                    <div class="ei-rangee {{ $agent->actif ? '' : 'opacity-50' }}">
                        <div class="min-w-0">
                            <div><span class="fw-semibold">{{ $agent->nom }}</span> <span class="text-muted small">{{ $agent->email }}</span></div>
                            <div class="text-muted small">
                                {{ $st['total'] }} scan{{ $st['total'] > 1 ? 's' : '' }}
                                · <span class="text-success">{{ $st['valides'] }} entrée{{ $st['valides'] > 1 ? 's' : '' }}</span>
                                · <span class="text-danger">{{ $st['total'] - $st['valides'] }} refus</span>
                                @if ($st['dernier'])
                                    · dernier {{ \Carbon\Carbon::parse($st['dernier'])->diffForHumans() }}
                                @endif
                            </div>
                        </div>
                        <form action="{{ route('superadmin.evenements-internes.agents.basculer', [$evenement, $agent->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $agent->actif ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                {{ $agent->actif ? 'Désactiver' : 'Réactiver' }}
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Aucun agent de scan pour le moment.</p>
                @endforelse
            </div>
        </div>

        <div class="sa-card mb-3">
            <div class="sa-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2" style="color:var(--sa-primary);"></i>Historique des scans</span>
                <span class="text-muted small">{{ $resume['entres'] }} / {{ $resume['billets'] }} billets entrés</span>
            </div>
            <div class="sa-card-body p-0">
                @php
                    $libelles = [
                        'valide' => ['Entrée validée', 'success'],
                        'deja_utilise' => ['Déjà utilisé', 'warning'],
                        'pas_encore_ouvert' => ['Trop tôt', 'info'],
                        'termine' => ['Événement terminé', 'secondary'],
                        'invalide' => ['Invalide', 'danger'],
                    ];
                @endphp
                @if ($scans->count() > 0)
                    <div class="table-responsive">
                        <table class="sa-table mb-0">
                            <thead>
                                <tr><th>Date</th><th>Agent</th><th>Billet</th><th>Résultat</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($scans as $scan)
                                    @php
                                        [$libelle, $couleur] = $libelles[$scan->details['resultat'] ?? 'invalide'] ?? $libelles['invalide'];
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap">{{ $scan->created_at?->isoFormat('D MMM HH:mm:ss') }}</td>
                                        <td>{{ $scan->agent?->nom ?? '—' }}</td>
                                        <td>
                                            <code>{{ $scan->ticket?->code_unique ?? ($scan->details['code'] ?? '—') }}</code>
                                            @if ($scan->ticket?->nom_tarif)<span class="text-muted small">· {{ $scan->ticket->nom_tarif }}</span>@endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $couleur }}">{{ $libelle }}</span>
                                            @if (($scan->details['resultat'] ?? '') !== 'valide' && ! empty($scan->details['raison']))
                                                <div class="text-muted small">{{ $scan->details['raison'] }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-2">{{ $scans->links() }}</div>
                @else
                    <p class="text-muted small p-3 mb-0">Aucun scan pour le moment.</p>
                @endif
            </div>
        </div>

        <div class="sa-card">
            <div class="sa-card-header"><span><i class="bi bi-qr-code me-2" style="color:var(--sa-primary);"></i>Lots de QR codes</span></div>
            <div class="sa-card-body p-0">
                @forelse ($lots as $lot)
                    <div class="ei-rangee">
                        <div>
                            <span class="fw-semibold">{{ $lot->tarif?->nom ?? '—' }}</span>
                            <span class="text-muted small">· {{ $lot->quantite }} QR · {{ $lot->created_at->isoFormat('D MMM YYYY') }}</span>
                        </div>
                        <a href="{{ route('superadmin.tickets-physiques.template', $lot) }}" class="btn btn-sm btn-outline-secondary">Ouvrir</a>
                    </div>
                @empty
                    <p class="text-muted small p-3 mb-0">Aucun QR code généré.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
.min-w-0 { min-width: 0; }
.ei-label { font-size: .8rem; font-weight: 600; margin-bottom: .3rem; display: block; }
.ei-infos { display: grid; grid-template-columns: 110px 1fr; gap: .4rem .8rem; margin: 0; font-size: .88rem; }
.ei-infos dt { color: #6b6474; font-weight: 600; }
.ei-infos dd { margin: 0; }
.ei-rangee { display: flex; justify-content: space-between; align-items: center; gap: .8rem; padding: .7rem 1rem; border-top: 1px solid #f0edf3; }
.ei-rangee:first-of-type { border-top: 0; }
</style>
@endsection
