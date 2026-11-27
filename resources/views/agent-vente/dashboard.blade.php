@extends('layouts.agent-vente')

@section('title', 'Point de vente — ' . $agent->evenement->titre)

@section('content')
@php
    $textes = $agent->evenement->getTextes();
    $estSpectacle = $textes['type'] === 'spectacle';
    $especesOk = $agent->evenement->ventesEspecesActivees();
    $cashBloque = ! $agent->evenement->gratuit && ! $especesOk;
    $paiementDefaut = old('methode_paiement', $cashBloque ? 'mobile_money' : 'cash');
    $tarifsPack = $tarifs->whereNull('evenement_date_id');
    $joursAvecTarifs = $agent->evenement->dates->filter(
        fn ($jour) => $tarifs->where('evenement_date_id', $jour->id)->isNotEmpty()
    );
@endphp

<div class="av-app">
    @include('agent-vente.partials.header', ['section' => 'Point de vente'])

    <main class="av-main">
        @if (session('success'))
            <div class="av-alert av-alert--success" id="successAlert" role="status">
                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                <div class="av-alert-body">
                    <strong>Vente enregistrée</strong>
                    <span>{{ session('success') }}</span>
                    @if (session('ticket_created'))
                        <a href="{{ route('agent-vente.ticket.pdf', session('ticket_created')) }}" target="_blank" rel="noopener">
                            <i class="bi bi-filetype-pdf" aria-hidden="true"></i> Ouvrir le billet PDF
                        </a>
                    @endif
                </div>
                <button type="button" class="av-alert-close js-alert-close" aria-label="Fermer la notification">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="av-alert av-alert--error" role="alert">
                <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                <div class="av-alert-body">
                    <strong>Vérifiez les informations saisies</strong>
                    <ul>
                        @foreach ($errors->all() as $erreur)
                            <li>{{ $erreur }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <section class="av-stats" aria-label="Statistiques de vente">
            <article class="av-stat">
                <span class="av-stat-icon av-stat-icon--violet"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span>
                <div><strong id="statTotalTickets">{{ $stats['total_tickets'] }}</strong><span>Billets vendus</span></div>
            </article>
            <article class="av-stat">
                <span class="av-stat-icon av-stat-icon--green"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
                <div><strong id="statMontantTotal">{{ number_format($stats['montant_total'], 0, ',', ' ') }} F</strong><span>Montant total</span></div>
            </article>
            <article class="av-stat">
                <span class="av-stat-icon av-stat-icon--blue"><i class="bi bi-calendar-check" aria-hidden="true"></i></span>
                <div><strong id="statAujourdHui">{{ $stats['aujourd_hui'] }}</strong><span>Aujourd'hui</span></div>
            </article>
            <article class="av-stat">
                <span class="av-stat-icon av-stat-icon--amber"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></span>
                <div><strong id="statMontantAjd">{{ number_format($stats['montant_ajd'], 0, ',', ' ') }} F</strong><span>Montant du jour</span></div>
            </article>
        </section>

        <div class="av-grid">
            <section class="av-card av-sale" aria-labelledby="avSaleTitle">
                <div class="av-card-head">
                    <div>
                        <span class="av-kicker">Nouvelle vente</span>
                        <h1 id="avSaleTitle">{{ $estSpectacle ? 'Vendre un billet' : 'Enregistrer une inscription' }}</h1>
                    </div>
                    <span class="av-live"><span aria-hidden="true"></span> En direct</span>
                </div>

                <form method="POST" action="{{ route('agent-vente.vendre') }}" id="venteForm">
                    @csrf

                    <fieldset class="av-block">
                        <legend><span class="av-num">1</span> Acheteur</legend>
                        <div class="av-fields">
                            <label class="av-field av-field--wide">
                                <span>Nom complet <em>*</em></span>
                                <input type="text" name="nom_acheteur" value="{{ old('nom_acheteur') }}" placeholder="Ex : Adja Koné" autocomplete="name" required>
                                @error('nom_acheteur') <small class="av-error">{{ $message }}</small> @enderror
                            </label>
                            <label class="av-field">
                                <span>Email <em>*</em></span>
                                <input type="email" name="email_acheteur" value="{{ old('email_acheteur') }}" placeholder="adja@email.com" autocomplete="email" inputmode="email" required>
                                @error('email_acheteur') <small class="av-error">{{ $message }}</small> @enderror
                            </label>
                            <label class="av-field">
                                <span>WhatsApp <small>(facultatif)</small></span>
                                <input type="tel" name="whatsapp_acheteur" value="{{ old('whatsapp_acheteur') }}" placeholder="+229 62 83 66 29" autocomplete="tel" inputmode="tel">
                                @error('whatsapp_acheteur') <small class="av-error">{{ $message }}</small> @enderror
                            </label>
                        </div>
                    </fieldset>

                    <fieldset class="av-block">
                        <legend><span class="av-num">2</span> Billet ou tarif</legend>

                        @if ($tarifs->isEmpty())
                            <p class="av-empty-inline">Aucun tarif disponible pour cet événement.</p>
                        @else
                            <div class="av-tarifs" id="listeTarifs">
                                @if ($tarifsPack->isNotEmpty())
                                    <p class="av-group-label">{{ $agent->evenement->estMultiJour() ? 'Tous les jours (pack)' : 'Tarifs' }}</p>
                                    @foreach ($tarifsPack as $tarif)
                                        @php $restantes = $agent->evenement->placesRestantesPourTarif($tarif); @endphp
                                        <label class="av-tarif{{ $restantes <= 0 ? ' is-disabled' : '' }}" data-prix="{{ (float) $tarif->prix }}">
                                            <input type="radio" name="tarif_id" value="{{ $tarif->id }}" data-prix="{{ (float) $tarif->prix }}" {{ $restantes <= 0 ? 'disabled' : '' }} @checked(old('tarif_id') == $tarif->id) required>
                                            <span class="av-tarif-info">
                                                <strong>{{ $tarif->getLabel() }}</strong>
                                                <small class="{{ $restantes <= 0 ? 'is-full' : '' }}">{{ $restantes <= 0 ? 'Complet' : $restantes . ' place(s) restante(s)' }}</small>
                                            </span>
                                            <span class="av-tarif-price">{{ $tarif->prix > 0 ? number_format($tarif->prix, 0, ',', ' ') . ' F' : 'Gratuit' }}</span>
                                            <span class="av-tarif-check" aria-hidden="true"><i class="bi bi-check-lg"></i></span>
                                        </label>
                                    @endforeach
                                @endif

                                @foreach ($joursAvecTarifs as $jour)
                                    <p class="av-group-label">{{ $jour->libelle() }} — {{ $jour->date_debut->isoFormat('ddd D MMM · HH:mm') }}</p>
                                    @foreach ($tarifs->where('evenement_date_id', $jour->id) as $tarif)
                                        @php $restantes = $agent->evenement->placesRestantesPourTarif($tarif); @endphp
                                        <label class="av-tarif{{ $restantes <= 0 ? ' is-disabled' : '' }}" data-prix="{{ (float) $tarif->prix }}">
                                            <input type="radio" name="tarif_id" value="{{ $tarif->id }}" data-prix="{{ (float) $tarif->prix }}" {{ $restantes <= 0 ? 'disabled' : '' }} @checked(old('tarif_id') == $tarif->id) required>
                                            <span class="av-tarif-info">
                                                <strong>{{ $tarif->getLabel() }}</strong>
                                                <small class="{{ $restantes <= 0 ? 'is-full' : '' }}">{{ $restantes <= 0 ? 'Complet' : $restantes . ' place(s) restante(s)' }}</small>
                                            </span>
                                            <span class="av-tarif-price">{{ $tarif->prix > 0 ? number_format($tarif->prix, 0, ',', ' ') . ' F' : 'Gratuit' }}</span>
                                            <span class="av-tarif-check" aria-hidden="true"><i class="bi bi-check-lg"></i></span>
                                        </label>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif

                        @error('tarif_id') <small class="av-error">{{ $message }}</small> @enderror

                        @if ($agent->evenement->estMultiJour())
                            <p class="av-capacity">
                                <i class="bi bi-info-circle" aria-hidden="true"></i>
                                {{ $agent->evenement->placesRestantesTotales() }} place(s) au total
                                @foreach ($agent->evenement->placesParJour()->reject(fn ($ligne) => $ligne['termine']) as $ligne)
                                    · {{ $ligne['jour']->libelle() }} : {{ $ligne['restantes'] > 0 ? $ligne['restantes'] : 'complet' }}
                                @endforeach
                            </p>
                        @endif
                    </fieldset>

                    <fieldset class="av-block">
                        <legend><span class="av-num">3</span> Paiement</legend>
                        <div class="av-pay" role="radiogroup" aria-label="Moyen de paiement">
                            <label class="av-pay-option{{ $paiementDefaut === 'cash' ? ' is-selected' : '' }}{{ $cashBloque ? ' is-disabled' : '' }}">
                                <input type="radio" name="methode_paiement" value="cash" @checked($paiementDefaut === 'cash') {{ $cashBloque ? 'disabled' : '' }} required>
                                <span class="av-pay-icon"><i class="bi bi-cash-coin" aria-hidden="true"></i></span>
                                <span class="av-pay-text"><strong>Espèces</strong><small>Encaissement immédiat</small></span>
                            </label>
                            <label class="av-pay-option{{ $paiementDefaut === 'mobile_money' ? ' is-selected' : '' }}">
                                <input type="radio" name="methode_paiement" value="mobile_money" @checked($paiementDefaut === 'mobile_money') required>
                                <span class="av-pay-icon"><i class="bi bi-phone" aria-hidden="true"></i></span>
                                <span class="av-pay-text"><strong>Mobile money</strong><small>Paiement par FedaPay</small></span>
                            </label>
                        </div>

                        @if ($cashBloque)
                            <p class="av-warning">
                                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                                Espèces bloquées — {{ $agent->evenement->ticketsEnLigneCount() }}/{{ (int) ceil($agent->evenement->capacite * 15 / 100) }} tickets vendus en ligne.
                            </p>
                        @endif
                        @error('methode_paiement') <small class="av-error">{{ $message }}</small> @enderror

                        <details class="av-promo" @if ($errors->has('code_promo')) open @endif>
                            <summary><i class="bi bi-ticket-perforated" aria-hidden="true"></i> Code promo <span>(facultatif)</span><i class="bi bi-chevron-down av-promo-chevron" aria-hidden="true"></i></summary>
                            <label class="av-field">
                                <span class="visually-hidden">Code promo</span>
                                <input type="text" name="code_promo" value="{{ old('code_promo') }}" maxlength="50" placeholder="Ex : BIENVENUE10" autocomplete="off">
                            </label>
                            @error('code_promo') <small class="av-error">{{ $message }}</small> @enderror
                        </details>
                    </fieldset>

                    <div class="av-checkout">
                        <div class="av-total">
                            <span>Montant du billet</span>
                            <strong id="avTotal">—</strong>
                        </div>
                        <button type="submit" class="av-submit" id="btnVente">
                            <i class="bi bi-check-lg" aria-hidden="true"></i>
                            <span id="btnVenteLabel">{{ $estSpectacle ? 'Vendre le billet' : "Enregistrer l'inscription" }}</span>
                        </button>
                        <p class="av-hint" id="avHint"><i class="bi bi-whatsapp" aria-hidden="true"></i> Le billet QR sera envoyé par e-mail.</p>
                    </div>
                </form>
            </section>

            <section class="av-card av-history" aria-labelledby="avHistoryTitle">
                <div class="av-card-head">
                    <div>
                        <span class="av-kicker">Suivi</span>
                        <h2 id="avHistoryTitle">Ventes du jour</h2>
                    </div>
                    <a class="av-link" href="{{ route('agent-vente.historique') }}">
                        Historique <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                    </a>
                </div>
                <p class="av-refresh"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Actualisation automatique</p>

                <div class="av-sales" id="historiqueBody">
                    @forelse ($ticketsAujourdHui as $ticket)
                        @include('agent-vente.partials.vente-card', ['ticket' => $ticket])
                    @empty
                        <div class="av-empty"><i class="bi bi-inbox" aria-hidden="true"></i><span>Aucune vente aujourd'hui</span></div>
                    @endforelse
                </div>
            </section>
        </div>
    </main>
</div>
@endsection

@push('styles')
@include('agent-vente.partials.styles')

@endpush

@push('scripts')
<script>
(function () {
    const form = document.getElementById('venteForm');
    const labelVente = @json($estSpectacle ? 'Vendre le billet' : "Enregistrer l'inscription");
    const routeHistorique = @json(route('agent-vente.historique.json'));

    function formatMontant(valeur) {
        return new Intl.NumberFormat('fr-FR').format(Number(valeur) || 0);
    }

    function selectedTarif() {
        return document.querySelector('input[name="tarif_id"]:checked');
    }

    function selectedPaiement() {
        const input = document.querySelector('input[name="methode_paiement"]:checked');
        return input ? input.value : 'cash';
    }

    function updateTarifs() {
        const tarif = selectedTarif();
        const total = document.getElementById('avTotal');
        if (total) {
            total.textContent = tarif
                ? (Number(tarif.dataset.prix) > 0 ? formatMontant(tarif.dataset.prix) + ' F' : 'Gratuit')
                : '—';
        }
        document.querySelectorAll('.av-tarif').forEach(function (carte) {
            carte.classList.toggle('is-selected', !!tarif && carte.contains(tarif));
        });
        updatePaiement();
    }

    function updatePaiement() {
        const mobile = selectedPaiement() === 'mobile_money';
        const label = document.getElementById('btnVenteLabel');
        const hint = document.getElementById('avHint');
        if (label) label.textContent = mobile ? 'Payer avec Mobile Money' : labelVente;
        if (hint) {
            hint.innerHTML = mobile
                ? '<i class="bi bi-phone" aria-hidden="true"></i> L\'acheteur paiera via Mobile Money.'
                : '<i class="bi bi-whatsapp" aria-hidden="true"></i> Le billet QR sera envoyé par e-mail.';
        }
        document.querySelectorAll('.av-pay-option').forEach(function (option) {
            const input = option.querySelector('input');
            option.classList.toggle('is-selected', !!input && input.checked);
        });
    }

    function saleCard(t) {
        const gratuit = Number(t.montant_val) <= 0;
        const montant = gratuit ? 'Gratuit' : escapeHtml(t.montant);
        const methode = gratuit ? '—' : escapeHtml(t.methode_label);
        const phone = t.whatsapp || t.telephone || '';
        const phoneHtml = phone
            ? '<span class="av-sale-phone">' + escapeHtml(phone) + '</span>'
            : '<span class="av-sale-phone"></span>';
        return '<article class="av-sale">'
            + '<div class="av-sale-top"><span class="av-sale-time">' + escapeHtml(t.date) + '</span>'
            + '<span class="av-sale-amount">' + montant + '</span></div>'
            + '<strong class="av-sale-name">' + escapeHtml(t.nom) + '</strong>'
            + '<div class="av-sale-meta">' + escapeHtml(t.tarif) + ' · ' + methode + '</div>'
            + '<div class="av-sale-bottom">' + phoneHtml
            + '<a class="av-pdf" href="/vente/tickets/' + encodeURIComponent(t.id) + '/pdf" target="_blank" rel="noopener" aria-label="Ouvrir le billet PDF de ' + escapeHtml(t.nom) + '">'
            + '<i class="bi bi-filetype-pdf" aria-hidden="true"></i> PDF</a></div>'
            + '</article>';
    }

    function renderHistorique(data) {
        const ids = {
            statTotalTickets: data.total_tickets,
            statMontantTotal: data.montant_total,
            statAujourdHui: data.aujourd_hui,
            statMontantAjd: data.montant_ajd
        };
        Object.keys(ids).forEach(function (id) {
            const element = document.getElementById(id);
            if (element && data[id] !== undefined) element.textContent = data[id];
        });

        const body = document.getElementById('historiqueBody');
        if (!body || !Array.isArray(data.tickets)) return;
        body.innerHTML = data.tickets.length
            ? data.tickets.map(saleCard).join('')
            : '<div class="av-empty"><i class="bi bi-inbox" aria-hidden="true"></i><span>Aucune vente aujourd\'hui</span></div>';
    }

    function chargerHistorique() {
        if (document.hidden) return;
        fetch(routeHistorique, { headers: { 'Accept': 'application/json' } })
            .then(function (response) { return response.ok ? response.json() : null; })
            .then(function (data) { if (data) renderHistorique(data); })
            .catch(function () {});
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[name="tarif_id"]').forEach(function (input) {
            input.addEventListener('change', updateTarifs);
        });
        document.querySelectorAll('input[name="methode_paiement"]').forEach(function (input) {
            input.addEventListener('change', updatePaiement);
        });
        updateTarifs();

        if (form) {
            form.addEventListener('submit', function () {
                if (selectedPaiement() !== 'mobile_money') return;
                const bouton = document.getElementById('btnVente');
                const label = document.getElementById('btnVenteLabel');
                if (bouton) bouton.disabled = true;
                if (label) label.textContent = 'Redirection vers le paiement…';
            });
        }

        document.querySelectorAll('.js-alert-close').forEach(function (bouton) {
            bouton.addEventListener('click', function () {
                const alerte = bouton.closest('.av-alert');
                if (alerte) alerte.remove();
            });
        });

        const alerte = document.getElementById('successAlert');
        if (alerte) setTimeout(function () { alerte.remove(); }, 8000);

        chargerHistorique();
        setInterval(chargerHistorique, 10000);
    });
})();
</script>
@endpush
