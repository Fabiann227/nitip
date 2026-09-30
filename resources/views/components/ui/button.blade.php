@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'type' => 'button', 'icon' => null, 'iconRight' => null, 'full' => false])
@php
    $classes = 'btn btn-'.$variant.($size !== 'md' ? ' btn-'.$size : '').($full ? ' w-full' : '');
    $iconSize = match ($size) { 'xs' => 'text-[16px]', 'sm' => 'text-[18px]', default => 'text-[20px]' };
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<span class="material-symbols-outlined {{ $iconSize }}">{{ $icon }}</span>@endif
        {{ $slot }}
        @if ($iconRight)<span class="material-symbols-outlined {{ $iconSize }}">{{ $iconRight }}</span>@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<span class="material-symbols-outlined {{ $iconSize }}">{{ $icon }}</span>@endif
        {{ $slot }}
        @if ($iconRight)<span class="material-symbols-outlined {{ $iconSize }}">{{ $iconRight }}</span>@endif
    </button>
@endif
