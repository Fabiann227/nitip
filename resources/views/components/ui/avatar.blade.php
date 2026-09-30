@props(['user', 'size' => 'md', 'tone' => null])
@php
    $sizes = ['xs' => 'w-7 h-7 text-[11px]', 'sm' => 'w-8 h-8 text-xs', 'md' => 'w-10 h-10 text-sm', 'lg' => 'w-14 h-14 text-lg', 'xl' => 'w-20 h-20 text-2xl'];
    $tones = ['bg-secondary-fixed text-on-secondary-container', 'bg-primary-fixed text-on-primary-fixed', 'bg-surface-high text-primary', 'bg-primary text-white'];
    $toneClass = $tone ?? $tones[$user->id % count($tones)];
    $url = $user->avatarUrl();
@endphp
<span {{ $attributes->merge(['class' => 'avatar '.$sizes[$size].' '.($url ? 'bg-surface-high' : $toneClass)]) }} title="{{ $user->name }}">
    @if ($url)
        <img src="{{ $url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
    @else
        {{ $user->initials() }}
    @endif
</span>
