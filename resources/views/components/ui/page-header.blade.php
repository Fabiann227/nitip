@props(['title', 'subtitle' => null, 'eyebrow' => null, 'back' => null, 'backLabel' => 'Kembali'])
<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6']) }}>
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="inline-flex items-center gap-1 text-xs font-semibold text-secondary hover:text-primary mb-2">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>{{ $backLabel }}
            </a>
        @endif
        @if ($eyebrow)<span class="eyebrow block mb-1">{{ $eyebrow }}</span>@endif
        <h1 class="font-sans font-bold text-2xl sm:text-3xl tracking-tight text-on-surface">{{ $title }}</h1>
        @if ($subtitle)<p class="text-sm text-on-surface-variant mt-1 max-w-2xl">{{ $subtitle }}</p>@endif
    </div>
    @if (trim($slot))
        <div class="flex flex-wrap items-center gap-2 shrink-0">{{ $slot }}</div>
    @endif
</div>
