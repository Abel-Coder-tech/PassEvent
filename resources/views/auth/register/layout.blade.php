@extends('layouts.public')

@section('styles')
<style>
    body {
        background: linear-gradient(135deg, #f5f5f7 0%, #e8e0ec 100%) !important;
    }
    .card-register {
        background: #fff;
        border-radius: 20px;
        padding: 1.5rem;
        max-width: 420px;
        margin: 0 auto;
        box-shadow: 0 8px 40px rgba(0,0,0,0.06);
    }
    .card-register .logo { max-width: 150px; height: auto; display: block; margin: 0 auto 1rem; }
    .card-register h1 { font-size: 1.5rem; font-weight: 700; color: #1d1d1f; margin-bottom: .35rem; text-align: center; }
    .card-register .subtitle { font-size: .9rem; color: #6c757d; margin-bottom: 1.5rem; text-align: center; }
    .card-register .form-control, .card-register .form-select {
        border-radius: 10px; padding: 0.65rem 1rem; border: 1.5px solid #e0dde3;
    }
    .card-register .form-control:focus, .card-register .form-select:focus {
        border-color: #542680; box-shadow: 0 0 0 3px rgba(84,38,128,0.12);
    }
    .card-register .is-invalid { border-color: #dc3545 !important; }
    .card-register .invalid-feedback { font-size: 0.8rem; }
    .card-register .form-label { font-size: 0.82rem; font-weight: 600; color: #495057; margin-bottom: 0.25rem; }
    .card-register .btn-primary {
        background: #542680; border: none; border-radius: 10px; padding: 0.7rem 1rem;
        font-weight: 600; width: 100%; transition: 0.2s; margin-top: 0.5rem; color: #fff;
    }
    .card-register .btn-primary:hover { background: #451e68; transform: translateY(-1px); color: #fff; }
    .card-register .btn-primary:disabled { opacity: 0.6; }
    .card-register .btn-secondary {
        background: transparent; border: 1.5px solid #e0dde3; border-radius: 10px; padding: 0.6rem 1rem;
        font-weight: 600; width: 100%; color: #6c757d; transition: 0.2s; margin-top: 0.5rem;
        text-decoration: none; display: block; text-align: center;
    }
    .card-register .btn-secondary:hover { background: #f8f6f9; }
    .card-register .login-link { margin-top: 1.5rem; font-size: 0.85rem; color: #6c757d; }
    .card-register .login-link a { color: #542680; font-weight: 600; text-decoration: none; }

    .btn-google {
        display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        background: #fff; border: 1.5px solid #e0dde3; border-radius: 10px; padding: 0.7rem 1rem;
        font-weight: 600; font-size: 0.9rem; color: #1d1d1f; text-decoration: none; transition: 0.2s;
    }
    .btn-google:hover { background: #f8f6f9; border-color: #9972B0; color: #1d1d1f; }
    .divider { display: flex; align-items: center; color: #ccc; font-size: 0.85rem; }
    .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #e0dde3; }
    .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 10px; padding: 0.6rem 1rem; font-size: 0.85rem; margin-bottom: 1rem; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 10px; padding: 0.6rem 1rem; font-size: 0.85rem; margin-bottom: 1rem; }

    .type-card {
        border: 2px solid #e0dde3; border-radius: 14px; padding: 1.25rem 0.75rem;
        cursor: pointer; transition: all 0.2s; text-align: center; height: 100%;
    }
    .type-card:hover { border-color: #9972B0; background: #faf8fb; }
    .type-card.selected { border-color: #542680; background: #f5f0f9; }
    .type-card .icon { font-size: 1.5rem; color: #542680; margin-bottom: 0.4rem; }
    .type-card .name { font-weight: 700; font-size: 0.85rem; color: #1d1d1f; margin-bottom: 0.2rem; }
    .type-card .desc { font-size: 0.72rem; color: #6c757d; line-height: 1.3; }
    input[type="radio"] { display: none; }
    .toggle-group { display: flex; gap: 0.5rem; }
    .toggle-btn {
        flex: 1; text-align: center; padding: 0.5rem 0.3rem; border-radius: 10px;
        border: 1.5px solid #e0dde3; cursor: pointer; font-weight: 600; font-size: 0.78rem;
        background: #fff; transition: 0.2s; color: #495057;
    }
    .toggle-btn:hover { border-color: #9972B0; }
    .toggle-btn.active { background: #f5f0f9; border-color: #542680; color: #542680; }
    .toggle-btn input { display: none; }
    .doc-info { background: #f8f6f9; border-radius: 10px; padding: 0.75rem 1rem; font-size: 0.82rem; color: #495057; }
    .doc-info i { color: #542680; margin-right: 0.35rem; }
    .recap-row { display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid #f0eeec; font-size: 0.9rem; }
    .recap-row:last-child { border-bottom: none; }
    .recap-label { color: #6c757d; }
    .recap-value { font-weight: 600; color: #1d1d1f; text-align: right; max-width: 60%; word-break: break-word; }
    .recap-section { background: #f8f6f9; border-radius: 12px; padding: 0.75rem 1rem; margin-bottom: 1rem; }
    .recap-section-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #9972B0; margin-bottom: 0.5rem; }
    .avatar-preview { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid #e0dde3; }
    .form-check-label { font-size: 0.85rem; color: #495057; }
    .form-check-input:checked { background-color: #542680; border-color: #542680; }
    .form-check-input:focus { box-shadow: 0 0 0 3px rgba(84,38,128,0.12); border-color: #542680; }
    .success-icon { font-size: 3.5rem; color: #2E7D4F; margin-bottom: 1rem; }
    .info-email { background: #f8f6f9; border-radius: 10px; padding: 0.6rem 1rem; font-size: 0.85rem; color: #542680; margin-bottom: 1rem; word-break: break-all; }
    .resend-link { font-size: 0.85rem; color: #6c757d; margin-top: 1rem; }
    .resend-link a { color: #542680; font-weight: 600; text-decoration: none; cursor: pointer; }
    .back-link { font-size: 0.85rem; color: #6c757d; margin-top: 0.75rem; }
    .back-link a { color: #6c757d; text-decoration: none; }

    .progress-volet {
        background: #f8f6f9;
        border-radius: 12px;
        padding: 0.75rem 1rem 0.5rem;
        margin-bottom: 1.25rem;
        border: 1px solid #eee;
        cursor: pointer;
        user-select: none;
        transition: background 0.2s;
    }
    .progress-volet:hover { background: #f5f0f9; }
    .progress-volet-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #9972B0;
    }
    .progress-volet-header i { font-size: 0.7rem; transition: transform 0.3s; }
    .progress-volet.open .progress-volet-header i { transform: rotate(180deg); }
    .progress-volet-body {
        overflow: hidden;
        max-height: 0;
        transition: max-height 0.35s ease, padding 0.3s ease;
        padding-top: 0;
    }
    .progress-volet.open .progress-volet-body {
        max-height: 200px;
        padding-top: 0.75rem;
    }
    .volet-steps {
        display: flex;
        align-items: center;
        gap: 0;
    }
    .volet-step {
        flex: 1;
        text-align: center;
        padding: 0.35rem 0.2rem;
        font-size: 0.68rem;
        font-weight: 600;
        color: #c5c5c5;
        background: #e8e8e8;
        border-right: 2px solid #fff;
        transition: all 0.3s;
        position: relative;
    }
    .volet-step:first-child { border-radius: 8px 0 0 8px; }
    .volet-step:last-child { border-radius: 0 8px 8px 0; border-right: none; }
    .volet-step.active { background: #542680; color: #fff; }
    .volet-step.done { background: #2e7d4f; color: #fff; }
    .volet-step .step-label { display: block; }
    .volet-step .step-icon { font-size: 0.8rem; }
</style>
@endsection

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card-register">
                    @hasSection('step')
                    @php
                        $allSteps = [0 => 'Compte', 1 => 'Identité'];
                        $current = (int) $__env->yieldContent('step');
                    @endphp
                    <div class="progress-volet open" id="progressVolet">
                        <div class="progress-volet-header" onclick="document.getElementById('progressVolet').classList.toggle('open')">
                            <span><i class="bi bi-stack me-1"></i> Progression</span>
                            <i class="bi bi-chevron-down"></i>
                        </div>
                        <div class="progress-volet-body">
                            <div class="volet-steps">
                                @foreach($allSteps as $i => $label)
                                <div class="volet-step {{ $i < $current ? 'done' : ($i === $current ? 'active' : '') }}">
                                    <span class="step-icon">
                                        {!! $i < $current ? '<i class="bi bi-check-circle-fill"></i>' : ($i === $current ? '<i class="bi bi-circle-fill"></i>' : '<i class="bi bi-circle"></i>') !!}
                                    </span>
                                    <span class="step-label">{{ $label }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    @hasSection('page-title')
                    <h1>@yield('page-title')</h1>
                    @endif
                    @hasSection('page-subtitle')
                    <p class="subtitle">@yield('page-subtitle')</p>
                    @endif
                    @yield('card-content')
                </div>
            </div>
        </div>
    </div>
@endsection