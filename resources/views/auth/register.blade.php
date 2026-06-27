<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/paxevent_icone.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — PaxEvent</title>
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
        .register-card {
            width: 100%;
            max-width: 820px;
            border-radius: 18px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            background: #fff;
            padding: 2.5rem;
        }
        .register-form h2 {
            font-size: 1.4rem;
            font-weight: 800;
            color: #211C31;
            margin-bottom: 0.1rem;
        }
        .register-form .subtitle {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
        }

        /* Stepper */
        .stepper {
            display: flex;
            justify-content: center;
            gap: 0;
            margin-bottom: 2rem;
            position: relative;
        }
        .stepper::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 15%;
            right: 15%;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }
        .stepper-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.4rem;
            flex: 1;
            position: relative;
            z-index: 1;
        }
        .stepper-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 700;
            background: #e0e0e0;
            color: #999;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        .stepper-step.active .stepper-circle {
            background: #542680;
            color: #fff;
            border-color: #542680;
        }
        .stepper-step.done .stepper-circle {
            background: #9972B0;
            color: #2e7d4f;
            border-color: #9972B0;
        }
        .stepper-label {
            font-size: 0.72rem;
            font-weight: 600;
            color: #999;
            text-align: center;
        }
        @media (max-width: 480px) {
            .register-card { padding: 1.25rem; }
            .step-card-body { padding: 1rem 0.75rem; }
            .stepper-circle { width: 32px; height: 32px; font-size: 0.8rem; }
            .stepper-label { font-size: 0.6rem; }
            .stepper::before { top: 16px; left: 10%; right: 10%; }
            .register-form h2 { font-size: 1.1rem; }
        }
        .stepper-step.active .stepper-label {
            color: #542680;
        }
        .stepper-step.done .stepper-label {
            color: #2e7d4f;
        }

        /* Step card */
        .step-content { display: none; }
        .step-content.active { display: block; }
        .step-card {
            border: 1px solid #ede5f0;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        .step-card-header {
            background: linear-gradient(135deg, #542680 0%, #542680 100%);
            color: #fff;
            padding: 0.85rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 700;
            font-size: 0.95rem;
        }
        .step-card-header i {
            font-size: 1.2rem;
        }
        .step-card-body {
            padding: 1.5rem 1.25rem;
        }
        .step-card + .step-card {
            margin-top: 1rem;
        }

        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.65rem 1rem 0.65rem 2.3rem;
            font-size: 0.85rem;
        }
        .form-control:focus {
            border-color: #542680;
            box-shadow: 0 0 0 3px rgba(84,38,128,0.12);
        }
        .input-group-custom { position: relative; }
        .input-group-custom .icon {
            position: absolute;
            left: 0.7rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9a9a9a;
            font-size: 0.9rem;
            z-index: 5;
            pointer-events: none;
        }
        textarea ~ .icon {
            top: 0.8rem !important;
            transform: none !important;
        }
        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        /* Type selector (gender-style) */
        .type-selector {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .type-option {
            flex: 1;
            min-width: 120px;
        }
        .type-option input { display: none; }
        .type-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            padding: 0.75rem 0.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.78rem;
            font-weight: 600;
            color: #6c757d;
        }
        .type-option label i {
            font-size: 1.3rem;
            color: #9a9a9a;
            transition: color 0.2s;
        }
        .type-option input:checked + label {
            border-color: #542680;
            background: rgba(109,53,112,0.06);
            color: #542680;
        }
        .type-option input:checked + label i {
            color: #542680;
        }

        /* Buttons */
        .btn-step {
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.88rem;
            padding: 0.65rem 1.5rem;
            transition: all 0.2s;
            border: none;
        }
        .btn-step-primary {
            background: #542680;
            color: #fff;
        }
        .btn-step-primary:hover { background: #3d1a5c; }
        .btn-step-outline {
            background: transparent;
            color: #542680;
            border: 1.5px solid #542680;
        }
        .btn-step-outline:hover { background: rgba(109,53,112,0.06); }

        /* Recap cards */
        .recap-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
        .recap-card {
            background: #faf8fb;
            border: 1px solid #ede5f0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }
        .recap-card.full { grid-column: 1 / -1; }
        .recap-card .recap-label {
            font-size: 0.7rem;
            color: #9a9a9a;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .recap-card .recap-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #211C31;
            margin-top: 0.15rem;
        }
        @media (max-width: 480px) {
            .recap-grid { grid-template-columns: 1fr; }
            .type-selector { flex-direction: column; }
            .d-flex.justify-content-between.mt-4 { flex-direction: column; gap: 0.5rem; }
            .d-flex.justify-content-between.mt-4 .btn-step { width: 100%; }
            .d-flex.justify-content-between.mt-3 { flex-direction: column; gap: 0.5rem; }
            .d-flex.justify-content-between.mt-3 .btn-step { width: 100%; }
        }

        .cgu-check {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.82rem;
            color: #6c757d;
        }
        .cgu-check input[type="checkbox"] {
            margin-top: 0.2rem;
            accent-color: #542680;
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        .error-text {
            font-size: 0.75rem;
            color: #dc3545;
            margin-top: 0.2rem;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-form" style="text-align:center;">
            <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" height="150" class="mb-2">
            <h2>Dévenez Organisateur</h2>
            <p class="subtitle">Créez votre compte en 3 étapes</p>

            @if ($errors->any())
                <div class="alert alert-danger py-2" style="font-size:0.82rem;">
                    @foreach ($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <!-- Stepper -->
            <div class="stepper" id="stepper">
                <div class="stepper-step active" data-step="1">
                    <div class="stepper-circle"><i class="bi bi-person"></i></div>
                    <span class="stepper-label">Identité</span>
                </div>
                <div class="stepper-step" data-step="2">
                    <div class="stepper-circle"><i class="bi bi-building"></i></div>
                    <span class="stepper-label">Organisation</span>
                </div>
                <div class="stepper-step" data-step="3">
                    <div class="stepper-circle"><i class="bi bi-check-lg"></i></div>
                    <span class="stepper-label">Validation</span>
                </div>
            </div>

            <form method="POST" action="{{ route('inscriptions.store') }}" id="registerForm">
                @csrf

                <!-- Step 1: Identité -->
                <div class="step-content active" data-step="1">
                    <div class="step-card">
                        <div class="step-card-header"><i class="bi bi-person-circle"></i> Identité</div>
                        <div class="step-card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Nom complet</label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-person icon"></i>
                                        <input type="text" class="form-control" name="nom" placeholder="Votre nom" value="{{ old('nom') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-envelope icon"></i>
                                        <input type="email" class="form-control" name="email" placeholder="exemple@email.com" value="{{ old('email') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Téléphone</label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-telephone icon"></i>
                                        <input type="tel" class="form-control" name="telephone" placeholder="Votre numéro" value="{{ old('telephone') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mot de passe</label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-lock icon"></i>
                                        <input type="password" class="form-control" name="mot_de_passe" id="reg_password" placeholder="Minimum 8 caractères" required>
                                        <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password" style="right: 4px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5;">
                                            <i class="bi bi-eye" style="color: #9a9a9a;"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirmer le mot de passe</label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-lock-fill icon"></i>
                                        <input type="password" class="form-control" name="mot_de_passe_confirmation" id="reg_password_confirmation" placeholder="Répétez le mot de passe" required>
                                        <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password" style="right: 4px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5;">
                                            <i class="bi bi-eye" style="color: #9a9a9a;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn-step btn-step-primary" onclick="nextStep()">Suivant <i class="bi bi-arrow-right ms-1"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Organisation -->
                <div class="step-content" data-step="2">
                    <div class="step-card">
                        <div class="step-card-header"><i class="bi bi-building"></i> Organisation</div>
                        <div class="step-card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Type de compte</label>
                                    <div class="type-selector">
                                        <div class="type-option">
                                            <input type="radio" name="type" id="type-universitaire" value="universitaire" {{ old('type') === 'universitaire' ? 'checked' : '' }}>
                                            <label for="type-universitaire">
                                                <i class="bi bi-mortarboard-fill"></i>
                                                Universitaire
                                            </label>
                                        </div>
                                        <div class="type-option">
                                            <input type="radio" name="type" id="type-professionnel" value="professionnel" {{ old('type') === 'professionnel' ? 'checked' : '' }}>
                                            <label for="type-professionnel">
                                                <i class="bi bi-briefcase-fill"></i>
                                                Professionnel
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Nom de l'organisation</label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-building icon"></i>
                                        <input type="text" class="form-control" name="organisation" placeholder="Nom de votre organisation" value="{{ old('organisation') }}" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description <span class="text-muted" style="font-weight: 400;">(optionnelle)</span></label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-file-text icon"></i>
                                        <textarea class="form-control" name="description" placeholder="Parlez un peu de votre événement ou organisation..." rows="3" style="padding-left: 2.3rem; resize: vertical;">{{ old('description') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn-step btn-step-outline" onclick="prevStep()"><i class="bi bi-arrow-left me-1"></i> Précédent</button>
                                <button type="button" class="btn-step btn-step-primary" onclick="nextStep()">Suivant <i class="bi bi-arrow-right ms-1"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Validation -->
                <div class="step-content" data-step="3">
                    <div class="step-card">
                        <div class="step-card-header"><i class="bi bi-file-earmark-check"></i> Validation</div>
                        <div class="step-card-body">
                            <div class="recap-grid" id="recapGrid">
                                <div class="recap-card">
                                    <div class="recap-label">Nom complet</div>
                                    <div class="recap-value" id="recap-nom"></div>
                                </div>
                                <div class="recap-card">
                                    <div class="recap-label">Email</div>
                                    <div class="recap-value" id="recap-email"></div>
                                </div>
                                <div class="recap-card">
                                    <div class="recap-label">Téléphone</div>
                                    <div class="recap-value" id="recap-telephone"></div>
                                </div>
                                <div class="recap-card">
                                    <div class="recap-label">Type</div>
                                    <div class="recap-value" id="recap-type"></div>
                                </div>
                                <div class="recap-card full">
                                    <div class="recap-label">Organisation</div>
                                    <div class="recap-value" id="recap-organisation"></div>
                                </div>
                                <div class="recap-card full" id="recap-description-wrap" style="display:none;">
                                    <div class="recap-label">Description</div>
                                    <div class="recap-value" id="recap-description"></div>
                                </div>
                            </div>

                            <div class="alert alert-info py-2 mt-3 mb-3" style="font-size:0.78rem; background: rgba(13,110,253,0.06); border: none; color: #0a58ca;">
                                <i class="bi bi-clock-history me-1"></i>
                                Après inscription, votre compte sera activé manuellement par l'administrateur sous 24h à 48h. Vous recevrez un email de confirmation dès l'activation.
                            </div>

                            <div class="cgu-check mb-3">
                                <input type="checkbox" name="cgu" id="cgu" required>
                                <label for="cgu">J'accepte les <a href="{{ route('cgu') }}" target="_blank">Conditions Générales d'Utilisation</a> et la <a href="{{ route('confidentialite') }}" target="_blank">Politique de confidentialité</a> de PaxEvent.</label>
                            </div>

                            <div class="d-flex justify-content-between mt-3">
                                <button type="button" class="btn-step btn-step-outline" onclick="prevStep()"><i class="bi bi-arrow-left me-1"></i> Précédent</button>
                                <button type="submit" class="btn-step btn-step-primary"><i class="bi bi-check-lg me-1"></i> Créer mon compte</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <p class="text-center mt-4" style="font-size:0.82rem; color:#6c757d;">
                Déjà un compte ? <a href="{{ route('login') }}" style="color: #542680; font-weight: 600;">Connectez-vous</a>
            </p>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 3;

        function updateStepper() {
            document.querySelectorAll('.stepper-step').forEach(el => {
                const step = parseInt(el.dataset.step);
                el.classList.remove('active', 'done');
                if (step === currentStep) el.classList.add('active');
                else if (step < currentStep) el.classList.add('done');
            });
            document.querySelectorAll('.step-content').forEach(el => {
                el.classList.toggle('active', parseInt(el.dataset.step) === currentStep);
            });
        }

        function validateStep(step) {
            let valid = true;
            const fields = [];

            if (step === 1) {
                fields.push('nom', 'email', 'telephone', 'mot_de_passe', 'mot_de_passe_confirmation');

                const pass = document.querySelector('input[name="mot_de_passe"]');
                const confirm = document.querySelector('input[name="mot_de_passe_confirmation"]');
                document.querySelectorAll('.error-text').forEach(e => e.remove());

                if (pass.value !== confirm.value) {
                    showError(confirm, 'Les mots de passe ne correspondent pas.');
                    valid = false;
                }
                if (pass.value.length < 8) {
                    showError(pass, 'Minimum 8 caractères.');
                    valid = false;
                }
            }

            if (step === 2) {
                fields.push('organisation');
                if (!document.querySelector('input[name="type"]:checked')) {
                    const container = document.querySelector('.type-selector');
                    if (!container.nextElementSibling?.classList.contains('error-text')) {
                        const err = document.createElement('div');
                        err.className = 'error-text';
                        err.textContent = 'Sélectionnez un type de compte.';
                        container.parentNode.appendChild(err);
                    }
                    valid = false;
                }
            }

            fields.forEach(name => {
                const input = document.querySelector(`input[name="${name}"]`);
                if (input && !input.value.trim()) {
                    showError(input, 'Ce champ est requis.');
                    valid = false;
                }
            });

            return valid;
        }

        function showError(input, msg) {
            const existing = input.parentNode.nextElementSibling;
            if (existing && existing.classList.contains('error-text')) return;
            const err = document.createElement('div');
            err.className = 'error-text';
            err.textContent = msg;
            input.parentNode.parentNode.appendChild(err);
            input.classList.add('is-invalid');
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                const e = this.parentNode.parentNode.querySelector('.error-text');
                if (e) e.remove();
            }, { once: true });
        }

        function nextStep() {
            if (!validateStep(currentStep)) return;
            if (currentStep === 2) fillRecap();
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepper();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                updateStepper();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        function fillRecap() {
            const getVal = (name) => {
                const el = document.querySelector(`input[name="${name}"]`);
                return el ? el.value : '';
            };

            document.getElementById('recap-nom').textContent = getVal('nom');
            document.getElementById('recap-email').textContent = getVal('email');
            document.getElementById('recap-telephone').textContent = getVal('telephone');

            const typeMap = { universitaire: 'Universitaire', professionnel: 'Professionnel' };
            const typeVal = document.querySelector('input[name="type"]:checked');
            document.getElementById('recap-type').textContent = typeVal ? (typeMap[typeVal.value] || typeVal.value) : '-';

            document.getElementById('recap-organisation').textContent = getVal('organisation');

            const desc = document.querySelector('textarea[name="description"]');
            if (desc && desc.value.trim()) {
                document.getElementById('recap-description').textContent = desc.value;
                document.getElementById('recap-description-wrap').style.display = 'block';
            } else {
                document.getElementById('recap-description-wrap').style.display = 'none';
            }
        }

        // Clear errors on input
        document.querySelectorAll('input, textarea, select').forEach(el => {
            el.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                const err = this.parentNode.parentNode.querySelector('.error-text');
                if (err) err.remove();
            });
        });

        document.querySelectorAll('.step-content').forEach(step => {
            step.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const stepNum = parseInt(this.dataset.step);
                    if (stepNum < totalSteps) {
                        nextStep();
                    }
                }
            });
        });

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
    </script>
</body>
</html>