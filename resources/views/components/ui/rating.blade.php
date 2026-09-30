@props(['value' => null, 'count' => null, 'size' => 'sm', 'showEmpty' => true])
@php $iconSize = $size === 'lg' ? 'text-[22px]' : ($size === 'md' ? 'text-[18px]' : 'text-[15px]'); @endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 font-semibold text-on-surface '.($size === 'lg' ? 'text-base' : 'text-xs')]) }}>
    @if ($value !== null)
        <span class="material-symbols-outlined filled text-secondary {{ $iconSize }}">star</span>
        <span class="tabular">{{ number_format($value, 1) }}</span>
        @if ($count !== null)<span class="text-muted font-normal">({{ $count }})</span>@endif
    @elseif ($showEmpty)
        <span class="material-symbols-outlined text-outline-variant {{ $iconSize }}">star</span>
        <span class="text-muted font-normal">Belum ada ulasan</span>
    @endif
</span>
