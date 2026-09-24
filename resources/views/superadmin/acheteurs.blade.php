@extends('superadmin.layouts.master')

@section('title', 'Acheteurs - Super Admin')
@section('page-title', 'Acheteurs')

@section('content')
@if (session('success'))
<div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="alert alert-danger py-2 small">{{ session('error') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
    <form method="GET" action="{{ route('superadmin.acheteurs') }}" class="d-inline-flex align-items-center gap-2 flex-wrap">
        <div class="position-relative">
            <i class="bi bi-search position-absolute" style="left:10px; top:50%; transform:translateY(-50%); color:#888; font-size:0.8rem; pointer-events:none;"></i>
            <input type="text" name="q" class="form-control form-control-sm ps-4" style="border-radius:10px; min-width:280px;" placeholder="Nom, email, WhatsApp, tel, référence" value="{{ request('q') }}" autocomplete="off">
        </div>
        <select name="evenement_id" class="form-select form-select-sm" style="border-radius:10px; width:auto; max-width:220px;">
            <option value="">Tous les événements</option>
            @foreach($evenements as $ev)
            <option value="{{ $ev->id }}" {{ (string) request('evenement_id') === (string) $ev->id ? 'selected' : '' }}>{{ $ev->titre }}</option>
            @endforeach
        </select>
        <select name="statut" class="form-select form-select-sm" style="border-radius:10px; width:auto;">
            <option value="">Tous les statuts</option>
            <option value="payé" {{ request('statut') === 'payé' ? 'selected' : '' }}>Payé</option>
            <option value="en_attente" {{ request('statut') === 'en_attente' ? 'selected' : '' }}>En attente</option>
            <option value="échoué" {{ request('statut') === 'échoué' ? 'selected' : '' }}>Échoué</option>
            <option value="remboursé" {{ request('statut') === 'remboursé' ? 'selected' : '' }}>Remboursé</option>
        </select>
        <input type="date" name="debut" class="form-control form-control-sm" style="border-radius:10px; width:auto;" value="{{ request('debut') }}">
        <span class="text-muted">→</span>
        <input type="date" name="fin" class="form-control form-control-sm" style="border-radius:10px; width:auto;" value="{{ request('fin') }}">
        <button type="submit" class="sa-btn sa-btn-sm sa-btn-primary">Filtrer</button>
        @if(request('q') || request('evenement_id') || request('debut') || request('fin') || request('statut'))
        <a href="{{ route('superadmin.acheteurs') }}" class="sa-btn sa-btn-sm" style="background:#e74c3c;border:none;color:#fff;border-radius:6px;text-decoration:none;">Réinitialiser</a>
        @endif
    </form>
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted" style="font-size:0.8rem;">{{ $acheteurs->total() }} achat(s)</span>
        <a href="{{ route('superadmin.acheteurs.export', request()->query()) }}" class="sa-btn sa-btn-sm" style="background:#27ae60;border:none;color:#fff;border-radius:6px;text-decoration:none;">
            <i class="bi bi-file-earmark-arrow-down me-1"></i>Export CSV
        </a>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-person-lines-fill me-2" style="color: var(--sa-primary);"></i>Contacts des acheteurs</span>
        <span class="text-muted ms-auto" style="font-size:0.8rem;">
            <i class="bi bi-info-circle me-1"></i>Nom, email et WhatsApp capturés à chaque achat (table ticket).
        </span>
    </div>
    <div class="sa-card-body p-0">
        @if($acheteurs->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-person-lines-fill" style="font-size:2.5rem;"></i>
            <p class="mt-2 mb-0">Aucun acheteur pour le moment.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Acheteur</th>
                        <th>WhatsApp</th>
                        <th>Téléphone</th>
                        <th>Événement</th>
                        <th>Date achat</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Référence</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($acheteurs as $t)
                    @php $wa = preg_replace('/[^0-9]/', '', (string) ($t->whatsapp_acheteur ?? '')); @endphp
                    <tr>
                        <td>
                            <strong>{{ $t->nom_acheteur ?? '—' }}</strong>
                            @if($t->email_acheteur)<div class="text-muted" style="font-size:0.75rem;">{{ $t->email_acheteur }}</div>@endif
                        </td>
                        <td>
                            @if($wa)
                            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" style="text-decoration:none; color:#25D366;">
                                <i class="bi bi-whatsapp me-1"></i>{{ $t->whatsapp_acheteur }}
                            </a>
                            @else <span class="text-muted">—</span> @endif
                        </td>
                        <td style="font-size:0.8rem;">{{ $t->telephone_paiement ?? $t->telephone_acheteur ?? '—' }}</td>
                        <td>{{ $t->evenement->titre ?? '-' }}</td>
                        <td class="text-nowrap" style="font-size:0.8rem;">{{ $t->date_achat ? \Carbon\Carbon::parse($t->date_achat)->isoFormat('D MMM YYYY HH:mm') : '-' }}</td>
                        <td><strong>@if($t->montant > 0){{ number_format($t->montant, 0, ',', ' ') }} F @else Gratuit @endif</strong></td>
                        <td>
                            @if($t->statut_paiement === 'payé') <span class="sa-badge sa-badge-success">Payé</span>
                            @elseif($t->statut_paiement === 'échoué') <span class="sa-badge sa-badge-danger">Échoué</span>
                            @elseif($t->statut_paiement === 'remboursé') <span class="sa-badge sa-badge-warning">Remboursé</span>
                            @else <span class="sa-badge sa-badge-secondary">{{ $t->statut_paiement }}</span>
                            @endif
                        </td>
                        <td style="font-family:monospace;font-size:0.75rem;">{{ $t->code_unique }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">{{ $acheteurs->links() }}</div>
@endsection