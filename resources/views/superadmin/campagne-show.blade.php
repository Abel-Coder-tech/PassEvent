@extends('superadmin.layouts.master')

@php
    $ev = $message->evenement;
    $noteDefaut = 'Bonjour '.($message->nom_complet ?: 'cher organisateur').",\n\nNous avons bien reçu votre demande de campagne marketing et nous revenons vers vous rapidement.\n\nCordialement,\nL'équipe PaxEvent";
    if ($message->reponse_admin) {
        $noteDefaut = $message->reponse_admin;
    }
    $nlObjet = $ev ? ('🎉 '.$ev->titre.' — réservez vos billets !') : '';
    $nlMessage = $ev
        ? ('🎉 '.$ev->titre." arrive bientôt !\n\n"
            .($ev->date_event ? '📅 '.$ev->date_event->isoFormat('D MMM YYYY HH:mm')."\n" : '')
            .($ev->lieu ? '📍 '.$ev->lieu."\n" : '')
            ."\nRéservez vos billets dès maintenant sur PaxEvent :\n"
            .route('evenements.public.show', $ev)."\n\nÉquipe PaxEvent")
        : '';
@endphp

@section('title', 'Demande de campagne - Super Admin')
@section('page-title', 'Demande de campagne')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('superadmin.campagnes-marketing') }}" class="text-decoration-none small" style="color: var(--sa-primary);">
        <i class="bi bi-arrow-left"></i> Toutes les demandes de campagne
    </a>
    @if($message->lu)
        <span class="sa-badge sa-badge-success">Lu</span>
    @else
        <span class="sa-badge sa-badge-warning">Non lu</span>
    @endif
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="sa-card h-100">
            <div class="sa-card-header">
                <span><i class="bi bi-megaphone-fill me-2" style="color: var(--sa-primary);"></i>Demande de campagne</span>
                <span class="text-muted" style="font-size:0.8rem;">{{ $message->created_at->isoFormat('D MMM YYYY HH:mm') }}</span>
            </div>
            <div class="sa-card-body">
                <div class="cp-detail-row">
                    <span class="cp-detail-label">Organisateur</span>
                    <span class="cp-detail-value"><strong>{{ $message->nom_complet }}</strong></span>
                </div>
                <div class="cp-detail-row">
                    <span class="cp-detail-label">Email</span>
                    <span class="cp-detail-value">{{ $message->email }}</span>
                </div>
                @if($message->telephone)
                <div class="cp-detail-row">
                    <span class="cp-detail-label">Téléphone</span>
                    <span class="cp-detail-value">{{ $message->telephone }}</span>
                </div>
                @endif
                <div class="cp-detail-row">
                    <span class="cp-detail-label">Événement</span>
                    <span class="cp-detail-value">
                        @if($ev)
                            <a href="{{ route('superadmin.evenements.voir', $ev) }}" class="text-decoration-none" style="color:var(--sa-primary);font-weight:600;">
                                <i class="bi bi-calendar-event me-1"></i>{{ $ev->titre }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </span>
                </div>
                <div class="cp-detail-row">
                    <span class="cp-detail-label">Objet</span>
                    <span class="cp-detail-value">{{ $message->objet }}</span>
                </div>
                <div class="cp-detail-row" style="border-bottom:none;">
                    <span class="cp-detail-label">Message</span>
                    <span class="cp-detail-value" style="white-space:pre-wrap;">{{ $message->message }}</span>
                </div>
            </div>
        </div>

        <div class="sa-card mt-3">
            <div class="sa-card-header">
                <span><i class="bi bi-send me-2" style="color: var(--sa-success);"></i>Répondre à l'organisateur</span>
            </div>
            <div class="sa-card-body">
                <form action="{{ route('superadmin.notifications.repondre', $message) }}" method="POST">
                    @csrf
                    <textarea name="note" class="form-control" rows="6" style="width:100%;border:1px solid #ddd;border-radius:8px;padding:0.5rem 0.7rem;font-size:0.85rem;resize:vertical;">{{ $noteDefaut }}</textarea>
                    <div style="display:flex;justify-content:flex-end;margin-top:0.7rem;">
                        <button type="submit" class="sa-btn" style="background:#3b82f6;border:none;color:#fff;padding:0.5rem 1.2rem;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer;">
                            <i class="bi bi-send me-1"></i> Envoyer la réponse
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="sa-card h-100">
            <div class="sa-card-header">
                <span><i class="bi bi-lightning-charge-fill me-2" style="color: var(--sa-primary);"></i>Actions rapides (booster)</span>
            </div>
            <div class="sa-card-body">
                @if($ev)
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a href="{{ route('superadmin.evenements.voir', $ev) }}" class="sa-btn sa-btn-outline" style="text-decoration:none;">
                            <i class="bi bi-calendar-event"></i> Voir l'événement
                        </a>
                        @if($ev->statut !== 'publié')
                            <form action="{{ route('superadmin.evenements.mettre-en-avant', $ev) }}" method="POST">
                                @csrf
                                <button type="submit" class="sa-btn" style="background:var(--sa-success);color:#fff;border:none;" title="Publier / remettre en avant">
                                    <i class="bi bi-check-lg"></i> Publier
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('superadmin.evenements.a-la-une', $ev) }}" method="POST">
                            @csrf
                            <button type="submit" class="sa-btn {{ $ev->a_la_une ? 'sa-btn-danger' : 'sa-btn-outline' }}" title="{{ $ev->a_la_une ? 'Retirer de la une' : 'Mettre à la une' }}">
                                <i class="bi bi-{{ $ev->a_la_une ? 'star-fill' : 'star' }}"></i>
                                {{ $ev->a_la_une ? 'Retirer de la une' : 'Mettre à la une' }}
                            </button>
                        </form>
                        @if($ev->a_la_une)
                            <form action="{{ route('superadmin.evenements.a-la-une.ordre', [$ev, 'haut']) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-outline" title="Monter dans la une"><i class="bi bi-arrow-up"></i></button>
                            </form>
                            <form action="{{ route('superadmin.evenements.a-la-une.ordre', [$ev, 'bas']) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-outline" title="Descendre dans la une"><i class="bi bi-arrow-down"></i></button>
                            </form>
                        @endif
                        <button type="button" class="sa-btn" style="background:#7B3FA0;color:#fff;border:none;"
                            data-prefill-id="single" onclick="ouvrirNewsletter(this)" title="Envoyer une newsletter">
                            <i class="bi bi-send-fill"></i> Newsletter
                        </button>
                    </div>
                    <div class="small text-muted">
                        <i class="bi bi-info-circle me-1"></i>Statut : <strong>{{ $ev->statut }}</strong>
                        @if($ev->a_la_une) · À la une @endif
                    </div>
                @else
                    <p class="text-muted small mb-0">Aucun événement lié à cette demande.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@include('superadmin.partials.newsletter-modal')

<style>
.cp-detail-row { display:flex;gap:1rem;padding:0.5rem 0;border-bottom:1px solid #f5f5f5;font-size:0.85rem; }
.cp-detail-row:last-child { border-bottom:none; }
.cp-detail-label { font-weight:600;color:#666;min-width:110px;flex-shrink:0; }
.cp-detail-value { color:#1a1a1a;word-break:break-word; }
</style>

@push('scripts')
<script>
const NEWSLETTER_PREFILL = { single: @json(['objet' => $nlObjet, 'message' => $nlMessage]) };
</script>
@endpush
@endsection