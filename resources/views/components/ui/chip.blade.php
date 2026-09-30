@props(['href' => null, 'active' => false, 'icon' => null])
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'chip'.($active ? ' chip-active' : '')]) }} @if($active) aria-current="true" @endif>
        @if ($icon)<span class="material-symbols-outlined text-[16px]">{{ $icon }}</span>@endif{{ $slot }}
    </a>
@else
    <span {{ $attributes->merge(['class' => 'chip'.($active ? ' chip-active' : '')]) }}>
        @if ($icon)<span class="material-symbols-outlined text-[16px]">{{ $icon }}</span>@endif{{ $slot }}
    </span>
@endif
