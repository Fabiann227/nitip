@props([
    'action', 'title', 'method' => 'POST', 'message' => null, 'confirm' => 'Ya, lanjutkan', 'variant' => 'primary',
    'reason' => false, 'reasonLabel' => 'Alasan', 'reasonPlaceholder' => 'Jelaskan secara singkat...',
    'buttonClass' => 'btn btn-outline', 'icon' => null, 'name' => null,
])
@php
    $name ??= 'confirm-'.substr(md5($action.$title), 0, 10);
    $openOnError = $errors->any() && old('_modal') === $name;
@endphp
<button type="button" x-data x-on:click="$dispatch('open-modal', '{{ $name }}')" {{ $attributes->merge(['class' => $buttonClass]) }}>
    @if ($icon)<span class="material-symbols-outlined text-[18px]">{{ $icon }}</span>@endif
    {{ $slot }}
</button>
<x-ui.modal :name="$name" :title="$title" :open-on-error="$openOnError">
    <form method="POST" action="{{ $action }}" class="space-y-4">
        @csrf
        @if ($method !== 'POST')@method($method)@endif
        <input type="hidden" name="_modal" value="{{ $name }}">
        @if ($message)<p class="text-sm text-on-surface-variant leading-relaxed">{{ $message }}</p>@endif
        @if ($reason)
            <x-ui.textarea name="reason" :label="$reasonLabel" :placeholder="$reasonPlaceholder" rows="3" required :value="$openOnError ? old('reason') : ''" />
        @endif
        {{ $fields ?? '' }}
        <div class="flex flex-col-reverse sm:flex-row gap-2 sm:justify-end pt-1">
            <button type="button" class="btn btn-outline" x-on:click="$dispatch('close-modal', '{{ $name }}')">Batal</button>
            <button type="submit" class="btn btn-{{ $variant }}">{{ $confirm }}</button>
        </div>
    </form>
</x-ui.modal>
