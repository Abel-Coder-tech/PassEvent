@extends('superadmin.layouts.master')

@section('title', 'Journal de sécurité - Super Admin')
@section('page-title', 'Journal de sécurité')

@section('content')
<style>
    .sa-label { display: block; font-size: 0.72rem; font-weight: 600; color: var(--sa-text-muted); margin-bottom: 0.25rem; }
    .sa-form-control-sm { padding: 0.35rem 0.6rem; font-size: 0.78rem; }
    .js-url { font-family: monospace; font-size: 0.72rem; word-break: break-all; max-width: 340px; }
    .js-details { font-family: monospace; font-size: 0.7rem; background: #f8f9fa; border-radius: 6px; padding: .5rem .7rem; white-space: pre-wrap; word-break: break-all; margin: .4rem 0 0; }
    .js-ip { font-family: monospace; font-size: 0.78rem; }
</style>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="sa-card"><div class="sa-card-body py-3">
        <div class="text-muted" style="font-size:0.72rem;">Événements</div>
        <div class="fw-bold" style="font-size:1.4rem;">{{ number_format($stats['total']) }}</div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="sa-card"><div class="sa-card-body py-3">
        <div class="text-muted" style="font-size:0.72rem;">Connexions échouées / bloquées</div>
        <div class="fw-bold" style="font-size:1.4rem;color:var(--sa-warning);">{{ number_format($stats['connexions']) }}</div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="sa-card"><div class="sa-card-body py-3">
        <div class="text-muted" style="font-size:0.72rem;">Attaques, robots, liens suspects</div>
        <div class="fw-bold" style="font-size:1.4rem;color:var(--sa-danger);">{{ number_format($stats['attaques']) }}</div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="sa-card"><div class="sa-card-body py-3">
        <div class="text-muted" style="font-size:0.72rem;">IP différentes</div>
        <div class="fw-bold" style="font-size:1.4rem;">{{ number_format($stats['ips']) }}</div>
    </div></div></div>
</div>

@if($resumeIp)
<div class="sa-card mb-4" style="border-left:4px solid var(--sa-danger);">
    <div class="sa-card-header">
        <span><i class="bi bi-geo-alt-fill me-2" style="color:var(--sa-danger);"></i>Activité de l'IP <span class="js-ip">{{ $resumeIp['ip'] }}</span>
            @if($resumeIp['bloquee'])<span class="sa-badge ms-2" style="background:var(--sa-danger);color:#fff;">Bloquée</span>@endif
        </span>
        <a href="{{ route('superadmin.journal-securite', array_filter(['periode' => $periode, 'type' => $type])) }}" class="text-muted" style="font-size:0.8rem;"><i class="bi bi-x-lg me-1"></i>Toutes les IP</a>
    </div>
    <div class="sa-card-body" style="font-size:0.82rem;">
        <div class="row g-3">
            <div class="col-md-7">
                <div><strong>{{ $resumeIp['total'] }}</strong> événement(s) au total, du
                    {{ \Carbon\Carbon::parse($resumeIp['premier'])->isoFormat('D MMM YYYY HH:mm') }} au
                    {{ \Carbon\Carbon::parse($resumeIp['dernier'])->isoFormat('D MMM YYYY HH:mm') }}.</div>
                <div class="mt-1">
                    @foreach($resumeIp['parType'] as $t => $n)
                        <span class="sa-badge sa-badge-info me-1" style="color:{{ $types[$t]['color'] ?? 'inherit' }};">{{ $types[$t]['label'] ?? $t }} : {{ $n }}</span>
                    @endforeach
                </div>
                @if($resumeIp['identifiants']->isNotEmpty())
                    <div class="mt-2 text-muted">Comptes visés : {{ $resumeIp['identifiants']->implode(', ') }}</div>
                @endif
            </div>
            <div class="col-md-5">
                @unless($resumeIp['bloquee'])
                <form method="POST" action="{{ route('superadmin.journal-securite.bloquer') }}" class="d-flex flex-wrap gap-2 align-items-end">
                    @csrf
                    <input type="hidden" name="ip" value="{{ $resumeIp['ip'] }}">
                    <div>
                        <label class="sa-label">Bloquer pendant</label>
                        <select name="duree" class="sa-form-control sa-form-control-sm">
                            <option value="1">1 heure</option>
                            <option value="24" selected>24 heures</option>
                            <option value="168">7 jours</option>
                            <option value="720">30 jours</option>
                            <option value="permanent">Définitivement</option>
                        </select>
                    </div>
                    <div class="flex-fill">
                        <label class="sa-label">Motif (facultatif)</label>
                        <input type="text" name="motif" class="sa-form-control sa-form-control-sm" maxlength="255" placeholder="Ex. scan de failles">
                    </div>
                    <button type="submit" class="sa-btn sa-btn-sm" style="background:var(--sa-danger);color:#fff;" onclick="return confirm('Bloquer {{ $resumeIp['ip'] }} ? Cette IP ne pourra plus accéder au site.')">
                        <i class="bi bi-slash-circle me-1"></i>Bloquer
                    </button>
                </form>
                @endunless
            </div>
        </div>
    </div>
</div>
@endif

<div class="sa-card mb-4">
    <div class="sa-card-body py-3">
        <form method="GET" action="{{ route('superadmin.journal-securite') }}" class="row g-2 align-items-end" id="journal-filtres">
            @if($ip !== '')<input type="hidden" name="ip" value="{{ $ip }}">@endif
            <div class="col-12 col-md-5">
                <label class="sa-label">Recherche</label>
                <input type="text" name="q" class="sa-form-control sa-form-control-sm" placeholder="IP, email tenté, lien, navigateur, règle…" value="{{ $q }}" autocomplete="off">
            </div>
            <div class="col-6 col-md-3">
                <label class="sa-label">Type</label>
                <select name="type" class="sa-form-control sa-form-control-sm">
                    <option value="">Tous</option>
                    @foreach($types as $valeur => $infos)
                        <option value="{{ $valeur }}" @selected($type === $valeur)>{{ $infos['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="sa-label">Période</label>
                <select name="periode" class="sa-form-control sa-form-control-sm">
                    @foreach(['1' => '24 heures', '7' => '7 jours', '30' => '30 jours', '90' => '90 jours'] as $valeur => $libelle)
                        <option value="{{ $valeur }}" @selected((string) $periode === $valeur)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <a href="{{ route('superadmin.journal-securite') }}" class="sa-btn sa-btn-sm w-100" style="background:#f1f2f6;border:1px solid #e3e6ed;color:#555;" title="Réinitialiser">
                    <i class="bi bi-arrow-clockwise me-1"></i>Réinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-9">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-shield-exclamation me-2" style="color: var(--sa-danger);"></i>Événements de sécurité</span>
                <span class="text-muted" style="font-size:0.8rem;">{{ $evenements->total() }} entrées</span>
            </div>
            <div class="sa-card-body p-0" style="overflow-x:auto;">
                <table class="sa-table">
                    <thead><tr><th>Date</th><th>Type</th><th>IP</th><th>Compte tenté</th><th>Lien demandé</th></tr></thead>
                    <tbody>
                        @forelse($evenements as $ev)
                        <tr>
                            <td style="font-size:0.72rem;white-space:nowrap;">{{ $ev->created_at->isoFormat('D MMM HH:mm:ss') }}</td>
                            <td>
                                <span class="sa-badge sa-badge-info" style="color:{{ $types[$ev->type]['color'] ?? 'inherit' }};">{{ $types[$ev->type]['label'] ?? $ev->type }}</span>
                                @if($ev->regle)<div class="text-muted" style="font-size:0.68rem;">{{ str_replace('_', ' ', $ev->regle) }}</div>@endif
                            </td>
                            <td>
                                <a href="{{ route('superadmin.journal-securite', ['ip' => $ev->ip, 'periode' => $periode]) }}" class="js-ip" title="Voir tout ce que cette IP a fait">{{ $ev->ip }}</a>
                                @if(in_array($ev->ip, $bloqueesActives, true))<div><span class="sa-badge" style="background:var(--sa-danger);color:#fff;font-size:0.62rem;">bloquée</span></div>@endif
                            </td>
                            <td style="font-size:0.75rem;">
                                {{ $ev->identifiant ?? '—' }}
                                @if($ev->espace)<div class="text-muted" style="font-size:0.68rem;">{{ str_replace('_', ' ', $ev->espace) }}</div>@endif
                            </td>
                            <td>
                                <div class="js-url"><strong>{{ $ev->methode }}</strong> {{ \Illuminate\Support\Str::limit($ev->url, 160) }}</div>
                                @if($ev->donnees || $ev->user_agent)
                                <details>
                                    <summary class="text-muted" style="font-size:0.7rem;cursor:pointer;">Détails</summary>
                                    @if($ev->donnees)<pre class="js-details">{{ json_encode($ev->donnees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>@endif
                                    @if($ev->user_agent)<div class="js-details">Navigateur : {{ $ev->user_agent }}</div>@endif
                                </details>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucun événement de sécurité sur cette période.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3 d-flex justify-content-center">{{ $evenements->links() }}</div>
    </div>

    <div class="col-lg-3">
        <div class="sa-card mb-3">
            <div class="sa-card-header"><span><i class="bi bi-bar-chart-line me-2" style="color:var(--sa-primary);"></i>IP les plus actives</span></div>
            <div class="sa-card-body p-0">
                @forelse($topIps as $top)
                    <a href="{{ route('superadmin.journal-securite', ['ip' => $top->ip, 'periode' => $periode]) }}" class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom text-decoration-none" style="color:inherit;">
                        <span class="js-ip">{{ $top->ip }} @if(in_array($top->ip, $bloqueesActives, true))<i class="bi bi-slash-circle" style="color:var(--sa-danger);" title="bloquée"></i>@endif</span>
                        <strong>{{ $top->total }}</strong>
                    </a>
                @empty
                    <div class="text-muted px-3 py-3" style="font-size:0.8rem;">Rien à signaler.</div>
                @endforelse
            </div>
        </div>

        <div class="sa-card">
            <div class="sa-card-header"><span><i class="bi bi-slash-circle me-2" style="color:var(--sa-danger);"></i>IP bloquées</span></div>
            <div class="sa-card-body p-0">
                @forelse($ipsBloquees as $b)
                    <div class="px-3 py-2 border-bottom" style="font-size:0.75rem;">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('superadmin.journal-securite', ['ip' => $b->ip, 'periode' => '90']) }}" class="js-ip">{{ $b->ip }}</a>
                            <form method="POST" action="{{ route('superadmin.journal-securite.debloquer', $b) }}" onsubmit="return confirm('Débloquer {{ $b->ip }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-link btn-sm p-0" style="font-size:0.72rem;color:var(--sa-success);">Débloquer</button>
                            </form>
                        </div>
                        <div class="text-muted">
                            @if(! $b->estActive()) Expiré
                            @elseif($b->expire_le) Jusqu'au {{ $b->expire_le->isoFormat('D MMM HH:mm') }}
                            @else Définitif
                            @endif
                            @if($b->motif) · {{ $b->motif }}@endif
                        </div>
                    </div>
                @empty
                    <div class="text-muted px-3 py-3" style="font-size:0.8rem;">Aucune IP bloquée.</div>
                @endforelse
            </div>
        </div>

        <div class="text-muted mt-3" style="font-size:0.72rem;">
            <i class="bi bi-info-circle me-1"></i>Les mots de passe, jetons et codes ne sont jamais enregistrés (remplacés par ••••••).
            Les robots d'attaque connus et les IP bloquées sont refusés automatiquement ; les autres tentatives sont seulement enregistrées.
            Une alerte email est envoyée quand une IP dépasse {{ \App\Services\SecuriteService::SEUIL_ALERTE }} événements en 5 minutes. Les entrées sont conservées 90 jours.
        </div>
    </div>
</div>

<script>
    // Recherche automatique, comme sur la page Logs
    (function () {
        const form = document.getElementById('journal-filtres');
        if (!form) return;
        const champ = form.querySelector('input[name="q"]');
        let minuteur;
        champ.addEventListener('input', function () {
            clearTimeout(minuteur);
            minuteur = setTimeout(() => form.submit(), 500);
        });
        form.querySelectorAll('select').forEach(s => s.addEventListener('change', () => form.submit()));
        if (champ.value) { champ.focus(); champ.setSelectionRange(champ.value.length, champ.value.length); }
    })();
</script>
@endsection
