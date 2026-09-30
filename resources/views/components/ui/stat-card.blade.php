@props(['label', 'value', 'icon' => null, 'hint' => null, 'tone' => 'primary', 'href' => null])
@php
    $tones = [
        'primary' => 'bg-primary-fixed/40 text-primary',
        'secondary' => 'bg-secondary-fixed/50 text-on-secondary-container',
        'warning' => 'bg-warning-container text-warning',
        'danger' => 'bg-error-container text-on-error-container',
        'info' => 'bg-info-container text-info',
        'neutral' => 'bg-surface-high text-on-surface-variant',
    ];
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'card p-4 flex items-start gap-3 '.($href ? 'card-hover' : '')]) }}>
    @if ($icon)
        <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 {{ $tones[$tone] ?? $tones['primary'] }}"><span class="material-symbols-outlined text-[22px]">{{ $icon }}</span></span>
    @endif
    <div class="min-w-0 flex-1">
        <p class="text-xs font-medium text-muted truncate">{{ $label }}</p>
        <p class="font-sans font-extrabold text-xl text-on-surface tabular leading-tight mt-0.5 truncate">{{ $value }}</p>
        @if ($hint)<p class="text-[11px] text-on-surface-variant mt-0.5 truncate">{{ $hint }}</p>@endif
    </div>
</{{ $tag }}>
