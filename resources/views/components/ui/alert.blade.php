@props(['type' => 'info', 'title' => null, 'icon' => null])
@php $icon ??= match ($type) { 'success' => 'check_circle', 'error' => 'error', 'warning' => 'warning', default => 'info' }; @endphp
<div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }} role="{{ $type === 'error' ? 'alert' : 'status' }}">
    <span class="material-symbols-outlined text-[20px] shrink-0 mt-0.5">{{ $icon }}</span>
    <div class="min-w-0 flex-1">
        @if ($title)<p class="font-semibold mb-0.5">{{ $title }}</p>@endif
        <div class="leading-relaxed">{{ $slot }}</div>
    </div>
</div>
