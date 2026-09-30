@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4">
        <p class="text-xs text-muted">
            Menampilkan
            <span class="font-semibold text-on-surface">{{ $paginator->firstItem() }}</span>
            –
            <span class="font-semibold text-on-surface">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-semibold text-on-surface">{{ $paginator->total() }}</span>
        </p>

        <ul class="inline-flex items-center gap-1">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li><span class="btn btn-xs btn-outline opacity-50 pointer-events-none" aria-disabled="true"><span class="material-symbols-outlined text-[18px]">chevron_left</span></span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-xs btn-outline" aria-label="Sebelumnya"><span class="material-symbols-outlined text-[18px]">chevron_left</span></a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="px-2 text-xs text-muted">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="btn btn-xs btn-primary min-w-8" aria-current="page">{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}" class="btn btn-xs btn-outline min-w-8" aria-label="Halaman {{ $page }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-xs btn-outline" aria-label="Berikutnya"><span class="material-symbols-outlined text-[18px]">chevron_right</span></a></li>
            @else
                <li><span class="btn btn-xs btn-outline opacity-50 pointer-events-none" aria-disabled="true"><span class="material-symbols-outlined text-[18px]">chevron_right</span></span></li>
            @endif
        </ul>
    </nav>
@endif
