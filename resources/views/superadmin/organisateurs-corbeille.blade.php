@extends('superadmin.layouts.master')

@section('title', 'Corbeille des organisateurs - Super Admin')
@section('page-title', 'Corbeille des organisateurs')

@section('content')
<div class="sa-card mb-3">
    <div class="sa-card-body" style="font-size:0.82rem;">
        <i class="bi bi-info-circle me-1" style="color: var(--sa-primary);"></i>
        Un organisateur supprimé arrive ici avec <strong>toutes ses données</strong> (événements, ventes, retraits). Il ne peut plus se connecter et ses événements sont masqués du public.
        <strong>Restaurer</strong> le remet exactement comme avant. La <strong>suppression définitive</strong> efface tout et ne peut pas être annulée.
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-trash3 me-2" style="color: var(--sa-danger);"></i>Organisateurs dans la corbeille</span>
        <a href="{{ route('superadmin.organisateurs') }}" class="text-muted" style="font-size:0.8rem;"><i class="bi bi-arrow-left me-1"></i>Retour aux organisateurs</a>
    </div>
    <div class="sa-card-body p-0" style="overflow-x:auto;">
        <table class="sa-table">
            <thead>
                <tr><th>Organisateur</th><th>Statut avant</th><th>Événements</th><th>Supprimé le</th><th>Par</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($comptes as $compte)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ trim(($compte->prenom ?? '').' '.$compte->nom) }}</div>
                        <div class="text-muted" style="font-size:0.75rem;">{{ $compte->email }} · ID {{ $compte->id }}</div>
                    </td>
                    <td><span class="sa-badge sa-badge-info">{{ $compte->statut_avant_suppression ?? '—' }}</span></td>
                    <td>{{ $compte->evenements_count }}</td>
                    <td style="font-size:0.78rem;white-space:nowrap;">{{ $compte->deleted_at->isoFormat('D MMM YYYY HH:mm') }}</td>
                    <td style="font-size:0.78rem;">{{ $compte->supprime_par ?? '—' }}</td>
                    <td class="text-end" style="white-space:nowrap;">
                        <form action="{{ route('superadmin.organisateurs.restaurer', $compte->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="sa-btn sa-btn-sm" style="background:var(--sa-success);color:#fff;"><i class="bi bi-arrow-counterclockwise me-1"></i>Restaurer</button>
                        </form>
                        <button type="button" class="sa-btn sa-btn-sm" style="background:#fff;border:1px solid var(--sa-danger);color:var(--sa-danger);"
                                data-bs-toggle="collapse" data-bs-target="#suppr-{{ $compte->id }}">
                            <i class="bi bi-x-octagon me-1"></i>Supprimer définitivement
                        </button>
                    </td>
                </tr>
                <tr class="collapse @if($errors->any() && old('compte_id') == $compte->id) show @endif" id="suppr-{{ $compte->id }}">
                    <td colspan="6" style="background:#fff5f5;">
                        <form action="{{ route('superadmin.organisateurs.supprimer-definitivement', $compte->id) }}" method="POST" class="d-flex flex-wrap align-items-center gap-2">
                            @csrf
                            <input type="hidden" name="compte_id" value="{{ $compte->id }}">
                            <span style="font-size:0.8rem;color:var(--sa-danger);"><i class="bi bi-exclamation-triangle-fill me-1"></i>Irréversible : le compte et ses {{ $compte->evenements_count }} événement(s), ventes et retraits seront effacés. Tapez <strong>{{ $compte->email }}</strong> pour confirmer :</span>
                            <input type="text" name="confirmation" class="sa-form-control sa-form-control-sm" style="max-width:260px;" autocomplete="off" required>
                            <button type="submit" class="sa-btn sa-btn-sm" style="background:var(--sa-danger);color:#fff;">Effacer définitivement</button>
                            @if($errors->has('confirmation') && old('compte_id') == $compte->id)
                                <span style="font-size:0.78rem;color:var(--sa-danger);">{{ $errors->first('confirmation') }}</span>
                            @endif
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">La corbeille est vide.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $comptes->links() }}</div>
@endsection
