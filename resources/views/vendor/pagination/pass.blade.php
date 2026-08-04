@php($label = $label ?? 'élément(s)')
<style>
.pass-pagination { width: 100%; }
.pass-pagination-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.pass-pagination-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    font-size: 0.78rem;
    color: #8b8b98;
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
.pass-pagination-foot {
    display: flex;
    justify-content: flex-end;
    margin-top: 0.65rem;
}
.pass-per-page {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: #8b8b98;
    margin: 0;
}
.pass-per-page select {
    padding: 0.3rem 0.55rem;
    border: 1px solid #e0dde3;
    border-radius: 7px;
    font-size: 0.78rem;
    font-weight: 600;
    color: #3b3b45;
    background: #fff;
    cursor: pointer;
}
.pass-per-page select:focus {
    outline: none;
    border-color: var(--violet, #542680);
    box-shadow: 0 0 0 0.15rem rgba(84, 38, 128, 0.12);
}
</style>

<div class="pass-pagination">
    <div class="pass-pagination-row">
        <span class="pass-pagination-meta">
            Page {{ $paginator->currentPage() }} sur {{ $paginator->lastPage() }} — {{ $paginator->total() }} {{ $label }}
        </span>

        @if ($paginator->hasPages())
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
        @endif
    </div>

    <div class="pass-pagination-foot">
        <form method="GET" action="{{ url()->current() }}" class="pass-per-page">
            @foreach (request()->except(['page', 'per_page']) as $key => $value)
                @if (is_array($value))
                    @foreach ($value as $v)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach

            <label for="per_page">Afficher</label>
            <select id="per_page" name="per_page" onchange="this.form.submit()">
                @foreach (\App\Support\PerPage::ALLOWED as $n)
                    <option value="{{ $n }}" @selected($n === $paginator->perPage())>{{ $n }}</option>
                @endforeach
            </select>
            <span>par page</span>
        </form>
    </div>
</div>
