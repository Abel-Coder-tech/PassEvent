@if($t->statut_paiement === 'payé' && ! $t->annule && $t->expire_at === null && $t->email_acheteur)
    <form method="POST" action="{{ route('superadmin.tickets.renvoyer', $t) }}" class="d-inline"
          onsubmit="return confirm('Renvoyer le billet {{ $t->code_unique }} par email ?')">
        @csrf
        <button type="submit" class="sa-btn sa-btn-sm"
                style="background:var(--sa-primary);border:none;color:#fff;padding:0.25rem 0.5rem;border-radius:6px;font-size:0.7rem;font-weight:600;display:inline-flex;align-items:center;gap:0.3rem;"
                title="Renvoyer le ticket à {{ $t->email_acheteur }}">
            <i class="bi bi-envelope-paper"></i> Renvoyer
        </button>
    </form>
@else
    <span class="text-muted">—</span>
@endif
