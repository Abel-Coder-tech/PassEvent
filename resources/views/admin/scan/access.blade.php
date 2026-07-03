@extends('layouts.app')

@section('title', 'Scan QR - PaxEvent')

@section('page-title', 'Accès scan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item active" aria-current="page">Scan QR</li>
@endsection

@section('content')
<style>
    .access-container { max-width: 640px; margin: 0 auto; }
    .evt-card {
        border-radius: 14px;
        border: 2px solid #eee;
        padding: 1.25rem;
        cursor: pointer;
        transition: all 0.2s;
        background: #fff;
    }
    .evt-card:hover { border-color: var(--violet); background: rgba(123,63,160,0.03); }
    .evt-card.selected { border-color: var(--violet); background: rgba(123,63,160,0.06); }
    .evt-card .badge-statut {
        font-size: 0.7rem;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-weight: 500;
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
    }
    .access-input:focus { border-color: var(--violet); box-shadow: 0 0 0 3px rgba(123,63,160,0.12); }
    .access-error { color: var(--danger); font-size: 0.85rem; display: none; }
</style>

<div class="page-content">
    <div class="access-container">

        {{-- Étape 1: Sélection événement --}}
        <div id="stepSelect">
            <div class="text-center mb-4">
                <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(123,63,160,0.08); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <i class="bi bi-qr-code-scan" style="font-size: 2rem; color: #7B3FA0;"></i>
                </div>
                <h4 class="fw-bold mb-1">Sélectionnez un événement</h4>
                <p class="text-muted" style="font-size: 0.9rem;">Choisissez l'événement que vous voulez scanner</p>
            </div>

            @forelse($evenements as $evt)
                @php
                    $colors = ['publié' => 'var(--vert)', 'brouillon' => '#f39c12', 'terminé' => 'var(--danger)', 'annulé' => '#888'];
                    $statutColor = $colors[$evt->statut] ?? '#888';
                @endphp
                <div class="evt-card mb-3" data-id="{{ $evt->id }}" data-titre="{{ $evt->titre }}" onclick="selectEvent(this)">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3 min-w-0">
                            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(123,63,160,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="bi bi-calendar-event" style="color: #7B3FA0; font-size: 1.1rem;"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="fw-semibold" style="font-size: 0.95rem;">{{ $evt->titre }}</div>
                                <div style="font-size: 0.78rem; color: #888;">
                                    {{ $evt->date_event->isoFormat('D MMM YYYY') }} · {{ $evt->lieu }}
                                </div>
                            </div>
                        </div>
                        <span class="badge-statut" style="background: {{ $statutColor }}20; color: {{ $statutColor }};">
                            {{ ucfirst($evt->statut) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x" style="font-size: 3rem; color: var(--gris); opacity: 0.3;"></i>
                    <p class="text-muted mt-2 mb-0">Aucun événement créé.</p>
                </div>
            @endforelse
        </div>

        {{-- Étape 2: Code d'accès --}}
        <div id="stepCode" style="display: none;">
            <div class="text-center mb-4">
                <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(123,63,160,0.08); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <i class="bi bi-shield-lock" style="font-size: 2rem; color: #7B3FA0;"></i>
                </div>
                <h4 class="fw-bold mb-1" id="codeTitle">Code d'accès</h4>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Entrez le code d'accès pour <strong id="codeEventName">—</strong></p>
            </div>

            <form id="accessCodeForm">
                @csrf
                <input type="hidden" name="evenement_id" id="evenementId">
                <div class="mb-4">
                    <input type="text" id="codeInput" name="code" class="form-control access-input"
                           placeholder="SCAN-XXXXXXXX" autocomplete="off" required autofocus>
                </div>
                <button type="submit" class="btn btn-vert w-100 py-3 fw-semibold" id="btnSubmit" style="border-radius: 12px;">
                    <i class="bi bi-unlock me-2"></i> Accéder au scan
                </button>
                <div class="access-error mt-3 text-center" id="accessError"></div>
            </form>

            <div class="text-center mt-3">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="backToEvents()" style="border-radius: 8px;">
                    <i class="bi bi-arrow-left me-1"></i> Changer d'événement
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let selectedEventId = null;

function selectEvent(el) {
    document.querySelectorAll('.evt-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    selectedEventId = el.dataset.id;
    document.getElementById('evenementId').value = selectedEventId;
    document.getElementById('codeEventName').textContent = el.dataset.titre;
    document.getElementById('stepSelect').style.display = 'none';
    document.getElementById('stepCode').style.display = 'block';
    document.getElementById('codeInput').focus();
}

function backToEvents() {
    document.querySelectorAll('.evt-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('stepSelect').style.display = 'block';
    document.getElementById('stepCode').style.display = 'none';
    document.getElementById('accessError').style.display = 'none';
    selectedEventId = null;
}

document.getElementById('accessCodeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const code = document.getElementById('codeInput').value.trim().toUpperCase();
    const btn = document.getElementById('btnSubmit');
    const error = document.getElementById('accessError');

    if (!code || !selectedEventId) {
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
        body: JSON.stringify({ evenement_id: selectedEventId, code: code }),
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
