@extends('superadmin.layouts.master')

@section('title', 'QR hors plateforme - Super Admin')
@section('page-title', 'QR codes hors plateforme')

@section('content')
@if (session('success'))
<div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="alert alert-danger py-2 small">{{ session('error') }}</div>
@endif

<div class="row g-3">
    {{-- Formulaire de génération --}}
    <div class="col-lg-5">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-qr-code me-2" style="color: var(--sa-primary);"></i>Nouveau lot de QR codes</span>
            </div>
            <div class="sa-card-body">
                <p class="text-muted small mb-3">
                    Pour un événement qui n'est pas enregistré sur PaxEvent : aucun organisateur, événement ni tarif à choisir.
                    Après la génération, vous importerez le visuel du ticket et placerez le QR code.
                </p>
                <form action="{{ route('superadmin.qr-externes.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.8rem;font-weight:600;">Quantité de QR codes <span class="text-danger">*</span></label>
                        <input type="number" name="quantite" class="sa-form-control" min="1" max="1000" value="{{ old('quantite', 50) }}" required>
                        @error('quantite')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.8rem;font-weight:600;">Nom de l'événement <span class="text-muted fw-normal">(facultatif)</span></label>
                        <input type="text" name="nom_evenement" class="sa-form-control" maxlength="150" value="{{ old('nom_evenement') }}" placeholder="Ex : Soirée de fin d'année">
                        @error('nom_evenement')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100" style="background:var(--sa-primary);border-color:var(--sa-primary);">
                        <i class="bi bi-magic me-1"></i> Générer les QR codes
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Lots déjà générés --}}
    <div class="col-lg-7">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-collection me-2" style="color: var(--sa-primary);"></i>Lots générés</span>
            </div>
            <div class="sa-card-body p-0">
                @forelse ($lots as $lot)
                    <div class="qx-lot">
                        <div class="qx-vignette">
                            @if ($lot->template_url)
                                <img src="{{ $lot->template_url }}" alt="">
                            @else
                                <i class="bi bi-image"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate">{{ $lot->nom_evenement ?? 'Événement sans nom' }}</div>
                            <div class="text-muted small">
                                {{ $lot->codes_externes_count }} QR code(s) · {{ $lot->created_at->isoFormat('D MMM YYYY à HH:mm') }}
                                @unless ($lot->aUnTemplate())
                                    · <span class="text-warning">QR non placé</span>
                                @endunless
                            </div>
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <a href="{{ route('superadmin.tickets-physiques.template', $lot) }}" class="btn btn-sm btn-outline-secondary" title="Placer le QR code">
                                <i class="bi bi-bounding-box"></i>
                            </a>
                            <a href="{{ route('superadmin.qr-externes.qr-pdf', $lot) }}" class="btn btn-sm btn-outline-secondary" title="Télécharger les QR codes (PDF)">
                                <i class="bi bi-qr-code"></i>
                            </a>
                            @if ($lot->aUnTemplate())
                                <a href="{{ route('superadmin.tickets-physiques.planche', $lot) }}" class="btn btn-sm btn-outline-primary" title="Télécharger la planche avec le visuel">
                                    <i class="bi bi-download"></i>
                                </a>
                            @endif
                            <form action="{{ route('superadmin.qr-externes.supprimer', $lot) }}" method="POST" onsubmit="return confirm('Supprimer ce lot et ses QR codes ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted small py-5">
                        <i class="bi bi-qr-code d-block mb-2" style="font-size:2rem;"></i>
                        Aucun lot pour le moment.
                    </div>
                @endforelse
            </div>
        </div>
        <div class="mt-3">{{ $lots->links() }}</div>
    </div>
</div>


<style>
.qx-lot { display: flex; align-items: center; gap: .8rem; padding: .8rem 1rem; border-top: 1px solid #f0edf3; }
.qx-lot:first-child { border-top: 0; }
.qx-vignette { width: 72px; height: 44px; flex: none; border-radius: 6px; background: #f1ebf6; overflow: hidden; display: grid; place-items: center; color: #9972B0; }
.qx-vignette img { width: 100%; height: 100%; object-fit: cover; }
.min-w-0 { min-width: 0; }
</style>
@endsection
