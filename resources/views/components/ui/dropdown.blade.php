@props(['align' => 'right', 'width' => 'w-56'])
<div x-data="{ open: false }" class="relative" x-on:keydown.escape.window="open = false">
    <div x-on:click="open = !open">{{ $trigger }}</div>
    <div x-show="open" x-cloak x-on:click.outside="open = false" x-on:click="open = false"
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         class="absolute z-50 mt-2 {{ $width }} {{ $align === 'right' ? 'right-0' : 'left-0' }} rounded-2xl bg-white border border-border shadow-modal p-1.5">
        {{ $slot }}
    </div>
</div>
