@extends('superadmin.layouts.master')

@section('title', 'Fiche membre — '.$membre->prenom.' '.$membre->nom)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h4 class="mb-0"><i class="bi bi-person-badge me-2" style="color: var(--sa-primary);"></i>Fiche membre</h4>
        <small class="text-muted">Espace Equipe — détail du compte et de ses accès</small>
    </div>
    <a href="{{ route('superadmin.parametres') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour aux paramètres
    </a>
</div>

<div class="sa-card mb-3">
    <div class="sa-card-body d-flex flex-wrap align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center"
             style="width:56px;height:56px;background:var(--sa-primary);color:#fff;font-weight:700;font-size:1.3rem;">
            {{ strtoupper(substr($membre->prenom ?: $membre->pseudo, 0, 1)) }}
        </div>
        <div class="flex-grow-1">
            <strong>{{ $membre->prenom }} {{ $membre->nom }}</strong> <code class="ms-1">{{ $membre->pseudo }}</code><br>
            <small class="text-muted">{{ $membre->email }}</small><br>
            <span class="sa-badge sa-badge-secondary mt-1">
                <i class="bi {{ $rolesEquipe[$roleMembre]['icone'] ?? 'bi-person-fill' }} me-1"></i>{{ $roleMembre ? ($rolesEquipe[$roleMembre]['libelle'] ?? $roleMembre) : 'Rôle non défini' }}
            </span>
            <span class="sa-badge sa-badge-{{ $membre->statut === 'actif' ? 'success' : 'danger' }} mt-1 ms-1">{{ $membre->statut === 'actif' ? 'Actif' : 'Désactivé' }}</span>
            @if($membre->must_change_password)
                <span class="sa-badge sa-badge-warning mt-1 ms-1"><i class="bi bi-key me-1"></i>Mot de passe temporaire</span>
            @endif
        </div>
        <div class="text-end text-nowrap">
            @if($membre->must_change_password)
                <form action="{{ route('superadmin.parametres.equipe.renvoyer-email', $membre) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Régénérer un mot de passe temporaire et le renvoyer à {{ $membre->email }} ?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-info" title="Renvoyer les identifiants par email">
                        <i class="bi bi-envelope-plus me-1"></i>Renvoyer les identifiants
                    </button>
                </form>
            @endif
            <form action="{{ route('superadmin.parametres.equipe.reinit', $membre) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Générer un nouveau mot de passe temporaire et l\'envoyer par email ?');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-warning" title="Réinitialiser le mot de passe">
                    <i class="bi bi-key-fill me-1"></i>Réinitialiser le mot de passe
                </button>
            </form>
            <form action="{{ route('superadmin.parametres.equipe.statut', $membre) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ $membre->statut === 'actif' ? 'Désactiver le compte' : 'Réactiver le compte' }}">
                    <i class="bi bi-{{ $membre->statut === 'actif' ? 'pause-circle' : 'play-circle' }} me-1"></i>{{ $membre->statut === 'actif' ? 'Désactiver' : 'Réactiver' }}
                </button>
            </form>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="sa-card h-100">
            <div class="sa-card-header"><span><i class="bi bi-sliders me-2" style="color: var(--sa-primary);"></i>Rôle et accès accordés</span></div>
            <form method="POST" action="{{ route('superadmin.parametres.equipe.roles', $membre) }}" id="formFicheRoles">
                @csrf
                <div class="sa-card-body py-3">
                    <label class="form-label fw-semibold" style="font-size: 0.8rem;">Rôle</label>
                    <select name="role" id="selectRoleFiche" class="form-select form-select-sm" required style="max-width: 320px;">
                        <option value="">— Choisir un rôle —</option>
                        @foreach($rolesEquipe as $slug => $role)
                            <option value="{{ $slug }}" @selected($slug === $roleMembre)>{{ $role['libelle'] }}</option>
                        @endforeach
                    </select>
                    <small id="descriptionRoleFiche" class="text-muted mt-1 d-block" style="font-size: 0.72rem;"></small>

                    <hr class="my-3">

                    <p class="fw-semibold mb-2" style="font-size: 0.8rem;">
                        <i class="bi bi-check2-square me-1" style="color: var(--sa-primary);"></i>Pages et actions autorisées
                        <button type="button" id="btnToutCocherFiche" class="btn btn-link p-0 ms-2" style="font-size: 0.7rem; color: var(--sa-primary);">Tout cocher</button>
                    </p>

                    @foreach($rolesEquipe as $slug => $role)
                        <div class="groupe-acces-fiche" data-role="{{ $slug }}" style="display:none;">
                            <div class="d-flex flex-wrap gap-1">
                                @foreach(\App\Models\User::ACCES_PAR_ROLE[$slug] ?? [] as $cle => $libelle)
                                    <label class="case-acces-fiche d-inline-flex align-items-center gap-1 px-2 py-1"
                                           style="border:1px solid #e3e6ed; border-radius:20px; font-size:0.72rem; cursor:pointer; background:#f8f9fa;">
                                        <input type="checkbox" name="acces[]" value="{{ $cle }}" class="form-check-input m-0 p-0" style="width:0.85em;height:0.85em;margin-top:0;"
                                               @checked(in_array($cle, $accesMembre))>
                                        {{ $libelle }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <p id="accesVideFiche" class="text-muted mb-0" style="font-size: 0.72rem; display:none;">
                        Aucune case cochée : le membre aura un rôle sans accès.
                    </p>

                    <button type="submit" class="btn btn-sm mt-3" style="background: var(--sa-primary); color: #fff;">
                        <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="sa-card h-100">
            <div class="sa-card-header"><span><i class="bi bi-speedometer2 me-2" style="color: var(--sa-primary);"></i>Son périmètre en direct</span></div>
            <div class="sa-card-body py-3">
                @forelse($statsEquipe as $cle => $valeur)
                    @php
                        $libelles = [
                            'organisateurs_attente' => ['Organisateurs en attente', 'bi-people', 'warning'],
                            'retraits_attente' => ['Demandes de retrait en attente', 'bi-cash-stack', 'info'],
                            'retraits_en_cours' => ['Retraits en cours de paiement', 'bi-arrow-repeat', 'secondary'],
                            'messages_non_lus' => ['Messages non lus', 'bi-envelope', 'danger'],
                            'tickets_incidents' => ['Incidents techniques à traiter', 'bi-tools', 'danger'],
                        ];
                        $l = $libelles[$cle] ?? [ucfirst(str_replace('_', ' ', $cle)), 'bi-list-check', 'secondary'];
                    @endphp
                    <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                        <span style="font-size: 0.82rem;"><i class="bi {{ $l[1] }} me-2" style="color: var(--sa-primary);"></i>{{ $l[0] }}</span>
                        <span class="sa-badge sa-badge-{{ $l[2] }}">{{ $valeur }}</span>
                    </div>
                @empty
                    <p class="text-muted mb-0" style="font-size: 0.8rem;">Aucun accès de consultation défini pour ce membre.</p>
                @endforelse

                <hr class="my-3">

                <p class="fw-semibold mb-2" style="font-size: 0.8rem;">
                    <i class="bi bi-clock-history me-1" style="color: var(--sa-primary);"></i>Dernières interventions de ce membre
                </p>
                @forelse($interventions as $log)
                    @php
                        $detailsLog = $log->details ?? [];
                        $libellesTypes = [
                            'organisateur_approuve' => 'A approuvé le compte',
                            'organisateur_rejete' => 'A rejeté le compte',
                            'organisateur_suspendu' => 'A suspendu le compte',
                            'organisateur_reactive' => 'A réactivé le compte',
                            'organisateur_corrections' => 'A demandé des corrections',
                            'organisateur_supprime' => 'A supprimé le compte',
                            'retrait_approuve' => 'A approuvé un retrait',
                            'retrait_confirme' => 'A confirmé le paiement d\'un retrait',
                            'retrait_rejete' => 'A rejeté un retrait',
                            'notification_repondue' => 'A répondu à un message',
                        ];
                    @endphp
                    <div class="border-start ps-2 mb-2" style="border-color: #e3e6ed !important;">
                        <span style="font-size: 0.78rem;">{{ $libellesTypes[$log->type_operation] ?? ucfirst(str_replace('_', ' ', $log->type_operation)) }}
                            @if(!empty($detailsLog['email'])) — <em>{{ $detailsLog['email'] }}</em>@elseif(!empty($detailsLog['retrait_id'])) — retrait n°{{ $detailsLog['retrait_id'] }}@endif
                        </span><br>
                        <small class="text-muted" style="font-size: 0.68rem;">{{ $log->created_at?->format('d/m/Y H:i') }}</small>
                    </div>
                @empty
                    <p class="text-muted mb-0" style="font-size: 0.78rem;">Aucune action enregistrée pour l'instant.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<form action="{{ route('superadmin.parametres.equipe.supprimer', $membre) }}" method="POST" class="mt-3"
      onsubmit="return confirm('Supprimer définitivement ce membre et son accès ?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger">
        <i class="bi bi-trash me-1"></i>Supprimer ce membre
    </button>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var select = document.getElementById('selectRoleFiche');
    if (!select) return;
    var descriptions = @json(collect($rolesEquipe)->map(fn ($r) => $r['description'] ?? '')->all());

    function majGroupe() {
        var slug = select.value;
        document.querySelectorAll('.groupe-acces-fiche').forEach(function (groupe) {
            groupe.style.display = groupe.dataset.role === slug ? '' : 'none';
        });
        document.getElementById('descriptionRoleFiche').textContent = slug ? (descriptions[slug] || '') : '';
        majVide();
    }
    function majVide() {
        var cochees = 0;
        document.querySelectorAll('.groupe-acces-fiche').forEach(function (groupe) {
            if (groupe.style.display !== 'none') {
                cochees = groupe.querySelectorAll('input:checked').length;
            }
        });
        document.getElementById('accesVideFiche').style.display = cochees === 0 && select.value ? '' : 'none';
    }

    select.addEventListener('change', majGroupe);

    document.getElementById('btnToutCocherFiche').addEventListener('click', function () {
        document.querySelectorAll('.groupe-acces-fiche').forEach(function (groupe) {
            if (groupe.style.display !== 'none') {
                groupe.querySelectorAll('input[type=checkbox]').forEach(function (c) { c.checked = true; });
            }
        });
        majVide();
    });

    document.addEventListener('change', function (e) {
        if (e.target.matches('.case-acces-fiche input')) majVide();
    });

    majGroupe();
});
</script>
@endpush
