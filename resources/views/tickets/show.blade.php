@extends('layouts.app')

@section('title', 'Details du ticket')

@section('page-title', 'Details du ticket')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
    <li class="breadcrumb-item active" aria-current="page">Détail</li>
@endsection

@section('topbar-actions')
<a href="{{ route('tickets.index') }}" class="btn btn-secondary-custom btn-sm">
    <i class="bi bi-arrow-left me-1"></i> Retour
</a>
@endsection

@section('content')
<div class="page-content">
    <div class="row g-4">
        <!-- Ticket Info -->
        <div class="col-lg-8">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-ticket-perforated me-2"></i>Informations du billet</h5>
                    @php
                        $isPaid = $ticket->statut_paiement === 'payé';
                        $isScanned = $ticket->utilise;
                        $isCancelled = in_array($ticket->statut_paiement, ['annulé', 'remboursé']);

                        if ($isCancelled) {
                            $badgeClass = 'bg-danger';
                            $statusLabel = ucfirst($ticket->statut_paiement);
                        } elseif ($isScanned) {
                            $badgeClass = 'status-badge status-termine';
                            $statusLabel = 'Scanne';
                        } elseif ($isPaid) {
                            $badgeClass = 'status-badge status-en-cours';
                            $statusLabel = 'Valide';
                        } else {
                            $badgeClass = 'badge bg-warning text-dark';
                            $statusLabel = 'En attente';
                        }
                    @endphp
                    <span class="{{ $badgeClass }}">{{ $statusLabel }}</span>
                </div>
                <div class="panel-card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Code unique</label>
                            <div class="fw-bold" style="font-size: 1.1rem; color: var(--violet); font-family: monospace;">{{ $ticket->code_unique }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Date d'achat</label>
                            <div class="fw-semibold">{{ $ticket->date_achat?->format('d M Y H:i') ?? '—' }}</div>
                        </div>
                    </div>

                    <hr>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Evenement</label>
                            <div class="fw-bold">{{ $ticket->evenement?->titre ?? '—' }}</div>
                            <small class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i>{{ $ticket->evenement?->date_event?->format('d M Y H:i') ?? '—' }}
                                <br>
                                <i class="bi bi-geo-alt me-1"></i>{{ $ticket->evenement?->lieu ?? '—' }}
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Tarif</label>
                            <div class="fw-bold">{{ ucfirst($ticket->categorie) }} / {{ ucfirst($ticket->type) }}</div>
                            <small class="text-muted">{{ $ticket->tarif?->libelle ?? '—' }}</small>
                        </div>
                    </div>

                    <hr>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Participant</label>
                            <div class="fw-bold">{{ $ticket->nom_acheteur }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Email</label>
                            <div class="fw-semibold">{{ $ticket->email_acheteur }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Telephone</label>
                            <div class="fw-semibold">{{ $ticket->telephone_acheteur }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Telephone paiement</label>
                            <div class="fw-semibold">{{ $ticket->telephone_paiement ?? '—' }}</div>
                        </div>
                    </div>

                    <hr>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Montant</label>
                            <div class="fw-bold" style="font-size: 1.25rem; color: var(--vert);">{{ number_format($ticket->montant, 0, ',', ' ') }} F</div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Methode de paiement</label>
                            <div class="fw-semibold">{{ ucfirst($ticket->methode_paiement ?? '—') }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Transaction ID</label>
                            <div class="fw-semibold" style="font-size: 0.85rem;">{{ $ticket->transaction_id ?? '—' }}</div>
                        </div>
                        @if($ticket->code_promo_utilise)
                            <div class="col-md-4">
                                <label class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Code promo</label>
                                <div class="promo-code">{{ $ticket->code_promo_utilise }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Side Panel -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="panel-card mb-3">
                <div class="panel-card-header">
                    <h5><i class="bi bi-lightning me-2"></i>Actions rapides</h5>
                </div>
                <div class="panel-card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('tickets.pdf', $ticket->id) }}" class="btn btn-violet w-100" style="border-radius: 8px;">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Telecharger le ticket PDF
                        </a>
                        <form action="{{ route('tickets.renvoyer', $ticket->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-vert w-100" style="border-radius: 8px;">
                                <i class="bi bi-send me-1"></i> Renvoyer par email
                            </button>
                        </form>
                        @if(!$isCancelled)
                            <form action="{{ route('tickets.annuler', $ticket->id) }}" method="POST" onsubmit="return confirm('Annuler ce ticket ?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-rouge w-100" style="border-radius: 8px;">
                                    <i class="bi bi-x-octagon me-1"></i> Annuler le ticket
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            @if($ticket->notifications->isNotEmpty())
                <div class="panel-card">
                    <div class="panel-card-header">
                        <h5><i class="bi bi-bell me-2"></i>Notifications</h5>
                        <span class="badge bg-secondary">{{ $ticket->notifications->count() }}</span>
                    </div>
                    <div class="panel-card-body p-0">
                        @foreach($ticket->notifications->take(5) as $notif)
                            <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
                                <i class="bi bi-{{ $notif->canal === 'whatsapp' ? 'whatsapp' : ($notif->canal === 'email' ? 'envelope' : 'chat') }}"
                                   style="color: {{ $notif->statut === 'envoyé' ? 'var(--vert)' : 'var(--danger)' }};"></i>
                                <div class="flex-grow-1" style="font-size: 0.82rem;">
                                    <span class="fw-semibold">{{ ucfirst($notif->canal) }}</span>
                                    <span class="text-muted ms-1">— {{ $notif->statut }}</span>
                                </div>
                                <small class="text-muted">{{ $notif->date_envoi?->format('d/m H:i') ?? '—' }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Logs -->
        <div class="col-12">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-clock-history me-2"></i>Historique / Logs</h5>
                    @if($logs->isNotEmpty())
                        <span class="badge bg-secondary">{{ $logs->count() }} entree{{ $logs->count() > 1 ? 's' : '' }}</span>
                    @endif
                </div>
                <div class="panel-card-body p-0">
                    @if($logs->isNotEmpty())
                        @foreach($logs as $log)
                            <div class="d-flex align-items-start gap-3 px-3 py-3 border-bottom">
                                <div class="mt-1">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px; background: {{ $log->type_operation === 'scan' ? 'rgba(18,151,110,0.08)' : ($log->type_operation === 'remboursement' ? 'rgba(231,76,60,0.08)' : 'rgba(135,66,139,0.08)') }};">
                                        <i class="bi bi-{{ $log->type_operation === 'scan' ? 'qr-code-scan' : ($log->type_operation === 'remboursement' ? 'arrow-return-left' : 'info-circle') }}"
                                           style="color: {{ $log->type_operation === 'scan' ? 'var(--vert)' : ($log->type_operation === 'remboursement' ? 'var(--danger)' : 'var(--violet)') }}; font-size: 0.85rem;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold" style="font-size: 0.85rem;">{{ ucfirst(str_replace('_', ' ', $log->type_operation)) }}</div>
                                    @if($log->details)
                                        <div style="font-size: 0.78rem; color: var(--gris);">
                                            @php
                                                $details = is_string($log->details) ? json_decode($log->details, true) : $log->details;
                                            @endphp
                                            @if(is_array($details))
                                                @foreach($details as $key => $value)
                                                    {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ is_array($value) ? json_encode($value) : $value }}@if(!$loop->last), @endif
                                                @endforeach
                                            @else
                                                {{ $log->details }}
                                            @endif
                                        </div>
                                    @endif
                                    @if($log->ip)
                                        <div style="font-size: 0.72rem; color: var(--gris);">
                                            IP: {{ $log->ip }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-end" style="min-width: 100px;">
                                    <small class="text-muted">{{ $log->created_at?->format('d M Y H:i') ?? '—' }}</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-clock-history d-block mb-2" style="font-size: 2rem; color: var(--gris);"></i>
                            <p class="text-muted mb-0" style="font-size: 0.85rem;">Aucun log pour ce ticket</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
