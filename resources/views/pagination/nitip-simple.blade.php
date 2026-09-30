@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-between gap-3 pt-4">
        @if ($paginator->onFirstPage())
            <span class="btn btn-sm btn-outline opacity-50 pointer-events-none">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-sm btn-outline">Sebelumnya</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-sm btn-outline">Berikutnya</a>
        @else
            <span class="btn btn-sm btn-outline opacity-50 pointer-events-none">Berikutnya</span>
        @endif
    </nav>
@endif
