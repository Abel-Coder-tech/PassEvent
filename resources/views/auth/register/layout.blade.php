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

    .steps-bar {
        display: flex; align-items: center; justify-content: center;
        padding: 1.25rem 1rem 0.5rem;
        background: #fff;
        border-bottom: 1px solid #f0eeec;
        flex-wrap: wrap;
    }
    .step-item {
        display: flex; align-items: center; gap: 0.4rem;
        font-size: 0.78rem; color: #ccc; font-weight: 500;
    }
    .step-item.active { color: #542680; font-weight: 700; }
    .step-item.done { color: #2e7d4f; font-weight: 600; cursor: pointer; }
    a.step-item.done:hover { opacity: 0.8; }
    .step-num {
        width: 26px; height: 26px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 700;
        background: #e0dde3; color: #fff; flex-shrink: 0;
    }
    .step-item.active .step-num { background: #542680; }
    .step-item.done .step-num { background: #2e7d4f; }
    .step-connector {
        width: 24px; height: 2px;
        background: #e0dde3; margin: 0 0.5rem; flex-shrink: 0;
    }
    .step-connector.done { background: #2e7d4f; }
</style>
@endsection

@section('content')
    @hasSection('step')
    @php
        $allSteps = [
            0 => 'Compte',
            1 => 'Identité',
            2 => 'Organisation',
            3 => 'Récapitulatif',
        ];
        $current = (int) $__env->yieldContent('step');
    @endphp
    <div class="steps-bar">
        @foreach($allSteps as $i => $label)
            @if($i > 0)
                <div class="step-connector {{ $i <= $current ? 'done' : '' }}"></div>
            @endif
            @if($i < $current)
                <a href="{{ route('inscriptions.previous', $i) }}" class="step-item done" style="text-decoration:none;">
                    <span class="step-num"><i class="bi bi-check" style="font-size:0.7rem;"></i></span>
                    {{ $label }}
                </a>
            @elseif($i === $current)
                <div class="step-item active">
                    <span class="step-num">{{ $i + 1 }}</span>
                    {{ $label }}
                </div>
            @else
                <div class="step-item">
                    <span class="step-num">{{ $i + 1 }}</span>
                    {{ $label }}
                </div>
            @endif
        @endforeach
    </div>
    @endif

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card-register">
                    <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo" style="max-width:150px;height:auto;display:block;margin:0 auto 1rem;">
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