<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_paxevent.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organisation — PaxEvent</title>
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
            padding: 1.5rem;
            max-width: 560px;
            width: 100%;
            box-shadow: 0 8px 40px rgba(0,0,0,0.06);
        }
        .card .logo { max-width: 150px; height: auto; display: block; margin: 0 auto 1rem; }
        .card h1 { font-size: 1.5rem; font-weight: 700; color: #1d1d1f; text-align: center; }
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
            text-decoration: none; display: block; text-align: center;
        }
        .btn-secondary:hover { background: #f8f6f9; }
        .type-card {
            border: 2px solid #e0dde3;
            border-radius: 14px;
            padding: 1.25rem;
            cursor: pointer;
            transition: all .2s;
            text-align: center;
            height: 100%;
        }
        .type-card:hover { border-color: #9972B0; background: #faf8fb; }
        .type-card.selected { border-color: #542680; background: #f5f0f9; }
        .type-card .icon { font-size: 2rem; color: #542680; margin-bottom: .5rem; }
        .type-card .name { font-weight: 700; font-size: 1rem; color: #1d1d1f; margin-bottom: .25rem; }
        .type-card .desc { font-size: .8rem; color: #6c757d; line-height: 1.4; }
        input[type="radio"] { display: none; }
        .toggle-group { display: flex; gap: .5rem; }
        .toggle-btn {
            flex: 1; text-align: center; padding: .6rem .5rem; border-radius: 10px;
            border: 1.5px solid #e0dde3; cursor: pointer; font-weight: 600; font-size: .85rem;
            background: #fff; transition: .2s; color: #495057;
        }
        .toggle-btn:hover { border-color: #9972B0; }
        .toggle-btn.active { background: #f5f0f9; border-color: #542680; color: #542680; }
        .toggle-btn input { display: none; }
        .doc-info { background: #f8f6f9; border-radius: 10px; padding: .75rem 1rem; font-size: .82rem; color: #495057; }
        .doc-info i { color: #542680; margin-right: .35rem; }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo">
        <h1>Type d'organisation</h1>
        <p class="subtitle">Choisissez votre type et fournissez les justificatifs</p>

        @if($errors->any())
            <div class="alert alert-danger py-2" style="font-size:.85rem;border-radius:10px;">
                @foreach($errors->all() as $e) {{ $e }} @break @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('inscriptions.post-org') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="type-card @if(old('type', $type) === 'universitaire') selected @endif" onclick="selectType(this, 'universitaire')">
                        <div class="icon"><i class="bi bi-building-columns"></i></div>
                        <div class="name">Universitaire</div>
                        <div class="desc">Vous représentez une université ou un établissement scolaire</div>
                        <input type="radio" name="type" value="universitaire" @if(old('type', $type) === 'universitaire') checked @endif required>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="type-card @if(old('type', $type) === 'particulier') selected @endif" onclick="selectType(this, 'particulier')">
                        <div class="icon"><i class="bi bi-person"></i></div>
                        <div class="name">Particulier</div>
                        <div class="desc">Vous organisez des événements en votre nom propre</div>
                        <input type="radio" name="type" value="particulier" @if(old('type', $type) === 'particulier') checked @endif required>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="type-card @if(old('type', $type) === 'organisation') selected @endif" onclick="selectType(this, 'organisation')">
                        <div class="icon"><i class="bi bi-building"></i></div>
                        <div class="name">Organisation</div>
                        <div class="desc">Vous représentez une entreprise, une association ou un club</div>
                        <input type="radio" name="type" value="organisation" @if(old('type', $type) === 'organisation') checked @endif required>
                    </label>
                </div>
            </div>

            <div id="fields-universitaire" style="display:none;">
                <div class="mb-3">
                    <label class="form-label">Nom de l'université</label>
                    <input type="text" name="organisation" class="form-control @error('organisation') is-invalid @enderror"
                           value="{{ old('organisation', $data['organisation'] ?? '') }}" placeholder="Ex: Université d'Abomey-Calavi">
                    @error('organisation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div id="fields-organisation" style="display:none;">
                <div class="mb-3">
                    <label class="form-label">Nom de l'organisation</label>
                    <input type="text" name="organisation" class="form-control @error('organisation') is-invalid @enderror"
                           value="{{ old('organisation', $data['organisation'] ?? '') }}" placeholder="Ex: ABC SARL">
                    @error('organisation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <div class="toggle-group">
                        @php $td = old('type_detail', $data['type_detail'] ?? ''); @endphp
                        <label class="toggle-btn {{ $td === 'entreprise' ? 'active' : '' }}">
                            <input type="radio" name="type_detail" value="entreprise" {{ $td === 'entreprise' ? 'checked' : '' }}> Entreprise
                        </label>
                        <label class="toggle-btn {{ $td === 'association' ? 'active' : '' }}">
                            <input type="radio" name="type_detail" value="association" {{ $td === 'association' ? 'checked' : '' }}> Association
                        </label>
                        <label class="toggle-btn {{ $td === 'club' ? 'active' : '' }}">
                            <input type="radio" name="type_detail" value="club" {{ $td === 'club' ? 'checked' : '' }}> Club
                        </label>
                    </div>
                    @error('type_detail') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>

            <div id="fields-particulier" style="display:none;">
                <p class="doc-info"><i class="bi bi-info-circle"></i> Vous organisez en tant que particulier.</p>
            </div>

            <div class="mb-3">
                <label class="form-label">Description <span class="text-muted fw-normal">(optionnelle)</span></label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                          rows="3" placeholder="Parlez-nous de votre activité..." style="resize:vertical;">{{ old('description', $data['description'] ?? '') }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" id="doc-label">Pièce justificative</label>
                <div id="doc-helper" class="doc-info mb-2"><i class="bi bi-file-earmark-text"></i> <span id="doc-text">Carte étudiante ou lettre de l'université</span></div>
                <input type="file" name="document_justificatif" class="form-control @error('document_justificatif') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                <div class="form-text">Format PDF, JPG ou PNG. Max 5 Mo.</div>
                @error('document_justificatif') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn-primary">Continuer</button>
            <a href="{{ route('inscriptions.identity') }}" class="btn-secondary">Précédent</a>
        </form>
    </div>

    <script>
        const docLabels = {
            universitaire: 'Carte étudiante ou lettre de l\'université',
            particulier: 'CIP (Carte d\'identité personnelle)',
            organisation: 'IFU ou RCCM'
        };

        function selectType(el, val) {
            document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector('input[type="radio"]').checked = true;

            document.getElementById('fields-universitaire').style.display = val === 'universitaire' ? 'block' : 'none';
            document.getElementById('fields-organisation').style.display = val === 'organisation' ? 'block' : 'none';
            document.getElementById('fields-particulier').style.display = val === 'particulier' ? 'block' : 'none';

            document.getElementById('doc-text').textContent = docLabels[val] || 'Document justificatif';
        }

        (function init() {
            const selected = document.querySelector('.type-card.selected');
            if (selected) {
                const val = selected.querySelector('input[type="radio"]').value;
                document.getElementById('fields-universitaire').style.display = val === 'universitaire' ? 'block' : 'none';
                document.getElementById('fields-organisation').style.display = val === 'organisation' ? 'block' : 'none';
                document.getElementById('fields-particulier').style.display = val === 'particulier' ? 'block' : 'none';
                document.getElementById('doc-text').textContent = docLabels[val] || 'Document justificatif';
            }
        })();

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
