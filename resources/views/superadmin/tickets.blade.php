@extends('superadmin.layouts.master')

@section('title', 'Tickets - Super Admin')
@section('page-title', 'Tickets')

@section('content')
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-ticket-perforated-fill me-2" style="color: var(--sa-primary);"></i>Tous les tickets</span>
        <span class="text-muted" style="font-size:0.8rem;">{{ $allTickets->total() }} total</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Code unique</th><th>Evenement</th><th>Acheteur</th><th>Montant</th><th>Statut</th><th>Utilise</th><th>Date achat</th></tr>
            </thead>
            <tbody>
                @foreach($allTickets as $t)
                <tr>
                    <td style="font-family:monospace;font-size:0.75rem;">{{ $t->code_unique }}</td>
                    <td>{{ $t->evenement->titre ?? '-' }}</td>
                    <td>{{ $t->email_acheteur }}<br><small class="text-muted">{{ $t->nom_acheteur ?? '-' }}</small></td>
                    <td><strong>{{ number_format($t->montant, 0, ',', ' ') }} F</strong></td>
                    <td>
                        @if($t->statut_paiement === 'payé') <span class="sa-badge sa-badge-success">Paye</span>
                        @elseif($t->statut_paiement === 'échoué') <span class="sa-badge sa-badge-danger">Echoue</span>
                        @elseif($t->statut_paiement === 'remboursé') <span class="sa-badge sa-badge-warning">Rembourse</span>
                        @else <span class="sa-badge sa-badge-secondary">{{ $t->statut_paiement }}</span>
                        @endif
                    </td>
                    <td>
                        @if($t->utilise) <span class="sa-badge sa-badge-success">Oui</span>
                        @else <span class="sa-badge sa-badge-secondary">Non</span>
                        @endif
                    </td>
                    <td style="font-size:0.75rem;">{{ $t->date_achat ? \Carbon\Carbon::parse($t->date_achat)->format('d M Y H:i') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $allTickets->links() }}</div>
@endsection
