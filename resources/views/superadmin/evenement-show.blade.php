@extends('superadmin.layouts.master')

@section('title', $evenement->titre . ' — Événement')
@section('page-title', Str::limit($evenement->titre, 40))

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('superadmin.evenements') }}" class="text-decoration-none small" style="color: var(--sa-primary);">
        <i class="bi bi-arrow-left"></i> Tous les événements
    </a>
    <a href="{{ route('superadmin.organisateurs.voir', $evenement->user) }}" class="text-decoration-none small" style="color: var(--sa-primary);">
        <i class="bi bi-person"></i> {{ $evenement->user->nom ?? 'Organisateur' }}
    </a>
</div>

@php
    $statutEffectif = $evenement->statutEffectif();
    $badgeMap = ['publié' => 'success', 'brouillon' => 'secondary', 'annulé' => 'danger', 'passé' => 'passed'];
    $labelMap = ['publié' => 'Publié', 'brouillon' => 'Brouillon', 'annulé' => 'Annulé', 'passé' => 'Passé'];
    $statutEspeces = $evenement->ventes_especes ?? $evenement->user?->ventes_especes;
    $labelEspecesEffectif = $statutEspeces === 'toujours' ? 'Toujours autorisées' : ($statutEspeces === 'jamais' ? 'Jamais (bloquées)' : 'Auto (règle 15 %)');
    $labelCommissionEffectif = $commissionPct;
    $limiteAgents = $evenement->limiteAgentsVente();
    $labelAgentsEffectif = $limiteAgents === null ? 'illimité' : ($limiteAgents . ' agents');
@endphp

<div class="row g-2 mb-4">
    <div class="col-6 col-md-3">
        <div class="sa-card text-center py-3">
            <div class="fw-bold fs-4" style="color: var(--sa-primary);">{{ $evenement->quota_vendu }} / {{ $evenement->capacite }}</div>
            <small class="text-muted">Places vendues</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="sa-card text-center py-3">
            <div class="fw-bold fs-4" style="color: var(--sa-success);">{{ $evenement->tickets_vendus ?? 0 }}</div>
            <small class="text-muted">Tickets payés</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="sa-card text-center py-3">
            <div class="fw-bold fs-4" style="color: #3498db;">{{ number_format($evenement->recettes ?? 0, 0, ',', ' ') }} F</div>
            <small class="text-muted">Recettes</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="sa-card text-center py-3">
            <div class="fw-bold fs-4" style="color: var(--sa-danger);">{{ number_format($commission, 0, ',', ' ') }} F</div>
            <small class="text-muted">Commission ({{ $commissionPct }}%)</small>
        </div>
    </div>
</div>

<div class="row g-2 mb-4">
    <div class="col-6 col-md-2">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="color:#3498db;">{{ number_format($mobileRecettes, 0, ',', ' ') }} F</div>
            <small class="text-muted">Mobile (FedaPay)</small>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="color:#f39c12;">{{ number_format($cashRecettes, 0, ',', ' ') }} F</div>
            <small class="text-muted">Espèces</small>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="color:var(--sa-success);">{{ number_format($recettesNettes, 0, ',', ' ') }} F</div>
            <small class="text-muted">Net (après commission)</small>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="color: var(--sa-primary);">{{ $ticketsScannes }}</div>
            <small class="text-muted">Tickets scannés</small>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="color: var(--sa-success);">{{ $agentsScan }}</div>
            <small class="text-muted">Agents scan</small>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="color: #3498db;">{{ $agentsVente }}</div>
            <small class="text-muted">Agents vente</small>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="sa-card h-100">
            <div class="sa-card-header">
                <span><i class="bi bi-sliders me-2" style="color: var(--sa-primary);"></i>Contrôles de l'événement</span>
                <span class="text-muted" style="font-size:0.8rem;">Spécifique &gt; organisateur &gt; défaut</span>
            </div>
            <div class="sa-card-body">
                <form action="{{ route('superadmin.evenements.controles', $evenement) }}" method="POST">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">Ventes espèces</label>
                            <select name="ventes_especes" class="sa-form-control">
                                <option value="">Héritage ({{ $labelEspecesEffectif }})</option>
                                <option value="toujours" @selected($evenement->ventes_especes === 'toujours')>Toujours autorisées</option>
                                <option value="jamais" @selected($evenement->ventes_especes === 'jamais')>Jamais (bloquées)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Commission (%)</label>
                            <input type="number" name="commission_pourcentage" class="sa-form-control" min="0" max="10" step="0.5" value="{{ $evenement->commission_pourcentage }}" placeholder="Héritage ({{ $labelCommissionEffectif }} %)">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Agents de vente (max)</label>
                            <select name="max_agents_vente" class="sa-form-control">
                                <option value="">Défaut (2 agents)</option>
                                <option value="5" @selected($evenement->max_agents_vente === 5)>5 agents</option>
                                <option value="10" @selected($evenement->max_agents_vente === 10)>10 agents</option>
                                <option value="0" @selected($evenement->max_agents_vente === 0)>Illimité</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="sa-btn sa-btn-primary w-100"><i class="bi bi-check-lg"></i> Enregistrer</button>
                        </div>
                    </div>
                </form>
                <div class="mt-3 small text-muted">
                    <i class="bi bi-info-circle me-1"></i>Effectif actuel : espèces « {{ $labelEspecesEffectif }} », commission {{ $labelCommissionEffectif }} %, agents de vente : {{ $labelAgentsEffectif }}.
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="sa-card h-100">
            <div class="sa-card-header">
                <span><i class="bi bi-shield-exclamation me-2" style="color: var(--sa-danger);"></i>Actions</span>
            </div>
            <div class="sa-card-body">
                <div class="d-flex flex-wrap gap-2">
                    <form action="{{ route('superadmin.evenements.suspendre', $evenement) }}" method="POST" onsubmit="return confirm('Suspendre {{ $evenement->titre }} ?')">
                        @csrf
                        <button type="submit" class="sa-btn sa-btn-danger"><i class="bi bi-pause-fill"></i> Suspendre</button>
                    </form>
                    <form action="{{ route('superadmin.evenements.masquer', $evenement) }}" method="POST">
                        @csrf
                        <button type="submit" class="sa-btn sa-btn-outline"><i class="bi bi-eye-slash"></i> Masquer</button>
                    </form>
                    @if($evenement->statut !== 'publié')
                        <form action="{{ route('superadmin.evenements.mettre-en-avant', $evenement) }}" method="POST">
                            @csrf
                            <button type="submit" class="sa-btn" style="background:var(--sa-success);color:#fff;border:none;"><i class="bi bi-check-lg"></i> Publier</button>
                        </form>
                    @endif
                    <form action="{{ route('superadmin.evenements.supprimer', $evenement) }}" method="POST" onsubmit="return confirm('Supprimer définitivement {{ $evenement->titre }} ? Cette action est irréversible.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="sa-btn sa-btn-danger"><i class="bi bi-trash-fill"></i> Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="sa-card h-100">
            <div class="sa-card-header">
                <span><i class="bi bi-ticket-perforated me-2" style="color: var(--sa-success);"></i>Tarifs</span>
            </div>
            <div class="sa-card-body p-0">
                <table class="sa-table">
                    <thead><tr><th>Nom</th><th>Prix</th><th>Vendus</th><th>Statut</th></tr></thead>
                    <tbody>
                        @forelse($evenement->tarifs as $tarif)
                        <tr>
                            <td>{{ $tarif->nom }}</td>
                            <td>{{ number_format($tarif->prix, 0, ',', ' ') }} F</td>
                            <td>{{ $tarif->quantite_vendue ?? 0 }}</td>
                            <td>{{ $tarif->statut === 'actif' ? 'Actif' : ucfirst($tarif->statut) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Aucun tarif</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="sa-card h-100">
            <div class="sa-card-header">
                <span><i class="bi bi-calendar-event me-2" style="color: var(--sa-primary);"></i>Informations</span>
            </div>
            <div class="sa-card-body">
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f0f0f0;">
                    <span class="text-muted small">Statut</span>
                    <span class="sa-badge sa-badge-{{ $badgeMap[$statutEffectif] ?? 'warning' }}">{{ $labelMap[$statutEffectif] ?? $statutEffectif }}</span>
                </div>
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f0f0f0;">
                    <span class="text-muted small">Date</span>
                    <span class="fw-semibold small">{{ $evenement->date_event->isoFormat('D MMM YYYY HH:mm') }}</span>
                </div>
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f0f0f0;">
                    <span class="text-muted small">Lieu</span>
                    <span class="fw-semibold small">{{ $evenement->lieu }}</span>
                </div>
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f0f0f0;">
                    <span class="text-muted small">Catégorie</span>
                    <span class="fw-semibold small">{{ ucfirst($evenement->categorie ?? '-') }}</span>
                </div>
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f0f0f0;">
                    <span class="text-muted small">Gratuit</span>
                    <span class="fw-semibold small">{{ $evenement->gratuit ? 'Oui' : 'Non' }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted small">Organisateur</span>
                    <a href="{{ route('superadmin.organisateurs.voir', $evenement->user) }}" class="fw-semibold small text-decoration-none" style="color: var(--sa-primary);">{{ $evenement->user->nom ?? '-' }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sa-card mb-4">
    <div class="sa-card-header">
        <span><i class="bi bi-ticket me-2" style="color: var(--sa-success);"></i>Derniers tickets</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Date</th><th>Acheteur</th><th>Email</th><th>Tarif</th><th>Montant</th><th>Méthode</th></tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr>
                    <td style="font-size:0.78rem;">{{ $ticket->date_achat->isoFormat('D MMM YYYY HH:mm') }}</td>
                    <td>{{ $ticket->nom_acheteur }}</td>
                    <td>{{ $ticket->email_acheteur }}</td>
                    <td>{{ $ticket->nom_tarif }}</td>
                    <td class="fw-bold" style="color: var(--sa-success);">{{ number_format($ticket->montant, 0, ',', ' ') }} F</td>
                    <td>{{ \App\Models\Ticket::methodePaiementLabel($ticket->methode_paiement) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Aucun ticket vendu</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-arrow-repeat me-2" style="color: var(--sa-primary);"></i>Historique des modifications</span>
    </div>
    <div class="sa-card-body p-0">
        @php
            $formatControle = function ($champ, $valeur) {
                if ($champ === 'ventes_especes') {
                    return $valeur === 'toujours' ? 'Toujours' : ($valeur === 'jamais' ? 'Jamais' : 'Héritage');
                }
                if ($champ === 'commission_pourcentage') {
                    return $valeur === null || $valeur === '' ? 'Défaut (10 %)' : number_format((float) $valeur, 2, ',', '') . ' %';
                }
                if ($champ === 'max_agents_vente') {
                    if ($valeur === null || $valeur === '') return 'Défaut (2 agents)';
                    if ((int) $valeur === 0) return 'Illimité';
                    return (int) $valeur . ' agents';
                }
                return '-';
            };
            $champsControles = ['ventes_especes' => 'Ventes espèces', 'commission_pourcentage' => 'Commission', 'max_agents_vente' => 'Agents de vente'];
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
                                <td>{{ $formatControle($champ, $ancienne) }}</td>
                                <td>{{ $formatControle($champ, $nouvelle) }}</td>
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
@endsection
