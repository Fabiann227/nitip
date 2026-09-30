@props(['icon' => 'inbox', 'title', 'description' => null, 'compact' => false])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center text-center '.($compact ? 'py-8 px-4' : 'py-14 px-6')]) }}>
    <div class="w-16 h-16 rounded-2xl bg-surface-low text-primary flex items-center justify-center mb-4 relative">
        <div class="absolute inset-0 rounded-2xl bg-primary-fixed/30 blur-md"></div>
        <span class="material-symbols-outlined text-[32px] relative">{{ $icon }}</span>
    </div>
    <h3 class="font-sans font-bold text-base text-on-surface">{{ $title }}</h3>
    @if ($description)<p class="text-sm text-on-surface-variant mt-1 max-w-sm">{{ $description }}</p>@endif
    @if (trim($slot))<div class="mt-5 flex flex-wrap items-center justify-center gap-2">{{ $slot }}</div>@endif
</div>
