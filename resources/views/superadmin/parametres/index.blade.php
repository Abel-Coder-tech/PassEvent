@extends('superadmin.layouts.master')

@section('title', 'Paramètres - Super Admin PaxEvent')
@section('page-title', 'Paramètres')

@section('content')
@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-qr-code me-2" style="color: var(--sa-primary);"></i>Commission des tickets physiques</span>
            </div>
            <div class="sa-card-body">
                <p class="text-muted mb-3" style="font-size:0.9rem;">
                    Taux global par défaut pour les commandes de tickets physiques. Un taux spécifique peut être défini dans la fiche de chaque organisateur.
                </p>

                <form action="{{ route('superadmin.parametres.commission-physique.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Taux de commission (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" name="commission_physique" class="sa-form-control @error('commission_physique') is-invalid @enderror" value="{{ old('commission_physique', $commissionPhysique) }}" required>
                            <span class="input-group-text">%</span>
                        </div>
                        @error('commission_physique')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="sa-btn sa-btn-primary">
                        <i class="bi bi-save me-1"></i> Enregistrer
                    </button>
                    <a href="{{ route('superadmin.parametres') }}" class="sa-btn sa-btn-outline ms-2">Retour aux paramètres</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
