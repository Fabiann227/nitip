@props(['events'])
<ol {{ $attributes->merge(['class' => 'relative space-y-5']) }}>
    @forelse ($events as $event)
        <li class="relative flex gap-3">
            @if (! $loop->last)
                <span class="absolute left-4 top-9 bottom-[-20px] w-px bg-border" aria-hidden="true"></span>
            @endif
            <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 z-10 {{ $event->to_status?->badgeClass() ?? 'badge-neutral' }}">
                <span class="material-symbols-outlined text-[16px]">{{ $event->type->icon() }}</span>
            </span>
            <div class="min-w-0 flex-1 pt-1">
                <p class="text-sm text-on-surface leading-snug">{{ $event->description }}</p>
                <p class="text-xs text-muted mt-0.5">
                    {{ $event->actor?->shortName() ?? 'Sistem' }} &bull;
                    <time datetime="{{ $event->created_at->toIso8601String() }}" title="{{ $event->created_at->translatedFormat('d M Y H:i') }}">{{ $event->created_at->diffForHumans() }}</time>
                </p>
            </div>
        </li>
    @empty
        <li class="text-sm text-muted">Belum ada aktivitas.</li>
    @endforelse
</ol>
