@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex gap-2 items-center justify-between">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-4 py-2 text-xs font-bold text-slate-400 bg-slate-100 border border-slate-200 cursor-not-allowed leading-5 rounded-lg">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 leading-5 rounded-lg hover:text-blue-700 hover:bg-slate-50 transition-colors">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 leading-5 rounded-lg hover:text-blue-700 hover:bg-slate-50 transition-colors">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="inline-flex items-center px-4 py-2 text-xs font-bold text-slate-400 bg-slate-100 border border-slate-200 cursor-not-allowed leading-5 rounded-lg">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif
