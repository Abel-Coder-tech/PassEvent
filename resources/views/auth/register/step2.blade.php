<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_paxevent.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informations — PaxEvent</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f5f7 0%, #e8e0ec 100%);
            padding: 1rem;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 8px 40px rgba(0,0,0,0.06);
        }
        .card .logo { max-width: 100%; height: auto; }
        .card h1 { font-size: 1.5rem; font-weight: 700; color: #1d1d1f; margin-bottom: .25rem; text-align: center; }
        .card .subtitle { font-size: .9rem; color: #6c757d; margin-bottom: 1.5rem; text-align: center; }
        .form-control, .form-select { border-radius: 10px; padding: .65rem 1rem; border: 1.5px solid #e0dde3; }
        .form-control:focus, .form-select:focus { border-color: #542680; box-shadow: 0 0 0 3px rgba(84,38,128,.12); }
        .is-invalid { border-color: #dc3545 !important; }
        .invalid-feedback { font-size: .8rem; }
        .form-label { font-size: .82rem; font-weight: 600; color: #495057; margin-bottom: .25rem; }
        .btn-primary {
            background: #542680; border: none; border-radius: 10px; padding: .7rem 1rem;
            font-weight: 600; width: 100%; transition: .2s; margin-top: .5rem;
        }
        .btn-primary:hover { background: #451e68; transform: translateY(-1px); }
        .btn-secondary {
            background: transparent; border: 1.5px solid #e0dde3; border-radius: 10px; padding: .6rem 1rem;
            font-weight: 600; width: 100%; color: #6c757d; transition: .2s; margin-top: .5rem;
        }
        .btn-secondary:hover { background: #f8f6f9; }
        .type-badge { display: inline-block; background: #f5f0f9; color: #542680; font-size: .75rem; font-weight: 700; padding: .25rem .75rem; border-radius: 20px; margin-bottom: 1.25rem; }
        .toggle-group { display: flex; gap: .5rem; }
        .toggle-btn {
            flex: 1; text-align: center; padding: .6rem .5rem; border-radius: 10px;
            border: 1.5px solid #e0dde3; cursor: pointer; font-weight: 600; font-size: .85rem;
            background: #fff; transition: .2s; color: #495057;
        }
        .toggle-btn:hover { border-color: #9972B0; }
        .toggle-btn.active { background: #f5f0f9; border-color: #542680; color: #542680; }
        .toggle-btn input { display: none; }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo">
        <h1>Vos informations</h1>
        <p class="subtitle">
            <span class="type-badge">
                @if($type === 'universitaire') <i class="bi bi-building-columns"></i>
                @elseif($type === 'particulier') <i class="bi bi-person"></i>
                @else <i class="bi bi-building"></i>
                @endif
                {{ ucfirst($type) }}
            </span>
        </p>

        @if($errors->any())
            <div class="alert alert-danger py-2" style="font-size:.85rem;border-radius:10px;">
                @foreach($errors->all() as $e) {{ $e }} @break @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('inscriptions.post-infos') }}" enctype="multipart/form-data">
            @csrf

            @if($type === 'universitaire')
                <div class="mb-3">
                    <label class="form-label">Nom de l'universit&eacute;</label>
                    <input type="text" name="organisation" class="form-control @error('organisation') is-invalid @enderror"
                           value="{{ old('organisation', $data['organisation'] ?? '') }}" required>
                    @error('organisation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nom du responsable</label>
                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                           value="{{ old('nom', $data['nom'] ?? '') }}" required>
                    @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            @elseif($type === 'particulier')
                <div class="mb-3">
                    <label class="form-label">Nom complet</label>
                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                           value="{{ old('nom', $data['nom'] ?? '') }}" required>
                    @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            @elseif($type === 'organisation')
                <div class="mb-3">
                    <label class="form-label">Nom de l'organisation</label>
                    <input type="text" name="organisation" class="form-control @error('organisation') is-invalid @enderror"
                           value="{{ old('organisation', $data['organisation'] ?? '') }}" required>
                    @error('organisation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nom du responsable</label>
                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                           value="{{ old('nom', $data['nom'] ?? '') }}" required>
                    @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <div class="toggle-group">
                        @php $td = old('type_detail', $data['type_detail'] ?? ''); @endphp
                        <label class="toggle-btn {{ $td === 'entreprise' ? 'active' : '' }}">
                            <input type="radio" name="type_detail" value="entreprise" {{ $td === 'entreprise' ? 'checked' : '' }} required>
                            Entreprise
                        </label>
                        <label class="toggle-btn {{ $td === 'association' ? 'active' : '' }}">
                            <input type="radio" name="type_detail" value="association" {{ $td === 'association' ? 'checked' : '' }} required>
                            Association
                        </label>
                        <label class="toggle-btn {{ $td === 'club' ? 'active' : '' }}">
                            <input type="radio" name="type_detail" value="club" {{ $td === 'club' ? 'checked' : '' }} required>
                            Club
                        </label>
                    </div>
                    @error('type_detail') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label">T&eacute;l&eacute;phone</label>
                <input type="tel" name="telephone" class="form-control @error('telephone') is-invalid @enderror"
                       value="{{ old('telephone', $data['telephone'] ?? '') }}" required placeholder="+229 XX XX XX XX">
                @error('telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="mot_de_passe" class="form-control @error('mot_de_passe') is-invalid @enderror"
                       minlength="8" required placeholder="Min. 8 caract&egrave;res">
                @error('mot_de_passe') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Confirmer le mot de passe</label>
                <input type="password" name="mot_de_passe_confirmation" class="form-control"
                       required placeholder="R&eacute;p&eacute;tez le mot de passe">
            </div>

            <div class="mb-3">
                <label class="form-label">
                    @if($type === 'particulier') Photo de profil
                    @else Logo
                    @endif
                    <span class="text-muted fw-normal">(optionnel)</span>
                </label>
                <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
                <div class="form-text">Formats accept&eacute;s : JPG, PNG. Max 2 Mo.</div>
                @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary">Continuer</button>
            <a href="{{ route('inscriptions.type') }}" class="btn-secondary d-block text-center text-decoration-none">Pr&eacute;c&eacute;dent</a>
        </form>
    </div>

    <script>
        document.querySelectorAll('.toggle-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.toggle-group').querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                this.querySelector('input').checked = true;
            });
        });
    </script>
</body>
</html>
