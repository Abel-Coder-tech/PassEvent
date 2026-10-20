<div class="empty-state">
    <i class="bi {{ $icon ?? 'bi-inbox' }}"></i>
    <p class="empty-title">{{ $title ?? 'Rien à afficher' }}</p>
    <p class="text-muted small mb-0">{{ $text ?? '' }}</p>
    @isset($action)
        <a href="{{ $action }}" class="btn btn-violet btn-sm mt-3">{{ $actionLabel ?? 'Continuer' }}</a>
    @endisset
</div>