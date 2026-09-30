@props(['name', 'title' => null, 'maxWidth' => 'md', 'openOnError' => false])
@php $width = match ($maxWidth) { 'sm' => 'sm:max-w-sm', 'lg' => 'sm:max-w-2xl', 'xl' => 'sm:max-w-4xl', default => 'sm:max-w-lg' }; @endphp
<template x-teleport="body">
    <div x-data="{ open: {{ $openOnError ? 'true' : 'false' }} }"
         x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
         x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
         x-on:keydown.escape.window="open = false"
         x-show="open" x-cloak
         class="fixed inset-0 z-[90] flex items-end sm:items-center justify-center sm:p-4"
         role="dialog" aria-modal="true" @if($title) aria-label="{{ $title }}" @endif>
        <div x-show="open" x-transition.opacity.duration.200ms class="absolute inset-0 bg-on-surface/40 backdrop-blur-[2px]" x-on:click="open = false"></div>
        <div x-show="open" x-trap.noscroll.inert="open"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-2 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-4"
             class="relative w-full {{ $width }} bg-white rounded-t-3xl sm:rounded-3xl shadow-modal max-h-[92vh] flex flex-col">
            <div class="flex items-center justify-between gap-3 px-5 pt-5 pb-3">
                <div class="sm:hidden absolute top-2 left-1/2 -translate-x-1/2 w-10 h-1 rounded-full bg-slate-300"></div>
                @if ($title)<h2 class="font-sans font-bold text-lg text-on-surface">{{ $title }}</h2>@endif
                <button type="button" class="btn-icon -mr-2" x-on:click="open = false" aria-label="Tutup"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="px-5 pb-5 overflow-y-auto">
                {{ $slot }}
            </div>
        </div>
    </div>
</template>
