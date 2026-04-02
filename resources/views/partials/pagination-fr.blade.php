@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator */
    /** @var string $itemLabel */
@endphp
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4 pt-4 border-t border-slate-200/80 dark:border-slate-700/80">
    <p class="text-sm text-slate-600 dark:text-slate-400">
        @if ($paginator->total() === 0)
            Aucun résultat.
        @else
            Affichage de <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $paginator->firstItem() }}</span>
            à <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $paginator->lastItem() }}</span>
            sur <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $paginator->total() }}</span>
            {{ $itemLabel }}
        @endif
    </p>
    @if ($paginator->total() > 0)
        <form method="get" class="flex flex-wrap items-center gap-2">
            @foreach (request()->except(['per_page', 'page', '_token', '_method']) as $name => $value)
                @if (is_array($value))
                    @foreach ($value as $k => $v)
                        <input type="hidden" name="{{ $name }}[{{ $k }}]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endif
            @endforeach
            <label class="text-sm font-medium text-slate-600 dark:text-slate-300 whitespace-nowrap" for="pagination-per-page-{{ $paginator->getPageName() }}">Lignes par page</label>
            <select id="pagination-per-page-{{ $paginator->getPageName() }}" name="per_page" onchange="this.form.submit()" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 min-w-[4.5rem]" aria-label="Lignes par page">
                @foreach (\App\Support\PaginationPerPage::OPTIONS as $opt)
                    <option value="{{ $opt }}" @selected((int) $paginator->perPage() === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
            <input type="hidden" name="page" value="1">
        </form>
    @endif
</div>
@if ($paginator->hasPages())
    <div class="mt-3">{{ $paginator->links() }}</div>
@endif
