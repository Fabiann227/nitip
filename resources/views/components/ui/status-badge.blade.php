@props(['status', 'icon' => true])
@php $iconName = $icon && method_exists($status, 'icon') ? $status->icon() : null; @endphp
<span {{ $attributes->merge(['class' => 'badge '.$status->badgeClass()]) }}>
    @if ($iconName)<span class="material-symbols-outlined text-[14px]">{{ $iconName }}</span>@endif
    {{ $status->label() }}
</span>
