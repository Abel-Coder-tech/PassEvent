@extends('layouts.app')

@section('title', 'Récapitulatif')
@section('page-title', 'Finaliser la création de votre compte')

@section('content')
<style>
    .wizard-card {
        max-width: 640px; margin: 0 auto; background: #fff; border-radius: 16px;
        padding: 1.5rem; box-shadow: 0 4px 24px rgba(0,0,0,0.04);
    }
    .wizard-step { display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 0.5rem; }
    .wizard-step .step { display: flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: var(--gris); font-weight: 500; }
    .wizard-step .step.active { color: var(--violet); font-weight: 700; }
    .wizard-step .step.done { color: var(--vert); font-weight: 600; }
    .wizard-step .num { width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; background: var(--ligne); color: #fff; flex-shrink: 0; }
    .wizard-step .done .num { background: var(--vert); }
    .wizard-step .active .num { background: var(--violet); }
    .wizard-step .connector { width: 24px; height: 2px; background: var(--ligne); }
    .wizard-step .connector.done { background: var(--vert); }
    .recap-card { background: var(--gris-clair); border-radius: 14px; padding: 1rem 1.25rem; margin-bottom: 1rem; border: 1px solid #eeedeb; }
    .recap-card-title { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--violet-clair); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
    .recap-card-title i { font-size: 0.85rem; }
    .recap-row { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid rgba(0,0,0,0.04); font-size: 0.88rem; }
    .recap-row:last-child { border-bottom: none; }
    .recap-label { color: var(--gris-fonce); font-weight: 500; display: flex; align-items: center; gap: 0.35rem; }
    .recap-value { font-weight: 600; color: var(--sombre); text-align: right; max-width: 55%; word-break: break-word; }
    .recap-badge { background: #e8f5e9; color: var(--vert); font-size: 0.72rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 20px; display: inline-flex; align-items: center; gap: 0.25rem; }
</style>
<div class="page-content">
    <div class="wizard-card">
        <div class="wizard-step">
            <div class="step done"><span class="num"><i class="bi bi-check" style="font-size:0.7rem;"></i></span> Type &amp; Justificatifs</div>
            <div class="connector done"></div>
            <div class="step active"><span class="num">2</span> Récapitulatif</div>
        </div>
        <hr style="margin:0.75rem 0 1.25rem;border-color:var(--gris-clair);">

        @if($errors->any())
            <div class="alert alert-danger py-2" style="font-size:.85rem;border-radius:10px;">
                @foreach($errors->all() as $e) {{ $e }} @break @endforeach
            </div>
        @endif

        <div class="recap-card">
            <div class="recap-card-title"><i class="bi bi-person-badge"></i> Identité</div>
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-person" style="color:var(--violet-clair);font-size:0.8rem;"></i> Nom</span>
                <span class="recap-value">{{ $user->nom }}</span>
            </div>
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-envelope" style="color:var(--violet-clair);font-size:0.8rem;"></i> Email</span>
                <span class="recap-value">{{ $user->email }}</span>
            </div>
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-telephone" style="color:var(--violet-clair);font-size:0.8rem;"></i> Téléphone</span>
                <span class="recap-value">{{ $user->telephone }}</span>
            </div>

        </div>

        <div class="recap-card">
            <div class="recap-card-title"><i class="bi bi-building"></i> Organisation</div>
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-tag" style="color:var(--violet-clair);font-size:0.8rem;"></i> Type</span>
                <span class="recap-value">
                    @if($data['type'] === 'universitaire') <i class="bi bi-building-columns"></i>
                    @elseif($data['type'] === 'particulier') <i class="bi bi-person"></i>
                    @else <i class="bi bi-building"></i>
                    @endif
                    {{ ucfirst($data['type']) }}
                </span>
            </div>
            @if($data['type'] === 'universitaire' || $data['type'] === 'organisation')
                <div class="recap-row">
                    <span class="recap-label"><i class="bi bi-house" style="color:var(--violet-clair);font-size:0.8rem;"></i> {{ $data['type'] === 'universitaire' ? 'Université' : 'Organisation' }}</span>
                    <span class="recap-value">{{ $data['organisation'] }}</span>
                </div>
                @if($data['type'] === 'organisation')
                <div class="recap-row">
                    <span class="recap-label"><i class="bi bi-briefcase" style="color:var(--violet-clair);font-size:0.8rem;"></i> Structure</span>
                    <span class="recap-value">
                        @php
                            $labels = ['entreprise' => 'Entreprise', 'association' => 'Association/ONG'];
                        @endphp
                        {{ $labels[$data['type_detail']] ?? ucfirst($data['type_detail']) }}
                    </span>
                </div>
                @endif
                @if(!empty($data['nom_representant']))
                <div class="recap-row">
                    <span class="recap-label"><i class="bi bi-person" style="color:var(--violet-clair);font-size:0.8rem;"></i> Nom du représentant</span>
                    <span class="recap-value">{{ $data['nom_representant'] }}</span>
                </div>
                @endif
                @if(!empty($data['fonction']))
                <div class="recap-row">
                    <span class="recap-label"><i class="bi bi-person-badge" style="color:var(--violet-clair);font-size:0.8rem;"></i> Fonction du représentant</span>
                    <span class="recap-value">{{ $data['fonction'] }}</span>
                </div>
                @endif
            @endif
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-file-earmark-pdf" style="color:var(--violet-clair);font-size:0.8rem;"></i> Justificatif</span>
                <span class="recap-value"><span class="recap-badge"><i class="bi bi-check-circle-fill"></i> Fourni</span></span>
            </div>
            @if(!empty($data['document_cip']))
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-person-vcard" style="color:var(--violet-clair);font-size:0.8rem;"></i> CIP représentant</span>
                <span class="recap-value"><span class="recap-badge"><i class="bi bi-check-circle-fill"></i> Fournie</span></span>
            </div>
            @endif
            @php
                $cipNum = !empty($data['numero_cip']) ? $data['numero_cip'] : ($data['numero_cip_rep'] ?? '');
            @endphp
            @if(!empty($cipNum))
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-key" style="color:var(--violet-clair);font-size:0.8rem;"></i> N° CIP</span>
                <span class="recap-value">{{ $cipNum }}</span>
            </div>
            @endif
            @if(!empty($data['numero_rc']))
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-key" style="color:var(--violet-clair);font-size:0.8rem;"></i> N° RC / Récépissé</span>
                <span class="recap-value">{{ $data['numero_rc'] }}</span>
            </div>
            @endif
            <div class="recap-row">
                <span class="recap-label"><i class="bi bi-pen" style="color:var(--violet-clair);font-size:0.8rem;"></i> Signature</span>
                <span class="recap-value"><span class="recap-badge"><i class="bi bi-check-circle-fill"></i> Fournie</span></span>
            </div>
        </div>

        <form method="POST" action="{{ route('profil.submit') }}">
            @csrf
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="cgu" id="cgu" value="1" required @checked(old('cgu'))>
                    <label class="form-check-label" for="cgu">
                        J'accepte les <a href="{{ route('cgu') }}" target="_blank">conditions générales d'utilisation</a>
                        et la <a href="{{ route('confidentialite') }}" target="_blank">politique de confidentialité</a>
                    </label>
                </div>
            </div>
            <button type="submit" id="bouton-soumission" class="btn btn-primary w-100" style="background:var(--violet);border:none;border-radius:10px;padding:0.7rem 1rem;font-weight:600;" @disabled(!old('cgu'))>Soumettre pour validation</button>
            <a href="{{ route('profil.step2') }}" class="btn btn-secondary w-100 d-block text-center" style="border-radius:10px;padding:0.6rem 1rem;font-weight:600;margin-top:0.5rem;text-decoration:none;">Modifier les informations</a>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        const caseCgu = document.getElementById('cgu');
        const bouton = document.getElementById('bouton-soumission');
        if (!caseCgu || !bouton) return;

        const synchroniser = () => {
            bouton.disabled = !caseCgu.checked;
        };

        caseCgu.addEventListener('change', synchroniser);
        synchroniser();
    })();
</script>
@endsection
