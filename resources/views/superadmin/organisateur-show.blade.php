@extends('superadmin.layouts.master')

@section('title', $user->nom . ' — Organisateur')
@section('page-title', $user->nom)

@section('content')
<div class="mb-3">
    <a href="{{ route('superadmin.organisateurs') }}" class="text-decoration-none small" style="color: var(--sa-primary);">
        <i class="bi bi-arrow-left"></i> Tous les organisateurs
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="sa-card h-100">
            <div class="sa-card-body text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                    style="width: 64px; height: 64px; background: rgba(107,63,160,0.1);">
                    <i class="bi bi-person" style="font-size: 2rem; color: var(--sa-primary);"></i>
                </div>
                <h5 class="fw-bold">{{ $user->nom }}</h5>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                @php
                    $badgeMap = ['actif' => 'success', 'bloque' => 'danger', 'rejete' => 'danger', 'en_attente' => 'warning', 'corrections_demandees' => 'warning', 'incomplet' => 'secondary'];
                    $labelMap = ['actif' => 'Actif', 'bloque' => 'Bloqué', 'rejete' => 'Rejeté', 'en_attente' => 'En attente', 'corrections_demandees' => 'Corrections demandées', 'incomplet' => 'Incomplet'];
                @endphp
                <span class="sa-badge sa-badge-{{ $badgeMap[$user->statut] ?? 'secondary' }} mb-2">
                    {{ $labelMap[$user->statut] ?? ucfirst($user->statut) }}
                </span>
                @if($user->organisation)
                    <p class="small mb-0 mt-2"><i class="bi bi-building"></i> {{ $user->organisation }}</p>
                @endif
                @if($user->telephone)
                    <p class="small mb-0"><i class="bi bi-telephone"></i> {{ $user->telephone }}</p>
                @endif
                @if($user->type)
                    <p class="small mb-0 mt-1"><i class="bi bi-tag"></i> {{ ucfirst($user->type) }}</p>
                @endif
                @if($user->document_justificatif)
                    <div class="mt-3 pt-2 border-top">
                        <a href="{{ asset('storage/' . $user->document_justificatif) }}" target="_blank" class="btn btn-sm text-white fw-semibold" style="background:var(--sa-primary);border-radius:6px;text-decoration:none;">
                            <i class="bi bi-eye me-1"></i> Justificatif
                        </a>
                        <a href="{{ asset('storage/' . $user->document_justificatif) }}" download class="btn btn-sm" style="border:1px solid #ddd;border-radius:6px;text-decoration:none;color:#666;">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                @endif
                @if($user->signature)
                    <div class="mt-2">
                        <a href="{{ asset('storage/' . $user->signature) }}" target="_blank" class="btn btn-sm text-white fw-semibold" style="background:var(--sa-primary);border-radius:6px;text-decoration:none;">
                            <i class="bi bi-pen me-1"></i> Signature
                        </a>
                        <a href="{{ asset('storage/' . $user->signature) }}" download class="btn btn-sm" style="border:1px solid #ddd;border-radius:6px;text-decoration:none;color:#666;">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="row g-2">
            <div class="col-3">
                <div class="sa-card text-center py-3">
                    <div class="fw-bold fs-4" style="color: var(--sa-primary);">{{ $evenements->count() }}</div>
                    <small class="text-muted">Événements</small>
                </div>
            </div>
            <div class="col-3">
                <div class="sa-card text-center py-3">
                    <div class="fw-bold fs-4" style="color: var(--sa-success);">{{ number_format($totalTickets, 0, ',', ' ') }}</div>
                    <small class="text-muted">Tickets vendus</small>
                </div>
            </div>
            <div class="col-3">
                <div class="sa-card text-center py-3">
                    <div class="fw-bold fs-4" style="color: #3498db;">{{ number_format($totalRecettes, 0, ',', ' ') }} F</div>
                    <small class="text-muted">Revenus totaux</small>
                </div>
            </div>
            <div class="col-3">
                <div class="sa-card text-center py-3">
                    <div class="fw-bold fs-4" style="color: #f39c12;">{{ $aujourdhui }}</div>
                    <small class="text-muted">Aujourd'hui</small>
                </div>
            </div>
        </div>

        <div class="row g-2 mt-2">
            <div class="col-4">
                <div class="sa-card text-center py-2">
                    <div class="fw-bold" style="color: var(--sa-primary);">{{ $agentsScan }}</div>
                    <small class="text-muted">Agents scan</small>
                </div>
            </div>
            <div class="col-4">
                <div class="sa-card text-center py-2">
                    <div class="fw-bold" style="color: var(--sa-success);">{{ $agentsVente }}</div>
                    <small class="text-muted">Agents vente</small>
                </div>
            </div>
            <div class="col-4">
                <div class="sa-card text-center py-2">
                    <div class="fw-bold" style="color: #3498db;">{{ $scansAujourdhui }}</div>
                    <small class="text-muted">Scans aujourd'hui</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">
        <div class="col-3">
            <div class="sa-card text-center py-2">
                <div class="fw-bold" style="color:#3498db;">{{ number_format($mobileRecettes, 0, ',', ' ') }} F</div>
                <small class="text-muted">Mobile (FedaPay)</small>
            </div>
        </div>
        <div class="col-3">
            <div class="sa-card text-center py-2">
                <div class="fw-bold" style="color:#f39c12;">{{ number_format($cashRecettes, 0, ',', ' ') }} F</div>
                <small class="text-muted">Espèces</small>
            </div>
        </div>
        <div class="col-3">
            <div class="sa-card text-center py-2">
                <div class="fw-bold" style="color:var(--sa-danger);">{{ number_format($commission, 0, ',', ' ') }} F</div>
                <small class="text-muted">Commission ({{ $commissionPct }}%)</small>
            </div>
        </div>
        <div class="col-3">
            <div class="sa-card text-center py-2">
                <div class="fw-bold" style="color:var(--sa-success);">{{ number_format($retirable, 0, ',', ' ') }} F</div>
                <small class="text-muted">Retirable</small>
            </div>
        </div>
    </div>

    <div class="sa-card mb-4">
        <div class="sa-card-header">
            <span><i class="bi bi-sliders me-2" style="color: var(--sa-primary);"></i>Contrôles organisateur</span>
            <span class="text-muted" style="font-size:0.8rem;">S'applique à tous ses événements, sauf surchargés</span>
        </div>
        <div class="sa-card-body">
            <form action="{{ route('superadmin.organisateurs.controles', $user) }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold mb-1">Ventes espèces</label>
                        <select name="ventes_especes" class="sa-form-control">
                            <option value="">Auto (règle 15 %)</option>
                            <option value="toujours" @selected($user->ventes_especes === 'toujours')>Toujours autorisées</option>
                            <option value="jamais" @selected($user->ventes_especes === 'jamais')>Jamais (bloquées)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold mb-1">Commission (%)</label>
                        <input type="number" name="commission_pourcentage" class="sa-form-control" min="0" max="10" step="0.5" value="{{ $user->commission_pourcentage }}" placeholder="Défaut : 10 %">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="sa-btn sa-btn-primary"><i class="bi bi-check-lg"></i> Enregistrer les contrôles</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="sa-card mb-4">
        <div class="sa-card-header">
            <span><i class="bi bi-shield-exclamation me-2" style="color: var(--sa-danger);"></i>Actions</span>
            <span class="text-muted" style="font-size:0.8rem;">Modération et communication</span>
        </div>
        <div class="sa-card-body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                @if(in_array($user->statut, ['en_attente', 'incomplet', 'corrections_demandees']))
                    <form action="{{ route('superadmin.organisateurs.approuver', $user) }}" method="POST" onsubmit="return confirm('Approuver {{ $user->nom }} ?')">
                        @csrf
                        <button type="submit" class="sa-btn sa-btn-primary"><i class="bi bi-check-lg"></i> Approuver</button>
                    </form>
                    <button class="sa-btn sa-btn-warning" onclick="document.getElementById('correctionsModal').style.display='flex'">
                        <i class="bi bi-pencil-square"></i> Demander des corrections
                    </button>
                    <button class="sa-btn sa-btn-danger" onclick="document.getElementById('rejetModal').style.display='flex'">
                        <i class="bi bi-x-lg"></i> Rejeter
                    </button>
                @endif
                @if($user->statut === 'actif')
                    <form action="{{ route('superadmin.organisateurs.suspendre', $user) }}" method="POST" onsubmit="return confirm('Suspendre {{ $user->nom }} ? Ses événements seront annulés.')">
                        @csrf
                        <button type="submit" class="sa-btn sa-btn-danger"><i class="bi bi-pause-fill"></i> Suspendre</button>
                    </form>
                @endif
                <form action="{{ route('superadmin.organisateurs.supprimer', $user) }}" method="POST" onsubmit="return confirm('Supprimer définitivement {{ $user->nom }} ? Cette action est irréversible.')">
                    @csrf
                    <button type="submit" class="sa-btn sa-btn-danger"><i class="bi bi-trash"></i> Supprimer</button>
                </form>
            </div>
            <hr style="margin:0.75rem 0;border-color:#eee;">
            <h6 style="font-size:0.85rem;font-weight:700;margin-bottom:0.5rem;color:var(--sa-primary);">
                <i class="bi bi-envelope me-1"></i> Envoyer un email
            </h6>
            <form action="{{ route('superadmin.organisateurs.email', $user) }}" method="POST">
                @csrf
                <div class="mb-2">
                    <input type="text" name="sujet" class="sa-form-control" placeholder="Sujet" required>
                </div>
                <div class="mb-2">
                    <textarea name="message" class="sa-form-control" rows="3" placeholder="Votre message..." required style="resize:vertical;"></textarea>
                </div>
                <button type="submit" class="sa-btn sa-btn-primary"><i class="bi bi-send"></i> Envoyer</button>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-calendar-event me-2" style="color: var(--sa-primary);"></i>Événements</span>
            </div>
            <div class="sa-card-body p-0">
                <table class="sa-table">
                    <thead>
                        <tr><th>Titre</th><th>Date</th><th>Tickets</th><th>Revenus</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        @forelse($evenements as $ev)
                        <tr>
                            <td><strong><a href="{{ route('superadmin.evenements.voir', $ev) }}" class="text-decoration-none" style="color: var(--sa-primary);">{{ $ev->titre }}</a></strong></td>
                            <td style="font-size:0.78rem;">{{ $ev->date_event->isoFormat('D MMM YYYY') }}</td>
                            <td>{{ $ev->tickets_vendus }} / {{ $ev->capacite }}</td>
                            <td>{{ number_format($ev->recettes, 0, ',', ' ') }} F</td>
                            <td>
                                @php $st = $ev->statutEffectif(); @endphp
                                @if($st === 'passé')
                                    <span class="sa-badge sa-badge-passed">Passé</span>
                                @elseif($st === 'publié')
                                    <span class="sa-badge sa-badge-success">Publié</span>
                                @elseif($st === 'brouillon')
                                    <span class="sa-badge sa-badge-secondary">Brouillon</span>
                                @else
                                    <span class="sa-badge sa-badge-danger">{{ ucfirst($st) }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucun événement</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-pie-chart me-2" style="color: var(--sa-success);"></i>Répartition par événement</span>
            </div>
            <div class="sa-card-body p-3">
                @forelse($evenements as $ev)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <strong style="font-size:0.82rem;">{{ Str::limit($ev->titre, 30) }}</strong>
                        <div class="text-muted" style="font-size:0.72rem;">{{ $ev->tickets_vendus }} tickets</div>
                    </div>
                    <div class="fw-bold" style="color: var(--sa-success);">{{ number_format($ev->recettes, 0, ',', ' ') }} F</div>
                </div>
                @empty
                <p class="text-muted text-center py-3 mb-0">Aucune donnée</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-clock-history me-2" style="color: var(--sa-primary);"></i>Derniers tickets vendus</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Date</th><th>Événement</th><th>Acheteur</th><th>Email</th><th>Montant</th><th>Méthode</th></tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr>
                    <td style="font-size:0.78rem;">{{ $ticket->date_achat->isoFormat('D MMM YYYY HH:mm') }}</td>
                    <td>{{ Str::limit($ticket->evenement->titre, 25) }}</td>
                    <td>{{ $ticket->nom_acheteur }}</td>
                    <td>{{ $ticket->email_acheteur }}</td>
                    <td class="fw-bold" style="color: var(--sa-success);">{{ number_format($ticket->montant, 0, ',', ' ') }} F</td>
                    <td>{{ \App\Models\Ticket::methodePaiementLabel($ticket->methode_paiement) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Aucun ticket vendu</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($tickets->hasPages())
    <div class="p-3 d-flex justify-content-center">{{ $tickets->links() }}</div>
    @endif
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-arrow-repeat me-2" style="color: var(--sa-primary);"></i>Historique des modifications</span>
        <span class="text-muted" style="font-size:0.8rem;">Taux et statuts</span>
    </div>
    <div class="sa-card-body p-0">
        @php
            $labelsEspeces = [null => 'Auto (règle 15 %)', 'toujours' => 'Toujours', 'jamais' => 'Jamais'];
            $labelsCommission = fn($v) => $v === null || $v === '' ? 'Défaut (10 %)' : number_format((float) $v, 2, ',', '') . ' %';
            $champsControles = ['ventes_especes' => 'Ventes espèces', 'commission_pourcentage' => 'Commission'];
        @endphp
        <table class="sa-table">
            <thead>
                <tr><th>Date</th><th>Champ</th><th>Ancienne valeur</th><th>Nouvelle valeur</th><th>Superadmin</th></tr>
            </thead>
            <tbody>
                @forelse($historique as $log)
                    @php
                        $anciens = $log->details['ancien'] ?? [];
                        $nouveaux = $log->details['nouveau'] ?? [];
                        $aDesChangements = false;
                    @endphp
                    @foreach($champsControles as $champ => $libelle)
                        @php
                            $ancienne = $anciens[$champ] ?? null;
                            $nouvelle = $nouveaux[$champ] ?? null;
                        @endphp
                        @if($ancienne !== $nouvelle)
                            @php $aDesChangements = true; @endphp
                            <tr>
                                <td style="font-size:0.78rem;">{{ \Carbon\Carbon::parse($log->created_at)->isoFormat('D MMM YYYY HH:mm') }}</td>
                                <td>{{ $libelle }}</td>
                                <td>{{ $champ === 'ventes_especes' ? ($labelsEspeces[$ancienne] ?? '-') : $labelsCommission($ancienne) }}</td>
                                <td>{{ $champ === 'ventes_especes' ? ($labelsEspeces[$nouvelle] ?? '-') : $labelsCommission($nouvelle) }}</td>
                                <td>{{ $log->details['par'] ?? '-' }}</td>
                            </tr>
                        @endif
                    @endforeach
                    @if(!$aDesChangements)
                        <tr>
                            <td style="font-size:0.78rem;">{{ \Carbon\Carbon::parse($log->created_at)->isoFormat('D MMM YYYY HH:mm') }}</td>
                            <td>{{ $log->type_operation === 'evenement_annule' ? 'Annulation' : 'Contrôles' }}</td>
                            <td colspan="2" class="text-muted">{{ $log->type_operation === 'evenement_annule' ? 'Événement annulé' : 'Modification enregistrée' }}</td>
                            <td>{{ $log->details['par'] ?? '-' }}</td>
                        </tr>
                    @endif
                @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Aucune modification enregistrée</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($historique->hasPages())
        <div class="p-3 d-flex justify-content-center">{{ $historique->links() }}</div>
        @endif
    </div>
</div>

{{-- Modal Rejet --}}
<div id="rejetModal" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-header">
            <h5><i class="bi bi-x-circle me-2" style="color:var(--sa-danger);"></i>Rejeter {{ $user->nom }}</h5>
            <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
        </div>
        <form action="{{ route('superadmin.organisateurs.rejeter', $user) }}" method="POST">
            @csrf
            <div class="modal-body">
                <p style="font-size:0.85rem;color:#666;margin-bottom:1rem;">Expliquez le motif du rejet. L'organisateur recevra un email avec cette explication.</p>
                <textarea name="motif" class="sa-form-control" rows="4" placeholder="Motif du rejet..." required style="resize:vertical;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="sa-btn sa-btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Annuler</button>
                <button type="submit" class="sa-btn sa-btn-danger"><i class="bi bi-x-lg"></i> Rejeter</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Corrections --}}
<div id="correctionsModal" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-header">
            <h5><i class="bi bi-pencil-square me-2" style="color:var(--sa-warning);"></i>Demander des corrections</h5>
            <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
        </div>
        <form action="{{ route('superadmin.organisateurs.corrections', $user) }}" method="POST">
            @csrf
            <div class="modal-body">
                <p style="font-size:0.85rem;color:#666;margin-bottom:1rem;">Indiquez les modifications nécessaires. L'organisateur recevra un email et pourra corriger son profil.</p>
                <textarea name="motif" class="sa-form-control" rows="4" placeholder="Détail des corrections à apporter..." required style="resize:vertical;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="sa-btn sa-btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Annuler</button>
                <button type="submit" class="sa-btn sa-btn-warning"><i class="bi bi-send"></i> Envoyer</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.modal-box {
    background: #fff;
    border-radius: 14px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    animation: modalIn 0.2s ease;
}
@keyframes modalIn {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #eee;
}
.modal-header h5 { margin: 0; font-size: 1rem; font-weight: 700; }
.modal-close {
    background: none; border: none;
    font-size: 1.5rem; cursor: pointer;
    color: #999; line-height: 1;
}
.modal-close:hover { color: #333; }
.modal-body { padding: 1.25rem; }
.modal-footer {
    padding: 0.75rem 1.25rem;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}
.sa-btn-warning {
    background: #e0a800; border: none; color: #fff; padding: 0.4rem 0.9rem;
    border-radius: 8px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s;
}
.sa-btn-warning:hover { opacity: 0.85; }
</style>
@endsection