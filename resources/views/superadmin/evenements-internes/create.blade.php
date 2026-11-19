@extends('superadmin.layouts.master')

@section('title', 'Nouvel événement interne - Super Admin')
@section('page-title', 'Nouvel événement interne')

@php
    $anciennesDates = old('dates', ['']);
    $anciensTarifs = old('tarifs', [['nom' => 'Standard', 'prix' => '']]);
@endphp

@section('content')
@if ($errors->any())
<div class="alert alert-danger py-2 small">
    <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $erreur)<li>{{ $erreur }}</li>@endforeach
    </ul>
</div>
@endif

<form action="{{ route('superadmin.evenements-internes.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="sa-card mb-3">
                <div class="sa-card-header"><span><i class="bi bi-calendar-event me-2" style="color:var(--sa-primary);"></i>L'événement</span></div>
                <div class="sa-card-body">
                    <div class="mb-3">
                        <label class="ei-label">Nom de l'événement <span class="text-danger">*</span></label>
                        <input type="text" name="titre" class="sa-form-control" maxlength="255" value="{{ old('titre') }}" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="ei-label">Prénom de l'auteur <span class="text-danger">*</span></label>
                            <input type="text" name="auteur_prenom" class="sa-form-control" maxlength="100" value="{{ old('auteur_prenom') }}" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="ei-label">Nom de l'auteur <span class="text-danger">*</span></label>
                            <input type="text" name="auteur_nom" class="sa-form-control" maxlength="100" value="{{ old('auteur_nom') }}" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-8">
                            <label class="ei-label">Lieu <span class="text-danger">*</span></label>
                            <input type="text" name="lieu" class="sa-form-control" maxlength="255" value="{{ old('lieu') }}" required>
                        </div>
                        <div class="col-sm-4">
                            <label class="ei-label">Capacité <span class="text-danger">*</span></label>
                            <input type="number" name="capacite" class="sa-form-control" min="1" value="{{ old('capacite', 500) }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="ei-label">Description <span class="text-muted fw-normal">(facultatif)</span></label>
                        <textarea name="description" class="sa-form-control" rows="3" maxlength="5000">{{ old('description') }}</textarea>
                    </div>
                    <div>
                        <label class="ei-label">Image <span class="text-muted fw-normal">(facultatif · JPG, PNG, WEBP · 5 Mo max)</span></label>
                        <input type="file" name="image" class="sa-form-control" accept="image/jpeg,image/png,image/webp">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="sa-card mb-3">
                <div class="sa-card-header"><span><i class="bi bi-clock me-2" style="color:var(--sa-primary);"></i>Dates</span></div>
                <div class="sa-card-body">
                    <div id="eiDates">
                        @foreach ($anciennesDates as $date)
                            <div class="ei-ligne">
                                <input type="datetime-local" name="dates[]" class="sa-form-control" value="{{ $date }}" required>
                                <button type="button" class="btn btn-sm btn-outline-danger ei-retirer" title="Retirer"><i class="bi bi-x-lg"></i></button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="eiAjouterDate"><i class="bi bi-plus-lg me-1"></i>Ajouter une date</button>
                </div>
            </div>

            <div class="sa-card mb-3">
                <div class="sa-card-header"><span><i class="bi bi-tags me-2" style="color:var(--sa-primary);"></i>Tarifs</span></div>
                <div class="sa-card-body">
                    <p class="text-muted small mb-2">Le prix est facultatif : il sert seulement d'indication, aucun paiement n'est encaissé.</p>
                    <div id="eiTarifs">
                        @foreach ($anciensTarifs as $i => $tarif)
                            <div class="ei-ligne">
                                <input type="text" name="tarifs[{{ $i }}][nom]" class="sa-form-control" placeholder="Nom (ex : VIP)" maxlength="100" value="{{ $tarif['nom'] ?? '' }}" required>
                                <input type="number" name="tarifs[{{ $i }}][prix]" class="sa-form-control ei-prix" placeholder="Prix (F)" min="0" value="{{ $tarif['prix'] ?? '' }}">
                                <button type="button" class="btn btn-sm btn-outline-danger ei-retirer" title="Retirer"><i class="bi bi-x-lg"></i></button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="eiAjouterTarif"><i class="bi bi-plus-lg me-1"></i>Ajouter un tarif</button>
                </div>
            </div>

            <div class="alert alert-light border small"><i class="bi bi-eye-slash me-1"></i>L'événement sera masqué du public : ni liste, ni recherche, ni page publique.</div>
            <button type="submit" class="btn btn-primary w-100" style="background:var(--sa-primary);border-color:var(--sa-primary);">
                <i class="bi bi-check-lg me-1"></i>Créer l'événement
            </button>
        </div>
    </div>
</form>

<script>
    (function () {
        let indexTarif = {{ count($anciensTarifs) }};
        const retirer = (conteneur) => conteneur.addEventListener('click', function (e) {
            const btn = e.target.closest('.ei-retirer');
            if (!btn) return;
            // On garde toujours au moins une ligne
            if (conteneur.querySelectorAll('.ei-ligne').length > 1) btn.closest('.ei-ligne').remove();
        });
        const dates = document.getElementById('eiDates');
        const tarifs = document.getElementById('eiTarifs');
        retirer(dates);
        retirer(tarifs);
        document.getElementById('eiAjouterDate').addEventListener('click', function () {
            const ligne = dates.querySelector('.ei-ligne').cloneNode(true);
            ligne.querySelector('input').value = '';
            dates.appendChild(ligne);
        });
        document.getElementById('eiAjouterTarif').addEventListener('click', function () {
            const div = document.createElement('div');
            div.className = 'ei-ligne';
            div.innerHTML = `<input type="text" name="tarifs[${indexTarif}][nom]" class="sa-form-control" placeholder="Nom (ex : VIP)" maxlength="100" required>
                <input type="number" name="tarifs[${indexTarif}][prix]" class="sa-form-control ei-prix" placeholder="Prix (F)" min="0">
                <button type="button" class="btn btn-sm btn-outline-danger ei-retirer" title="Retirer"><i class="bi bi-x-lg"></i></button>`;
            indexTarif++;
            tarifs.appendChild(div);
        });
    })();
</script>

<style>
.ei-label { font-size: .8rem; font-weight: 600; margin-bottom: .3rem; display: block; }
.ei-ligne { display: flex; gap: .5rem; align-items: center; margin-bottom: .5rem; }
.ei-ligne .ei-prix { max-width: 120px; }
</style>
@endsection
