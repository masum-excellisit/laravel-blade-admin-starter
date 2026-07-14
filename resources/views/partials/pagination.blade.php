@if ($paginator->hasPages())
<nav class="flex items-center justify-between flex-wrap gap-3 mt-5">
    <p class="text-sm text-slate-500 dark:text-slate-400">
        Showing <span class="font-medium text-slate-700 dark:text-slate-200">{{ $paginator->firstItem() ?? 0 }}</span>–<span class="font-medium text-slate-700 dark:text-slate-200">{{ $paginator->lastItem() ?? 0 }}</span> of <span class="font-medium text-slate-700 dark:text-slate-200">{{ $paginator->total() }}</span>
    </p>
    <div class="flex items-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center h-9 w-9 rounded-lg text-slate-300 dark:text-slate-600 cursor-not-allowed"><x-icon name="chevron-left" class="w-4 h-4" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center justify-center h-9 w-9 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700"><x-icon name="chevron-left" class="w-4 h-4" /></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="inline-flex items-center justify-center h-9 min-w-9 px-2 text-slate-400">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="inline-flex items-center justify-center h-9 min-w-9 px-2 rounded-lg brand-gradient text-white text-sm font-medium shadow">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="inline-flex items-center justify-center h-9 min-w-9 px-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-sm">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center justify-center h-9 w-9 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700"><x-icon name="chevron-right" class="w-4 h-4" /></a>
        @else
            <span class="inline-flex items-center justify-center h-9 w-9 rounded-lg text-slate-300 dark:text-slate-600 cursor-not-allowed"><x-icon name="chevron-right" class="w-4 h-4" /></span>
        @endif
    </div>
</nav>
@endif
