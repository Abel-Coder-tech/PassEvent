@extends('superadmin.layouts.master')

@section('title', 'Generer un lot de tickets physiques - Super Admin')
@section('page-title', 'Generer un lot de tickets physiques')

@section('content')
@if (session('error'))
<div class="alert alert-danger py-2 small">{{ session('error') }}</div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-plus-square-fill me-2" style="color: var(--sa-primary);"></i>Nouveau lot</span>
            </div>
            <div class="sa-card-body">
                <form action="{{ route('superadmin.tickets-physiques.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Organisateur</label>
                            <select name="user_id" id="sel_organisateur" class="sa-form-control" required>
                                <option value="">-- Choisir un organisateur --</option>
                                @foreach($organisateurs as $org)
                                <option value="{{ $org->id }}">{{ $org->nom }}</option>
                                @endforeach
                            </select>
                            @error('user_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Nom du lot</label>
                            <input type="text" name="nom" class="sa-form-control" placeholder="Ex : Lot guichet principal" value="{{ old('nom') }}" required>
                            @error('nom')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Evenement</label>
                            <select name="evenement_id" id="sel_evenement" class="sa-form-control" required disabled>
                                <option value="">-- Choisir un organisateur d'abord --</option>
                            </select>
                            @error('evenement_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Tarif</label>
                            <select name="tarif_id" id="sel_tarif" class="sa-form-control" disabled>
                                <option value="">-- Choisir un evenement d'abord --</option>
                            </select>
                            @error('tarif_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Commission (en %)</label>
                            <input type="number" name="commission_pourcentage" id="inp_commission" class="sa-form-control" min="0" max="100" step="0.01" value="{{ old('commission_pourcentage') }}" placeholder="Defaut : commission evenement">
                            @error('commission_pourcentage')<small class="text-danger">{{ $message }}</small>@enderror
                            <small class="text-muted">Laissez vide pour reprendre le taux de l'evenement. Le prix du ticket est celui du tarif choisi.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Quantite</label>
                            <input type="number" name="quantite" id="inp_quantite" class="sa-form-control" min="1" max="500" value="{{ old('quantite') }}" placeholder="Nombre de tickets" required>
                            @error('quantite')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="sa-btn sa-btn-primary"><i class="bi bi-magic"></i> Generer le lot</button>
                            <a href="{{ route('superadmin.tickets-physiques') }}" class="sa-btn" style="background:#f1f2f6;border:none;color:#666;">Annuler</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-info-circle me-2" style="color: var(--sa-primary);"></i>Informations</span>
            </div>
            <div class="sa-card-body" style="font-size:0.82rem;color:#555;line-height:1.6;">
                <ul class="mb-0 ps-3">
                    <li>Les tickets physiques ne comptent pas dans la capacite de l'evenement.</li>
                    <li>Chaque ticket recoit un code unique <code>PAX-XXXXX</code> scannable a l'entree.</li>
                    <li>La commission est calculee au taux effectif de l'evenement et suivie separement.</li>
                    <li>Le lot doit etre transmis a l'organisateur avant qu'il ne puisse telecharger la planche de QR codes.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const selOrg = document.getElementById('sel_organisateur');
    const selEvt = document.getElementById('sel_evenement');
    const selTar = document.getElementById('sel_tarif');
    const inpCommission = document.getElementById('inp_commission');

    function reset(select, placeholder) {
        select.innerHTML = '<option value="">' + placeholder + '</option>';
        select.disabled = true;
    }

    selOrg.addEventListener('change', function () {
        reset(selEvt, '-- Aucun evenement --');
        reset(selTar, '-- Choisir un evenement --');
        inpCommission.value = '';
        if (!this.value) return;

        fetch('{{ route("superadmin.tickets-physiques.evenements") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ user_id: this.value }),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.evenements.length) {
                selEvt.innerHTML = '<option value="">-- Aucun evenement --</option>';
                return;
            }
            selEvt.innerHTML = data.evenements.map(e =>
                '<option value="' + e.id + '">' + e.titre + (e.date_event ? ' (' + e.date_event + ')' : '') + '</option>'
            ).join('');
            selEvt.disabled = false;
        });
    });

    selEvt.addEventListener('change', function () {
        reset(selTar, '-- Aucun tarif --');
        if (!this.value) return;

        fetch('{{ route("superadmin.tickets-physiques.tarifs") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ evenement_id: this.value }),
        })
        .then(r => r.json())
        .then(data => {
            inpCommission.value = '';
            if (data.gratuit) {
                selTar.innerHTML = '<option value="">Evenement gratuit (tarif auto)</option>';
                if (data.commission) inpCommission.value = data.commission;
                return;
            }
            if (!data.tarifs.length) {
                selTar.innerHTML = '<option value="">-- Aucun tarif actif --</option>';
                if (data.commission) inpCommission.value = data.commission;
                return;
            }
            selTar.innerHTML = '<option value="">-- Choisir un tarif --</option>' + data.tarifs.map(t =>
                '<option value="' + t.id + '">' + t.nom + ' - ' + t.prix + ' FCFA</option>'
            ).join('');
            selTar.disabled = false;
            if (data.commission) inpCommission.value = data.commission;
        });
    });
})();
</script>
@endpush
