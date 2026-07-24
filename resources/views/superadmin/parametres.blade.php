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
                    <span class="sa-badge sa-badge-success">Configuré</span>
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
@endsection
