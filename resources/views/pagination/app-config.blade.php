@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="app-config-pagination-nav">
        <div class="flex gap-2 items-center justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-500 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 cursor-not-allowed rounded-lg">
                    Précédent
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="app-config-page-link inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700">
                    Précédent
                </a>
            @endif
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="app-config-page-link inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700">
                    Suivant
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-500 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 cursor-not-allowed rounded-lg">
                    Suivant
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:gap-2 sm:items-center sm:justify-end">
            <span class="inline-flex rtl:flex-row-reverse shadow-sm rounded-lg">
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" class="inline-flex items-center px-2 py-2 text-sm font-medium text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 cursor-not-allowed rounded-l-lg">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="app-config-page-link inline-flex items-center px-2 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-l-lg hover:bg-slate-50 dark:hover:bg-slate-700" aria-label="Page précédente">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600">{{ $element }}</span>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-semibold text-white bg-emerald-600 border border-emerald-600">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="app-config-page-link inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="app-config-page-link inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-r-lg hover:bg-slate-50 dark:hover:bg-slate-700" aria-label="Page suivante">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                    </a>
                @else
                    <span class="inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 cursor-not-allowed rounded-r-lg">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                    </span>
                @endif
            </span>
        </div>
    </nav>
@endif
