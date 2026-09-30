@props(['name', 'label' => null, 'accept' => 'image/*', 'maxMb' => 4, 'multiple' => false, 'hint' => null, 'required' => false, 'optional' => false, 'id' => null, 'icon' => 'upload_file', 'labelIcon' => null])
@php
    $id ??= str_replace(['[', ']'], ['-', ''], $name);
    $dotName = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $hasError = $errors->has($dotName) || $errors->has($dotName.'.*');
@endphp
<div x-data="filePicker({ accept: @js($accept), maxMb: {{ (int) $maxMb }} })">
    @if ($label)
        <x-ui.label :for="$id" :required="$required" :optional="$optional" :icon="$labelIcon">{{ $label }}</x-ui.label>
    @endif
    <label for="{{ $id }}" class="flex items-center gap-3 p-3 rounded-xl border border-dashed cursor-pointer transition-colors hover:bg-surface-low {{ $hasError ? 'border-error' : 'border-slate-300' }}">
        <template x-if="preview">
            <img :src="preview" alt="Pratinjau" class="w-14 h-14 rounded-lg object-cover border border-border">
        </template>
        <template x-if="!preview">
            <span class="w-12 h-12 rounded-xl bg-surface-low text-primary flex items-center justify-center shrink-0"><span class="material-symbols-outlined">{{ $icon }}</span></span>
        </template>
        <span class="min-w-0 flex-1">
            <span class="block text-sm font-semibold text-on-surface truncate" x-text="name || 'Pilih {{ $multiple ? 'berkas' : 'file' }}...'"></span>
            <span class="block text-xs text-muted">{{ $hint ?? "Maksimal {$maxMb} MB" }}</span>
        </span>
        <span class="btn btn-xs btn-outline shrink-0">Pilih</span>
    </label>
    <input type="file" name="{{ $name }}" id="{{ $id }}" accept="{{ $accept }}" @if($multiple) multiple @endif @if($required) required @endif class="sr-only" x-on:change="onChange($event)" {{ $attributes }}>
    <p class="error-text" x-show="error" x-cloak><span class="material-symbols-outlined text-[14px]">error</span><span x-text="error"></span></p>
    <x-ui.field-error :name="$name" />
    @if ($multiple)
        @error($dotName.'.*')<p class="error-text"><span class="material-symbols-outlined text-[14px]">error</span><span>{{ $message }}</span></p>@enderror
    @endif
</div>
