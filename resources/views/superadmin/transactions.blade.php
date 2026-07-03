@extends('superadmin.layouts.master')

@section('title', 'Transactions - Super Admin')
@section('page-title', 'Transactions')

@section('content')
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-cash-stack me-2" style="color: var(--sa-primary);"></i>Transactions FedaPay</span>
        <span class="text-muted" style="font-size:0.8rem;">{{ $transactions->total() }} total</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Transaction ID</th><th>Evenement</th><th>Montant</th><th>Statut</th><th>Methode</th><th>Acheteur</th><th>Date</th></tr>
            </thead>
            <tbody>
                @foreach($transactions as $t)
                <tr>
                    <td style="font-family:monospace;font-size:0.75rem;">{{ $t->transaction_id }}</td>
                    <td>{{ $t->evenement->titre ?? '-' }}</td>
                    <td><strong>{{ number_format($t->montant, 0, ',', ' ') }} F</strong></td>
                    <td>
                        @if($t->statut_paiement === 'payé') <span class="sa-badge sa-badge-success">Reussi</span>
                        @elseif($t->statut_paiement === 'échoué') <span class="sa-badge sa-badge-danger">Echoue</span>
                        @elseif($t->statut_paiement === 'remboursé') <span class="sa-badge sa-badge-warning">Rembourse</span>
                        @else <span class="sa-badge sa-badge-secondary">{{ $t->statut_paiement }}</span>
                        @endif
                    </td>
                    <td>{{ $t->methode_paiement ?? '-' }}</td>
                    <td>{{ $t->email_acheteur }}</td>
                    <td style="font-size:0.75rem;">{{ $t->created_at->isoFormat('D MMM YYYY HH:mm') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $transactions->links() }}</div>
@endsection
