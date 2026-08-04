@php($label = $label ?? 'élément(s)')
<style>
.pass-pagination { width: 100%; }
.pass-pagination-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    font-size: 0.78rem;
    color: #8b8b98;
    margin-bottom: 0.65rem;
}
.pass-pagination-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.pass-pagination-pages {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    flex-wrap: wrap;
    list-style: none;
    margin: 0;
    padding: 0;
}
.pass-page-item a,
.pass-page-item > span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.9rem;
    height: 1.9rem;
    padding: 0 0.45rem;
    border: 1px solid #e0dde3;
    border-radius: 7px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #3b3b45;
    background: #fff;
    text-decoration: none;
    transition: all 0.15s ease;
}
.pass-page-item a:hover {
    border-color: var(--violet, #542680);
    color: var(--violet, #542680);
    background: #f8f6f9;
}
.pass-page-active > span {
    background: var(--violet, #542680);
    border-color: var(--violet, #542680);
    color: #fff;
}
.pass-page-ellipsis > span {
    border: none;
    background: transparent;
    color: #a0a0ac;
}
.pass-pagination-btns {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.pass-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.42rem 0.95rem;
    border: 1px solid #e0dde3;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #3b3b45;
    background: #fff;
    text-decoration: none;
    line-height: 1;
    transition: all 0.15s ease;
}
.pass-btn:hover {
    border-color: var(--violet, #542680);
    color: var(--violet, #542680);
    background: #f8f6f9;
}
.pass-btn-disabled {
    opacity: 0.45;
    pointer-events: none;
}
</style>

<div class="pass-pagination">
    <div class="pass-pagination-meta">
        <span>{{ $paginator->perPage() }} par page</span>
        <span>·</span>
        <span>Page {{ $paginator->currentPage() }} sur {{ $paginator->lastPage() }} — {{ $paginator->total() }} {{ $label }}</span>
    </div>

    @if ($paginator->hasPages())
        <div class="pass-pagination-row">
            {{-- Pages (à gauche) --}}
            <ul class="pass-pagination-pages">
                @if (isset($elements) && is_array($elements))
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <li class="pass-page-item pass-page-ellipsis" aria-disabled="true"><span>…</span></li>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="pass-page-item pass-page-active" aria-current="page"><span>{{ $page }}</span></li>
                                @else
                                    <li class="pass-page-item"><a href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                @endif
            </ul>

            {{-- Précédent / Suivant (à droite) --}}
            <div class="pass-pagination-btns">
                @if ($paginator->onFirstPage())
                    <span class="pass-btn pass-btn-disabled">Précédent</span>
                @else
                    <a class="pass-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">Précédent</a>
                @endif

                @if ($paginator->hasMorePages())
                    <a class="pass-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Suivant</a>
                @else
                    <span class="pass-btn pass-btn-disabled">Suivant</span>
                @endif
            </div>
        </div>
    @endif
</div>
