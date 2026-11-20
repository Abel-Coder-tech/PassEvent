<article class="av-sale">
    <div class="av-sale-top">
        <span class="av-sale-time">{{ $ticket->date_achat->format(! empty($afficherDate) ? 'd/m/Y H:i' : 'H:i') }}</span>
        <span class="av-sale-amount">{{ $ticket->montant > 0 ? number_format($ticket->montant, 0, ',', ' ') . ' F' : 'Gratuit' }}</span>
    </div>
    <strong class="av-sale-name">{{ $ticket->nom_acheteur }}</strong>
    <div class="av-sale-meta">
        {{ $ticket->tarif?->getLabel() ?? 'N/A' }} · {{ $ticket->montant > 0 ? \App\Models\Ticket::methodePaiementLabel($ticket->methode_paiement) : '—' }}
    </div>
    <div class="av-sale-bottom">
        <span class="av-sale-phone">{{ $ticket->whatsapp_acheteur ?? $ticket->telephone_paiement ?? $ticket->telephone_acheteur ?? '' }}</span>
        <a class="av-pdf" href="{{ route('agent-vente.ticket.pdf', $ticket) }}" target="_blank" rel="noopener" aria-label="Ouvrir le billet PDF de {{ $ticket->nom_acheteur }}">
            <i class="bi bi-filetype-pdf" aria-hidden="true"></i> PDF
        </a>
    </div>
</article>
