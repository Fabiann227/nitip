@props(['padding' => true, 'hover' => false, 'title' => null, 'subtitle' => null, 'icon' => null])
<div {{ $attributes->merge(['class' => 'card'.($hover ? ' card-hover' : '')]) }}>
    @if ($title)
        <div class="card-header">
            <div class="flex items-center gap-2.5 min-w-0">
                @if ($icon)<span class="material-symbols-outlined text-primary text-[22px]">{{ $icon }}</span>@endif
                <div class="min-w-0">
                    <h2 class="font-sans font-bold text-base text-on-surface truncate">{{ $title }}</h2>
                    @if ($subtitle)<p class="text-xs text-muted">{{ $subtitle }}</p>@endif
                </div>
            </div>
            @if (isset($actions))<div class="shrink-0 flex items-center gap-2">{{ $actions }}</div>@endif
        </div>
    @endif
    @if ($padding)
        <div class="card-body">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif
</div>
