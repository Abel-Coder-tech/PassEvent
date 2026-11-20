@extends('layouts.agent-vente')

@section('title', 'Historique des ventes — ' . $agent->evenement->titre)

@section('content')
<div class="av-app">
    @include('agent-vente.partials.header', ['section' => 'Historique'])

    <main class="av-main">
        <section class="av-card" aria-labelledby="avHistoriqueTitle">
            <div class="av-card-head">
                <div>
                    <span class="av-kicker">Suivi</span>
                    <h1 id="avHistoriqueTitle">Historique des ventes</h1>
                </div>
                <a class="av-link av-back" href="{{ route('agent-vente.dashboard') }}">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i> Point de vente
                </a>
            </div>

            <div class="av-filters" role="group" aria-label="Filtrer par période">
                @foreach ([
                    'aujourdhui' => "Aujourd'hui",
                    'hier' => 'Hier',
                    'semaine' => 'Cette semaine',
                    'mois' => 'Ce mois',
                    'tout' => 'Tout',
                ] as $value => $label)
                    <a class="av-filter{{ $periode === $value ? ' is-active' : '' }}"
                       href="{{ route('agent-vente.historique', ['periode' => $value]) }}"
                       @if ($periode === $value) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </div>

            <div class="av-summary" style="padding: 0 16px 14px;" aria-label="Récapitulatif de la page">
                <article class="av-stat">
                    <span class="av-stat-icon av-stat-icon--violet"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span>
                    <div><strong>{{ $tickets->total() }}</strong><span>Vente(s) au total</span></div>
                </article>
                <article class="av-stat">
                    <span class="av-stat-icon av-stat-icon--green"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
                    <div><strong>{{ number_format($montantFiltre, 0, ',', ' ') }} F</strong><span>Montant de la page</span></div>
                </article>
            </div>

            <div class="av-sales" style="padding-top: 0;">
                @forelse ($tickets as $ticket)
                    @include('agent-vente.partials.vente-card', ['ticket' => $ticket, 'afficherDate' => true])
                @empty
                    <div class="av-empty"><i class="bi bi-inbox" aria-hidden="true"></i><span>Aucune vente pour cette période</span></div>
                @endforelse
            </div>

            <div class="av-pagination">
                {{ $tickets->appends(['periode' => $periode])->links() }}
            </div>
        </section>
    </main>
</div>
@endsection

@push('styles')
@include('agent-vente.partials.styles')
@endpush
