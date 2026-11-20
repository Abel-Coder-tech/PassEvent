<header class="av-topbar">
    <div class="av-brand">
        <a href="{{ route('agent-vente.dashboard') }}" aria-label="Retour au point de vente">
            <img src="{{ asset_v('images/logo_paxevent_blanc.png') }}" alt="PaxEvent" height="34">
        </a>
        <span class="av-brand-divider" aria-hidden="true"></span>
        <div class="av-brand-event">
            <span>{{ $section ?? 'Espace vente' }}</span>
            <strong title="{{ $agent->evenement->titre }}">{{ $agent->evenement->titre }}</strong>
        </div>
    </div>
    <div class="av-top-actions">
        <span class="av-agent"><i class="bi bi-person-badge" aria-hidden="true"></i>{{ $agent->nom }}</span>
        <form method="POST" action="{{ route('agent-vente.logout') }}" class="m-0">
            @csrf
            <button type="submit" class="av-icon-btn" aria-label="Se déconnecter" title="Se déconnecter">
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
            </button>
        </form>
    </div>
</header>
