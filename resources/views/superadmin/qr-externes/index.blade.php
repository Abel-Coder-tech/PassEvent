@extends('superadmin.layouts.master')

@section('title', 'QR hors plateforme - Super Admin')
@section('page-title', 'QR codes hors plateforme')

@php
    $evenementChoisi = (int) old('evenement_id', request('evenement'));
    $tarifsParEvenement = $evenements->mapWithKeys(fn ($e) => [
        $e->id => $e->tarifs->map(fn ($t) => [
            'id' => $t->id,
            'nom' => $t->nom,
            'prix' => (float) $t->prix,
        ])->values(),
    ]);
@endphp

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
                @if ($evenements->isEmpty())
                    <div class="text-center py-3">
                        <p class="text-muted small mb-3">Créez d'abord un événement interne : ses tarifs serviront à générer les QR codes.</p>
                        <a href="{{ route('superadmin.evenements-internes.creer') }}" class="btn btn-sm btn-primary" style="background:var(--sa-primary);border-color:var(--sa-primary);">
                            <i class="bi bi-plus-lg me-1"></i>Créer un événement interne
                        </a>
                    </div>
                @else
                    <p class="text-muted small mb-3">
                        Les QR codes générés sont de vrais billets : vos agents les scannent avec PaxAgent.
                        Vous importerez ensuite le visuel du ticket et placerez le QR code.
                    </p>
                    <form action="{{ route('superadmin.qr-externes.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Événement <span class="text-danger">*</span></label>
                            <select name="evenement_id" id="qxEvenement" class="sa-form-control" required>
                                <option value="">-- Choisir un événement interne --</option>
                                @foreach ($evenements as $ev)
                                    <option value="{{ $ev->id }}" @selected($evenementChoisi === $ev->id)>
                                        {{ $ev->titre }}{{ $ev->date_event ? ' — '.$ev->date_event->isoFormat('D MMM YYYY') : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('evenement_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="mb-3" id="qxTarifsBloc" hidden>
                            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Quantité par tarif <span class="text-danger">*</span></label>
                            <div id="qxTarifs"></div>
                            <div class="d-flex justify-content-between small text-muted mt-2">
                                <span>Total</span><strong id="qxTotal">0 QR code</strong>
                            </div>
                            @error('quantites')<small class="text-danger d-block">{{ $message }}</small>@enderror
                            @error('quantites.*')<small class="text-danger d-block">{{ $message }}</small>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="qxGenerer" style="background:var(--sa-primary);border-color:var(--sa-primary);" disabled>
                            <i class="bi bi-magic me-1"></i> Générer les QR codes
                        </button>
                    </form>
                @endif
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
                            <div class="fw-semibold text-truncate">
                                {{ $lot->titreEvenement() ?? 'Événement sans nom' }}
                                @if ($lot->tarif)<span class="text-muted fw-normal">· {{ $lot->tarif->nom }}</span>@endif
                            </div>
                            <div class="text-muted small">
                                {{ $lot->externe ? $lot->codes_externes_count : $lot->nb_tickets }} QR code(s) · {{ $lot->created_at->isoFormat('D MMM YYYY à HH:mm') }}
                                @if ($lot->externe)
                                    · <span class="text-muted">non scannable</span>
                                @endif
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

@if ($evenements->isNotEmpty())
<script>
    (function () {
        const tarifs = @json($tarifsParEvenement);
        const anciennes = @json(old('quantites', []));
        const select = document.getElementById('qxEvenement');
        const bloc = document.getElementById('qxTarifsBloc');
        const liste = document.getElementById('qxTarifs');
        const total = document.getElementById('qxTotal');
        const bouton = document.getElementById('qxGenerer');
        const fcfa = (v) => v > 0 ? new Intl.NumberFormat('fr-FR').format(v) + ' F' : 'Sans prix';

        function majTotal() {
            const somme = [...liste.querySelectorAll('input')].reduce((s, i) => s + (parseInt(i.value, 10) || 0), 0);
            total.textContent = somme + ' QR code' + (somme > 1 ? 's' : '');
            bouton.disabled = somme <= 0;
        }

        // Affiche les tarifs de l'événement choisi
        function afficher() {
            const lignes = tarifs[select.value] || [];
            liste.innerHTML = '';
            bloc.hidden = lignes.length === 0;
            lignes.forEach(function (t) {
                const div = document.createElement('div');
                div.className = 'qx-tarif';
                div.innerHTML = `<div><div class="fw-semibold"></div><div class="text-muted small">${fcfa(t.prix)}</div></div>
                    <input type="number" min="0" max="1000" class="sa-form-control" name="quantites[${t.id}]" value="${anciennes[t.id] ?? 0}">`;
                div.querySelector('.fw-semibold').textContent = t.nom;
                liste.appendChild(div);
            });
            majTotal();
        }

        select.addEventListener('change', afficher);
        liste.addEventListener('input', majTotal);
        afficher();
    })();
</script>
@endif

<style>
.qx-tarif { display: flex; justify-content: space-between; align-items: center; gap: .8rem; padding: .55rem 0; border-top: 1px solid #f0edf3; }
.qx-tarif:first-child { border-top: 0; }
.qx-tarif input { max-width: 110px; }
.qx-lot { display: flex; align-items: center; gap: .8rem; padding: .8rem 1rem; border-top: 1px solid #f0edf3; }
.qx-lot:first-child { border-top: 0; }
.qx-vignette { width: 72px; height: 44px; flex: none; border-radius: 6px; background: #f1ebf6; overflow: hidden; display: grid; place-items: center; color: #9972B0; }
.qx-vignette img { width: 100%; height: 100%; object-fit: cover; }
.min-w-0 { min-width: 0; }
</style>
@endsection
