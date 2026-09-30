@props(['href', 'active' => false, 'count' => null, 'icon' => null])
<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-4 h-10 rounded-xl text-sm font-semibold whitespace-nowrap transition-colors '.($active ? 'bg-primary text-white shadow-sm' : 'bg-white border border-border text-on-surface-variant hover:bg-surface-low hover:text-on-surface')]) }} @if($active) aria-current="page" @endif>
    @if ($icon)<span class="material-symbols-outlined text-[18px]">{{ $icon }}</span>@endif
    {{ $slot }}
    @if ($count !== null)<span class="text-[11px] px-1.5 py-0.5 rounded-full {{ $active ? 'bg-white/20 text-white' : 'bg-surface-high text-secondary' }} font-bold">{{ $count }}</span>@endif
</a>
