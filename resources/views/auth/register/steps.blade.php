@php
    $steps = [
        0 => ['label' => 'Compte', 'route' => 'inscriptions.create'],
        1 => ['label' => 'Identité', 'route' => 'inscriptions.identity'],
        2 => ['label' => 'Organisation', 'route' => 'inscriptions.org'],
        3 => ['label' => 'Récapitulatif', 'route' => 'inscriptions.recap'],
    ];
@endphp
@foreach($steps as $i => $step)
    @if($i > 0)
        <div class="step-connector {{ $i <= $current ? 'done' : '' }}"></div>
    @endif
    <div class="step-item
        {{ $i < $current ? 'done' : '' }}
        {{ $i === $current ? 'active' : '' }}">
        <span class="step-number">
            @if($i < $current)
                <i class="bi bi-check" style="font-size:0.7rem;"></i>
            @else
                {{ $i + 1 }}
            @endif
        </span>
        {{ $step['label'] }}
    </div>
@endforeach
