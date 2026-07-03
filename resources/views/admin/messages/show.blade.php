@extends('layouts.app')

@section('title', 'Message - ' . $message->objet)

@section('page-title', 'Details du message')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.messages.index') }}">Messages</a></li>
    <li class="breadcrumb-item active" aria-current="page">Détail</li>
@endsection

@section('content')
<div class="page-content">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Message details -->
            <div class="panel-card mb-4">
                <div class="panel-card-header">
                    <h5><i class="bi bi-envelope me-2" style="color: var(--violet);"></i>{{ $message->objet }}</h5>
                    @if(!$message->lu)
                        <span class="badge bg-primary">Nouveau</span>
                    @elseif($message->reponse_admin)
                        <span class="badge" style="background: rgba(18,151,110,0.12); color: var(--vert);">Repondu</span>
                    @else
                        <span class="status-badge status-termine">Lu</span>
                    @endif
                </div>
                <div class="panel-card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted" style="font-size: 0.78rem; font-weight: 600;">EXPEDITEUR</label>
                                <div class="fw-bold">{{ $message->nom_complet }}</div>
                                <a href="mailto:{{ $message->email }}" class="text-decoration-none" style="color: var(--violet); font-size: 0.85rem;">{{ $message->email }}</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted" style="font-size: 0.78rem; font-weight: 600;">DATE D'ENVOI</label>
                                <div class="fw-bold">{{ $message->created_at->format('d/m/Y a H:i') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted" style="font-size: 0.78rem; font-weight: 600;">MESSAGE</label>
                        <div class="p-3 rounded" style="background: var(--blanc-casse); font-size: 0.9rem; line-height: 1.7; white-space: pre-wrap;">{{ $message->message }}</div>
                    </div>

                    @if($message->reponse_admin)
                        <div class="mb-4">
                            <label class="text-muted" style="font-size: 0.78rem; font-weight: 600;">REPONSE ({{ $message->date_reponse?->format('d/m/Y H:i') }})</label>
                            <div class="p-3 rounded" style="background: rgba(18,151,110,0.08); font-size: 0.9rem; line-height: 1.7; white-space: pre-wrap;">{{ $message->reponse_admin }}</div>
                        </div>
                    @endif

                    <div class="d-flex gap-2 justify-content-between">
                        <a href="{{ route('admin.messages.index') }}" class="btn btn-secondary-custom" style="border-radius: 8px;">
                            <i class="bi bi-arrow-left me-1"></i> Retour
                        </a>
                        <form action="{{ route('admin.messages.destroy', $message->id) }}" method="POST" onsubmit="return confirm('Supprimer ce message ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-rouge" style="border-radius: 8px;">
                                <i class="bi bi-trash me-1"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Reply form -->
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-reply me-2" style="color: var(--vert);"></i>Repondre</h5>
                </div>
                <div class="panel-card-body">
                    <form action="{{ route('admin.messages.repondre', $message->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="reponse_admin" class="form-label fw-semibold">Votre reponse</label>
                            <textarea class="form-control" id="reponse_admin" name="reponse_admin" rows="6" placeholder="Redigez votre reponse ici...">{{ old('reponse_admin') }}</textarea>
                            @error('reponse_admin')
                                <div class="text-danger mt-1" style="font-size: 0.82rem;">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">La reponse sera enregistree dans le systeme et visible dans le dashboard.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="mailto:{{ $message->email }}" class="btn btn-vert" style="border-radius: 8px;">
                                <i class="bi bi-envelope me-1"></i> Envoyer par email
                            </a>
                            <button type="submit" class="btn btn-outline-vert" style="border-radius: 8px;">
                                <i class="bi bi-save me-1"></i> Enregistrer la reponse
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
