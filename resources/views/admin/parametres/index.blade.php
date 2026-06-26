@extends('layouts.app')

@section('title', 'Paramètres - PaxEvent')

@section('page-title', 'Paramètres')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Paramètres</li>
@endsection

@section('content')
<style>
    .settings-sidebar {
        width: 220px;
        flex-shrink: 0;
    }
    .settings-nav-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 0.75rem;
        border-radius: 8px;
        color: var(--gris);
        font-size: 0.82rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
    }
    .settings-nav-link:hover {
        background: rgba(109,78,114,0.06);
        color: var(--sombre);
    }
    .settings-nav-link.active {
        background: rgba(109,78,114,0.1);
        color: var(--violet);
        font-weight: 600;
    }
    .settings-nav-link.danger {
        color: var(--danger);
    }
    .settings-nav-link.danger:hover {
        background: rgba(231,76,60,0.08);
    }
    .settings-nav-link.danger.active {
        background: rgba(231,76,60,0.12);
        color: var(--danger);
        font-weight: 600;
    }
    .settings-content {
        flex: 1;
        min-width: 0;
    }
    .settings-section {
        display: none;
    }
    .settings-section.active {
        display: block;
    }
    .avatar-wrapper {
        position: relative;
        display: inline-block;
    }
    .avatar-initial {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--violet);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
    }
    .avatar-image {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--blanc-casse);
    }
    .toggle-switch {
        position: relative;
        width: 44px;
        height: 24px;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #ccc;
        transition: 0.3s;
        border-radius: 24px;
    }
    .toggle-slider:before {
        content: '';
        position: absolute;
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
    }
    .toggle-switch input:checked + .toggle-slider {
        background-color: var(--vert);
    }
    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(20px);
    }
    .masked-key {
        font-family: monospace;
        letter-spacing: 2px;
    }
    @media (max-width: 767.98px) {
        .settings-sidebar {
            width: 100%;
            margin-bottom: 1rem;
        }
    }
</style>

<div class="page-content">
    <p class="text-muted mb-4" style="font-size: 0.9rem;">Gerez votre compte et la configuration de la plateforme.</p>

    <div class="d-flex flex-column flex-md-row gap-4">
        {{-- Sidebar --}}
        <div class="settings-sidebar">
            <nav class="d-flex flex-column gap-1">
                <button class="settings-nav-link active" onclick="showSection('profil', this)">
                    <i class="bi bi-person"></i> Profil
                </button>
                <button class="settings-nav-link" onclick="showSection('securite', this)">
                    <i class="bi bi-shield-lock"></i> Securite
                </button>
                <button class="settings-nav-link" onclick="showSection('notifications', this)">
                    <i class="bi bi-bell"></i> Notifications
                </button>
                <button class="settings-nav-link" onclick="showSection('paiement', this)">
                    <i class="bi bi-credit-card"></i> Paiement KKiaPay
                </button>
                <button class="settings-nav-link" onclick="showSection('scan', this)">
                    <i class="bi bi-qr-code-scan"></i> Acces scan
                </button>
                <button class="settings-nav-link danger" onclick="showSection('danger', this)">
                    <i class="bi bi-exclamation-triangle"></i> Zone de danger
                </button>
            </nav>
        </div>

        {{-- Content --}}
        <div class="settings-content">
            {{-- Profil --}}
            <div id="section-profil" class="settings-section active">
                <div class="panel-card">
                    <div class="panel-card-header">
                        <h5><i class="bi bi-person me-2" style="color: var(--violet);"></i>Informations du profil</h5>
                    </div>
                    <div class="panel-card-body">
                        <p class="text-muted mb-4" style="font-size: 0.82rem;">Ces informations sont visibles par les participants sur vos evenements.</p>

                        <form action="{{ route('parametres.profil.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            {{-- Avatar --}}
                            <div class="d-flex align-items-center gap-3 mb-4 pb-4" style="border-bottom: 1px solid #f0f0f0;">
                                <div class="avatar-wrapper">
                                    @if(Auth::user()->avatar)
                                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" class="avatar-image">
                                    @else
                                        <div class="avatar-initial">{{ strtoupper(substr(Auth::user()->nom, 0, 1)) }}</div>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size: 0.95rem;">{{ Auth::user()->nom }}</div>
                                    <div class="text-muted" style="font-size: 0.78rem;">
                                        {{ Auth::user()->organisation ? 'Organisateur : ' . Auth::user()->organisation : 'Organisateur' }}
                                    </div>
                                    <div class="d-flex gap-2 mt-2">
                                        <label class="btn btn-sm" style="border-radius: 6px; padding: 0.25rem 0.75rem; border: 1px solid var(--vert); color: var(--vert); background: transparent; cursor: pointer; font-size: 0.78rem;">
                                            <i class="bi bi-camera me-1"></i> Changer la photo
                                            <input type="file" name="avatar" accept="image/*" class="d-none" onchange="this.form.submit()">
                                        </label>
                                        @if(Auth::user()->avatar)
                                            <form action="{{ route('parametres.avatar.delete') }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm" style="border-radius: 6px; padding: 0.25rem 0.75rem; border: 1px solid var(--danger); color: var(--danger); background: transparent; font-size: 0.78rem;" onclick="return confirm('Supprimer la photo de profil ?')">
                                                    Supprimer
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Nom complet --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.82rem;">Nom complet <span class="text-danger">*</span></label>
                                <input type="text" name="nom" class="form-control" value="{{ old('nom', Auth::user()->nom) }}" required>
                                @error('nom')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                            </div>

                            {{-- Organisation --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.82rem;">Organisation</label>
                                <input type="text" name="organisation" class="form-control" value="{{ old('organisation', Auth::user()->organisation) }}" placeholder="Ex: UPAO Evenements">
                                @error('organisation')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                            </div>

                            {{-- Type de compte --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.82rem;">Type de compte</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->type === 'universitaire' ? 'Universitaire' : 'Professionnel' }}" readonly style="background: #f5f5f5;">
                                <small class="text-muted">Le type de compte ne peut pas être modifié.</small>
                            </div>

                            {{-- Email --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.82rem;">Adresse email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', Auth::user()->email) }}" required>
                                @error('email')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                            </div>

                            {{-- Description --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-semibold mb-0" style="font-size: 0.82rem;">Description</label>
                                    <span class="text-muted" style="font-size: 0.72rem;">Affichee sur la page publique de vos evenements</span>
                                </div>
                                <textarea name="description" class="form-control" rows="4" placeholder="Presentez-vous brièvement...">{{ old('description', Auth::user()->description) }}</textarea>
                                @error('description')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3" style="border-top: 1px solid #f0f0f0;">
                                <span class="text-muted" style="font-size: 0.78rem;">
                                    <i class="bi bi-clock me-1"></i>Derniere modification : {{ Auth::user()->updated_at->diffForHumans() }}
                                </span>
                                <button type="submit" class="btn btn-vert" style="border-radius: 8px;">
                                    <i class="bi bi-check-lg me-1"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Securite --}}
            <div id="section-securite" class="settings-section">
                <div class="panel-card">
                    <div class="panel-card-header">
                        <h5><i class="bi bi-shield-lock me-2" style="color: var(--vert);"></i>Changer le mot de passe</h5>
                    </div>
                    <div class="panel-card-body">
                        <p class="text-muted mb-4" style="font-size: 0.82rem;">Utilisez un mot de passe fort avec au moins 8 caracteres, une majuscule et un caractere special.</p>

                        <form action="{{ route('parametres.securite.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.82rem;">Mot de passe actuel <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password" name="mot_de_passe_actuel" id="param_actuel" class="form-control" required>
                                    <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password" style="right: 4px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5;">
                                        <i class="bi bi-eye" style="color: #9a9a9a;"></i>
                                    </button>
                                </div>
                                @error('mot_de_passe_actuel')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size: 0.82rem;">Nouveau mot de passe <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="password" name="mot_de_passe" id="param_new" class="form-control" required>
                                        <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password" style="right: 4px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5;">
                                            <i class="bi bi-eye" style="color: #9a9a9a;"></i>
                                        </button>
                                    </div>
                                    @error('mot_de_passe')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size: 0.82rem;">Confirmer <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="password" name="mot_de_passe_confirmation" id="param_confirm" class="form-control" required>
                                        <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password" style="right: 4px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5;">
                                            <i class="bi bi-eye" style="color: #9a9a9a;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3" style="border-top: 1px solid #f0f0f0;">
                                <button type="submit" class="btn btn-vert" style="border-radius: 8px;">
                                    <i class="bi bi-lock me-1"></i> Modifier le mot de passe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Notifications --}}
            <div id="section-notifications" class="settings-section">
                <div class="panel-card">
                    <div class="panel-card-header">
                        <h5><i class="bi bi-bell me-2" style="color: var(--violet);"></i>Preferences de notification</h5>
                    </div>
                    <div class="panel-card-body">
                        <p class="text-muted mb-4" style="font-size: 0.82rem;">Choisissez les notifications que vous souhaitez recevoir par email.</p>

                        <form action="{{ route('parametres.notifications.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="d-flex justify-content-between align-items-center p-3 mb-3 rounded" style="background: var(--blanc-casse);">
                                <div>
                                    <div class="fw-semibold" style="font-size: 0.88rem;">Nouveaux evenements</div>
                                    <div class="text-muted" style="font-size: 0.78rem;">Notification lors de la creation d'un nouvel evenement</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="notif_email_evenement" value="1" {{ Auth::user()->notif_email_evenement ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="d-flex justify-content-between align-items-center p-3 mb-3 rounded" style="background: var(--blanc-casse);">
                                <div>
                                    <div class="fw-semibold" style="font-size: 0.88rem;">Nouveaux tickets</div>
                                    <div class="text-muted" style="font-size: 0.78rem;">Notification lors de l'achat d'un nouveau ticket</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="notif_email_ticket" value="1" {{ Auth::user()->notif_email_ticket ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="d-flex justify-content-between align-items-center p-3 mb-3 rounded" style="background: var(--blanc-casse);">
                                <div>
                                    <div class="fw-semibold" style="font-size: 0.88rem;">Paiements confirmes</div>
                                    <div class="text-muted" style="font-size: 0.78rem;">Notification lors de la confirmation d'un paiement</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="notif_email_paiement" value="1" {{ Auth::user()->notif_email_paiement ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="d-flex justify-content-between align-items-center p-3 mb-3 rounded" style="background: var(--blanc-casse);">
                                <div>
                                    <div class="fw-semibold" style="font-size: 0.88rem;">Scans effectues</div>
                                    <div class="text-muted" style="font-size: 0.78rem;">Notification lors du scan d'un ticket a l'entree</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="notif_scan" value="1" {{ Auth::user()->notif_scan ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="pt-3" style="border-top: 1px solid #f0f0f0;">
                                <button type="submit" class="btn btn-vert" style="border-radius: 8px;">
                                    <i class="bi bi-check-lg me-1"></i> Enregistrer les preferences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Paiement KKiaPay --}}
            <div id="section-paiement" class="settings-section">
                <div class="panel-card">
                    <div class="panel-card-header">
                        <h5><i class="bi bi-credit-card me-2" style="color: var(--vert);"></i>Configuration KKiaPay</h5>
                    </div>
                    <div class="panel-card-body">
                        <p class="text-muted mb-4" style="font-size: 0.82rem;">Configurez vos cles API KKiaPay pour recevoir les paiements. Vous pouvez les obtenir sur <a href="https://kkiapay.me" target="_blank" style="color: var(--vert);">kkiapay.me</a>.</p>

                        <form action="{{ route('parametres.paiement.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-semibold" style="font-size: 0.82rem;">Activer KKiaPay</label>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="kkiapay_active" value="1" {{ Auth::user()->kkiapay_active ? 'checked' : '' }}>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.82rem;">Public Key (Cle publique)</label>
                                <input type="text" name="kkiapay_public_key" class="form-control" value="{{ old('kkiapay_public_key', Auth::user()->kkiapay_public_key) }}" placeholder="Ex: xxxxxxxxxxxxxxxxxxxx">
                                @if(Auth::user()->kkiapay_public_key)
                                    <small class="text-muted">Cle configuree : <code class="masked-key">{{ substr(Auth::user()->kkiapay_public_key, 0, 4) }}••••{{ substr(Auth::user()->kkiapay_public_key, -4) }}</code></small>
                                @endif
                                @error('kkiapay_public_key')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.82rem;">Secret Key (Cle secrete)</label>
                                <div class="position-relative">
                                    <input type="password" name="kkiapay_secret_key" id="param_secret" class="form-control" value="{{ old('kkiapay_secret_key', Auth::user()->kkiapay_secret_key) }}" placeholder="Ex: xxxxxxxxxxxxxxxxxxxx">
                                    <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password" style="right: 4px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5;">
                                        <i class="bi bi-eye" style="color: #9a9a9a;"></i>
                                    </button>
                                </div>
                                @if(Auth::user()->kkiapay_secret_key)
                                    <small class="text-muted">Cle configuree : <code class="masked-key">{{ substr(Auth::user()->kkiapay_secret_key, 0, 4) }}••••{{ substr(Auth::user()->kkiapay_secret_key, -4) }}</code></small>
                                @endif
                                @error('kkiapay_secret_key')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.82rem;">API Key (Cle API)</label>
                                <div class="position-relative">
                                    <input type="password" name="kkiapay_api_key" id="param_api" class="form-control" value="{{ old('kkiapay_api_key', Auth::user()->kkiapay_api_key) }}" placeholder="Ex: xxxxxxxxxxxxxxxxxxxx">
                                    <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password" style="right: 4px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5;">
                                        <i class="bi bi-eye" style="color: #9a9a9a;"></i>
                                    </button>
                                </div>
                                @if(Auth::user()->kkiapay_api_key)
                                    <small class="text-muted">Cle configuree : <code class="masked-key">{{ substr(Auth::user()->kkiapay_api_key, 0, 4) }}••••{{ substr(Auth::user()->kkiapay_api_key, -4) }}</code></small>
                                @endif
                                @error('kkiapay_api_key')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                            </div>

                            <div class="alert alert-info" style="background: rgba(66,140,121,0.08); border-radius: 8px; border: none; font-size: 0.82rem;">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>Conseil de securite :</strong> Ne partagez jamais vos cles API. La cle secrete et la cle API ne doivent etre utilisees que cote serveur.
                            </div>

                            <div class="pt-3" style="border-top: 1px solid #f0f0f0;">
                                <button type="submit" class="btn btn-vert" style="border-radius: 8px;">
                                    <i class="bi bi-check-lg me-1"></i> Enregistrer la configuration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Acces scan --}}
            <div id="section-scan" class="settings-section">
                <div class="panel-card">
                    <div class="panel-card-header">
                        <h5><i class="bi bi-qr-code-scan me-2" style="color: var(--teal);"></i>Configuration du scan</h5>
                    </div>
                    <div class="panel-card-body">
                        <p class="text-muted mb-4" style="font-size: 0.82rem;">Le code d'acces scan permet a vos agents de verifier les tickets a l'entree des evenements.</p>

                        <form action="{{ route('parametres.scan.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label class="form-label fw-semibold" style="font-size: 0.82rem;">Code d'acces scan</label>
                                <div class="input-group">
                                    <input type="text" name="code_acces_scan" class="form-control" value="{{ old('code_acces_scan', Auth::user()->code_acces_scan) }}" placeholder="Ex: SCAN2026" maxlength="50">
                                    <button type="button" class="btn btn-secondary-custom" onclick="generateScanCode()" style="border-radius: 0 8px 8px 0;">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Generer
                                    </button>
                                </div>
                                @error('code_acces_scan')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                                <small class="text-muted mt-1 d-block">Minimum 4 caracteres. Laissez vide pour desactiver le code d'acces.</small>
                            </div>

                            @if(Auth::user()->code_acces_scan)
                                <div class="p-3 rounded mb-4" style="background: rgba(18,151,110,0.06); border-left: 4px solid var(--vert);">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-check-circle" style="color: var(--vert); font-size: 1.25rem;"></i>
                                        <div>
                                            <div class="fw-semibold" style="font-size: 0.88rem;">Scan actif</div>
                                            <div class="text-muted" style="font-size: 0.78rem;">Le code d'acces est actuellement configure. Les agents doivent l'utiliser pour scanner les tickets.</div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="p-3 rounded mb-4" style="background: rgba(243,156,18,0.06); border-left: 4px solid #f39c12;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-exclamation-triangle" style="color: #f39c12; font-size: 1.25rem;"></i>
                                        <div>
                                            <div class="fw-semibold" style="font-size: 0.88rem;">Scan non configure</div>
                                            <div class="text-muted" style="font-size: 0.78rem;">Aucun code d'acces n'est defini. Configurez-en un pour permettre le scan des tickets.</div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="pt-3" style="border-top: 1px solid #f0f0f0;">
                                <button type="submit" class="btn btn-vert" style="border-radius: 8px;">
                                    <i class="bi bi-check-lg me-1"></i> Enregistrer le code
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Zone de danger --}}
            <div id="section-danger" class="settings-section">
                <div class="panel-card" style="border: 1px solid rgba(231,76,60,0.2);">
                    <div class="panel-card-header" style="background: rgba(231,76,60,0.04); border-bottom-color: rgba(231,76,60,0.1);">
                        <h5><i class="bi bi-exclamation-triangle me-2" style="color: var(--danger);"></i>Zone de danger</h5>
                    </div>
                    <div class="panel-card-body">
                        <p class="text-muted mb-4" style="font-size: 0.82rem;">Ces actions sont irreversibles. Soyez sur de ce que vous faites avant de continuer.</p>

                        <div class="p-3 rounded mb-4" style="background: rgba(231,76,60,0.04); border: 1px solid rgba(231,76,60,0.15);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold" style="color: var(--danger); font-size: 0.95rem;">Supprimer votre compte</div>
                                    <div class="text-muted" style="font-size: 0.78rem;">Cette action est irreversible. Tous vos evenements, tickets et donnees seront supprimes definitivement.</div>
                                </div>
                                <button type="button" class="btn btn-outline-rouge" style="border-radius: 8px; white-space: nowrap;" data-bs-toggle="modal" data-bs-target="#modalDeleteAccount">
                                    Supprimer
                                </button>
                            </div>
                        </div>

                        <div class="p-3 rounded" style="background: rgba(243,156,18,0.04); border: 1px solid rgba(243,156,18,0.15);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold" style="color: #f39c12; font-size: 0.95rem;">Exporter mes donnees</div>
                                    <div class="text-muted" style="font-size: 0.78rem;">Telecharger une copie de toutes vos donnees personnelles et de vos evenements.</div>
                                </div>
                                <button type="button" class="btn btn-sm" style="border-radius: 8px; border: 1px solid #f39c12; color: #f39c12; background: transparent;">
                                    <i class="bi bi-download me-1"></i> Exporter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal suppression compte --}}
<div class="modal fade" id="modalDeleteAccount" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: 1px solid rgba(231,76,60,0.2);">
            <div class="modal-header" style="border-bottom-color: rgba(231,76,60,0.1);">
                <h5 class="modal-title" style="color: var(--danger);">
                    <i class="bi bi-exclamation-triangle me-2"></i>Supprimer mon compte
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" style="border-radius: 8px; font-size: 0.85rem;">
                    <strong>Attention !</strong> Cette action est irreversible.
                </div>
                <p class="text-muted" style="font-size: 0.88rem;">Pour confirmer la suppression, tapez <strong>supprimer</strong> ci-dessous :</p>
                <form action="{{ route('parametres.compte.delete') }}" method="POST">
                    @csrf
                    <input type="text" name="confirmation" class="form-control" placeholder="Tapez 'supprimer'" required>
                    @error('confirmation')<div class="text-danger mt-1" style="font-size: 0.78rem;">{{ $message }}</div>@enderror
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal" style="border-radius: 8px;">Annuler</button>
                        <button type="submit" class="btn btn-outline-rouge" style="border-radius: 8px;">Supprimer definitivement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input');
        if (!input) return;
        const icon = this.querySelector('i');
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });
});

function showSection(sectionId, el) {
    document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.settings-nav-link').forEach(l => l.classList.remove('active'));

    document.getElementById('section-' + sectionId).classList.add('active');
    el.classList.add('active');
}

function generateScanCode() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let code = 'SCAN-';
    for (let i = 0; i < 6; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.querySelector('input[name="code_acces_scan"]').value = code;
}
</script>
@endsection
