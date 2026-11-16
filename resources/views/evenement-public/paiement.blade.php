@extends('layouts.public')

@section('title', 'Paiement — ' . $ticket->evenement->titre . ' — PaxEvent')
@section('description', 'Finalisez votre achat de billets pour ' . $ticket->evenement->titre . ' sur PaxEvent. Paiement sécurisé.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('evenements.public') }}">Evenements</a></li>
    <li class="breadcrumb-item"><a href="{{ route('evenements.public.show', $ticket->evenement) }}">{{ Str::limit($ticket->evenement->titre, 30) }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Paiement</li>
@endsection

@section('content')
@php
    $evenement = $ticket->evenement;
    $textes = $evenement->getTextes();
    $premiereDate = $evenement->dates->first()?->date_debut;
    $infos = collect([
        $premiereDate ? Str::ucfirst($premiereDate->isoFormat('dddd D MMMM')) : null,
        $premiereDate?->format('H:i'),
        $evenement->lieu,
    ])->filter()->implode(' · ');
    if ($evenement->dates->count() > 1) {
        $infos = 'Du ' . $evenement->dates->first()->date_debut->isoFormat('D MMM') . ' au ' . $evenement->dates->last()->date_debut->isoFormat('D MMM') . ($evenement->lieu ? ' · ' . $evenement->lieu : '');
    }
    $fcfa = fn ($n) => $n <= 0 ? 'Gratuit' : number_format($n, 0, ',', ' ') . ' FCFA';
@endphp
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                @if(session('error'))
                    <div class="alert alert-danger py-2" style="font-size: 0.85rem; border-radius: 10px;">
                        <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
                    </div>
                @endif

                <div class="cmd-carte">
                    <div class="cmd-entete" @if($evenement->image) style="background-image:linear-gradient(180deg,rgba(33,28,49,.15),rgba(33,28,49,.85)),url('{{ asset('storage/' . $evenement->image) }}');" @endif>
                        <div class="cmd-kicker">Récapitulatif</div>
                        <h1 class="cmd-titre">{{ $evenement->titre }}</h1>
                    </div>
                    <div class="cmd-corps">
                        @if($infos)
                            <p class="cmd-infos">{{ $infos }}</p>
                        @endif

                        @foreach($lignesCommande as $ligne)
                            <div class="cmd-ligne">
                                <span>{{ $ligne['quantite'] }} × {{ $ligne['nom'] }}</span>
                                <strong>{{ $fcfa($ligne['montant']) }}</strong>
                            </div>
                        @endforeach

                        @if($ticket->code_promo_utilise)
                            <div class="cmd-ligne">
                                <span class="badge" style="background: rgba(84,38,128,0.1); color: #542680; font-size: 0.75rem;">
                                    <i class="bi bi-tag me-1"></i>Code promo : {{ $ticket->code_promo_utilise }}
                                </span>
                            </div>
                        @endif

                        <div class="cmd-total">
                            <span>Total</span>
                            <strong>{{ $fcfa($montantTotal) }}</strong>
                        </div>

                        <p class="cmd-envoi">
                            <i class="bi bi-envelope me-2"></i>
                            @if($nombreBillets > 1)
                                Les {{ $nombreBillets }} {{ mb_strtolower($textes['billet_pluriel']) }} seront envoyés à <strong>{{ $ticket->email_acheteur }}</strong>
                            @else
                                Votre {{ mb_strtolower($textes['billet']) }} PDF sera envoyé à <strong>{{ $ticket->email_acheteur }}</strong>
                            @endif
                        </p>

                        @php
                            $etape = match ($etat['etat']) { 'verification' => 2, 'paye' => 4, default => 1 };
                            $refCommande = $ticket->transaction_id;
                        @endphp
                        <ol class="pay-etapes" aria-label="Étapes de la commande">
                            @foreach(['Commande', 'Paiement', 'Confirmation', 'Billets envoyés'] as $i => $libelle)
                                <li class="{{ $i < $etape ? 'fait' : ($i === $etape ? 'actif' : '') }}" data-etape="{{ $i }}">
                                    <span class="pay-pastille">@if($i < $etape)<i class="bi bi-check-lg"></i>@else{{ $i + 1 }}@endif</span>
                                    <span class="pay-libelle">{{ $libelle }}</span>
                                </li>
                            @endforeach
                        </ol>

                        {{-- À payer --}}
                        <div class="pay-etat" data-etat="a_payer" @if($etat['etat'] !== 'a_payer') hidden @endif>
                            @if($publicKey)
                                <button type="button" id="btnFedaPay" class="show-btn-payer">
                                    <i class="bi bi-shield-lock me-2"></i> Payer {{ number_format($montantTotal, 0, ',', ' ') }} FCFA
                                </button>
                            @else
                                <div class="pay-alerte pay-alerte-avert">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <div>
                                        <strong>Le paiement en ligne est momentanément indisponible.</strong>
                                        <p>Vos places restent réservées le temps indiqué ci-dessous. Réessayez dans quelques minutes.</p>
                                        <button type="button" class="pay-btn-secondaire" onclick="location.reload()"><i class="bi bi-arrow-clockwise me-1"></i> Réessayer</button>
                                    </div>
                                </div>
                            @endif
                            <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                                <small class="text-muted" style="font-size: 0.75rem;">Paiement sécurisé par</small>
                                <strong style="color: #542680; font-size: 0.9rem;">FedaPay</strong>
                            </div>
                            <div class="pay-chrono" id="payChrono" @if($etat['secondes_restantes'] === null) hidden @endif>
                                <i class="bi bi-clock me-1"></i> Places réservées encore <strong id="payChronoValeur">--:--</strong>
                            </div>
                            <div id="payFenetre" class="pay-alerte pay-alerte-info mt-3" hidden>
                                <i class="bi bi-window-stack"></i>
                                <div>
                                    <strong id="payFenetreTitre">Fenêtre de paiement FedaPay ouverte</strong>
                                    <p id="payFenetreTexte">Choisissez votre moyen de paiement puis validez la demande sur votre téléphone. Ne fermez pas la fenêtre avant la fin.</p>
                                    <button type="button" class="pay-btn-secondaire" id="btnRouvrir" hidden><i class="bi bi-arrow-clockwise me-1"></i> Rouvrir la fenêtre de paiement</button>
                                </div>
                            </div>
                            <div id="paymentError" class="pay-alerte pay-alerte-info mt-3" hidden>
                                <i class="bi bi-info-circle"></i>
                                <div>
                                    <strong id="paymentErrorTitre">Paiement non finalisé</strong>
                                    <p id="paymentErrorTexte">La fenêtre de paiement a été fermée. Aucun montant n'a été débité.</p>
                                    <button type="button" class="pay-btn-secondaire" id="btnReessayer"><i class="bi bi-arrow-clockwise me-1"></i> Réessayer le paiement</button>
                                </div>
                            </div>
                        </div>

                        {{-- Paiement envoyé, en attente de confirmation --}}
                        <div class="pay-etat" data-etat="verification" @if($etat['etat'] !== 'verification') hidden @endif>
                            <div class="pay-attente">
                                <div class="pay-progression" role="progressbar" aria-label="Confirmation du paiement en cours"><span></span></div>
                                <strong id="payAttenteTitre">Confirmation de votre paiement…</strong>
                                <p id="payAttenteTexte">Validez la demande sur votre téléphone si ce n'est pas déjà fait. Cette page se met à jour toute seule.</p>
                            </div>
                            <div class="pay-alerte pay-alerte-info mt-3" id="payAttenteLongue" hidden>
                                <i class="bi bi-hourglass-split"></i>
                                <div>
                                    <strong>La confirmation prend plus de temps que prévu.</strong>
                                    <p>C'est fréquent avec le mobile money. Si vous avez été débité, vos billets vous seront envoyés à <strong>{{ $ticket->email_acheteur }}</strong> dès la confirmation : vous pouvez fermer cette page.</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="pay-btn-secondaire" id="btnVerifier"><i class="bi bi-arrow-clockwise me-1"></i> Vérifier à nouveau</button>
                                        <a class="pay-btn-lien" href="{{ route('contact') }}?sujet={{ urlencode('Paiement en attente — commande '.$refCommande) }}"><i class="bi bi-life-preserver me-1"></i> Contacter le support</a>
                                    </div>
                                    <small class="d-block mt-2 text-muted">Référence de commande : <code>{{ $refCommande }}</code></small>
                                </div>
                            </div>
                        </div>

                        {{-- Paiement confirmé (bascule automatique) --}}
                        <div class="pay-etat" data-etat="paye" hidden>
                            <div class="pay-alerte pay-alerte-ok">
                                <i class="bi bi-check-circle-fill"></i>
                                <div><strong>Paiement confirmé !</strong><p>Redirection vers vos billets…</p></div>
                            </div>
                        </div>

                        {{-- Échecs : cause en clair + action --}}
                        @foreach([
                            'refuse' => ['bi-x-octagon', 'Le paiement n\'a pas abouti', 'Il a été refusé ou annulé par le service de paiement. Les places ont été libérées. Vous pouvez refaire votre commande.'],
                            'expire' => ['bi-alarm', 'Votre réservation a expiré', 'Le délai de paiement est dépassé et les places ont été libérées. Aucun montant n\'a été débité. Vous pouvez refaire votre commande s\'il reste des places.'],
                            'annule' => ['bi-slash-circle', 'Cette commande n\'est plus active', 'Elle a été annulée ou remboursée. Vous pouvez passer une nouvelle commande.'],
                        ] as $cle => [$icone, $titre, $texte])
                            <div class="pay-etat" data-etat="{{ $cle }}" @if($etat['etat'] !== $cle) hidden @endif>
                                <div class="pay-alerte pay-alerte-erreur">
                                    <i class="bi {{ $icone }}"></i>
                                    <div>
                                        <strong>{{ $titre }}</strong>
                                        <p>{{ $texte }}</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a class="show-btn-payer d-inline-block w-auto px-4 text-decoration-none" href="{{ route('evenements.public.show', $evenement) }}"><i class="bi bi-arrow-repeat me-1"></i> Refaire ma commande</a>
                                            <a class="pay-btn-lien" href="{{ route('contact') }}?sujet={{ urlencode('Problème de paiement — commande '.$refCommande) }}">J'ai été débité</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('evenements.public.show', $evenement) }}" style="color:#542680; font-size:0.85rem; font-weight:600; text-decoration:none;">
                        <i class="bi bi-arrow-left me-1"></i> Retour à l'événement
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.cmd-carte{background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 6px 24px rgba(33,28,49,.08)}
.cmd-entete{min-height:200px;display:flex;flex-direction:column;justify-content:flex-end;padding:1.25rem 1.5rem;background:linear-gradient(135deg,#542680,#9972B0);background-size:cover;background-position:center;color:#fff}
.cmd-kicker{font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;opacity:.85}
.cmd-titre{font-size:1.6rem;font-weight:800;margin:.2rem 0 0;color:#fff}
.cmd-corps{padding:1.25rem 1.5rem 1.5rem}
.cmd-infos{color:#6b6474;font-size:.92rem;margin-bottom:.9rem}
.cmd-ligne{display:flex;justify-content:space-between;gap:1rem;padding:.35rem 0;font-size:.95rem;color:#211C31}
.cmd-total{display:flex;justify-content:space-between;align-items:baseline;border-top:2px dashed #ddd6e3;margin-top:.8rem;padding-top:1rem;font-weight:700;color:#211C31}
.cmd-total strong{font-size:1.9rem;font-weight:800}
.cmd-envoi{font-size:.88rem;color:#4a4453;margin:.6rem 0 1.2rem}
.show-btn-payer{width:100%;border:0;border-radius:12px;padding:.95rem;background:#542680;color:#fff;font-weight:700;font-size:1rem}
.show-btn-payer:hover{background:#45206a}
.pay-etapes{list-style:none;display:flex;gap:.25rem;padding:0;margin:0 0 1.25rem;counter-reset:e}
.pay-etapes li{flex:1;display:flex;flex-direction:column;align-items:center;gap:.3rem;position:relative;font-size:.7rem;color:#9a93a3;text-align:center}
.pay-etapes li:not(:last-child)::after{content:"";position:absolute;top:13px;left:calc(50% + 16px);right:calc(-50% + 16px);height:2px;background:#e6e0ec}
.pay-etapes li.fait:not(:last-child)::after{background:#542680}
.pay-pastille{width:28px;height:28px;border-radius:50%;display:grid;place-items:center;background:#f1ebf6;color:#9a93a3;font-weight:700;font-size:.8rem;transition:all .3s}
.pay-etapes li.fait .pay-pastille{background:#542680;color:#fff}
.pay-etapes li.actif .pay-pastille{background:#fff;color:#542680;box-shadow:0 0 0 2px #542680}
.pay-etapes li.actif{color:#542680;font-weight:700}
.pay-chrono{text-align:center;margin-top:.6rem;font-size:.82rem;color:#6b6474}
.pay-chrono.urgent{color:#c0392b}
.pay-alerte{display:flex;gap:.75rem;padding:.9rem 1rem;border-radius:12px;font-size:.88rem;text-align:left}
.pay-alerte>i{font-size:1.25rem;line-height:1.2}
.pay-alerte p{margin:.25rem 0 .6rem;color:#4a4453}
.pay-alerte-info{background:#f4f0f8;color:#542680}
.pay-alerte-avert{background:#fff7e6;color:#8a5a00}
.pay-alerte-erreur{background:#fdecec;color:#a12a2a}
.pay-alerte-ok{background:#e9f8ef;color:#1e7a46}
.pay-btn-secondaire{border:1px solid #542680;background:#fff;color:#542680;border-radius:10px;padding:.45rem .9rem;font-weight:600;font-size:.85rem}
.pay-btn-secondaire:disabled{opacity:.6}
.pay-btn-lien{display:inline-flex;align-items:center;padding:.45rem .5rem;color:#542680;font-weight:600;font-size:.85rem;text-decoration:none}
.pay-attente{text-align:center;padding:.5rem 0}
.pay-attente strong{display:block;margin-top:1rem;color:#211C31}
.pay-attente p{font-size:.88rem;color:#6b6474;margin:.3rem 0 0}
.pay-progression{height:6px;border-radius:6px;background:#f1ebf6;overflow:hidden;position:relative}
.pay-progression span{position:absolute;inset:0 auto 0 0;width:35%;border-radius:6px;background:linear-gradient(90deg,#9972B0,#542680);animation:pay-glisse 1.4s ease-in-out infinite}
@keyframes pay-glisse{0%{left:-35%}100%{left:100%}}
@media (prefers-reduced-motion: reduce){.pay-progression span{animation:none;width:100%;opacity:.5}}
</style>
@endsection

@section('scripts')
<script src="https://cdn.fedapay.com/checkout.js?v=1.1.3"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statutUrl = @json(route('paiement.statut', $ticket->id));
    const callbackUrl = @json(route('paiement.callback')) + '?ticket={{ $ticket->id }}';
    let etat = @json($etat['etat']);
    let secondes = @json($etat['secondes_restantes']);
    const debutAttente = Date.now();

    // ---------- Affichage d'un état ----------
    const etapeDe = { a_payer: 1, verification: 2, paye: 4 };
    function afficher(nouvel) {
        etat = nouvel;
        document.querySelectorAll('.pay-etat').forEach(el => el.hidden = el.dataset.etat !== nouvel);
        const etape = etapeDe[nouvel] ?? 1;
        document.querySelectorAll('.pay-etapes li').forEach(li => {
            const i = Number(li.dataset.etape);
            li.classList.toggle('fait', i < etape);
            li.classList.toggle('actif', i === etape);
        });
        if (nouvel === 'verification') demarrerSuivi();
    }

    // ---------- Compte à rebours de la réservation ----------
    const chrono = document.getElementById('payChrono');
    const chronoValeur = document.getElementById('payChronoValeur');
    function majChrono() {
        if (secondes === null || etat !== 'a_payer') return;
        const m = Math.floor(secondes / 60), s = secondes % 60;
        chronoValeur.textContent = m + ':' + String(s).padStart(2, '0');
        chrono.classList.toggle('urgent', secondes <= 120);
        if (secondes <= 0) { verifier(); return; }
        secondes--;
    }
    if (secondes !== null) { majChrono(); setInterval(majChrono, 1000); }

    // ---------- Suivi du statut (attente de confirmation) ----------
    let suivi = null;
    async function verifier() {
        try {
            const r = await fetch(statutUrl, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
            if (!r.ok) return;
            const d = await r.json();
            if (d.secondes_restantes !== null) secondes = d.secondes_restantes;
            if (d.etat === 'paye' && d.redirection) {
                afficher('paye');
                setTimeout(() => window.location.href = d.redirection, 900);
                return;
            }
            if (d.etat !== etat) afficher(d.etat);
        } catch (e) { /* réseau coupé : on réessaie au prochain passage */ }
    }
    function demarrerSuivi() {
        if (suivi) return;
        const titre = document.getElementById('payAttenteTitre');
        const texte = document.getElementById('payAttenteTexte');
        const longue = document.getElementById('payAttenteLongue');
        suivi = setInterval(function () {
            const ecoule = (Date.now() - debutAttente) / 1000;
            if (etat !== 'verification') { clearInterval(suivi); suivi = null; return; }
            if (ecoule > 30 && ecoule <= 120) {
                titre.textContent = 'Toujours en cours de confirmation…';
                texte.textContent = 'Ça prend un peu plus de temps que d\'habitude. Gardez cette page ouverte.';
            } else if (ecoule > 120) {
                titre.textContent = 'Paiement en cours de vérification';
                texte.textContent = 'Nous attendons la confirmation de l\'opérateur.';
                longue.hidden = false;
            }
            verifier();
        }, 4000);
    }
    const btnVerifier = document.getElementById('btnVerifier');
    if (btnVerifier) btnVerifier.addEventListener('click', async function () {
        btnVerifier.disabled = true;
        await verifier();
        setTimeout(() => btnVerifier.disabled = false, 2000);
    });
    if (etat === 'verification') demarrerSuivi();

    // ---------- Paiement FedaPay ----------
    const btn = document.getElementById('btnFedaPay');
    const errorDiv = document.getElementById('paymentError');
    function erreur(titre, texte, peutReessayer) {
        document.getElementById('paymentErrorTitre').textContent = titre;
        document.getElementById('paymentErrorTexte').textContent = texte;
        document.getElementById('btnReessayer').hidden = !peutReessayer;
        errorDiv.hidden = false;
    }
    document.getElementById('btnReessayer').addEventListener('click', function () {
        if (typeof FedaPay === 'undefined') { window.location.reload(); return; }
        errorDiv.hidden = true;
        btn && btn.click();
    });
    if (!btn) return;

    // La fenêtre FedaPay s'ouvre par-dessus la page : on indique derrière ce
    // qui se passe, et on détecte une fenêtre qui ne s'est jamais affichée
    // (connexion lente, bloqueur…) pour proposer de la rouvrir.
    const fenetre = document.getElementById('payFenetre');
    const btnRouvrir = document.getElementById('btnRouvrir');
    let surveillanceFenetre = null;
    function fenetreFedaPayVisible() {
        return Array.from(document.querySelectorAll('iframe')).some(f =>
            /fedapay/i.test(f.src || f.name || f.id || '') && f.offsetParent !== null);
    }
    btn.addEventListener('click', function () {
        if (typeof FedaPay === 'undefined') return;
        errorDiv.hidden = true;
        fenetre.hidden = false;
        btnRouvrir.hidden = true;
        document.getElementById('payFenetreTitre').textContent = 'Ouverture de la fenêtre de paiement…';
        clearTimeout(surveillanceFenetre);
        let essais = 0;
        (function surveiller() {
            if (fenetreFedaPayVisible()) {
                document.getElementById('payFenetreTitre').textContent = 'Fenêtre de paiement FedaPay ouverte';
                return;
            }
            if (++essais >= 10) { // ~10 s sans fenêtre
                document.getElementById('payFenetreTitre').textContent = 'La fenêtre de paiement ne s\'est pas affichée';
                document.getElementById('payFenetreTexte').textContent = 'Votre connexion est peut-être lente. Aucun montant n\'a été débité.';
                btnRouvrir.hidden = false;
                return;
            }
            surveillanceFenetre = setTimeout(surveiller, 1000);
        })();
    });
    btnRouvrir.addEventListener('click', () => btn.click());

    if (typeof FedaPay === 'undefined') {
        btn.disabled = true;
        erreur('Le module de paiement ne s\'est pas chargé', 'Vérifiez votre connexion internet puis rechargez la page.', true);
        document.getElementById('btnReessayer').innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Recharger la page';
        return;
    }

    const nameParts = @json(trim((string) $ticket->nom_acheteur)).split(' ');
    const firstname = nameParts.slice(0, -1).join(' ') || nameParts[0] || 'Client';
    const lastname = nameParts.length > 1 ? nameParts[nameParts.length - 1] : '';

    FedaPay.init(btn, {
        public_key: @json($publicKey),
        environment: @json($sandbox ? 'sandbox' : 'live'),
        transaction: {
            amount: {{ (int) $montantTotal }},
            description: @json('Ticket - '.$ticket->evenement->titre),
            external_id: '{{ $ticket->id }}',
            custom_metadata: {
                ticket_id: '{{ $ticket->id }}',
                group_transaction_id: @json($ticket->transaction_id)
            }
        },
        customer: {
            email: @json($ticket->email_acheteur),
            firstname: firstname,
            lastname: lastname
        },
        currency: { iso: 'XOF' },
        onComplete: function(data) {
            clearTimeout(surveillanceFenetre);
            fenetre.hidden = true;
            if (data.transaction && data.transaction.id) {
                // Transaction créée : on montre tout de suite l'attente de
                // confirmation, le temps que le serveur vérifie le paiement.
                afficher('verification');
                const pm = data.transaction.payment_method || 'mobile_money';
                const ph = data.transaction.phone || '';
                window.location.href = callbackUrl + '&id=' + encodeURIComponent(data.transaction.id)
                    + '&status=' + encodeURIComponent(data.transaction.status || 'approved')
                    + '&payment_method=' + encodeURIComponent(pm) + '&phone=' + encodeURIComponent(ph);
            } else {
                erreur('Paiement non finalisé', 'La fenêtre de paiement a été fermée avant la fin. Aucun montant n\'a été débité.', true);
            }
        }
    });
});
</script>
@endsection
