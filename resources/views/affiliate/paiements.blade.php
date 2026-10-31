@extends('layouts.affiliate')

@section('title', 'Paiements — Espace affilié PaxEvent')

@section('content')
    <h1 class="h4 fw-bold mb-1">Paiements</h1>
    <p class="text-muted small mb-4">Demandez le reversement de vos commissions disponibles.</p>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="stat-card card h-100">
                <div class="label">Solde disponible</div>
                <div class="value">{{ number_format((float) $affiliate->solde_disponible, 0, ',', ' ') }} <small class="fs-6">FCFA</small></div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="stat-card card h-100">
                <div class="label">En attente</div>
                <div class="value">{{ number_format((float) $affiliate->solde_en_attente, 0, ',', ' ') }} <small class="fs-6">FCFA</small></div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="stat-card card h-100">
                <div class="label">Total gagné</div>
                <div class="value">{{ number_format((float) $affiliate->total_gagne, 0, ',', ' ') }} <small class="fs-6">FCFA</small></div>
            </div>
        </div>
    </div>

    <div class="card aff-card mb-4">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3"><i class="bi bi-cash-stack me-1"></i> Nouvelle demande de retrait</h2>

            @if(!$affiliate->operateur_paiement || !$affiliate->numero_paiement)
                @php
                    $manquant = array_filter([
                        'opérateur de paiement' => !$affiliate->operateur_paiement,
                        'numéro Mobile Money' => !$affiliate->numero_paiement,
                    ]);
                @endphp
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Pour demander un retrait, complétez d'abord vos coordonnées de paiement
                    ({{ implode(', ', array_keys($manquant)) }}) dans votre
                    <a href="{{ route('affiliate.profil') }}" class="alert-link">profil</a>.
                </div>
            @else
                <form method="POST" action="{{ route('affiliate.paiements.demander') }}" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-semibold text-muted mb-1">Montant à retirer (FCFA)</label>
                        <input type="number" name="montant" class="form-control @error('montant') is-invalid @enderror"
                               min="5000" step="100" max="{{ (int) $affiliate->solde_disponible }}"
                               value="{{ old('montant') }}" placeholder="5000">
                        @error('montant')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Minimum 5 000 FCFA · Disponible : {{ number_format((float) $affiliate->solde_disponible, 0, ',', ' ') }} FCFA</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1">Versement</label>
                        <div class="form-control-plaintext small">
                            {{ $affiliate->operateur_paiement }} · {{ $affiliate->numero_paiement }}
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <button type="submit" class="btn btn-violet w-100"><i class="bi bi-send me-1"></i> Demander</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="card aff-card">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3">Historique des demandes</h2>
            @if($payouts->isEmpty())
                @include('affiliate.partials.empty-state', [
                    'icon' => 'bi-wallet2',
                    'title' => 'Aucune demande de retrait',
                    'text' => 'Vos demandes de reversement apparaîtront ici.',
                ])
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="small text-muted">
                                <th>Date</th>
                                <th class="text-end">Montant</th>
                                <th>Versement</th>
                                <th class="text-end">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payouts as $payout)
                                <tr>
                                    <td class="small">{{ $payout->created_at?->format('d/m/Y') }}</td>
                                    <td class="text-end fw-semibold">{{ number_format((float) $payout->montant, 0, ',', ' ') }} FCFA</td>
                                    <td class="small">{{ $payout->operateur }} · {{ $payout->numero_paiement }}</td>
                                    <td class="text-end">
                                        @php
                                            $badge = [
                                                'demande' => 'bg-warning-subtle text-warning-emphasis',
                                                'valide' => 'bg-info-subtle text-info-emphasis',
                                                'paye' => 'bg-success-subtle text-success-emphasis',
                                                'refuse' => 'bg-danger-subtle text-danger-emphasis',
                                            ][$payout->statut] ?? 'bg-secondary-subtle text-secondary-emphasis';
                                            $label = ['demande' => 'En cours', 'valide' => 'Validé', 'paye' => 'Payé', 'refuse' => 'Refusé'][$payout->statut] ?? $payout->statut;
                                        @endphp
                                        <span class="badge {{ $badge }}">{{ $label }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
