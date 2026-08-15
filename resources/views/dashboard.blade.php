@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de Bord')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Tableau de bord</li>
@endsection

@section('content')
<div class="page-content">

    @if(Auth::user()->statut === 'incomplet')
    <div class="alert alert-warning d-flex align-items-center gap-3 flex-wrap" style="border:none;background:#fff3e0;border-radius:12px;padding:0.75rem 1rem;">
        <div style="flex:1;">
            <strong style="color:#8b6914;"><i class="bi bi-exclamation-triangle-fill me-1"></i> Profil incomplet</strong>
            <span style="color:#6c5b3a;font-size:0.85rem;"> — Finalisez la création de votre compte pour créer des événements</span>
        </div>
        <a href="{{ route('profil.step2') }}" class="btn btn-sm" style="background:#542680;color:#fff;border-radius:8px;font-weight:600;text-decoration:none;white-space:nowrap;">
            <i class="bi bi-arrow-right-circle me-1"></i> Compléter mon profil
        </a>
    </div>
    @elseif(Auth::user()->statut === 'corrections_demandees')
    <div class="alert alert-warning d-flex align-items-center gap-3 flex-wrap" style="border:none;background:#fff8e1;border-radius:12px;padding:0.75rem 1rem;border-left:4px solid #e0a800;">
        <div style="flex:1;">
            <strong style="color:#8b6914;"><i class="bi bi-pencil-square me-1"></i> Corrections demandées</strong>
            <span style="color:#6c5b3a;font-size:0.85rem;"> — Des modifications sont requises sur votre profil. Corrigez puis soumettez à nouveau.</span>
        </div>
        <a href="{{ route('profil.step2') }}" class="btn btn-sm" style="background:#542680;color:#fff;border-radius:8px;font-weight:600;text-decoration:none;white-space:nowrap;">
            <i class="bi bi-arrow-right-circle me-1"></i> Modifier mon profil
        </a>
    </div>
    @endif

    {{-- Notifications / messages techniques --}}
    @if($notifications->isNotEmpty())
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="panel-card" style="border-left:4px solid #7B3FA0;">
                <div class="panel-card-header">
                    <h5>
                        <i class="bi bi-bell me-1" style="color:#7B3FA0;"></i> Notifications
                        @if($notificationsNonLues > 0)
                            <span class="badge bg-danger" style="font-size:0.65rem;vertical-align:middle;">{{ $notificationsNonLues }} non lu{{ $notificationsNonLues > 1 ? 's' : '' }}</span>
                        @endif
                    </h5>
                    <a href="{{ route('admin.messages.index') }}">Tout voir</a>
                </div>
                <div class="panel-card-body" style="padding:0;">
                    @foreach($notifications as $notification)
                    <div class="d-flex align-items-center px-3 py-2 {{ $notification->lu ? '' : 'bg-light' }}" style="{{ $loop->last ? '' : 'border-bottom:1px solid #f5f5f5;' }}">
                        <div style="width:32px;height:32px;background:{{ $notification->lu ? 'rgba(152,145,155,0.12)' : 'rgba(135,66,139,0.12)' }};border-radius:8px;display:flex;align-items:center;justify-content:center;margin-right:0.75rem;flex-shrink:0;">
                            <i class="bi bi-info-circle" style="color:{{ $notification->lu ? '#98919b' : '#7B3FA0' }};font-size:0.85rem;"></i>
                        </div>
                        <a href="{{ route('admin.messages.show', $notification->id) }}" style="flex:1;min-width:0;text-decoration:none;color:inherit;">
                            <div style="font-size:0.82rem;font-weight:600;color:var(--sombre);">
                                {{ $notification->objet }}
                                @if(!$notification->lu)<span class="badge bg-primary" style="font-size:0.6rem;">Nouveau</span>@endif
                            </div>
                            <div style="font-size:0.7rem;color:var(--gris);">
                                {{ \Illuminate\Support\Str::limit(strip_tags($notification->message), 90) }}
                            </div>
                        </a>
                        <span style="font-size:0.65rem;color:var(--gris);white-space:nowrap;">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Actions rapides --}}
    <div class="row g-2 mb-4 align-items-center">
        <div class="col-md-6">
            <span style="font-size:0.82rem;color:var(--gris);font-weight:500;">
                <i class="bi bi-calendar3 me-1"></i> {{ now()->isoFormat('D MMM YYYY') }}
            </span>
        </div>
        <div class="col-md-6 d-flex justify-content-md-end gap-2 flex-wrap">
            <button type="button" class="btn btn-sm" style="background:#7B3FA0;color:#fff;border-radius:8px;font-weight:600;font-size:0.78rem;" data-bs-toggle="modal" data-bs-target="#demandeSuperadminModal">
                <i class="bi bi-headset me-1"></i> Contacter PaxEvent
            </button>
            <a href="{{ route('admin.evenements.create') }}" class="btn btn-sm" style="background:var(--vert);color:#fff;border-radius:8px;font-weight:600;font-size:0.78rem;">
                <i class="bi bi-plus-lg me-1"></i> Nouvel événement
            </a>
            <a href="{{ route('admin.evenements.index') }}" class="btn btn-sm" style="border:1px solid #e0dde3;border-radius:8px;font-weight:600;font-size:0.78rem;">
                <i class="bi bi-calendar-week me-1"></i> Mes événements
            </a>
            <a href="{{ route('scan.index') }}" class="btn btn-sm" style="border:1px solid #e0dde3;border-radius:8px;font-weight:600;font-size:0.78rem;">
                <i class="bi bi-qr-code-scan me-1"></i> Scanner
            </a>
        </div>
    </div>

    {{-- Metrics --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.1);">⛺</div>
                <div class="metric-label">Événements</div>
                <div class="metric-value">{{ $totalEvenements }}</div>
                <div class="metric-subtitle">{{ $evenementsActifs }} actifs</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);">🎫</div>
                <div class="metric-label">Tickets vendus</div>
                <div class="metric-value">{{ number_format($ticketsVendus, 0, ',', ' ') }}</div>
                <div class="metric-subtitle">
                    @php
                        $thisMonth = App\Models\Ticket::whereHas('evenement', fn($q) => $q->where('user_id', auth()->id()))
                            ->where('statut_paiement', 'payé')
                            ->where('date_achat', '>=', now()->startOfMonth())
                            ->count();
                        $lastMonth = App\Models\Ticket::whereHas('evenement', fn($q) => $q->where('user_id', auth()->id()))
                            ->where('statut_paiement', 'payé')
                            ->whereBetween('date_achat', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
                            ->count();
                        $pct = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100) : 0;
                    @endphp
                    @if($pct > 0)
                        <span style="color:var(--vert);">+{{ $pct }}%</span> vs mois dernier
                    @elseif($pct < 0)
                        <span style="color:var(--danger);">{{ $pct }}%</span> vs mois dernier
                    @else
                        vs mois dernier
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--teal);">
                <div class="metric-icon" style="background: rgba(66,140,121,0.1);"><i class="bi bi-cash-coin" style="color:var(--teal);"></i></div>
                <div class="metric-label">Revenu brut</div>
                <div class="metric-value" style="font-size:1.3rem;">{{ number_format($recettesTotales, 0, ',', ' ') }}</div>
                <div class="metric-subtitle">FCFA · Commission {{ $commissionPct }}%</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);"><i class="bi bi-wallet2" style="color:var(--vert);"></i></div>
                <div class="metric-label">Net retirable (Mobile)</div>
                <div class="metric-value" style="font-size:1.3rem;color:var(--vert);">{{ number_format($retirable, 0, ',', ' ') }}</div>
                <div class="metric-subtitle">FCFA après commission et retraits</div>
            </div>
        </div>
    </div>

    {{-- Répartition Mobile / Espèces --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: #3498db;">
                <div class="metric-icon" style="background: rgba(52,152,219,0.1);"><i class="bi bi-phone" style="color:#3498db;"></i></div>
                <div class="metric-label">Paiements Mobile</div>
                <div class="metric-value" style="font-size:1.2rem;color:#3498db;">{{ number_format($mobileRecettes, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Retirable via FedaPay</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: #f39c12;">
                <div class="metric-icon" style="background: rgba(243,156,18,0.1);"><i class="bi bi-cash" style="color:#f39c12;"></i></div>
                <div class="metric-label">Paiements Espèces</div>
                <div class="metric-value" style="font-size:1.2rem;color:#f39c12;">{{ number_format($cashRecettes, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Déjà en votre possession</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.1);"><i class="bi bi-percent" style="color:var(--violet);"></i></div>
                <div class="metric-label">Commission ({{ $commissionPct }}%)</div>
                <div class="metric-value" style="font-size:1.2rem;color:var(--danger);">{{ number_format($commission, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Sur tous les tickets</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);"><i class="bi bi-cash-stack" style="color:var(--vert);"></i></div>
                <div class="metric-label">Net total</div>
                <div class="metric-value" style="font-size:1.2rem;color:var(--vert);">{{ number_format($recettesNettes, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Brut − Commission</div>
            </div>
        </div>
    </div>

    {{-- Répartition par opérateur mobile --}}
    @if($mobileRecettes > 0)
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-phone me-1" style="color:#3498db;"></i> Répartition par opérateur mobile</h5>
                </div>
                <div class="panel-card-body">
                    <div class="row g-2">
                        @php $totalMobileTickets = 0; @endphp
                        @foreach($reseauxConfig as $key => $cfg)
                            @php
                                $data = $reseauxPaiement->get($key);
                                $count = $data ? (int) $data->total : 0;
                                $montant = $data ? (int) $data->montant : 0;
                                $totalMobileTickets += $count;
                            @endphp
                            <div class="col-md-3">
                                <div class="d-flex align-items-center p-3 rounded-3" style="background:#f8f6f9;">
                                    <div class="me-3">
                                        <div style="width:40px;height:40px;background:rgba(52,152,219,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                            <i class="bi bi-phone" style="color:#3498db;"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div style="font-size:0.82rem;font-weight:600;color:var(--sombre);">{{ $cfg['label'] }}</div>
                                        <div style="font-size:0.78rem;color:var(--gris);">
                                            {{ $count }} ticket{{ $count > 1 ? 's' : '' }} · {{ number_format($montant, 0, ',', ' ') }} FCFA
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Répartition par moyen de paiement --}}
    @if($ticketsVendus > 0)
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-wallet2 me-1" style="color:#7B3FA0;"></i> Répartition par moyen de paiement</h5>
                </div>
                <div class="panel-card-body">
                    <div class="row g-2">
                        @php
                            $moyensConfig = [
                                'mobile_money' => ['label' => 'Mobile Money', 'icon' => 'bi-phone', 'color' => '#3498db'],
                                'bancaire' => ['label' => 'Carte bancaire', 'icon' => 'bi-credit-card', 'color' => '#7B3FA0'],
                                'especes' => ['label' => 'Espèces', 'icon' => 'bi-cash', 'color' => '#2e9e4f'],
                            ];
                            $totalMoyens = $moyensPaiement->sum('total');
                        @endphp
                        @foreach($moyensConfig as $mKey => $mCfg)
                            @php
                                $mData = $moyensPaiement->get($mKey);
                                $mCount = $mData ? (int) $mData->total : 0;
                                $mMontant = $mData ? (int) $mData->montant : 0;
                                $mPct = $totalMoyens > 0 ? round(($mCount / $totalMoyens) * 100) : 0;
                            @endphp
                            <div class="col-md-4">
                                <div class="d-flex align-items-center p-3 rounded-3" style="background:#f8f6f9;">
                                    <div class="me-3">
                                        <div style="width:40px;height:40px;background:{{ $mCfg['color'] }}22;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                            <i class="bi {{ $mCfg['icon'] }}" style="color:{{ $mCfg['color'] }};"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div style="font-size:0.82rem;font-weight:600;color:var(--sombre);">{{ $mCfg['label'] }}</div>
                                        <div style="font-size:0.78rem;color:var(--gris);">
                                            {{ $mCount }} ticket{{ $mCount > 1 ? 's' : '' }} · {{ number_format($mMontant, 0, ',', ' ') }} FCFA · {{ $mPct }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Graphique + Événements récents --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-graph-up me-1" style="color:var(--vert);"></i> Ventes (7 derniers jours)</h5>
                </div>
                <div class="panel-card-body">
                    <canvas id="ventesChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel-card" style="height:100%;">
                <div class="panel-card-header">
                    <h5><i class="bi bi-calendar-event me-1" style="color:var(--violet);"></i> Événements récents</h5>
                    <a href="{{ route('admin.evenements.index') }}">Voir tout</a>
                </div>
                <div class="panel-card-body" style="padding:0;">
                    @forelse($evenementsRecents as $evenement)
                        @php
                            $now = now();
                            $isPast = $evenement->date_event < $now;
                            $isToday = $evenement->date_event->isToday();

                            if ($evenement->statut === 'terminé' || $evenement->statut === 'annulé') {
                                $statusLabel = 'Terminé';
                                $dotColor = '#98919b';
                            } elseif ($isPast && $evenement->statut === 'publié') {
                                $statusLabel = 'Terminé';
                                $dotColor = '#98919b';
                            } elseif ($isToday || $evenement->statut === 'publié') {
                                $statusLabel = $isToday ? 'En cours' : 'À venir';
                                $dotColor = $isToday ? '#12976e' : '#87428b';
                            } else {
                                $statusLabel = ucfirst($evenement->statut);
                                $dotColor = '#98919b';
                            }
                        @endphp
                        <div class="d-flex align-items-center px-3 py-2" style="border-bottom:1px solid #f5f5f5;">
                            <div style="width:8px;height:8px;border-radius:50%;background:{{ $dotColor }};margin-right:0.75rem;flex-shrink:0;"></div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:0.82rem;font-weight:600;color:var(--sombre);">{{ $evenement->titre }}</div>
                                <div style="font-size:0.7rem;color:var(--gris);">
                                    {{ $evenement->date_event->isoFormat('D MMM YYYY') }} — {{ $evenement->quota_vendu }} tickets
                                </div>
                            </div>
                            <span style="font-size:0.65rem;font-weight:600;color:{{ $dotColor }};white-space:nowrap;">{{ $statusLabel }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4" style="color:var(--gris);">
                            <i class="bi bi-calendar-x d-block mb-2" style="font-size:1.5rem;"></i>
                            <small>Aucun événement</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Derniers tickets vendus --}}
    <div class="row g-3">
        <div class="col-12">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-ticket-perforated me-1" style="color:var(--teal);"></i> Derniers tickets vendus</h5>
                    <a href="{{ route('tickets.index') }}">Voir tout</a>
                </div>
                <div class="panel-card-body" style="padding:0;">
                    @php
                        $recentTickets = App\Models\Ticket::whereHas('evenement', fn($q) => $q->where('user_id', auth()->id()))
                            ->where('statut_paiement', 'payé')
                            ->latest('date_achat')
                            ->take(5)
                            ->get();
                    @endphp

                    @if($recentTickets->isNotEmpty())
                        @foreach($recentTickets as $ticket)
                            <div class="d-flex align-items-center px-3 py-2" style="border-bottom:1px solid #f5f5f5;">
                                <div style="width:28px;height:28px;background:rgba(18,151,110,0.1);border-radius:6px;display:flex;align-items:center;justify-content:center;margin-right:0.75rem;flex-shrink:0;">
                                    <i class="bi bi-ticket" style="color:var(--vert);font-size:0.75rem;"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:0.82rem;font-weight:600;color:var(--sombre);">
                                        {{ $ticket->nom_acheteur ?? 'Anonyme' }}
                                    </div>
                                    <div style="font-size:0.68rem;color:var(--gris);">
                                        {{ $ticket->evenement->titre ?? '—' }} · {{ $ticket->date_achat?->format('d/m H:i') }}
                                    </div>
                                </div>
                                <div style="font-size:0.85rem;font-weight:700;color:var(--vert);white-space:nowrap;">
                                    {{ number_format($ticket->montant, 0, ',', ' ') }} F
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4" style="color:var(--gris);">
                            <i class="bi bi-cart-x d-block mb-2" style="font-size:1.5rem;"></i>
                            <small>Aucune vente récente</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal : demander au super admin --}}
    <div class="modal fade" id="demandeSuperadminModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:14px;border:none;">
                <form action="{{ route('demande-superadmin.store') }}" method="POST" id="demandeSuperadminForm">
                    @csrf
                    <div class="modal-header" style="border-bottom:1px solid #f0eef2;padding:1rem 1.25rem;">
                        <h5 class="modal-title" style="font-size:1rem;font-weight:700;color:var(--sombre);">
                            <i class="bi bi-headset me-1" style="color:#7B3FA0;"></i> Contacter PaxEvent
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="padding:1.25rem;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Motif de la demande</label>
                            <select name="objet" id="demande_objet" class="form-select form-select-sm" required>
                                <option value="">-- Choisir un motif --</option>
                                <option value="ticket_physique">Ticket physique (QR Code)</option>
                                <option value="reduction_commission">Réduction Commission</option>
                                <option value="augmentation_agents">Augmentation des agents</option>
                                <option value="evenement_a_la_une">Événement à la une</option>
                                <option value="probleme_technique">Problème technique</option>
                            </select>
                        </div>

                        <div class="mb-3" id="demande_evenement_group" style="display:none;">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Événement concerné</label>
                            <select name="evenement_id" id="demande_evenement" class="form-select form-select-sm">
                                <option value="">-- Choisir un événement --</option>
                                @foreach($evenementsPourDemande as $evt)
                                    <option value="{{ $evt->id }}" data-tarifs='@json($evt->tarifs->map(fn($t) => ["id" => $t->id, "nom" => $t->nom]))'>{{ $evt->titre }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Quantités par tarif (ticket physique) --}}
                        <div class="mb-3" id="demande_quantites_group" style="display:none;">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Quantités par tarif</label>
                            <div id="demande_quantites" class="vstack gap-2"></div>
                        </div>

                        {{-- Pourcentage de commission (réduction commission) --}}
                        <div class="mb-3" id="demande_commission_group" style="display:none;">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Pourcentage demandé (%)</label>
                            <input type="number" name="commission_pourcentage" id="demande_commission" class="form-control form-control-sm" min="0" max="100" step="0.01" placeholder="Ex : 5">
                        </div>

                        <div class="mb-1">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Message</label>
                            <textarea name="message" id="demande_message" class="form-control form-control-sm" rows="3" maxlength="2000" placeholder="Décrivez votre demande..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top:1px solid #f0eef2;padding:0.75rem 1.25rem;">
                        <button type="button" class="btn btn-sm" data-bs-dismiss="modal" style="border:1px solid #e0dde3;border-radius:8px;">Annuler</button>
                        <button type="submit" class="btn btn-sm" style="background:#7B3FA0;color:#fff;border-radius:8px;font-weight:600;">
                            <i class="bi bi-send me-1"></i> Envoyer la demande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    const ctx = document.getElementById('ventesChart').getContext('2d');

    const data = @json($ventes7Jours);
    const labels = @json($joursLabels);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: labels.map((_, i) => i === 6 ? '#12976e' : '#b2e0d6'),
                borderRadius: 4,
                borderSkipped: false,
                barPercentage: 0.6,
                categoryPercentage: 0.7,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#3d4345',
                    padding: 10,
                    cornerRadius: 6,
                    callbacks: {
                        label: function(ctx) {
                            return ctx.parsed.y + ' tickets';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                    ticks: { color: '#98919b', font: { size: 10 }, stepSize: 1 },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#98919b', font: { size: 10 } },
                    border: { display: false }
                }
            }
        }
    });

    // ---- Modal « Contacter PaxEvent » : champs conditionnels ----
    const demandeObjet = document.getElementById('demande_objet');
    const demandeEvenementGroup = document.getElementById('demande_evenement_group');
    const demandeEvenement = document.getElementById('demande_evenement');
    const demandeQuantitesGroup = document.getElementById('demande_quantites_group');
    const demandeQuantites = document.getElementById('demande_quantites');
    const demandeCommissionGroup = document.getElementById('demande_commission_group');

    const OBJET_EVENEMENT = ['ticket_physique', 'reduction_commission', 'augmentation_agents', 'evenement_a_la_une'];

    function resetDemande() {
        demandeEvenementGroup.style.display = 'none';
        demandeQuantitesGroup.style.display = 'none';
        demandeCommissionGroup.style.display = 'none';
        demandeEvenement.value = '';
        demandeQuantites.innerHTML = '';
    }

    demandeObjet.addEventListener('change', function () {
        resetDemande();
        const objet = this.value;
        if (!objet) return;

        if (OBJET_EVENEMENT.includes(objet)) {
            demandeEvenementGroup.style.display = '';
            demandeEvenement.required = true;
        } else {
            demandeEvenement.required = false;
        }

        if (objet === 'reduction_commission') {
            demandeCommissionGroup.style.display = '';
        }
    });

    demandeEvenement.addEventListener('change', function () {
        demandeQuantites.innerHTML = '';
        const objet = demandeObjet.value;
        if (objet !== 'ticket_physique') return;

        const option = this.selectedOptions && this.selectedOptions[0];
        if (!option) return;
        let tarifs = [];
        try { tarifs = JSON.parse(option.dataset.tarifs || '[]'); } catch (e) {}

        if (!tarifs.length) {
            demandeQuantitesGroup.style.display = 'none';
            return;
        }
        demandeQuantitesGroup.style.display = '';

        tarifs.forEach(t => {
            const div = document.createElement('div');
            div.className = 'd-flex align-items-center justify-content-between gap-2';
            div.innerHTML = '<span style="font-size:0.8rem;color:#444;flex:1;">' + t.nom + '</span>' +
                '<input type="number" class="form-control form-control-sm" style="width:110px;" ' +
                'name="quantites[' + t.id + ']" min="0" max="5000" step="1" placeholder="Qté">';
            demandeQuantites.appendChild(div);
        });
    });

    document.getElementById('demandeSuperadminModal').addEventListener('hidden.bs.modal', function () {
        demandeObjet.value = '';
        resetDemande();
        const msg = document.getElementById('demande_message');
        msg.value = '';
        msg.required = true;
    });
</script>
@endsection
