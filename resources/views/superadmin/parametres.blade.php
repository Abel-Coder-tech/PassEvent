@extends('superadmin.layouts.master')

@section('title', 'Paramètres - Super Admin')
@section('page-title', 'Paramètres de la plateforme')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="border-radius: 10px; font-size: 0.85rem;">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" style="border-radius: 10px; font-size: 0.85rem;">
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Gestion de l'equipe --}}
<div class="sa-card mb-3">
    <div class="sa-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="bi bi-people-fill me-2" style="color: var(--sa-primary);"></i>Équipe PaxEvent</span>
        <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjoutMembre"
                style="background: var(--sa-primary); color: white; border-radius: 8px; font-size: 0.8rem;">
            <i class="bi bi-person-plus-fill me-1"></i> Ajouter un membre
        </button>
    </div>
    <div class="sa-card-body">
        @if($equipe->isEmpty())
            <p class="text-muted text-center py-3 mb-0" style="font-size: 0.85rem;">
                Aucun membre dans l'équipe pour le moment. Cliquez sur <a href="#" data-bs-toggle="modal" data-bs-target="#modalAjoutMembre" style="color: var(--sa-primary); text-decoration: none;">« Ajouter un membre »</a> pour créer un compte,
                puis attribuez-lui ses rôles.
            </p>
        @else
            <p class="text-muted mb-2" style="font-size: 0.78rem;">
                <strong>Étape 1 :</strong> Créez le compte du membre.<br>
                <strong>Étape 2 :</strong> Choisissez son rôle dans la liste déroulante, puis cochez les pages et actions auxquelles il a droit.
                Chaque membre ne voit que ce qui est coché ; toute page hors périmètre affiche « Accès non autorisé ».
            </p>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" style="font-size: 0.82rem;">
                    <thead>
                        <tr style="color: #6c757d;">
                            <th>Membre</th>
                            <th>Pseudo</th>
                            <th>Role</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($equipe as $membre)
                            @php $roleMembre = $membre->roleEquipe(); @endphp
                            <tr>
                                <td>
                                    <strong>{{ $membre->prenom }} {{ $membre->nom }}</strong><br>
                                    <small class="text-muted">{{ $membre->email }}</small>
                                </td>
                                <td><code>{{ $membre->pseudo }}</code></td>
                                <td style="min-width: 200px;">
                                    @if($roleMembre)
                                        <span class="sa-badge sa-badge-secondary"><i class="bi {{ $rolesEquipe[$roleMembre]['icone'] ?? 'bi-person-fill' }} me-1"></i>{{ $rolesEquipe[$roleMembre]['libelle'] ?? $roleMembre }}</span>
                                        <br><small class="text-muted">{{ count($membre->accesEquipe()) }} accès autorisé{{ count($membre->accesEquipe()) > 1 ? 's' : '' }}</small>
                                    @else
                                        <span class="sa-badge sa-badge-danger">À définir</span>
                                    @endif
                                    <br>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-1" style="font-size: 0.7rem;"
                                            data-modifier-acces
                                            data-membre="{{ $membre->id }}"
                                            data-action="{{ route('superadmin.parametres.equipe.roles', $membre) }}"
                                            data-role="{{ $roleMembre }}"
                                            data-nom="{{ $membre->prenom }} {{ $membre->nom }}"
                                            data-acces='@json($membre->accesEquipe())'>
                                        <i class="bi bi-sliders me-1"></i>Modifier l'accès
                                    </button>
                                </td>
                                <td>
                                    @if($membre->statut === 'actif')
                                        <span class="sa-badge sa-badge-success">Actif</span>
                                    @else
                                        <span class="sa-badge sa-badge-danger">Désactivé</span>
                                    @endif
                                    @if($membre->must_change_password)
                                        <br><small style="color:#b8860b;"><i class="bi bi-key me-1"></i>Mot de passe temporaire</small>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('superadmin.parametres.equipe.voir', $membre) }}" class="btn btn-sm btn-outline-primary" title="Voir la fiche du membre">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form action="{{ route('superadmin.parametres.equipe.statut', $membre) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ $membre->statut === 'actif' ? 'Désactiver le compte' : 'Réactiver le compte' }}">
                                            <i class="bi bi-{{ $membre->statut === 'actif' ? 'pause-circle' : 'play-circle' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('superadmin.parametres.equipe.supprimer', $membre) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer définitivement ce membre et son accès ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer le membre">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="row g-3">

    {{-- Profil super admin --}}
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header"><span><i class="bi bi-person-fill me-2" style="color: var(--sa-primary);"></i>Mon profil</span></div>
            <div class="sa-card-body">
                <form action="{{ route('superadmin.parametres.profil.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.82rem;">Nom complet <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control" value="{{ old('nom', $user->nom) }}" required>
                        @error('nom')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.82rem;">Adresse email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.82rem;">Numéro de téléphone</label>
                        <input type="tel" name="telephone" class="form-control" value="{{ old('telephone', $user->telephone) }}" placeholder="+229 XX XX XX XX">
                        @error('telephone')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn" style="background: var(--sa-primary); color: white; border-radius: 8px; font-size: 0.85rem;">
                        <i class="bi bi-check-lg me-1"></i> Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Réseaux sociaux --}}
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header"><span><i class="bi bi-share-fill me-2" style="color: var(--sa-primary);"></i>Réseaux sociaux</span></div>
            <div class="sa-card-body">
                <form action="{{ route('superadmin.parametres.reseaux.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.82rem;">
                            <i class="bi bi-facebook me-1" style="color: #1877f2;"></i> Facebook
                        </label>
                        <input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $user->facebook_url) }}" placeholder="https://facebook.com/...">
                        @error('facebook_url')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.82rem;">
                            <i class="bi bi-instagram me-1" style="color: #e4405f;"></i> Instagram
                        </label>
                        <input type="url" name="instagram_url" class="form-control" value="{{ old('instagram_url', $user->instagram_url) }}" placeholder="https://instagram.com/...">
                        @error('instagram_url')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.82rem;">
                            <i class="bi bi-tiktok me-1" style="color: #000;"></i> TikTok
                        </label>
                        <input type="url" name="tiktok_url" class="form-control" value="{{ old('tiktok_url', $user->tiktok_url) }}" placeholder="https://tiktok.com/...">
                        @error('tiktok_url')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.82rem;">
                            <i class="bi bi-twitter-x me-1" style="color: #000;"></i> Twitter / X
                        </label>
                        <input type="url" name="twitter_url" class="form-control" value="{{ old('twitter_url', $user->twitter_url) }}" placeholder="https://x.com/...">
                        @error('twitter_url')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.82rem;">
                            <i class="bi bi-youtube me-1" style="color: #ff0000;"></i> YouTube
                        </label>
                        <input type="url" name="youtube_url" class="form-control" value="{{ old('youtube_url', $user->youtube_url) }}" placeholder="https://youtube.com/...">
                        @error('youtube_url')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.82rem;">
                            <i class="bi bi-linkedin me-1" style="color: #0a66c2;"></i> LinkedIn
                        </label>
                        <input type="url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', $user->linkedin_url) }}" placeholder="https://linkedin.com/in/...">
                        @error('linkedin_url')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.82rem;">
                            <i class="bi bi-globe me-1" style="color: var(--sa-primary);"></i> Site web
                        </label>
                        <input type="url" name="website_url" class="form-control" value="{{ old('website_url', $user->website_url) }}" placeholder="https://...">
                        @error('website_url')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn" style="background: var(--sa-primary); color: white; border-radius: 8px; font-size: 0.85rem;">
                        <i class="bi bi-check-lg me-1"></i> Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Configuration plateforme --}}
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header"><span><i class="bi bi-gear-fill me-2" style="color: var(--sa-primary);"></i>Configuration plateforme</span></div>
            <div class="sa-card-body">
                <p class="text-muted" style="font-size:0.85rem;">Les paramètres de configuration sont définis dans le fichier <code>.env</code> et les fichiers de configuration Laravel.</p>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>Mode maintenance</span>
                    <span class="sa-badge sa-badge-secondary">Désactivé</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>PaxEvent Mail</span>
                    <span class="text-muted" style="font-size:0.8rem;">contact@paxevent.com</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span>FedaPay</span>
                    @if(config('services.fedapay.public_key') && config('services.fedapay.secret_key'))
                        <span class="sa-badge sa-badge-success">Configuré</span>
                    @else
                        <span class="sa-badge sa-badge-danger">Non configuré</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Informations système --}}
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header"><span><i class="bi bi-bar-chart-steps me-2" style="color: var(--sa-primary);"></i>Informations système</span></div>
            <div class="sa-card-body">
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Version Laravel</span><span class="text-muted">{{ app()->version() }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Environnement</span><span class="text-muted">{{ app()->environment() }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Total utilisateurs</span><strong>{{ \App\Models\User::count() }}</strong></div>
                <div class="d-flex justify-content-between py-2"><span>Total événements</span><strong>{{ \App\Models\Evenement::count() }}</strong></div>
            </div>
        </div>
    </div>
</div>

{{-- Modal : etape 1, creation du compte du membre --}}
<div class="modal fade" id="modalAjoutMembre" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px;">
            <div class="modal-header" style="background: var(--sa-primary); color: #fff; border-radius: 14px 14px 0 0;">
                <h5 class="modal-title" style="font-size: 1rem;"><i class="bi bi-person-plus-fill me-2"></i>Étape 1 — Créer le compte du membre</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('superadmin.parametres.equipe.ajouter') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <p class="text-muted mb-3" style="font-size: 0.78rem;">Le compte est créé sans rôle. Dès la création, un second modal s'ouvre pour choisir son rôle et cocher ses accès.</p>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size: 0.8rem;">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="prenom" class="form-control form-control-sm" value="{{ old('prenom') }}" required>
                            @error('prenom')<div class="text-danger mt-1" style="font-size: 0.72rem;">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size: 0.8rem;">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control form-control-sm" value="{{ old('nom') }}" required>
                            @error('nom')<div class="text-danger mt-1" style="font-size: 0.72rem;">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size: 0.8rem;">Adresse email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email') }}" required>
                            @error('email')<div class="text-danger mt-1" style="font-size: 0.72rem;">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size: 0.8rem;">Pseudo de connexion <span class="text-danger">*</span></label>
                            <input type="text" name="pseudo" class="form-control form-control-sm" value="{{ old('pseudo') }}" placeholder="ex : jean.dupont" required>
                            <small class="text-muted" style="font-size: 0.7rem;">Lettres, chiffres, points, tirets et underscores uniquement.</small>
                            @error('pseudo')<div class="text-danger mt-1" style="font-size: 0.72rem;">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                <span>Mot de passe temporaire <span class="text-danger">*</span></span>
                                <button type="button" class="btn btn-link p-0" id="btnGenererMdp" style="font-size: 0.75rem; color: var(--sa-primary);">Générer</button>
                            </label>
                            <input type="text" name="mot_de_passe" id="champMdpTemporaire" class="form-control form-control-sm font-monospace"
                                   value="{{ old('mot_de_passe') }}" autocomplete="new-password" required minlength="8">
                            <small class="text-muted" style="font-size: 0.7rem;">Il sera envoyé par email ; le membre devra le changer à sa première connexion.</small>
                            @error('mot_de_passe')<div class="text-danger mt-1" style="font-size: 0.72rem;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm" style="background: var(--sa-primary); color: #fff;">
                        <i class="bi bi-check-lg me-1"></i>Créer le compte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal : etape 2, role et acces fins du membre --}}
<div class="modal fade" id="modalRolesMembre" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px;">
            <div class="modal-header" style="background: var(--sa-primary); color: #fff; border-radius: 14px 14px 0 0;">
                <h5 class="modal-title" style="font-size: 1rem;"><i class="bi bi-sliders me-2"></i>Étape 2 — Rôle de <span id="rolesMembreNom"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRolesMembre" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <label class="form-label fw-semibold" style="font-size: 0.8rem;">Rôle <span class="text-danger">*</span></label>
                    <select name="role" id="selectRoleEquipe" class="form-select form-select-sm" required>
                        <option value="">— Choisir un rôle —</option>
                        @foreach($rolesEquipe as $slug => $role)
                            <option value="{{ $slug }}">{{ $role['libelle'] }}</option>
                        @endforeach
                    </select>
                    <small id="descriptionRole" class="text-muted mt-1 d-block" style="font-size: 0.72rem;"></small>

                    <hr class="my-3">

                    <p class="fw-semibold mb-2" style="font-size: 0.8rem;">
                        <i class="bi bi-check2-square me-1" style="color: var(--sa-primary);"></i>Pages et actions autorisées
                        <button type="button" id="btnToutCocher" class="btn btn-link p-0 ms-2" style="font-size: 0.7rem; color: var(--sa-primary); display:none;">Tout cocher</button>
                    </p>

                    @foreach($rolesEquipe as $slug => $role)
                        <div class="groupe-acces" data-role="{{ $slug }}" style="display:none;">
                            <div class="d-flex flex-wrap gap-1">
                                @foreach(\App\Models\User::ACCES_PAR_ROLE[$slug] ?? [] as $cle => $libelle)
                                    <label class="case-acces d-inline-flex align-items-center gap-1 px-2 py-1"
                                           style="border:1px solid #e3e6ed; border-radius:20px; font-size:0.72rem; cursor:pointer; background:#f8f9fa;">
                                        <input type="checkbox" name="acces[]" value="{{ $cle }}" class="form-check-input m-0 p-0" style="width:0.85em;height:0.85em;margin-top:0;">
                                        {{ $libelle }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <p id="accesVide" class="text-muted mb-0" style="font-size: 0.72rem; display:none;">
                        Aucune case cochée : le membre aura un rôle sans accès — cochez au moins une page ou action.
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm" style="background: var(--sa-primary); color: #fff;">
                        <i class="bi bi-check-lg me-1"></i>Enregistrer les accès
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var boutonGenerer = document.getElementById('btnGenererMdp');
    var champ = document.getElementById('champMdpTemporaire');
    if (boutonGenerer && champ) {
        boutonGenerer.addEventListener('click', function () {
            var caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
            var motDePasse = '';
            for (var i = 0; i < 10; i++) {
                motDePasse += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
            }
            champ.value = motDePasse;
            champ.type = 'text';
        });
    }

    // ---- Etape 2 : role (liste deroulante) + acces fins (cases a cocher) ----
    var modalEl = document.getElementById('modalRolesMembre');
    if (!modalEl) return;
    var selectRole = document.getElementById('selectRoleEquipe');
    var descriptionRole = document.getElementById('descriptionRole');
    var formRoles = document.getElementById('formRolesMembre');
    var nomMembre = document.getElementById('rolesMembreNom');
    var btnToutCocher = document.getElementById('btnToutCocher');
    var accesVide = document.getElementById('accesVide');

    function afficherGroupe(roleSlug, accesCochees) {
        document.querySelectorAll('.groupe-acces').forEach(function (groupe) {
            var visible = groupe.dataset.role === roleSlug;
            groupe.style.display = visible ? '' : 'none';
            if (!visible) return;
            groupe.querySelectorAll('input[name="acces[]"]').forEach(function (box) {
                box.checked = accesCochees.indexOf(box.value) !== -1;
            });
        });
        btnToutCocher.style.display = roleSlug ? '' : 'none';
        majAvertissement();
    }

    function majAvertissement() {
        var cochees = modalEl.querySelectorAll('.groupe-acces input[name="acces[]"]:checked').length;
        accesVide.style.display = cochees === 0 && selectRole.value ? '' : 'none';
    }

    selectRole.addEventListener('change', function () {
        var option = selectRole.options[selectRole.selectedIndex];
        descriptionRole.textContent = option.value ? (option.textContent + ' — acces a cocher ci-dessous') : '';
        afficherGroupe(selectRole.value, []);
    });

    btnToutCocher.addEventListener('click', function () {
        modalEl.querySelectorAll('.groupe-acces input[name="acces[]"]').forEach(function (box) {
            if (box.closest('.groupe-acces').style.display !== 'none') box.checked = true;
        });
        majAvertissement();
    });

    modalEl.addEventListener('change', majAvertissement);

    function ouvrirModalRoles(bouton) {
        formRoles.action = bouton.dataset.action;
        nomMembre.textContent = bouton.dataset.nom;
        selectRole.value = bouton.dataset.role || '';
        var evt = new Event('change');
        selectRole.dispatchEvent(evt);
        afficherGroupe(bouton.dataset.role || [], JSON.parse(bouton.dataset.acces || '[]'));
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    document.querySelectorAll('[data-modifier-acces]').forEach(function (bouton) {
        bouton.addEventListener('click', function () { ouvrirModalRoles(bouton); });
    });

    @if(session('ouvrir_roles'))
    (function () {
        var cible = document.querySelector('[data-modifier-acces][data-membre="{{ session('ouvrir_roles') }}"]');
        if (cible) ouvrirModalRoles(cible);
    })();
    @endif
});
</script>
@endpush
