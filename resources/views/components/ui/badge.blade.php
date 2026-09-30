@props(['variant' => 'neutral', 'dot' => false, 'icon' => null])
<span {{ $attributes->merge(['class' => 'badge badge-'.$variant.($dot ? ' badge-dot' : '')]) }}>
    @if ($icon)<span class="material-symbols-outlined text-[14px]">{{ $icon }}</span>@endif
    {{ $slot }}
</span>
