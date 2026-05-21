@extends('layouts.app')

@section('title', 'Scan QR - PassEvent')

@section('page-title', 'Accès scan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Scan QR</li>
@endsection

@section('content')
<style>
    .access-container {
        max-width: 520px;
        margin: 2rem auto;
    }
    .access-card {
        background: #fff;
        border-radius: 20px;
        padding: 2.5rem 2rem;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        text-align: center;
    }
    .access-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(123,63,160,0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }
    .access-icon i {
        font-size: 2.2rem;
        color: #7B3FA0;
    }
    .access-input {
        font-family: 'Courier New', monospace;
        font-size: 1.3rem;
        letter-spacing: 4px;
        text-transform: uppercase;
        text-align: center;
        padding: 1rem;
        border-radius: 12px;
        border: 2px solid #e0e0e0;
        transition: border-color 0.3s;
    }
    .access-input:focus {
        border-color: #7B3FA0;
        box-shadow: 0 0 0 3px rgba(123,63,160,0.12);
    }
    .access-btn {
        padding: 0.85rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
    }
    .access-error {
        color: var(--danger);
        font-size: 0.85rem;
        margin-top: 0.75rem;
        display: none;
    }
    .access-info {
        font-size: 0.82rem;
        color: #888;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #f0f0f0;
    }
</style>

<div class="page-content">
    <div class="access-container">
        <div class="access-card">
            <div class="access-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h4 class="fw-bold mb-2">Code d'accès requis</h4>
            <p class="text-muted mb-4" style="font-size: 0.9rem;">
                Entrez le code d'accès fourni par l'organisateur pour accéder au scan.
            </p>

            <form id="accessCodeForm">
                @csrf
                <div class="mb-4">
                    <input type="text" id="codeInput" name="code" class="form-control access-input"
                           placeholder="SCAN-XXXXXXXX" autocomplete="off" required
                           autofocus>
                </div>
                <button type="submit" class="btn btn-vert w-100 access-btn" id="btnSubmit">
                    <i class="bi bi-unlock me-2"></i> Accéder au scan
                </button>
                <div class="access-error" id="accessError"></div>
            </form>

            <div class="access-info">
                <i class="bi bi-info-circle me-1"></i>
                Chaque code d'accès est lié à un événement spécifique.
                Demandez le code à votre organisateur.
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('accessCodeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const code = document.getElementById('codeInput').value.trim().toUpperCase();
    const btn = document.getElementById('btnSubmit');
    const error = document.getElementById('accessError');

    if (!code) {
        error.textContent = 'Veuillez entrer un code d\'accès.';
        error.style.display = 'block';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Vérification...';
    error.style.display = 'none';

    fetch('{{ route('scan.access-code') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ code: code }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            error.textContent = data.message;
            error.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-unlock me-2"></i> Accéder au scan';
        }
    })
    .catch(() => {
        error.textContent = 'Erreur de connexion. Réessayez.';
        error.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-unlock me-2"></i> Accéder au scan';
    });
});

document.getElementById('codeInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('accessCodeForm').dispatchEvent(new Event('submit'));
    }
});
</script>
@endsection
