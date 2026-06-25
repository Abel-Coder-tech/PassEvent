@extends('layouts.public')

@section('title', 'Contact - PaxEvent')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Contact</li>
@endsection

@section('content')
<!-- Hero -->
<section class="contact-hero">
    <div class="contact-hero-bg">
        <div class="contact-circle c1"></div>
        <div class="contact-circle c2"></div>
        <div class="contact-circle c3"></div>
    </div>
    <div class="container position-relative" style="z-index:2;">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <span class="contact-hero-chip">Contact</span>
                <h1 class="contact-hero-title">Contactez-nous</h1>
                <p class="contact-hero-sub" style="color:var(--accent);">Une question ou un problème ? Nous sommes là pour vous aider</p>
            </div>
        </div>
    </div>
</section>

<section class="contact-section">
    <div class="container">
        <div class="row g-5">
            <!-- Formulaire -->
            <div class="col-12 col-lg-7">
                <div class="contact-form-card">
                    <h4 class="contact-form-title">
                        <i class="bi bi-envelope me-2" style="color: var(--violet);"></i>
                        Envoyez-nous un message
                    </h4>

                    <form action="{{ route('contact.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="nom_complet" class="contact-label">Nom et prénoms <span class="text-danger">*</span></label>
                            <div class="contact-input-wrap">
                                <i class="bi bi-person"></i>
                                <input type="text" class="contact-input @error('nom_complet') is-invalid @enderror" id="nom_complet" name="nom_complet" value="{{ old('nom_complet') }}" placeholder="Ex: Kofi Mensah" required>
                            </div>
                            @error('nom_complet')<div class="text-danger mt-1" style="font-size:0.82rem;">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="contact-label">Adresse email <span class="text-danger">*</span></label>
                            <div class="contact-input-wrap">
                                <i class="bi bi-envelope"></i>
                                <input type="email" class="contact-input @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="votre@email.com" required>
                            </div>
                            @error('email')<div class="text-danger mt-1" style="font-size:0.82rem;">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="objet" class="contact-label">Objet <span class="text-danger">*</span></label>
                            <div class="contact-input-wrap">
                                <i class="bi bi-chat-dots"></i>
                                <input type="text" class="contact-input @error('objet') is-invalid @enderror" id="objet" name="objet" value="{{ old('objet') }}" placeholder="Ex: Problème de paiement, Question sur un événement..." required>
                            </div>
                            @error('objet')<div class="text-danger mt-1" style="font-size:0.82rem;">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="message" class="contact-label">Message <span class="text-danger">*</span></label>
                            <textarea class="contact-textarea @error('message') is-invalid @enderror" id="message" name="message" rows="5" placeholder="Décrivez votre demande en détail..." required>{{ old('message') }}</textarea>
                            <small class="text-muted">Minimum 10 caractères, maximum 2000 caractères</small>
                            @error('message')<div class="text-danger mt-1" style="font-size:0.82rem;">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="contact-submit">
                            <i class="bi bi-send me-2"></i> Envoyer le message
                        </button>
                    </form>
                </div>
            </div>

            <!-- Infos -->
            <div class="col-12 col-lg-5">
                <div class="contact-info-card">
                    <h5 class="contact-info-title">Nos coordonnées</h5>

                    <a href="https://wa.me/22943704513" class="contact-info-item">
                        <div class="contact-info-icon" style="background: rgba(84,38,128,0.12); color: var(--violet);">
                            <i class="bi bi-whatsapp"></i>
                        </div>
                        <div>
                            <h6>WhatsApp</h6>
                            <span>+229 62 83 66 29</span>
                            <small>Réponse rapide garantie</small>
                        </div>
                    </a>

                    <a href="mailto:paxevent09@gmail.com" class="contact-info-item">
                        <div class="contact-info-icon" style="background: rgba(135,66,139,0.1); color: var(--violet);">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div>
                            <h6>Email</h6>
                            <span>paxevent09@gmail.com</span>
                            <small>Réponse sous 24h</small>
                        </div>
                    </a>

                    <a href="tel:+22943704513" class="contact-info-item">
                        <div class="contact-info-icon" style="background: rgba(84,38,128,0.1); color: var(--violet);">
                            <i class="bi bi-phone"></i>
                        </div>
                        <div>
                            <h6>Téléphone</h6>
                            <span>+229 62 83 66 29</span>
                            <small>Lun-Ven, 8h-18h</small>
                        </div>
                    </a>
                </div>

                <div class="contact-map-card">
                    <div class="contact-map">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63405.62982906847!2d2.36686!3d6.3703!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x102355ec41a5e8e3%3A0xe545b0b0e4f5d5c0!2sCotonou%2C%20Benin!5e0!3m2!1sfr!2sbj!4v1700000000000!5m2!1sfr!2sbj"
                            width="100%"
                            height="220"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    <div class="contact-map-footer">
                        <i class="bi bi-geo-alt-fill" style="color: var(--violet);"></i>
                        <strong>Porto-Novo, Bénin</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* ===== HERO ===== */
.contact-hero {
    padding: 4rem 0 3rem;
    background: linear-gradient(160deg, #211C31 0%, #211C31 50%, #211C31 100%);
    position: relative;
    overflow: hidden;
}
.contact-hero-bg {
    position: absolute; inset: 0;
    pointer-events: none;
}
.contact-circle {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
}
.contact-circle.c1 { width: 400px; height: 400px; background: #542680; top: -150px; right: -80px; opacity: 0.18; }
.contact-circle.c2 { width: 300px; height: 300px; background: #FED514; bottom: -120px; left: -60px; opacity: 0.12; }
.contact-circle.c3 { width: 150px; height: 150px; background: #9972B0; top: 40%; left: 20%; opacity: 0.06; }

.contact-hero-chip {
    display: inline-flex;
    padding: 0.35rem 1rem;
    background: rgba(178,224,214,0.12);
    border: 1px solid rgba(178,224,214,0.2);
    border-radius: 20px;
    color: #9972B0;
    font-size: 0.78rem;
    font-weight: 600;
    margin-bottom: 1rem;
}
.contact-hero-title {
    font-size: 2.6rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 0.75rem;
    line-height: 1.15;
}
.contact-hero-sub {
    font-size: 1.05rem;
    color: rgba(255,255,255,0.6);
    margin: 0;
}

/* ===== SECTION ===== */
.contact-section {
    padding: 4rem 0;
    background: #f7f5f3;
}

/* ===== FORMULAIRE ===== */
.contact-form-card {
    background: #fff;
    border-radius: 18px;
    padding: 2.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid #ede5f0;
}
.contact-form-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #211C31;
    margin: 0 0 1.5rem;
}
.contact-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #211C31;
    margin-bottom: 0.3rem;
    display: block;
}
.contact-input-wrap {
    position: relative;
}
.contact-input-wrap i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9a9a9a;
    font-size: 0.9rem;
    z-index: 5;
}
.contact-input {
    width: 100%;
    padding: 0.7rem 1rem 0.7rem 2.4rem;
    border: 1px solid #e5e5e5;
    border-radius: 10px;
    font-size: 0.88rem;
    outline: none;
    transition: all 0.2s;
    background: #fafafa;
}
.contact-input:focus {
    border-color: #542680;
    box-shadow: 0 0 0 3px rgba(123,63,160,0.08);
    background: #fff;
}
.contact-textarea {
    width: 100%;
    padding: 0.7rem 1rem;
    border: 1px solid #e5e5e5;
    border-radius: 10px;
    font-size: 0.88rem;
    outline: none;
    transition: all 0.2s;
    background: #fafafa;
    resize: vertical;
}
.contact-textarea:focus {
    border-color: #542680;
    box-shadow: 0 0 0 3px rgba(123,63,160,0.08);
    background: #fff;
}
.contact-submit {
    width: 100%;
    padding: 0.85rem;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
    font-weight: 700;
    font-size: 0.95rem;
    transition: all 0.25s;
    cursor: pointer;
}
.contact-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(123,63,160,0.35);
}

/* ===== INFOS ===== */
.contact-info-card {
    background: #fff;
    border-radius: 18px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid #ede5f0;
    margin-bottom: 1.5rem;
}
.contact-info-title {
    font-size: 1rem;
    font-weight: 700;
    color: #211C31;
    margin: 0 0 1.25rem;
}
.contact-info-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 0.85rem 0;
    border-bottom: 1px solid #f5f3f6;
    text-decoration: none;
    transition: all 0.2s;
}
.contact-info-item:last-child { border-bottom: none; }
.contact-info-item:hover {
    padding-left: 0.5rem;
}
.contact-info-item h6 {
    font-size: 0.82rem;
    font-weight: 700;
    color: #211C31;
    margin: 0 0 0.1rem;
}
.contact-info-item span {
    font-size: 0.85rem;
    color: var(--violet);
    font-weight: 600;
    display: block;
}
.contact-info-item small {
    font-size: 0.72rem;
    color: #9a9a9a;
}
.contact-info-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

/* ===== MAP ===== */
.contact-map-card {
    background: #fff;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid #ede5f0;
}
.contact-map iframe {
    display: block;
}
.contact-map-footer {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.85rem 1.25rem;
    font-size: 0.85rem;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 767px) {
    .contact-hero-title { font-size: 1.8rem; }
    .contact-form-card { padding: 1.5rem; }
    .contact-info-card { padding: 1.5rem; }
}
</style>
@endsection
